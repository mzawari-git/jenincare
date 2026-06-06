package com.ebtikar.skinanalyzer.camera

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.ImageFormat
import android.graphics.SurfaceTexture
import android.hardware.camera2.CameraAccessException
import android.hardware.camera2.CameraCaptureSession
import android.hardware.camera2.CameraCharacteristics
import android.hardware.camera2.CameraDevice
import android.hardware.camera2.CameraManager
import android.hardware.camera2.CameraMetadata
import android.hardware.camera2.CaptureRequest
import android.hardware.camera2.params.StreamConfigurationMap
import android.media.ImageReader
import android.os.Handler
import android.os.HandlerThread
import android.util.Size
import android.view.Surface
import com.ebtikar.skinanalyzer.util.ImageUtils
import kotlinx.coroutines.suspendCancellableCoroutine
import timber.log.Timber
import java.io.File
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
                facing == CameraCharacteristics.LENS_FACING_FRONT ||
                    facing == CameraCharacteristics.LENS_FACING_EXTERNAL
            }

            if (cameraId != null) {
                val characteristics = cameraManager.getCameraCharacteristics(cameraId)
                val config = characteristics.get(CameraCharacteristics.SCALER_STREAM_CONFIGURATION_MAP)
                supportedSizes = config?.getOutputSizes(ImageFormat.JPEG)?.toList() ?: emptyList()
                Timber.i("Found camera $cameraId with ${supportedSizes.size} supported sizes")
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

        return supportedSizes
            .filter { it.width <= TARGET_RESOLUTION.width && it.height <= TARGET_RESOLUTION.height }
            .maxByOrNull { it.width * it.height }
            ?: TARGET_RESOLUTION
    }

    suspend fun openCamera(cameraId: String, surface: Surface? = null): CameraDevice =
        suspendCancellableCoroutine { continuation ->
            startBackgroundThread()

            if (surface != null) {
                previewSurface = surface
            }

            val cameraManager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager

            try {
                cameraManager.openCamera(cameraId, object : CameraDevice.StateCallback() {
                    override fun onOpened(camera: CameraDevice) {
                        cameraDevice = camera
                        Timber.i("Camera opened: $cameraId")
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

    suspend fun captureFrame(): Bitmap = suspendCancellableCoroutine { continuation ->
        val device = cameraDevice ?: run {
            if (continuation.isActive) {
                continuation.resumeWithException(IllegalStateException("Camera not open"))
            }
            return@suspendCancellableCoroutine
        }

        val size = getOptimalSize()

        imageReader = ImageReader.newInstance(size.width, size.height, ImageFormat.JPEG, 2).apply {
            setOnImageAvailableListener({ reader ->
                val image = reader.acquireLatestImage() ?: return@setOnImageAvailableListener
                try {
                    val buffer = image.planes[0].buffer
                    val bytes = ByteArray(buffer.remaining())
                    buffer.get(bytes)

                    val bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
                    if (continuation.isActive) {
                        continuation.resume(bitmap)
                    }
                } catch (e: Exception) {
                    Timber.e(e, "Failed to capture frame")
                    if (continuation.isActive) {
                        continuation.resumeWithException(e)
                    }
                } finally {
                    image.close()
                }
            }, backgroundHandler)
        }

        try {
            val captureBuilder = device.createCaptureRequest(CameraDevice.TEMPLATE_STILL_CAPTURE).apply {
                addTarget(imageReader!!.surface)
                set(CaptureRequest.CONTROL_MODE, CameraMetadata.CONTROL_MODE_AUTO)
                configureAutoExposure(this)
            }

            device.createCaptureSession(
                listOf(imageReader!!.surface),
                object : CameraCaptureSession.StateCallback() {
                    override fun onConfigured(session: CameraCaptureSession) {
                        captureSession = session
                        try {
                            session.capture(captureBuilder.build(), object : CameraCaptureSession.CaptureCallback() {
                                override fun onCaptureCompleted(
                                    session: CameraCaptureSession,
                                    request: CaptureRequest,
                                    result: android.hardware.camera2.TotalCaptureResult
                                ) {
                                    Timber.d("Frame captured successfully")
                                }
                            }, backgroundHandler)
                        } catch (e: CameraAccessException) {
                            Timber.e(e, "Capture request failed")
                            if (continuation.isActive) {
                                continuation.resumeWithException(e)
                            }
                        }
                    }

                    override fun onConfigureFailed(session: CameraCaptureSession) {
                        Timber.e("Capture session configuration failed")
                        if (continuation.isActive) {
                            continuation.resumeWithException(IllegalStateException("Session config failed"))
                        }
                    }
                },
                backgroundHandler
            )
        } catch (e: CameraAccessException) {
            Timber.e(e, "Failed to create capture session")
            if (continuation.isActive) {
                continuation.resumeWithException(e)
            }
        }
    }

    fun getPreviewSurface(): Surface? = previewSurface

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

    fun configureAutoExposure(builder: CaptureRequest.Builder) {
        builder.set(CaptureRequest.CONTROL_AE_MODE, CaptureRequest.CONTROL_AE_MODE_ON)
        builder.set(CaptureRequest.CONTROL_AE_TARGET_FPS_RANGE, android.util.Range(MIN_FPS, 30))
    }

    fun release() {
        closeCamera()
    }
}
