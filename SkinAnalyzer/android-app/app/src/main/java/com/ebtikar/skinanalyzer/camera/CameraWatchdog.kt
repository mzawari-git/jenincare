package com.ebtikar.skinanalyzer.camera

import android.content.Context
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import android.os.Handler
import android.os.Looper
import timber.log.Timber
import java.util.concurrent.CopyOnWriteArrayList
import java.util.concurrent.atomic.AtomicLong
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Camera Watchdog — monitors frame timestamps during preview and capture.
 *
 * If no new frame arrives within [FRAME_TIMEOUT_MS], the watchdog:
 *   1. Logs the event for diagnostics
 *   2. Attempts a USB device reset via [UsbManager]
 *   3. Notifies listeners so the caller can reopen the camera session
 *
 * Usage:
 *   cameraWatchdog.startMonitoring()
 *   // ... in ImageReader.OnImageAvailableListener or CaptureCallback:
 *   cameraWatchdog.onFrameArrived()
 *   // ... on cleanup:
 *   cameraWatchdog.stopMonitoring()
 */
@Singleton
class CameraWatchdog @Inject constructor(
    private val context: Context
) {

    companion object {
        /** Max time (ms) without a frame before triggering reset */
        const val FRAME_TIMEOUT_MS = 2000L

        /** How often (ms) the watchdog checks the timestamp */
        const val CHECK_INTERVAL_MS = 500L

        /** Max consecutive timeouts before declaring camera dead */
        const val MAX_CONSECUTIVE_TIMEOUTS = 3
    }

    interface WatchdogListener {
        fun onCameraTimeout(consecutiveTimeouts: Int)
        fun onCameraResetAttempt(success: Boolean)
        fun onCameraRecovered()
    }

    private val handler = Handler(Looper.getMainLooper())
    private val lastFrameTimestamp = AtomicLong(0L)
    private val consecutiveTimeouts = AtomicLong(0)
    private val listeners = CopyOnWriteArrayList<WatchdogListener>()

    @Volatile private var isMonitoring = false
    @Volatile private var isPaused = false

    /** Total timeout events since last startMonitoring() */
    var totalTimeoutEvents: Long = 0L
        private set

    /** Total USB reset attempts since last startMonitoring() */
    var totalResetAttempts: Long = 0L
        private set

    /** Total successful resets since last startMonitoring() */
    var totalSuccessfulResets: Long = 0L
        private set

    /** Timestamp of last timeout event */
    var lastTimeoutTimestamp: Long = 0L
        private set

    /** Whether camera is currently considered healthy */
    val isHealthy: Boolean get() = consecutiveTimeouts.get() < MAX_CONSECUTIVE_TIMEOUTS

    /** Current consecutive timeout count */
    val currentConsecutiveTimeouts: Long get() = consecutiveTimeouts.get()

    private val checkRunnable = object : Runnable {
        override fun run() {
            if (!isMonitoring || isPaused) return

            val lastFrame = lastFrameTimestamp.get()
            if (lastFrame == 0L) {
                // No frame received yet, keep waiting
                handler.postDelayed(this, CHECK_INTERVAL_MS)
                return
            }

            val elapsed = System.currentTimeMillis() - lastFrame
            if (elapsed > FRAME_TIMEOUT_MS) {
                val timeouts = consecutiveTimeouts.incrementAndGet()
                totalTimeoutEvents++
                lastTimeoutTimestamp = System.currentTimeMillis()

                Timber.w("Camera Watchdog: NO FRAME for ${elapsed}ms (timeout #$timeouts)")

                if (timeouts >= MAX_CONSECUTIVE_TIMEOUTS) {
                    Timber.e("Camera Watchdog: $timeouts consecutive timeouts — camera likely dead, attempting USB reset")
                    listeners.forEach { it.onCameraTimeout(timeouts.toInt()) }
                    attemptUsbReset()
                } else {
                    listeners.forEach { it.onCameraTimeout(timeouts.toInt()) }
                }
            } else {
                // Frame arrived recently, camera is healthy
                if (consecutiveTimeouts.get() > 0) {
                    Timber.i("Camera Watchdog: camera recovered after ${consecutiveTimeouts.get()} timeouts")
                    consecutiveTimeouts.set(0)
                    listeners.forEach { it.onCameraRecovered() }
                }
            }

            handler.postDelayed(this, CHECK_INTERVAL_MS)
        }
    }

    fun addListener(listener: WatchdogListener) {
        listeners.add(listener)
    }

    fun removeListener(listener: WatchdogListener) {
        listeners.remove(listener)
    }

    /**
     * Call this from ImageReader.OnImageAvailableListener or CaptureCallback
     * to signal that a new frame has arrived.
     */
    fun onFrameArrived() {
        lastFrameTimestamp.set(System.currentTimeMillis())
    }

    /**
     * Call this when capture starts to reset the "no frame" timer,
     * since capture mode doesn't produce continuous preview frames.
     */
    fun onCaptureStarted() {
        isPaused = true
        lastFrameTimestamp.set(System.currentTimeMillis())
    }

    /**
     * Call this when capture ends to resume monitoring.
     */
    fun onCaptureEnded() {
        lastFrameTimestamp.set(System.currentTimeMillis())
        isPaused = false
    }

    fun startMonitoring() {
        if (isMonitoring) return
        isMonitoring = true
        isPaused = false
        lastFrameTimestamp.set(System.currentTimeMillis())
        consecutiveTimeouts.set(0)
        totalTimeoutEvents = 0
        totalResetAttempts = 0
        totalSuccessfulResets = 0
        handler.postDelayed(checkRunnable, CHECK_INTERVAL_MS)
        Timber.i("Camera Watchdog started (timeout=${FRAME_TIMEOUT_MS}ms, check=${CHECK_INTERVAL_MS}ms)")
    }

    fun stopMonitoring() {
        isMonitoring = false
        handler.removeCallbacks(checkRunnable)
        Timber.i("Camera Watchdog stopped. Events: $totalTimeoutEvents timeouts, $totalResetAttempts resets, $totalSuccessfulResets successful")
    }

    fun reset() {
        consecutiveTimeouts.set(0)
        lastFrameTimestamp.set(System.currentTimeMillis())
    }

    private fun attemptUsbReset() {
        totalResetAttempts++
        try {
            val usbManager = context.getSystemService(Context.USB_SERVICE) as? UsbManager
            if (usbManager == null) {
                Timber.e("Camera Watchdog: UsbManager unavailable")
                listeners.forEach { it.onCameraResetAttempt(false) }
                return
            }

            // Find the camera USB device (OV13850)
            val cameraDevice = findCameraUsbDevice(usbManager)
            if (cameraDevice == null) {
                Timber.w("Camera Watchdog: camera USB device not found for reset")
                listeners.forEach { it.onCameraResetAttempt(false) }
                return
            }

            // Reset the USB connection
            val success = resetUsbDevice(usbManager, cameraDevice)
            if (success) {
                totalSuccessfulResets++
                Timber.i("Camera Watchdog: USB reset successful for ${cameraDevice.deviceName}")
            } else {
                Timber.w("Camera Watchdog: USB reset failed for ${cameraDevice.deviceName}")
            }
            listeners.forEach { it.onCameraResetAttempt(success) }
        } catch (e: Exception) {
            Timber.e(e, "Camera Watchdog: USB reset exception")
            listeners.forEach { it.onCameraResetAttempt(false) }
        }
    }

    private fun findCameraUsbDevice(usbManager: UsbManager): UsbDevice? {
        return usbManager.deviceList.values.firstOrNull { device ->
            // OV13850 typically has these USB attributes
            val name = device.deviceName.lowercase()
            name.contains("camera") || name.contains("video") ||
                name.contains("ov13850") || name.contains("uvc") ||
                device.deviceClass == 0x0E ||  // Video class
                device.deviceSubclass == 0x01   // Video Control
        }
    }

    private fun resetUsbDevice(usbManager: UsbManager, device: UsbDevice): Boolean {
        return try {
            // Reset via sysfs unbind/bind (requires shell permissions or root)
            val devicePath = device.deviceName
            val unbind = Runtime.getRuntime().exec(arrayOf("sh", "-c",
                "echo '$devicePath' > /sys/bus/usb/drivers/usb/unbind 2>/dev/null"
            ))
            val unbindExit = unbind.waitFor()
            if (unbindExit != 0) {
                val stderr = unbind.errorStream.bufferedReader().readText()
                Timber.w("Camera Watchdog: USB unbind failed (exit=$unbindExit): $stderr")
                return false
            }

            Thread.sleep(1000)

            val bind = Runtime.getRuntime().exec(arrayOf("sh", "-c",
                "echo '$devicePath' > /sys/bus/usb/drivers/usb/bind 2>/dev/null"
            ))
            val bindExit = bind.waitFor()
            if (bindExit == 0) {
                Timber.i("Camera Watchdog: USB reset via sysfs unbind/bind succeeded")
                true
            } else {
                val stderr = bind.errorStream.bufferedReader().readText()
                Timber.w("Camera Watchdog: USB bind failed (exit=$bindExit): $stderr")
                false
            }
        } catch (e: Exception) {
            Timber.e(e, "Camera Watchdog: USB reset method failed")
            false
        }
    }

    fun getStatusSummary(): String = buildString {
        appendLine("=== Camera Watchdog Status ===")
        appendLine("Monitoring: $isMonitoring")
        appendLine("Paused: $isPaused")
        appendLine("Healthy: $isHealthy")
        appendLine("Consecutive Timeouts: $currentConsecutiveTimeouts / $MAX_CONSECUTIVE_TIMEOUTS")
        appendLine("Total Timeout Events: $totalTimeoutEvents")
        appendLine("Total Reset Attempts: $totalResetAttempts")
        appendLine("Successful Resets: $totalSuccessfulResets")
        appendLine("Last Timeout: ${if (lastTimeoutTimestamp > 0) java.text.SimpleDateFormat("HH:mm:ss.SSS", java.util.Locale.US).format(java.util.Date(lastTimeoutTimestamp)) else "none"}")
        val lastFrame = lastFrameTimestamp.get()
        appendLine("Last Frame: ${if (lastFrame > 0) "${System.currentTimeMillis() - lastFrame}ms ago" else "none yet"}")
        appendLine("Timeout Threshold: ${FRAME_TIMEOUT_MS}ms")
    }
}
