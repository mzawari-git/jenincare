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
import android.media.ImageReader
import android.os.Handler
import android.os.HandlerThread
import android.util.Size
import android.view.Surface
import android.view.TextureView
import android.view.WindowManager
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.channels.Channel.Factory.UNLIMITED
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlinx.coroutines.withTimeoutOrNull
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
        private const val MIN_FPS = 30
    }

    private var cameraDevice: CameraDevice? = null
    private var captureSession: CameraCaptureSession? = null
    private var imageReader: ImageReader? = null
    private var backgroundHandler: Handler? = null
    private var backgroundThread: HandlerThread? = null
    private var supportedSizes: List<Size> = emptyList()
    private var previewSurface: Surface? = null
    private var sensorOrientation: Int = 0
    private var previewTargetSize: Size = TARGET_RESOLUTION
    private var displayRotationDegrees: Int = 0
    private var flashAvailable: Boolean = false
    private val imageChannel = Channel<Bitmap>(UNLIMITED)
    private var previewRequestBuilder: CaptureRequest.Builder? = null

    var isDisplayPortrait: Boolean = false

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
                flashAvailable = characteristics.get(CameraCharacteristics.FLASH_INFO_AVAILABLE) ?: false
                Timber.i("Found camera $cameraId with ${supportedSizes.size} sizes, orientation=$sensorOrientation, flash=$flashAvailable")
            } else {
                Timber.w("No suitable camera found")
            }
            cameraId
        } catch (e: SecurityException) {
            Timber.e(e, "Camera permission not granted")
            null
        }
    }

    fun getOptimalSize(): Size {
        if (supportedSizes.isEmpty()) return TARGET_RESOLUTION
        val targetWidth = if (isDisplayPortrait) TARGET_RESOLUTION.height else TARGET_RESOLUTION.width
        val targetHeight = if (isDisplayPortrait) TARGET_RESOLUTION.width else TARGET_RESOLUTION.height
        return supportedSizes
            .filter { it.width <= targetWidth && it.height <= targetHeight }
            .maxByOrNull { it.width * it.height }
            ?: Size(targetWidth, targetHeight)
    }

    suspend fun openCamera(cameraId: String, surface: Surface? = null): CameraDevice =
        suspendCancellableCoroutine { continuation ->
            startBackgroundThread()

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
                                val size = getOptimalSize()
                                previewTargetSize = size
                                imageReader = ImageReader.newInstance(size.width, size.height, ImageFormat.JPEG, 2)
                                imageReader!!.setOnImageAvailableListener({ reader ->
                                    val image = reader.acquireLatestImage() ?: return@setOnImageAvailableListener
                                    try {
                                        val buffer = image.planes[0].buffer
                                        val bytes = ByteArray(buffer.remaining())
                                        buffer.get(bytes)
                                        var bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
                                        if (bitmap != null) {
                                            val orientation = getJpegOrientation()
                                            if (orientation != 0) {
                                                val matrix = Matrix().apply { postRotate(orientation.toFloat()) }
                                                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                                                if (rotated !== bitmap) bitmap.recycle()
                                                bitmap = rotated
                                            }
                                        }
                                        if (bitmap != null) {
                                            imageChannel.trySend(bitmap)
                                        }
                                    } catch (e: Exception) {
                                        Timber.e(e, "Failed to decode captured frame")
                                    } finally {
                                        image.close()
                                    }
                                }, backgroundHandler)
                                val targets = listOf(ps, imageReader!!.surface)
                                camera.createCaptureSession(
                                    targets,
                                    object : CameraCaptureSession.StateCallback() {
                                        override fun onConfigured(session: CameraCaptureSession) {
                                            captureSession = session
                                            previewRequestBuilder = camera.createCaptureRequest(CameraDevice.TEMPLATE_PREVIEW).apply {
                                                addTarget(ps)
                                                configureAutoExposure(this)
                                                configureAutoFocusPreview(this)
                                            }
                                            session.setRepeatingRequest(previewRequestBuilder!!.build(), null, backgroundHandler)
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

    suspend fun captureFrame(): Bitmap? {
        val device = cameraDevice ?: return null
        val session = captureSession ?: return null
        val reader = imageReader ?: return null

        try {
            session.stopRepeating()
        } catch (e: Exception) {
            Timber.w(e, "stopRepeating failed")
        }

        try {
            val captureBuilder = device.createCaptureRequest(CameraDevice.TEMPLATE_STILL_CAPTURE).apply {
                addTarget(reader.surface)
                set(CaptureRequest.CONTROL_MODE, CameraMetadata.CONTROL_MODE_AUTO)
                set(CaptureRequest.CONTROL_AF_MODE, CaptureRequest.CONTROL_AF_MODE_CONTINUOUS_PICTURE)
                set(CaptureRequest.JPEG_ORIENTATION, getJpegOrientation())
                set(CaptureRequest.JPEG_QUALITY, 95.toByte())
                configureAutoExposure(this)
            }
            session.capture(captureBuilder.build(), object : CameraCaptureSession.CaptureCallback() {
                override fun onCaptureCompleted(
                    session: CameraCaptureSession,
                    request: CaptureRequest,
                    result: android.hardware.camera2.TotalCaptureResult
                ) {
                    Timber.d("Still capture submitted")
                }
            }, backgroundHandler)
        } catch (e: CameraAccessException) {
            Timber.e(e, "Still capture failed")
        }

        val bitmap = withTimeoutOrNull(5000L) { imageChannel.receive() }
        if (bitmap == null) {
            Timber.w("captureFrame timed out waiting for image")
        }

        try {
            val preview = previewRequestBuilder
            if (preview != null) {
                session.setRepeatingRequest(preview.build(), null, backgroundHandler)
            }
        } catch (e: Exception) {
            Timber.w(e, "Failed to restart preview after capture")
        }

        return bitmap
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
        previewRequestBuilder = null
        imageChannel.tryReceive().let { while (imageChannel.tryReceive().isSuccess) { } }
        stopBackgroundThread()
        Timber.i("Camera released")
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
        builder.set(CaptureRequest.CONTROL_AE_TARGET_FPS_RANGE, android.util.Range(MIN_FPS, 30))
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
        return (sensorOrientation + displayRotationDegrees) % 360
    }

    fun rotateTextureView(textureView: TextureView) {
        if (textureView.width == 0 || textureView.height == 0) return
        val rotation = (sensorOrientation - displayRotationDegrees + 360) % 360
        if (rotation == 0) return
        val matrix = Matrix()
        val cx = textureView.width / 2f
        val cy = textureView.height / 2f
        matrix.postRotate(rotation.toFloat(), cx, cy)
        if (rotation == 90 || rotation == 270) {
            val scale = maxOf(
                textureView.width.toFloat() / textureView.height.toFloat(),
                textureView.height.toFloat() / textureView.width.toFloat()
            )
            matrix.postScale(scale, scale, cx, cy)
        }
        textureView.setTransform(matrix)
    }

    fun getPreviewSize(): Size = previewTargetSize

    fun release() {
        closeCamera()
    }
}
