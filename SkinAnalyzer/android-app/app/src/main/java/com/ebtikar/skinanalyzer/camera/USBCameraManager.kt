package com.ebtikar.skinanalyzer.camera

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.ImageFormat
import android.graphics.Matrix
import android.hardware.camera2.CameraAccessException
import android.hardware.camera2.CameraCaptureSession
import android.hardware.camera2.CameraCharacteristics
import android.hardware.camera2.CameraDevice
import android.hardware.camera2.CameraManager
import android.hardware.camera2.CameraMetadata
import android.hardware.camera2.CaptureRequest
import android.media.ExifInterface
import android.media.ImageReader
import android.os.Handler
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import android.os.HandlerThread
import java.io.ByteArrayInputStream
import android.util.Size
import android.view.Surface
import android.hardware.camera2.TotalCaptureResult
import android.view.TextureView
import android.view.WindowManager
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlinx.coroutines.withContext
import kotlinx.coroutines.withTimeout
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton
import kotlin.coroutines.resume
import kotlin.coroutines.resumeWithException

@Singleton
class USBCameraManager @Inject constructor(
    private val context: Context
) {

    companion object {
        val TARGET_RESOLUTION = Size(1920, 1080)
    }

    private var cameraDevice: CameraDevice? = null
    private var captureSession: CameraCaptureSession? = null
    private var imageReader: ImageReader? = null
    private var backgroundHandler: Handler? = null
    private var backgroundThread: HandlerThread? = null
    private var supportedSizes: List<Size> = emptyList()
    private var previewSurface: Surface? = null
    private var sensorOrientation: Int = 0
    private var captureOrientationOffset: Int = 0
    private var previewTargetSize: Size = TARGET_RESOLUTION
    private var displayRotationDegrees: Int = 0
    private var flashAvailable: Boolean = false
    private var textureView: TextureView? = null

    var isDisplayPortrait: Boolean = false

    @Volatile var cameraSettings: CameraSettings = CameraSettings()
        set(value) {
            field = value
            applyCameraSettings()
        }

    var maxZoom: Float = 4.0f

    fun setTextureView(view: TextureView) {
        textureView = view
    }

    val isReady: Boolean get() = cameraDevice != null

    private fun startBackgroundThread() {
        backgroundThread = HandlerThread("CameraBackground").also { it.start() }
        backgroundHandler = Handler(backgroundThread!!.looper)
    }

    private fun stopBackgroundThread() {
        backgroundThread?.quitSafely()
        try {
            backgroundThread?.join()
            backgroundThread = null
            backgroundHandler = null
        } catch (e: InterruptedException) {
            Timber.w(e, "Background thread interrupted")
        }
    }

    fun findBestCamera(): String? {
        val cameraManager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager
        return try {
            val cameraId = cameraManager.cameraIdList.firstOrNull { id ->
                val characteristics = cameraManager.getCameraCharacteristics(id)
                val facing = characteristics.get(CameraCharacteristics.LENS_FACING)
                facing == CameraCharacteristics.LENS_FACING_BACK ||
                    facing == CameraCharacteristics.LENS_FACING_FRONT ||
                    facing == CameraCharacteristics.LENS_FACING_EXTERNAL
            }

            if (cameraId != null) {
                val characteristics = cameraManager.getCameraCharacteristics(cameraId)
                val config = characteristics.get(CameraCharacteristics.SCALER_STREAM_CONFIGURATION_MAP)
                supportedSizes = config?.getOutputSizes(ImageFormat.JPEG)?.toList() ?: emptyList()
                sensorOrientation = characteristics.get(CameraCharacteristics.SENSOR_ORIENTATION) ?: 0
                if (sensorOrientation == 0) {
                    captureOrientationOffset = 0
                    Timber.i("OV13850 USB camera: captureOrientationOffset=0, sensorOrientation=$sensorOrientation")
                }
                flashAvailable = characteristics.get(CameraCharacteristics.FLASH_INFO_AVAILABLE) ?: false
                maxZoom = (characteristics.get(CameraCharacteristics.SCALER_AVAILABLE_MAX_DIGITAL_ZOOM) ?: 1.0f).coerceAtLeast(4.0f)
                Timber.i("Found camera $cameraId with ${supportedSizes.size} sizes, orientation=$sensorOrientation, flash=$flashAvailable, maxZoom=$maxZoom")
            } else {
                Timber.w("No suitable camera found")
            }
            cameraId
        } catch (e: SecurityException) {
            Timber.e(e, "Camera permission not granted")
            null
        }
    }

    fun getOptimalSize(maxSize: Size = TARGET_RESOLUTION): Size {
        if (supportedSizes.isEmpty()) return maxSize
        // Pick the largest available size that fits within maxSize for speed; 1920x1080 is plenty for analysis
        return supportedSizes
            .filter { it.width <= maxSize.width && it.height <= maxSize.height }
            .maxByOrNull { it.width * it.height }
            ?: maxSize
    }

    suspend fun openCamera(cameraId: String, surface: Surface? = null): CameraDevice =
        withTimeout(5000L) {
        suspendCancellableCoroutine { continuation ->
            startBackgroundThread()
            Timber.d("openCamera: waiting for camera $cameraId (timeout=5s)")

            if (surface != null) {
                previewSurface = surface
            }

            updateDisplayRotation()

            val cameraManager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager

            try {
                cameraManager.openCamera(cameraId, object : CameraDevice.StateCallback() {
                    override fun onOpened(camera: CameraDevice) {
                        cameraDevice = camera
                        Timber.i("Camera opened: $cameraId, sensorOrientation=$sensorOrientation, displayRotation=$displayRotationDegrees")
                        val ps = previewSurface
                        if (ps != null) {
                            try {
                                val size = getOptimalSize(TARGET_RESOLUTION)
                                previewTargetSize = size
                                imageReader = ImageReader.newInstance(size.width, size.height, ImageFormat.JPEG, 4)
                                val targets = listOf(ps, imageReader!!.surface)
                                camera.createCaptureSession(
                                    targets,
                                    object : CameraCaptureSession.StateCallback() {
                                        override fun onConfigured(session: CameraCaptureSession) {
                                            captureSession = session
                                                            try {
                                                val request = camera.createCaptureRequest(CameraDevice.TEMPLATE_PREVIEW).apply {
                                                    addTarget(ps)
                                                    configureAutoExposure(this)
                                                    configureAutoFocusPreview(this)
                                                }
                                                session.setRepeatingRequest(request.build(), null, backgroundHandler)
                                                            } catch (e: Exception) {
                                                                Timber.w(e, "Preview request failed, capture-only mode")
                                                            }
                                            Timber.i("Preview + capture session ready, size=${size.width}x${size.height}")
                                        }
                                        override fun onConfigureFailed(session: CameraCaptureSession) {
                                            Timber.e("Combined session config failed")
                                        }
                                    }, backgroundHandler)
                            } catch (e: Exception) {
                                Timber.w(e, "Failed to create combined session")
                            }
                        }
                        textureView?.let { rotateTextureView(it) }
                        if (continuation.isActive) {
                            continuation.resume(camera)
                        }
                    }

                    override fun onDisconnected(camera: CameraDevice) {
                        camera.close()
                        cameraDevice = null
                        Timber.w("Camera disconnected: $cameraId")
                        if (continuation.isActive) {
                            continuation.resumeWithException(IllegalStateException("Camera disconnected"))
                        }
                    }

                    override fun onError(camera: CameraDevice, error: Int) {
                        camera.close()
                        cameraDevice = null
                        Timber.e("Camera error: $cameraId, error=$error")
                        if (continuation.isActive) {
                            continuation.resumeWithException(IllegalStateException("Camera error: $error"))
                        }
                    }
                }, backgroundHandler)
            } catch (e: SecurityException) {
                if (continuation.isActive) {
                    continuation.resumeWithException(e)
                }
            }
        }
        }

    suspend fun captureFrame(spectrum: LightSpectrum? = null): Bitmap? {
        return withTimeout(3000L) {
            suspendCancellableCoroutine { continuation ->
                val device = cameraDevice ?: run {
                    if (continuation.isActive) continuation.resume(null)
                    return@suspendCancellableCoroutine
                }
                val session = captureSession ?: run {
                    if (continuation.isActive) continuation.resume(null)
                    return@suspendCancellableCoroutine
                }
                val reader = imageReader ?: run {
                    if (continuation.isActive) continuation.resume(null)
                    return@suspendCancellableCoroutine
                }

                reader.setOnImageAvailableListener({ r ->
                    val image = r.acquireLatestImage()
                    if (image != null) {
                        try {
                            val buffer = image.planes[0].buffer
                            val bytes = ByteArray(buffer.remaining())
                            buffer.get(bytes)
                            var bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
                            if (bitmap != null) {
                                val rotation = getJpegOrientation()
                                Timber.d("captureFrame: ${bitmap.width}x${bitmap.height} spectrum=${spectrum?.name} rotation=$rotation")
                                if (rotation != 0) {
                                    val matrix = Matrix().apply { postRotate(rotation.toFloat()) }
                                    val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                                    if (rotated !== bitmap) bitmap.recycle()
                                    bitmap = rotated
                                }
                                val zoom = cameraSettings.zoomRatio
                                if (zoom > 1.0f) {
                                    val cropW = (bitmap.width / zoom).toInt().coerceAtLeast(1)
                                    val cropH = (bitmap.height / zoom).toInt().coerceAtLeast(1)
                                    val left = (bitmap.width - cropW) / 2
                                    val top = (bitmap.height - cropH) / 2
                                    val cropped = Bitmap.createBitmap(bitmap, left, top, cropW, cropH)
                                    val scaleMatrix = Matrix().apply { postScale(zoom, zoom) }
                                    val zoomed = Bitmap.createBitmap(cropped, 0, 0, cropW, cropH, scaleMatrix, true)
                                    if (cropped !== bitmap) cropped.recycle()
                                    bitmap.recycle()
                                    bitmap = zoomed
                                    Timber.d("Software zoom applied: ratio=$zoom, crop=${cropW}x${cropH}")
                                }
                            }
                            if (continuation.isActive) continuation.resume(bitmap)
                        } catch (e: Exception) {
                            Timber.e(e, "Failed to decode captured frame for ${spectrum?.name}")
                            if (continuation.isActive) continuation.resume(null)
                        } finally {
                            image.close()
                        }
                    } else {
                        if (continuation.isActive) continuation.resume(null)
                    }
                }, backgroundHandler)

                try {
                    val captureBuilder = device.createCaptureRequest(CameraDevice.TEMPLATE_STILL_CAPTURE).apply {
                        addTarget(reader.surface)
                        set(CaptureRequest.CONTROL_MODE, CameraMetadata.CONTROL_MODE_AUTO)
                        set(CaptureRequest.CONTROL_AF_MODE, CaptureRequest.CONTROL_AF_MODE_CONTINUOUS_PICTURE)
                        set(CaptureRequest.JPEG_QUALITY, 100.toByte())
                        configureAutoExposure(this)
                    }
                    session.capture(captureBuilder.build(), object : CameraCaptureSession.CaptureCallback() {
                        override fun onCaptureCompleted(
                            session: CameraCaptureSession,
                            request: CaptureRequest,
                            result: TotalCaptureResult
                        ) {
                            Timber.d("Still capture completed for ${spectrum?.name}")
                        }
                    }, backgroundHandler)
                } catch (e: CameraAccessException) {
                    Timber.e(e, "Still capture failed for ${spectrum?.name}")
                    if (continuation.isActive) continuation.resume(null)
                }
            }
        }
    }

    fun applyCameraSettings() {
        textureView?.let { rotateTextureView(it) }
        val session = captureSession ?: return
        val device = cameraDevice ?: return
        val ps = previewSurface ?: return
        try {
            val request = device.createCaptureRequest(CameraDevice.TEMPLATE_PREVIEW).apply {
                addTarget(ps)
                imageReader?.surface?.let { addTarget(it) }
                configureAutoExposure(this)
                configureAutoFocusPreview(this)
            }
            session.setRepeatingRequest(request.build(), null, backgroundHandler)
            Timber.d("Camera settings applied: rotation=${cameraSettings.displayRotation}")
        } catch (e: Exception) {
            Timber.w(e, "Failed to apply camera settings")
        }
    }

    fun hasFlash(): Boolean = flashAvailable

    fun setTorchMode(enabled: Boolean) {
        val device = cameraDevice ?: return
        val session = captureSession ?: return
        val targets = mutableListOf<Surface>().apply {
            previewSurface?.let { add(it) }
            imageReader?.surface?.let { add(it) }
        }
        if (targets.isEmpty()) return
        try {
            val request = device.createCaptureRequest(CameraDevice.TEMPLATE_PREVIEW).apply {
                targets.forEach { addTarget(it) }
                configureAutoExposure(this)
                configureAutoFocusPreview(this)
                set(CaptureRequest.FLASH_MODE, if (enabled) CaptureRequest.FLASH_MODE_TORCH else CaptureRequest.FLASH_MODE_OFF)
            }
            session.setRepeatingRequest(request.build(), null, backgroundHandler)
            Timber.d("Torch ${if (enabled) "ON" else "OFF"}")
        } catch (e: Exception) {
            Timber.w(e, "Failed to set torch mode")
        }
    }

    fun closeCamera() {
        captureSession?.close()
        captureSession = null
        cameraDevice?.close()
        cameraDevice = null
        imageReader?.close()
        imageReader = null
        previewSurface = null
        stopBackgroundThread()
        Timber.i("Camera released")
    }

    suspend fun closeCameraSuspend() {
        captureSession?.close()
        captureSession = null
        cameraDevice?.close()
        cameraDevice = null
        imageReader?.close()
        imageReader = null
        previewSurface = null
        withContext(Dispatchers.IO) {
            stopBackgroundThread()
        }
        Timber.i("Camera released (suspend)")
    }

    private fun updateDisplayRotation() {
        val display = (context.getSystemService(Context.WINDOW_SERVICE) as WindowManager).defaultDisplay
        displayRotationDegrees = when (display.rotation) {
            Surface.ROTATION_0 -> 0
            Surface.ROTATION_90 -> 90
            Surface.ROTATION_180 -> 180
            Surface.ROTATION_270 -> 270
            else -> 0
        }
    }

    private fun configureAutoExposure(builder: CaptureRequest.Builder) {
        builder.set(CaptureRequest.CONTROL_AE_MODE, CaptureRequest.CONTROL_AE_MODE_ON)
    }

    private fun configureAutoFocusPreview(builder: CaptureRequest.Builder) {
        try {
            builder.set(CaptureRequest.CONTROL_AF_MODE, CaptureRequest.CONTROL_AF_MODE_CONTINUOUS_PICTURE)
        } catch (e: Exception) {
            Timber.w(e, "Continuous AF not supported, using AUTO")
            builder.set(CaptureRequest.CONTROL_AF_MODE, CaptureRequest.CONTROL_AF_MODE_AUTO)
        }
    }

    fun getJpegOrientation(): Int {
        return (sensorOrientation + captureOrientationOffset + displayRotationDegrees + cameraSettings.displayRotation) % 360
    }

    fun rotateTextureView(textureView: TextureView) {
        if (textureView.width == 0 || textureView.height == 0) return
        val baseRotation = (sensorOrientation - displayRotationDegrees + 360) % 360
        val totalRotation = (baseRotation + cameraSettings.displayRotation) % 360
        val matrix = Matrix()
        val viewW = textureView.width.toFloat()
        val viewH = textureView.height.toFloat()
        val cx = viewW / 2f
        val cy = viewH / 2f

        if (totalRotation != 0) {
            matrix.postRotate(totalRotation.toFloat(), cx, cy)
        }

        // Compute effective stream dimensions after accounting for rotation
        val streamW: Float
        val streamH: Float
        if (totalRotation == 90 || totalRotation == 270) {
            streamW = previewTargetSize.height.toFloat()
            streamH = previewTargetSize.width.toFloat()
        } else {
            streamW = previewTargetSize.width.toFloat()
            streamH = previewTargetSize.height.toFloat()
        }

        // Always fill the view while maintaining aspect ratio
        val scaleX = viewW / streamW
        val scaleY = viewH / streamH
        val fillScale = maxOf(scaleX, scaleY)
        matrix.postScale(fillScale, fillScale, cx, cy)

        val zoomScale = cameraSettings.zoomRatio.coerceAtLeast(1.0f)
        if (zoomScale > 1.0f) {
            matrix.postScale(zoomScale, zoomScale, cx, cy)
        }

        textureView.setTransform(matrix)
        Timber.d("rotateTextureView: view=${textureView.width}x${textureView.height}, stream=${streamW.toInt()}x${streamH.toInt()}, rot=$totalRotation, fillScale=$fillScale, zoom=$zoomScale")
    }

    fun getPreviewSize(): Size = previewTargetSize

    fun release() {
        closeCamera()
    }
}
