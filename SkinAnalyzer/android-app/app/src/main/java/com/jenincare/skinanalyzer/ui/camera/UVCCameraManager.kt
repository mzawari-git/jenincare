package com.jenincare.skinanalyzer.ui.camera

import android.Manifest
import android.content.Context
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.ImageFormat
import android.graphics.Rect
import android.graphics.RectF
import android.graphics.SurfaceTexture
import android.graphics.YuvImage
import android.hardware.Camera
import android.hardware.camera2.CameraCaptureSession
import android.hardware.camera2.CameraCharacteristics
import android.hardware.camera2.CameraDevice
import android.hardware.camera2.CameraManager
import android.hardware.camera2.CaptureRequest
import android.hardware.usb.UsbDevice
import android.media.ImageReader
import android.os.Handler
import android.os.HandlerThread
import android.util.Log
import android.util.Size
import android.view.Surface
import android.view.TextureView
import androidx.core.content.ContextCompat
import com.serenegiant.usb.USBMonitor
import com.serenegiant.usb.UVCCamera
import kotlinx.coroutines.delay
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlinx.coroutines.withTimeout
import java.io.ByteArrayOutputStream
import java.nio.ByteBuffer
import java.util.concurrent.Executors
import java.util.concurrent.atomic.AtomicBoolean
import kotlin.coroutines.resume
import kotlin.coroutines.resumeWithException

