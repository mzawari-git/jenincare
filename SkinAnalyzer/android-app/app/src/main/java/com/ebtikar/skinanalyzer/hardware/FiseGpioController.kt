package com.ebtikar.skinanalyzer.hardware

import android.content.Context
import dagger.hilt.android.qualifiers.ApplicationContext
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FiseGpioController @Inject constructor(
    @ApplicationContext private val context: Context
) {

    private val gpioFiles = (0..4).map { File("/sys/class/fise_gpio$it/level") }
    private val ledFile = File("/sys/class/fise_led/level")
    private var _available = false
    var selinuxEnforcing: Boolean? = null
        private set

    val isAvailable: Boolean get() = _available

    private var _statusMessage = ""
    val statusMessage: String get() = _statusMessage

    val hasRoot: Boolean = false
    val rootManagerDetected: Boolean = false
    val detectedRootManagerPackage: String? = null

    init {
        checkSelinux()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        _available = gpioOk || ledOk
        if (_available) {
            _statusMessage = "أضواء التشخيص جاهزة ✓"
            Timber.i("FISE GPIO controller available: ${gpioFiles.size} channels, fise_gpio_exists=${gpioFiles.map { it.exists() }}, fise_led=${ledOk}, SELinux=$selinuxEnforcing")
            turnAllOff()
        } else {
            _statusMessage = "⚠️ أضواء التشخيص غير متصلة — FISE driver لا يوجد"
            Timber.w("FISE GPIO controller not available (SELinux=$selinuxEnforcing)")
            gpioFiles.forEach {
                Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${try { it.canWrite() } catch (_: Exception) { false }}")
            }
            Timber.w("  ${ledFile.absolutePath}: exists=${ledFile.exists()}")
        }
    }

    private fun checkSelinux() {
        selinuxEnforcing = try {
            val enforceFile = File("/sys/fs/selinux/enforce")
            if (enforceFile.exists()) enforceFile.readText().trim() == "1"
            else null
        } catch (e: Exception) {
            null
        }
    }

    private fun verifyWriteAccess(): Boolean {
        for (file in gpioFiles) {
            try {
                file.writeText("1")  // Write OFF (active LOW: 1=OFF) to avoid turning LEDs on during init
                val rb = file.readText().trim()
                Timber.d("GPIO write test ${file.absolutePath}: wrote=1(OFF), readback=$rb")
            } catch (e: Exception) {
                Timber.e(e, "GPIO write test FAILED for ${file.absolutePath} (SELinux=$selinuxEnforcing)")
                return false
            }
        }
        Timber.i("All FISE GPIO write tests passed")
        return true
    }

    suspend fun recheckAvailability(): Boolean {
        if (_available) return true
        Timber.i("Rechecking FISE GPIO availability at runtime...")
        checkSelinux()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        _available = gpioOk || ledOk
        if (_available) {
            _statusMessage = "أضواء التشخيص جاهزة ✓"
            Timber.i("FISE GPIO re-check: NOW available! gpio_ok=$gpioOk, led_ok=$ledOk")
            turnAllOff()
        } else {
            _statusMessage = "⚠️ أضواء التشخيص غير متصلة — FISE driver لا يوجد"
            Timber.w("FISE GPIO re-check: still unavailable. SELinux=$selinuxEnforcing")
            gpioFiles.forEach {
                Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${try { it.canWrite() } catch (_: Exception) { false }}")
            }
        }
        return _available
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val file = gpioFiles[index]
        if (!file.exists()) {
            Timber.w("FISE GPIO $index file does not exist: ${file.absolutePath}")
            return false
        }
        val value = if (on) "0" else "1"  // Active LOW: 0=ON, 1=OFF
        return try {
            file.writeText(value)
            val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
            Timber.i("FISE GPIO $index -> ${if (on) "ON" else "OFF"} (wrote=$value, readback=$readback)")
            true
        } catch (e: Exception) {
            Timber.e(e, "Failed to write FISE GPIO $index (SELinux=$selinuxEnforcing)")
            false
        }
    }

    fun setMasterLed(on: Boolean): Boolean {
        if (!ledFile.exists()) {
            Timber.w("FISE LED master file does not exist: ${ledFile.absolutePath}")
            return false
        }
        return try {
            val value = if (on) "0" else "1"  // Active LOW: 0=ON, 1=OFF
            ledFile.writeText(value)
            Timber.i("FISE LED master -> ${if (on) "ON" else "OFF"} (wrote=$value)")
            true
        } catch (e: Exception) {
            Timber.e(e, "Failed to write FISE LED master")
            false
        }
    }

    fun turnAllOff() {
        setMasterLed(false)
        for (i in gpioFiles.indices) {
            setGpio(i, false)
        }
    }

    fun supportsSpectrum(spectrum: LightSpectrum): Boolean =
        spectrumToGpioIndex(spectrum) >= 0

    fun activateSpectrum(spectrum: LightSpectrum): Boolean {
        val gpioIndex = spectrumToGpioIndex(spectrum)
        if (gpioIndex < 0) {
            Timber.w("No FISE GPIO channel for ${spectrum.name}")
            return false
        }
        turnAllOff()
        val gpioOk = setGpio(gpioIndex, true)
        val ledOk = setMasterLed(true)
        return gpioOk && ledOk
    }

    fun activateAll(): Boolean {
        var allOk = true
        for (i in gpioFiles.indices) {
            if (!setGpio(i, true)) allOk = false
        }
        val ledOk = setMasterLed(true)
        return allOk && ledOk
    }

    private fun spectrumToGpioIndex(spectrum: LightSpectrum): Int {
        return when (spectrum) {
            LightSpectrum.WHITE -> 0
            LightSpectrum.UV365 -> 1
            LightSpectrum.WOODS -> 2
            LightSpectrum.POL_P -> 3
            LightSpectrum.POL_N -> 4
            LightSpectrum.BLUE, LightSpectrum.RED, LightSpectrum.BROWN -> -1
            LightSpectrum.ALL -> -1
            LightSpectrum.OFF -> -2
            else -> -1
        }
    }
}
