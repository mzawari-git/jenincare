package com.ebtikar.skinanalyzer.camera

import android.graphics.Bitmap
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.util.ImageUtils
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FrameCapturePipeline @Inject constructor(
    private val spectrumController: SpectrumController,
    private val cameraManager: USBCameraManager
) {

    private val _captureState = MutableStateFlow(CaptureState.IDLE)
    val captureState: StateFlow<CaptureState> = _captureState.asStateFlow()

    private val _capturedFrames = MutableStateFlow<Map<LightSpectrum, File>>(emptyMap())
    val capturedFrames: StateFlow<Map<LightSpectrum, File>> = _capturedFrames.asStateFlow()

    private val _currentPhase = MutableStateFlow<CapturePhase?>(null)
    val currentPhase: MutableStateFlow<CapturePhase?> = _currentPhase

    private val _capturedBitmap = MutableStateFlow<Bitmap?>(null)
    val capturedBitmap: StateFlow<Bitmap?> = _capturedBitmap.asStateFlow()

    enum class CaptureState {
        IDLE,
        INITIALIZING,
        CAPTURING,
        PROCESSING,
        COMPLETE,
        ERROR
    }

    suspend fun startCaptureSequence(outputDir: File): Result<Map<LightSpectrum, File>> {
        return try {
            _captureState.value = CaptureState.INITIALIZING

            if (!outputDir.exists()) outputDir.mkdirs()

            val cameraId = cameraManager.findBestCamera()
            if (cameraId != null) {
                try {
                    cameraManager.openCamera(cameraId)
                } catch (e: Exception) {
                    Timber.w(e, "Camera open failed, proceeding with mock frames")
                }
            }

            val frames = mutableMapOf<LightSpectrum, File>()
            val sequence = LightSpectrum.CAPTURE_SEQUENCE.mapIndexed { index, spectrum ->
                CapturePhase(index, spectrum, spectrum.settlingWindowMs)
            }

            _captureState.value = CaptureState.CAPTURING

            for ((phaseIndex, phase) in sequence.withIndex()) {
                _currentPhase.value = phase.copy(status = CapturePhase.Status.ACTIVATING)

                spectrumController.activate(phase.spectrum)
                _currentPhase.value = phase.copy(status = CapturePhase.Status.SETTLING)

                kotlinx.coroutines.delay(phase.settlingWindowMs)
                _currentPhase.value = phase.copy(status = CapturePhase.Status.CAPTURING)

                val frameFile = File(outputDir, "frame_${phase.spectrum.name}.jpg")

                val bitmap = try {
                    if (cameraManager.isReady) {
                        cameraManager.captureFrame()
                    } else {
                        null
                    }
                } catch (e: Exception) {
                    Timber.w(e, "Camera capture failed for ${phase.spectrum.name}, using placeholder")
                    null
                }

                if (bitmap != null) {
                    ImageUtils.saveBitmap(bitmap, frameFile)
                    _capturedBitmap.value = bitmap
                } else {
                    createPlaceholderBitmap(frameFile, phase.spectrum)
                }

                frames[phase.spectrum] = frameFile
                _currentPhase.value = phase.copy(status = CapturePhase.Status.COMPLETE)
            }

            spectrumController.activate(LightSpectrum.OFF)
            cameraManager.closeCamera()
            _capturedFrames.value = frames
            _captureState.value = CaptureState.COMPLETE

            Timber.i("Capture sequence complete: ${frames.size} frames")
            Result.success(frames)
        } catch (e: Exception) {
            _captureState.value = CaptureState.ERROR
            cameraManager.closeCamera()
            Timber.e(e, "Capture sequence failed")
            Result.failure(e)
        }
    }

    private fun createPlaceholderBitmap(file: File, spectrum: LightSpectrum) {
        val bitmap = Bitmap.createBitmap(1920, 1080, Bitmap.Config.ARGB_8888)
        val canvas = android.graphics.Canvas(bitmap)
        canvas.drawColor(android.graphics.Color.DKGRAY)
        val paint = android.graphics.Paint().apply {
            color = android.graphics.Color.WHITE
            textSize = 48f
            textAlign = android.graphics.Paint.Align.CENTER
        }
        canvas.drawText(spectrum.displayName, 960f, 540f, paint)
        ImageUtils.saveBitmap(bitmap, file)
    }

    fun reset() {
        _captureState.value = CaptureState.IDLE
        _capturedFrames.value = emptyMap()
        _currentPhase.value = null
        _capturedBitmap.value = null
    }
}