class UVCCameraManager(
    private val context: Context,
    private val onFaceDetected: ((Boolean) -> Unit)? = null,
    private val onLightingQuality: ((Float) -> Unit)? = null,
    private val onFaceBoundingBox: ((RectF) -> Unit)? = null
) {
    companion object {
        private const val TAG = "UVCCameraMgr"
        private const val PREVIEW_WIDTH = 1280
        private const val PREVIEW_HEIGHT = 720
        private const val FACE_INTERVAL = 2
        private const val CAPTURE_TIMEOUT = 15000L
    }

    private var usbMonitor: USBMonitor? = null
    private var uvcCamera: UVCCamera? = null
    private var isRunning = AtomicBoolean(false)
    private var frameCount = 0
    private var lastPreviewBitmap: Bitmap? = null
    private val executor = Executors.newSingleThreadExecutor()
    private val faceDetector by lazy { TFLiteFaceDetector(context) }

    private var camera2Device: CameraDevice? = null
    private var camera2Session: CameraCaptureSession? = null
    private var camera2Reader: ImageReader? = null
    private var camera2Handler: Handler? = null
    private var camera2HandlerThread: HandlerThread? = null
    private var usingCamera2 = false
    private var camera2RetryQueue: MutableList<String> = mutableListOf()
    private var camera2Manager: CameraManager? = null
    private var camera2TextureView: TextureView? = null
    private var camera2Opened = false
    private var camera1Device: Camera? = null
    private var previewTextureView: TextureView? = null

    @Volatile
    var isCameraReady = false
        private set

    var onCameraReady: (() -> Unit)? = null
    var onCameraError: ((String) -> Unit)? = null

    fun startCamera(textureView: TextureView) {
        if (textureView.isAvailable) {
            initCamera(textureView)
        } else {
            textureView.surfaceTextureListener = object : TextureView.SurfaceTextureListener {
                override fun onSurfaceTextureAvailable(s: SurfaceTexture, w: Int, h: Int) {
                    initCamera(textureView)
                }
                override fun onSurfaceTextureSizeChanged(s: SurfaceTexture, w: Int, h: Int) {}
                override fun onSurfaceTextureDestroyed(s: SurfaceTexture) = true
                override fun onSurfaceTextureUpdated(s: SurfaceTexture) {}
            }
        }
    }

    private fun initCamera(textureView: TextureView) {
        previewTextureView = textureView
        usbMonitor = USBMonitor(context, object : USBMonitor.OnDeviceConnectListener {
            override fun onAttach(device: UsbDevice) {
                Log.d(TAG, "UVC device attached: ${device.deviceName}")
            }
            override fun onDettach(device: UsbDevice) {
                Log.d(TAG, "UVC device detached")
                releaseCamera()
            }
            override fun onConnect(device: UsbDevice, ctrlBlock: USBMonitor.UsbControlBlock, createNew: Boolean) {
                Log.d(TAG, "UVC permission granted, opening camera...")
                executor.execute {
                    try {
                        openUvcCamera(ctrlBlock, textureView)
                    } catch (e: Exception) {
                        Log.e(TAG, "Failed to open UVC device", e)
                        tryCamera2(textureView)
                    }
                }
            }
            override fun onDisconnect(device: UsbDevice, ctrlBlock: USBMonitor.UsbControlBlock) {
                Log.d(TAG, "UVC device disconnected")
                releaseCamera()
            }
            override fun onCancel(device: UsbDevice) {
                Log.e(TAG, "UVC permission denied")
                tryCamera2(textureView)
            }
        })

        usbMonitor?.register()
        val devices = usbMonitor?.deviceList
        if (!devices.isNullOrEmpty()) {
            usbMonitor?.requestPermission(devices[0])
        } else {
            Log.w(TAG, "No UVC devices, trying Camera2...")
            tryCamera2(textureView)
        }
    }

    private fun tryCamera2(textureView: TextureView) {
        if (ContextCompat.checkSelfPermission(context, Manifest.permission.CAMERA)
            != PackageManager.PERMISSION_GRANTED
        ) {
            Log.w(TAG, "Camera2 permission not granted")
            onCameraError?.invoke("كاميرا USB غير متصلة")
            return
        }
        val manager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager
        try {
            val allCameras = manager.cameraIdList.toList()
            Log.d(TAG, "Camera2 available IDs: ${allCameras.joinToString()}")
            camera2Manager = manager
            camera2TextureView = textureView
            camera2RetryQueue = if (allCameras.isNotEmpty()) allCameras.toMutableList() else {
                Log.w(TAG, "getCameraIdList empty, trying fallback IDs 1,0")
                mutableListOf("1", "0")
            }
            camera2Opened = false
            tryNextCamera2()
        } catch (e: Exception) {
            Log.e(TAG, "Camera2 init failed", e)
            onCameraError?.invoke("كاميرا USB غير متصلة")
        }
    }

    private fun tryNextCamera2() {
        if (camera2RetryQueue.isEmpty()) {
            Log.w(TAG, "No Camera2 cameras available, trying Camera1 API")
            tryCamera1(camera2TextureView ?: return)
            return
        }
        val cameraId = camera2RetryQueue.removeAt(0)
        val manager = camera2Manager ?: run {
            tryNextCamera2()
            return
        }
        try {
            val chars = manager.getCameraCharacteristics(cameraId)
            val facing = chars.get(CameraCharacteristics.LENS_FACING)
            Log.d(TAG, "Attempting camera $cameraId facing=$facing")
            openCamera2(cameraId, manager, camera2TextureView ?: return)
        } catch (e: Exception) {
            Log.w(TAG, "Camera $cameraId characteristics failed: ${e.message}")
            tryNextCamera2()
        }
    }

    private fun tryCamera1(textureView: TextureView) {
        try {
            val num = Camera.getNumberOfCameras()
            Log.d(TAG, "Camera1 API: $num cameras")
            val camId = 0
            val info = Camera.CameraInfo()
            Camera.getCameraInfo(camId, info)
            Log.d(TAG, "Camera1 camera $camId facing=${info.facing}")

            camera1Device = Camera.open(camId)
            Log.d(TAG, "Camera1 opened: $camId")

            val params = camera1Device!!.parameters
            val previewSize = params.supportedPreviewSizes
                .filter { it.width <= 1280 && it.height <= 720 }
                .maxByOrNull { it.width * it.height }
                ?: params.previewSize
            params.setPreviewSize(previewSize.width, previewSize.height)
            camera1Device!!.parameters = params

            val bufSize = previewSize.width * previewSize.height *
                ImageFormat.getBitsPerPixel(params.previewFormat) / 8
            camera1Device!!.addCallbackBuffer(ByteArray(bufSize))
            camera1Device!!.setPreviewCallbackWithBuffer { data, camera ->
                val yuv = YuvImage(data, params.previewFormat,
                    previewSize.width, previewSize.height, null)
                val out = ByteArrayOutputStream()
                yuv.compressToJpeg(Rect(0, 0, previewSize.width, previewSize.height), 80, out)
                val bmp = BitmapFactory.decodeByteArray(out.toByteArray(), 0, out.size())
                if (bmp != null) {
                    lastPreviewBitmap = bmp.copy(Bitmap.Config.ARGB_8888, false)
                    if (frameCount % FACE_INTERVAL == 0) {
                        val result = faceDetector.detectFace(bmp)
                        onFaceDetected?.invoke(result.confidence > 0.4f)
                        onFaceBoundingBox?.invoke(result.boundingBox)
                        val lighting = faceDetector.checkLightingQuality(bmp)
                        onLightingQuality?.invoke(lighting)
                    }
                    bmp.recycle()
                }
                frameCount++
                camera.addCallbackBuffer(data)
            }

            textureView.post {
                val vw = textureView.width
                val vh = textureView.height
                if (vw > 0 && vh > 0) {
                    val matrix = android.graphics.Matrix()
                    val pa = previewSize.width.toFloat() / previewSize.height
                    val va = vw.toFloat() / vh
                    if (pa > va) {
                        val sw = vh * pa
                        matrix.setScale(sw / vw, 1f)
                        matrix.postTranslate((vw - sw) / 2f, 0f)
                    } else {
                        val sh = vw / pa
                        matrix.setScale(1f, sh / vh)
                        matrix.postTranslate(0f, (vh - sh) / 2f)
                    }
                    textureView.setTransform(matrix)
                }
            }

            camera1Device!!.setPreviewTexture(textureView.surfaceTexture)
            camera1Device!!.startPreview()
            isRunning.set(true)
            isCameraReady = true
            usingCamera2 = false
            onCameraReady?.invoke()
            Log.d(TAG, "Camera1 preview started: ${previewSize.width}x${previewSize.height}")
        } catch (e: Exception) {
            Log.e(TAG, "Camera1 failed", e)
            tryCamera1Fallback(textureView)
        }
    }

    private fun tryCamera1Fallback(textureView: TextureView) {
        try {
            val num = Camera.getNumberOfCameras()
            if (num > 1) {
                Log.d(TAG, "Camera1 fallback: trying camera 1")
                val info = Camera.CameraInfo()
                Camera.getCameraInfo(1, info)
                camera1Device = Camera.open(1)
                val params = camera1Device!!.parameters
                val previewSize = params.supportedPreviewSizes
                    .filter { it.width <= 1280 && it.height <= 720 }
                    .maxByOrNull { it.width * it.height }
                    ?: params.previewSize
                params.setPreviewSize(previewSize.width, previewSize.height)
                camera1Device!!.parameters = params
                val bufSize = previewSize.width * previewSize.height *
                    ImageFormat.getBitsPerPixel(params.previewFormat) / 8
                camera1Device!!.addCallbackBuffer(ByteArray(bufSize))
                camera1Device!!.setPreviewCallbackWithBuffer { data, camera ->
                    val yuv = YuvImage(data, params.previewFormat,
                        previewSize.width, previewSize.height, null)
                    val out = ByteArrayOutputStream()
                    yuv.compressToJpeg(Rect(0, 0, previewSize.width, previewSize.height), 80, out)
                    val bmp = BitmapFactory.decodeByteArray(out.toByteArray(), 0, out.size())
                    if (bmp != null) {
                        lastPreviewBitmap = bmp.copy(Bitmap.Config.ARGB_8888, false)
                        if (frameCount % FACE_INTERVAL == 0) {
                            val result = faceDetector.detectFace(bmp)
                            onFaceDetected?.invoke(result.confidence > 0.4f)
                            onFaceBoundingBox?.invoke(result.boundingBox)
                            val lighting = faceDetector.checkLightingQuality(bmp)
                            onLightingQuality?.invoke(lighting)
                        }
                        bmp.recycle()
                    }
                    frameCount++
                    camera.addCallbackBuffer(data)
                }
                textureView.post {
                    val vw = textureView.width
                    val vh = textureView.height
                    if (vw > 0 && vh > 0) {
                        val matrix = android.graphics.Matrix()
                        val pa = previewSize.width.toFloat() / previewSize.height
                        val va = vw.toFloat() / vh
                        if (pa > va) {
                            val sw = vh * pa
                            matrix.setScale(sw / vw, 1f)
                            matrix.postTranslate((vw - sw) / 2f, 0f)
                        } else {
                            val sh = vw / pa
                            matrix.setScale(1f, sh / vh)
                            matrix.postTranslate(0f, (vh - sh) / 2f)
                        }
                        textureView.setTransform(matrix)
                    }
                }
                camera1Device!!.setPreviewTexture(textureView.surfaceTexture)
                camera1Device!!.startPreview()
                isRunning.set(true)
                isCameraReady = true
                onCameraReady?.invoke()
                Log.d(TAG, "Camera1 fallback started")
                return
            }
        } catch (e: Exception) {
            Log.e(TAG, "Camera1 fallback failed", e)
        }
        Log.w(TAG, "All camera attempts failed")
        onCameraError?.invoke("كاميرا USB غير متصلة")
    }

    private fun openCamera2(cameraId: String, manager: CameraManager, textureView: TextureView) {
        try {
            camera2HandlerThread = HandlerThread("Camera2Thread").apply { start() }
            camera2Handler = Handler(camera2HandlerThread!!.looper)

            val previewSize = Size(PREVIEW_WIDTH, PREVIEW_HEIGHT)

            val st = textureView.surfaceTexture
            st?.setDefaultBufferSize(previewSize.width, previewSize.height)
            val surface = Surface(st!!)

            textureView.post {
                val vw = textureView.width
                val vh = textureView.height
                if (vw > 0 && vh > 0) {
                    val matrix = android.graphics.Matrix()
                    val previewAspect = previewSize.width.toFloat() / previewSize.height
                    val viewAspect = vw.toFloat() / vh
                    if (previewAspect > viewAspect) {
                        val scaledW = vh * previewAspect
                        matrix.setScale(scaledW / vw, 1f)
                        matrix.postTranslate((vw - scaledW) / 2f, 0f)
                    } else {
                        val scaledH = vw / previewAspect
                        matrix.setScale(1f, scaledH / vh)
                        matrix.postTranslate(0f, (vh - scaledH) / 2f)
                    }
                    textureView.setTransform(matrix)
                }
            }

            camera2Reader = ImageReader.newInstance(
                previewSize.width, previewSize.height,
                ImageFormat.JPEG, 2
            )
            camera2Reader?.setOnImageAvailableListener({ reader ->
                val image = reader.acquireLatestImage() ?: return@setOnImageAvailableListener
                frameCount++
                if (frameCount % FACE_INTERVAL == 0) {
                    val buffer = image.planes[0].buffer
                    val bytes = ByteArray(buffer.remaining())
                    buffer.get(bytes)
                    val bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
                    if (bitmap != null) {
                        lastPreviewBitmap = bitmap.copy(Bitmap.Config.ARGB_8888, false)
                        val result = faceDetector.detectFace(bitmap)
                        onFaceDetected?.invoke(result.confidence > 0.4f)
                        onFaceBoundingBox?.invoke(result.boundingBox)
                        val lighting = faceDetector.checkLightingQuality(bitmap)
                        onLightingQuality?.invoke(lighting)
                    }
                }
                image.close()
            }, camera2Handler!!)

            manager.openCamera(cameraId, object : CameraDevice.StateCallback() {
                override fun onOpened(camera: CameraDevice) {
                    camera2Opened = true
                    camera2Device = camera
                    val targets = listOf(surface, camera2Reader!!.surface)
                    try {
                        camera.createCaptureSession(targets, object : CameraCaptureSession.StateCallback() {
                            override fun onConfigured(session: CameraCaptureSession) {
                                camera2Session = session
                                val request = camera.createCaptureRequest(
                                    CameraDevice.TEMPLATE_PREVIEW
                                ).apply {
                                    addTarget(surface)
                                    addTarget(camera2Reader!!.surface)
                                    set(CaptureRequest.CONTROL_MODE, CaptureRequest.CONTROL_MODE_AUTO)
                                }.build()
                                session.setRepeatingRequest(request, null, camera2Handler)
                                isRunning.set(true)
                                isCameraReady = true
                                usingCamera2 = true
                                onCameraReady?.invoke()
                                Log.d(TAG, "Camera2 preview started: ${previewSize.width}x${previewSize.height}")
                            }
                            override fun onConfigureFailed(session: CameraCaptureSession) {
                                Log.e(TAG, "Camera2 configure failed")
                                onCameraError?.invoke("فشل تشغيل الكاميرا")
                            }
                        }, camera2Handler)
                    } catch (e: Exception) {
                        Log.e(TAG, "Camera2 session error", e)
                        onCameraError?.invoke("فشل تشغيل الكاميرا: ${e.message}")
                    }
                }
                override fun onDisconnected(camera: CameraDevice) {
                    Log.w(TAG, "Camera $cameraId disconnected")
                    if (!camera2Opened) {
                        camera.close()
                        releaseCamera()
                        tryNextCamera2()
                    } else {
                        releaseCamera()
                    }
                }
                override fun onError(camera: CameraDevice, error: Int) {
                    Log.e(TAG, "Camera $cameraId error: $error")
                    if (!camera2Opened) {
                        camera.close()
                        releaseCamera()
                        tryNextCamera2()
                    } else {
                        releaseCamera()
                        onCameraError?.invoke("خطأ في الكاميرا")
                    }
                }
            }, camera2Handler)
        } catch (e: Exception) {
            Log.e(TAG, "Camera2 open failed", e)
            onCameraError?.invoke("فشل تشغيل الكاميرا: ${e.message}")
        }
    }

    private fun openUvcCamera(ctrlBlock: USBMonitor.UsbControlBlock, textureView: TextureView) {
        try {
            val camera = UVCCamera().apply {
                open(ctrlBlock)
                setPreviewSize(PREVIEW_WIDTH, PREVIEW_HEIGHT, UVCCamera.FRAME_FORMAT_MJPEG)
                setPreviewTexture(textureView.surfaceTexture)
                startPreview()

                textureView.post {
                    val vw = textureView.width
                    val vh = textureView.height
                    if (vw > 0 && vh > 0) {
                        val matrix = android.graphics.Matrix()
                        val previewAspect = PREVIEW_WIDTH.toFloat() / PREVIEW_HEIGHT
                        val viewAspect = vw.toFloat() / vh
                        if (previewAspect > viewAspect) {
                            val scaledW = vh * previewAspect
                            matrix.setScale(scaledW / vw, 1f)
                            matrix.postTranslate((vw - scaledW) / 2f, 0f)
                        } else {
                            val scaledH = vw / previewAspect
                            matrix.setScale(1f, scaledH / vh)
                            matrix.postTranslate(0f, (vh - scaledH) / 2f)
                        }
                        textureView.setTransform(matrix)
                    }
                }
                setFrameCallback({ frame ->
                    val size = frame.remaining()
                    if (size <= 0) return@setFrameCallback
                    frameCount++
                    if (frameCount % FACE_INTERVAL != 0) return@setFrameCallback
                    processPreviewFrame(frame, size)
                }, UVCCamera.PIXEL_FORMAT_NV21)
            }
            uvcCamera = camera
            isRunning.set(true)
            isCameraReady = true
            usingCamera2 = false
            onCameraReady?.invoke()
            Log.d(TAG, "UVC camera started: ${PREVIEW_WIDTH}x$PREVIEW_HEIGHT")
        } catch (e: Exception) {
            Log.e(TAG, "Failed to start UVC camera", e)
            tryCamera2(textureView)
        }
    }

    private fun processPreviewFrame(frame: ByteBuffer, size: Int) {
        try {
            val bytes = ByteArray(size)
            frame.get(bytes)
            frame.rewind()
            val bitmap = nv21ToBitmap(bytes, PREVIEW_WIDTH, PREVIEW_HEIGHT) ?: return
            
            // Critical Fix: Only keep a copy if we are NOT capturing to save memory
            // The captureImage() function will take its own copy when needed.
            lastPreviewBitmap?.recycle()
            lastPreviewBitmap = bitmap.copy(Bitmap.Config.ARGB_8888, false)
            
            if (frameCount % FACE_INTERVAL == 0) {
                val result = faceDetector.detectFace(bitmap)
                onFaceDetected?.invoke(result.confidence > 0.4f)
                onFaceBoundingBox?.invoke(result.boundingBox)
                val lighting = faceDetector.checkLightingQuality(bitmap)
                onLightingQuality?.invoke(lighting)
            }
            bitmap.recycle()
        } catch (e: Exception) {
            Log.e(TAG, "processPreviewFrame error", e)
        }
    }

    fun clearLastFrame() {
        lastPreviewBitmap?.recycle()
        lastPreviewBitmap = null
    }

    suspend fun captureImage(): Bitmap {
        if (!isRunning.get()) {
            throw IllegalStateException("Camera not initialized")
        }
        val deadline = System.currentTimeMillis() + CAPTURE_TIMEOUT
        while (System.currentTimeMillis() < deadline) {
            val current = lastPreviewBitmap
            if (current != null && !current.isRecycled) {
                val copy = current.copy(Bitmap.Config.ARGB_8888, false)
                if (copy != null) return copy
            }
            delay(50)
        }
        val tv = previewTextureView
        if (tv != null) {
            try {
                val bmp = tv.bitmap
                if (bmp != null) return bmp.copy(Bitmap.Config.ARGB_8888, false) ?: bmp
            } catch (_: Exception) { }
        }
        throw Exception("Capture timeout - no frame available")
    }

    private fun nv21ToBitmap(nv21: ByteArray, width: Int, height: Int): Bitmap? {
        return try {
            val yuvImage = YuvImage(nv21, ImageFormat.NV21, width, height, null)
            val out = ByteArrayOutputStream()
            // Optimization: Increase JPEG quality for analysis, but this is still a bottleneck.
            // In a production environment, use a C++ YUV2RGB converter via JNI.
            yuvImage.compressToJpeg(Rect(0, 0, width, height), 90, out)
            val jpegBytes = out.toByteArray()
            BitmapFactory.decodeByteArray(jpegBytes, 0, jpegBytes.size)
        } catch (e: Exception) {
            Log.e(TAG, "nv21ToBitmap failed", e)
            null
        }
    }

    fun release() {
        isRunning.set(false)
        isCameraReady = false
        try { uvcCamera?.stopPreview() } catch (_: Exception) { }
        try { uvcCamera?.close() } catch (_: Exception) { }
        uvcCamera = null
        try { camera2Session?.close() } catch (_: Exception) { }
        camera2Session = null
        try { camera2Device?.close() } catch (_: Exception) { }
        camera2Device = null
        try { camera2Reader?.close() } catch (_: Exception) { }
        camera2Reader = null
        try { camera2HandlerThread?.quitSafely() } catch (_: Exception) { }
        camera2HandlerThread = null
        camera2Handler = null
        try { camera1Device?.stopPreview() } catch (_: Exception) { }
        try { camera1Device?.release() } catch (_: Exception) { }
        camera1Device = null
        try { usbMonitor?.unregister() } catch (_: Exception) { }
        usbMonitor = null
        lastPreviewBitmap = null
        executor.shutdownNow()
    }

    private fun releaseCamera() {
        isRunning.set(false)
        isCameraReady = false
        try { uvcCamera?.stopPreview() } catch (_: Exception) { }
        try { uvcCamera?.close() } catch (_: Exception) { }
        uvcCamera = null
        try { camera2Session?.close() } catch (_: Exception) { }
        camera2Session = null
        try { camera2Device?.close() } catch (_: Exception) { }
        camera2Device = null
        try { camera2Reader?.close() } catch (_: Exception) { }
        camera2Reader = null
        try { camera1Device?.stopPreview() } catch (_: Exception) { }
        try { camera1Device?.release() } catch (_: Exception) { }
        camera1Device = null
    }
}
