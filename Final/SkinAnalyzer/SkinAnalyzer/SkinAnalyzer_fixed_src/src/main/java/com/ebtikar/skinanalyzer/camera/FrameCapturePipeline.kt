package com.ebtikar.skinanalyzer.camera

import android.graphics.Bitmap
import com.ebtikar.skinanalyzer.ai.FaceLandmarkDetector
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.util.ImageUtils
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
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
    private val cameraManager: USBCameraManager,
    private val serialBusManager: SerialBusManager,
    private val faceDetector: FaceLandmarkDetector,
    private val fiseGpioController: FiseGpioController
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
        WAITING_FOR_FACE,
        CAPTURING,
        PROCESSING,
        COMPLETE,
        ERROR
    }

    suspend fun startCaptureSequence(
        outputDir: File,
        spectra: List<LightSpectrum> = LightSpectrum.ALL_SPECTRA,
        previewSurface: android.view.Surface? = null,
        onStateChanged: (CaptureState) -> Unit = {},
        onProgress: suspend (CapturePhase, step: Int, totalSteps: Int) -> Unit = { _, _, _ -> }
    ): Result<Map<LightSpectrum, File>> {
        return try {
            _captureState.value = CaptureState.INITIALIZING
            onStateChanged(CaptureState.INITIALIZING)

            if (!outputDir.exists()) outputDir.mkdirs()

            val cameraId = cameraManager.findBestCamera()
            val cameraReady = if (cameraId != null) {
                try {
                    cameraManager.openCamera(cameraId, previewSurface)
                    true
                } catch (e: Exception) {
                    Timber.w(e, "Camera open failed")
                    false
                }
            } else {
                false
            }

            if (!cameraReady) {
                return Result.failure(IllegalStateException("Camera not ready"))
            }

            // Wait for human face before starting capture (with timeout)
            _captureState.value = CaptureState.WAITING_FOR_FACE
            onStateChanged(CaptureState.WAITING_FOR_FACE)
            var faceDetected = false
            var faceAttempts = 0
            val maxFaceAttempts = 5
            Timber.i("Waiting for face detection...")

            while (!faceDetected && faceAttempts < maxFaceAttempts) {
                try {
                    delay(2000)
                    val bitmap = cameraManager.captureFrame()
                    if (bitmap == null) {
                        Timber.w("captureFrame returned null, retrying...")
                        faceAttempts++
                        continue
                    }
                    val faces = faceDetector.detectFaces(bitmap)
                    if (faces.isNotEmpty()) {
                        faceDetected = true
                        Timber.i("Face detected, starting analysis")
                    } else {
                        faceAttempts++
                        Timber.d("No face detected (attempt $faceAttempts/$maxFaceAttempts)")
                    }
                } catch (e: Exception) {
                    faceAttempts++
                    Timber.w(e, "Face detection attempt $faceAttempts/$maxFaceAttempts failed")
                    delay(3000)
                }
            }

            Timber.i("Face detection result: detected=$faceDetected, attempts=$faceAttempts")

            _captureState.value = CaptureState.CAPTURING
            onStateChanged(CaptureState.CAPTURING)
            val frames = mutableMapOf<LightSpectrum, File>()
            val serialConnected = serialBusManager.isConnected
            val gpioAvailable = fiseGpioController.isAvailable
            val ledConnected = serialConnected || gpioAvailable

            Timber.i("Hardware check: serialConnected=$serialConnected, gpioAvailable=$gpioAvailable, selinux=${fiseGpioController.selinuxEnforcing}, ledConnected=$ledConnected")

            if (ledConnected) {
                if (gpioAvailable) {
                    Timber.i("Using FISE GPIO direct write - if SELinux=enforcing, writes may fail silently!")
                }
                if (serialConnected) {
                    Timber.i("Serial bus reports connected (port open) - using serial path")
                }
                Timber.i("LED hardware connected - using real spectral lighting")
                captureWithRealLeds(spectra, outputDir, frames, onProgress)
            } else {
                Timber.w("LED not connected - no FISE GPIO and no serial. Using digital spectral processing (torch/ambient)")
                captureWithDigitalSpectra(spectra, outputDir, frames, onProgress)
            }

            _capturedFrames.value = frames
            _captureState.value = CaptureState.COMPLETE

            Timber.i("Capture sequence complete: ${frames.size} frames (LED: $ledConnected)")
            Result.success(frames)
        } catch (e: Exception) {
            _captureState.value = CaptureState.ERROR
            Timber.e(e, "Capture sequence failed")
            Result.failure(e)
        } finally {
            if (fiseGpioController.isAvailable) {
                fiseGpioController.turnAllOff()
            }
            spectrumController.activate(LightSpectrum.OFF)
            cameraManager.closeCamera()
        }
    }

    private suspend fun captureWithRealLeds(
        spectra: List<LightSpectrum>,
        outputDir: File,
        frames: MutableMap<LightSpectrum, File>,
        onProgress: suspend (CapturePhase, step: Int, totalSteps: Int) -> Unit
    ) {
        val totalSteps = spectra.size + 1
        _captureState.value = CaptureState.CAPTURING

        Timber.i("captureWithRealLeds: serialBusManager.isConnected=${serialBusManager.isConnected}")

        val allPhase = CapturePhase(-1, LightSpectrum.ALL, 100, CapturePhase.Status.ACTIVATING)
        _currentPhase.value = allPhase
        onProgress(allPhase, 1, totalSteps)
        val allResult = spectrumController.activate(LightSpectrum.ALL)
        if (allResult.isFailure) {
            Timber.w("ALL lights activation failed: ${allResult.exceptionOrNull()?.message}")
        }
        _currentPhase.value = allPhase.copy(status = CapturePhase.Status.COMPLETE)
        onProgress(allPhase.copy(status = CapturePhase.Status.COMPLETE), 1, totalSteps)
        delay(300)

        for ((stepIndex, spectrum) in spectra.withIndex()) {
            val step = stepIndex + 2
            val phase = CapturePhase(stepIndex, spectrum, spectrum.settlingWindowMs)

            _currentPhase.value = phase.copy(status = CapturePhase.Status.ACTIVATING)
            onProgress(phase.copy(status = CapturePhase.Status.ACTIVATING), step, totalSteps)

            spectrumController.activate(spectrum)
            kotlinx.coroutines.delay(spectrum.settlingWindowMs)

            _currentPhase.value = phase.copy(status = CapturePhase.Status.SETTLING)
            onProgress(phase.copy(status = CapturePhase.Status.SETTLING), step, totalSteps)
            kotlinx.coroutines.delay(400)

            _currentPhase.value = phase.copy(status = CapturePhase.Status.CAPTURING)
            onProgress(phase.copy(status = CapturePhase.Status.CAPTURING), step, totalSteps)

            val frameFile = File(outputDir, "frame_${spectrum.name}.jpg")
            val bitmap = try {
                if (cameraManager.isReady) cameraManager.captureFrame() else null
            } catch (e: Exception) {
                Timber.w(e, "Camera capture failed for ${spectrum.name}")
                null
            }

            if (bitmap != null) {
                ImageUtils.saveBitmap(bitmap, frameFile)
                _capturedBitmap.value = bitmap
            } else {
                createPlaceholderBitmap(frameFile, spectrum)
            }
            frames[spectrum] = frameFile

            _currentPhase.value = phase.copy(status = CapturePhase.Status.COMPLETE)
            onProgress(phase.copy(status = CapturePhase.Status.COMPLETE), step, totalSteps)

            if (stepIndex < spectra.size - 1) {
                kotlinx.coroutines.delay(1500)
            }
        }
    }

    private suspend fun captureWithDigitalSpectra(
        spectra: List<LightSpectrum>,
        outputDir: File,
        frames: MutableMap<LightSpectrum, File>,
        onProgress: suspend (CapturePhase, step: Int, totalSteps: Int) -> Unit
    ) {
        val totalSteps = spectra.size
        _captureState.value = CaptureState.CAPTURING

        val initPhase = CapturePhase(0, LightSpectrum.WHITE, 40, CapturePhase.Status.CAPTURING)
        _currentPhase.value = initPhase
        onProgress(initPhase, 0, totalSteps)

        // Enable torch/flash for base illumination if available
        val torchOn = cameraManager.hasFlash().also { enabled ->
            if (enabled) {
                cameraManager.setTorchMode(true)
                delay(400) // wait for torch to stabilize
            }
        }

        val baseFile = File(outputDir, "frame_base.jpg")
        val baseBitmap = try {
            if (cameraManager.isReady) cameraManager.captureFrame() else null
        } catch (e: Exception) {
            Timber.w(e, "Base photo capture failed")
            null
        }

        // Turn off torch after capture
        if (torchOn) {
            cameraManager.setTorchMode(false)
        }

        if (baseBitmap == null) {
            Timber.w("No base photo captured, falling back to placeholders")
            for (spectrum in spectra) {
                val frameFile = File(outputDir, "frame_${spectrum.name}.jpg")
                createPlaceholderBitmap(frameFile, spectrum)
                frames[spectrum] = frameFile
            }
            return
        }

        ImageUtils.saveBitmap(baseBitmap, baseFile)
        _capturedBitmap.value = baseBitmap
        Timber.i("Base photo captured: ${baseBitmap.width}x${baseBitmap.height}")

        _captureState.value = CaptureState.PROCESSING

        for ((stepIndex, spectrum) in spectra.withIndex()) {
            val step = stepIndex + 1
            val phase = CapturePhase(stepIndex, spectrum, 0, CapturePhase.Status.PROCESSING)
            _currentPhase.value = phase
            onProgress(phase, step, totalSteps)

            val frameFile = File(outputDir, "frame_${spectrum.name}.jpg")
            val spectralBitmap = ImageUtils.applySpectralFilter(baseBitmap, spectrum.name)
            ImageUtils.saveBitmap(spectralBitmap, frameFile)
            frames[spectrum] = frameFile

            if (stepIndex < spectra.size - 1) {
                val completePhase = phase.copy(status = CapturePhase.Status.COMPLETE)
                _currentPhase.value = completePhase
                onProgress(completePhase, step, totalSteps)
                kotlinx.coroutines.delay(1500)
            }
        }

        val finalPhase = CapturePhase(spectra.size, LightSpectrum.OFF, 0, CapturePhase.Status.COMPLETE)
        _currentPhase.value = finalPhase
        onProgress(finalPhase, totalSteps, totalSteps)
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
