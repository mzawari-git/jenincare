package com.ebtikar.skinanalyzer.camera

import android.graphics.Bitmap
import com.ebtikar.skinanalyzer.ai.CVUtils
import com.ebtikar.skinanalyzer.ai.FaceLandmarkDetector
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.util.ImageUtils
import com.ebtikar.skinanalyzer.util.PreferencesManager
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
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
    private val fiseGpioController: FiseGpioController,
    private val preferencesManager: PreferencesManager
) {

    private val _captureState = MutableStateFlow(CaptureState.IDLE)
    val captureState: StateFlow<CaptureState> = _captureState.asStateFlow()

    private val _capturedFrames = MutableStateFlow<Map<LightSpectrum, File>>(emptyMap())
    val capturedFrames: StateFlow<Map<LightSpectrum, File>> = _capturedFrames.asStateFlow()

    private val _currentPhase = MutableStateFlow<CapturePhase?>(null)
    val currentPhase: MutableStateFlow<CapturePhase?> = _currentPhase

    private val _capturedBitmap = MutableStateFlow<Bitmap?>(null)
    val capturedBitmap: StateFlow<Bitmap?> = _capturedBitmap.asStateFlow()

    private val _capturedFrameSequence = MutableStateFlow<List<Pair<LightSpectrum, File>>>(emptyList())
    val capturedFrameSequence: StateFlow<List<Pair<LightSpectrum, File>>> = _capturedFrameSequence.asStateFlow()

    private val _positionScore = MutableStateFlow(0)
    val positionScore: StateFlow<Int> = _positionScore.asStateFlow()

    private val _positionMessage = MutableStateFlow("")
    val positionMessage: StateFlow<String> = _positionMessage.asStateFlow()

    private val _skinCenterX = MutableStateFlow(0.5f)
    val skinCenterX: StateFlow<Float> = _skinCenterX.asStateFlow()

    private val _skinCenterY = MutableStateFlow(0.5f)
    val skinCenterY: StateFlow<Float> = _skinCenterY.asStateFlow()

    private val _countdownValue = MutableStateFlow(0)
    val countdownValue: StateFlow<Int> = _countdownValue.asStateFlow()

    private val _captureFlash = MutableStateFlow(false)
    val captureFlash: StateFlow<Boolean> = _captureFlash.asStateFlow()

    private val _faceGuideVisible = MutableStateFlow(false)
    val faceGuideVisible: StateFlow<Boolean> = _faceGuideVisible.asStateFlow()

    enum class CaptureState {
        IDLE,
        INITIALIZING,
        WAITING_FOR_FACE,
        COUNTDOWN,
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
            _capturedFrameSequence.value = emptyList()
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

            if (!fiseGpioController.isAvailable) {
                Timber.w("GPIO not available. Trying runtime re-init...")
                _positionMessage.value = "⚠️ جاري محاولة توصيل أضواء التشخيص..."
                val gpioOk = fiseGpioController.recheckAvailability()
                Timber.i("GPIO runtime re-init result: available=$gpioOk, selinux=${fiseGpioController.selinuxEnforcing}")
                if (gpioOk) {
                    _positionMessage.value = "تم توصيل أضواء التشخيص ✓"
                } else {
                    _positionMessage.value = "⚠️ أضواء التشخيص غير متصلة — قم بتشغيل setup_gpio.ps1 عبر ADB"
                    Timber.e("GPIO unavailable after runtime re-init. Scan will proceed without LED hardware.")
                }
            }

            _captureState.value = CaptureState.WAITING_FOR_FACE
            onStateChanged(CaptureState.WAITING_FOR_FACE)

            spectrumController.activate(LightSpectrum.WHITE)
            delay(500) // Increased from 100ms — camera needs 300-500ms auto-exposure adjustment under bright LED

            val faceValidationEnabled = preferencesManager.faceValidationEnabledFlow.first()
            val faceThreshold = preferencesManager.faceValidationThresholdFlow.first()
            Timber.i("Face validation: enabled=$faceValidationEnabled, threshold=$faceThreshold")

            if (faceValidationEnabled) {
                Timber.i("Waiting for face position validation (HSV skin detection)...")
                _positionScore.value = 0
                _faceGuideVisible.value = true
                _positionMessage.value = "ضع وجهك في منتصف الإطار"
                var positionValid = false
                var positionAttempts = 0
                val maxPositionAttempts = 25
                var consecutiveValidCount = 0
                val requiredConsecutive = 1
                var lastScore = 0

                while (!positionValid && positionAttempts < maxPositionAttempts) {
                    try {
                        val rawBitmap = cameraManager.captureFrame()
                        if (rawBitmap == null) {
                            Timber.w("captureFrame returned null during position validation")
                            positionAttempts++
                            delay(100)
                            continue
                        }
                        if (rawBitmap.isRecycled) {
                            Timber.w("captureFrame returned recycled bitmap")
                            positionAttempts++
                            delay(100)
                            continue
                        }
                        val evalBitmap = if (rawBitmap.width > 960 || rawBitmap.height > 960) {
                            val scale = 960f / maxOf(rawBitmap.width, rawBitmap.height)
                            Bitmap.createScaledBitmap(rawBitmap,
                                (rawBitmap.width * scale).toInt().coerceAtLeast(1),
                                (rawBitmap.height * scale).toInt().coerceAtLeast(1), true)
                        } else rawBitmap

                        val normalizedBitmap = CVUtils.normalizeBrightness(evalBitmap)
                        val result = CVUtils.evaluateFacePosition(normalizedBitmap, faceThreshold)
                        if (normalizedBitmap !== evalBitmap) normalizedBitmap.recycle()
                        if (evalBitmap !== rawBitmap) evalBitmap.recycle()
                        _positionScore.value = result.score
                        lastScore = result.score
                        _skinCenterX.value = result.skinRegionCenterX
                        _skinCenterY.value = result.skinRegionCenterY
                        _positionMessage.value = when (result.messageKey) {
                            "face_not_visible" -> "لم يتم الكشف عن الوجه — تأكد من وجودك أمام الكاميرا"
                            "face_too_low" -> "ارفع رأسك لأعلى — يجب ظهور الجبهة"
                            "face_too_far" -> "اقترب من الكاميرا أكثر"
                            "face_too_close" -> "ابتعد قليلاً عن الكاميرا"
                            "face_off_center" -> "تمركز في منتصف الإطار"
                            "adjust_position" -> "اضبط وضعيتك — ارفع رأسك قليلاً"
                            else -> "تم الكشف عن الوجه — جاري القراءة..."
                        }
                        rawBitmap.recycle()

                        if (result.isValid) {
                            consecutiveValidCount++
                            if (consecutiveValidCount >= requiredConsecutive) {
                                positionValid = true
                                _positionMessage.value = "تم التحقق من وضع الوجه ✓ — جاري قراءة ملامح الوجه..."
                                Timber.i("Face position validated: score=${result.score}, coverage=${result.coverage}, topRatio=${result.topRatio}, consecutive=$consecutiveValidCount")
                            } else {
                                _positionMessage.value = "جاري قراءة الوجه... ($consecutiveValidCount/$requiredConsecutive)"
                                Timber.d("Face valid but need $requiredConsecutive consecutive: $consecutiveValidCount/$requiredConsecutive (score=${result.score})")
                                delay(200)
                            }
                        } else {
                            consecutiveValidCount = 0
                            positionAttempts++
                            Timber.d("Face position: score=${result.score}, ${result.messageKey} (attempt $positionAttempts/$maxPositionAttempts)")

                            if (positionAttempts % 5 == 0 && positionAttempts < maxPositionAttempts) {
                                Timber.w("HSV failed $positionAttempts attempts — trying ML Kit intermediate fallback")
                                _positionMessage.value = "جاري التحقق بواسطة الذكاء الاصطناعي..."
                                delay(100)
                                val mlFace = tryMlKitFallback()
                                if (mlFace) {
                                    positionValid = true
                                    _positionMessage.value = "تم التحقق بواسطة الذكاء الاصطناعي ✓ — جاري قراءة ملامح الوجه..."
                                    Timber.i("Face validated via ML Kit intermediate fallback (attempt $positionAttempts)")
                                }
                            } else {
                                delay(120)
                            }
                        }
                    } catch (e: Exception) {
                        Timber.w(e, "Position validation attempt failed, retrying...")
                        delay(400)
                    }
                }

                if (!positionValid) {
                    Timber.w("Face HSV failed after $positionAttempts attempts — trying ML Kit as fallback")
                    _positionMessage.value = "جاري التحقق بواسطة الذكاء الاصطناعي..."
                    delay(200)
                    val mlFace = tryMlKitFallback()
                    if (mlFace) {
                        positionValid = true
                        _positionMessage.value = "تم التحقق بواسطة الذكاء الاصطناعي ✓ — جاري قراءة ملامح الوجه..."
                        Timber.i("Face validated via ML Kit fallback")
                    }
                }

                if (!positionValid) {
                    _faceGuideVisible.value = false
                    Timber.w("Face not validated: HSV score never >= $faceThreshold and ML Kit also failed")
                    return Result.failure(IllegalStateException("لم يتم الكشف عن الوجه. تأكد من وجودك أمام الكاميرا في إضاءة جيدة"))
                }
            } else {
                Timber.i("Face validation disabled — skipping position check")
            }

            _positionMessage.value = "تم الكشف عن الوجه ✓ — جاري قراءة ملامح الوجه..."
            _faceGuideVisible.value = true
            delay(1500)
            _positionMessage.value = "تحليل ملامح الوجه بالذكاء الاصطناعي..."
            delay(1000)
            _positionMessage.value = "تم تحليل الوجه — جاري البدء بالمسح الضوئي..."

            spectrumController.activate(LightSpectrum.OFF)

            _captureState.value = CaptureState.CAPTURING
            onStateChanged(CaptureState.CAPTURING)
            val frames = mutableMapOf<LightSpectrum, File>()
            val serialConnected = serialBusManager.isConnected
            val gpioAvailable = fiseGpioController.isAvailable
            val ledConnected = serialConnected || gpioAvailable

            Timber.i("Hardware check: serialConnected=$serialConnected, gpioAvailable=$gpioAvailable, selinux=${fiseGpioController.selinuxEnforcing}, ledConnected=$ledConnected")

            if (!ledConnected) {
                Timber.e("No LED hardware detected (GPIO unavailable, serial disconnected) — LEDs will NOT turn on during scan")
                _positionMessage.value = "⚠️ لا توجد أضواء تشخيص — الصور ستكون بدون إضاءة. قم بتشغيل setup_gpio.ps1 أو توصيل USB"
            }
            if (gpioAvailable) {
                Timber.i("Using FISE GPIO direct write")
            }
            if (serialConnected) {
                Timber.i("Serial bus connected - using serial path")
            }
            captureWithRealLeds(spectra, outputDir, frames, onProgress)

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
            cameraManager.closeCameraSuspend()
        }
    }

    private suspend fun captureWithRealLeds(
        spectra: List<LightSpectrum>,
        outputDir: File,
        frames: MutableMap<LightSpectrum, File>,
        onProgress: suspend (CapturePhase, step: Int, totalSteps: Int) -> Unit
    ) {
        val totalSteps = spectra.size
        _captureState.value = CaptureState.CAPTURING

        Timber.i("captureWithRealLeds: serialBusManager.isConnected=${serialBusManager.isConnected}")

        for ((stepIndex, spectrum) in spectra.withIndex()) {
            val step = stepIndex + 1
            val phase = CapturePhase(stepIndex, spectrum, spectrum.settlingWindowMs)

            _currentPhase.value = phase.copy(status = CapturePhase.Status.ACTIVATING)
            onProgress(phase.copy(status = CapturePhase.Status.ACTIVATING), step, totalSteps)

            spectrumController.activate(spectrum)

            val extraSettle = when (spectrum) {
                LightSpectrum.UV365, LightSpectrum.WOODS -> 400L
                LightSpectrum.POL_P, LightSpectrum.POL_N -> 200L
                LightSpectrum.BLUE, LightSpectrum.RED, LightSpectrum.BROWN -> 300L
                else -> 100L
            }
            kotlinx.coroutines.delay(spectrum.settlingWindowMs + extraSettle)

            _currentPhase.value = phase.copy(status = CapturePhase.Status.SETTLING)
            onProgress(phase.copy(status = CapturePhase.Status.SETTLING), step, totalSteps)
            kotlinx.coroutines.delay(300)

            _captureState.value = CaptureState.COUNTDOWN
            for (count in 3 downTo 1) {
                _countdownValue.value = count
                kotlinx.coroutines.delay(400)
            }
            _countdownValue.value = 0

            _currentPhase.value = phase.copy(status = CapturePhase.Status.CAPTURING)
            onProgress(phase.copy(status = CapturePhase.Status.CAPTURING), step, totalSteps)

            val frameFile = File(outputDir, "frame_${spectrum.name}.jpg")
            var capturedSuccessfully = false
            val maxRetries = 3

            for (retry in 0 until maxRetries) {
                val bitmap = try {
                    if (cameraManager.isReady) cameraManager.captureFrame(spectrum) else null
                } catch (e: Exception) {
                    Timber.w(e, "Camera capture failed for ${spectrum.name} (retry ${retry + 1})")
                    null
                }

                if (bitmap != null && !bitmap.isRecycled && bitmap.width > 0 && bitmap.height > 0) {
                    val brightness = CVUtils.computePixelStats(bitmap).brightness
                    if (brightness < 5f && retry < maxRetries - 1) {
                        Timber.w("Frame too dark (${brightness}) for ${spectrum.name}, retrying...")
                        bitmap.recycle()
                        delay(300)
                        continue
                    }

                    _captureFlash.value = true
                    delay(80)
                    _captureFlash.value = false

                    val rawFile = File(outputDir, "frame_${spectrum.name}_raw.jpg")
                    ImageUtils.saveBitmap(bitmap, rawFile)

                    val filtered = ImageUtils.applySpectralFilter(bitmap, spectrum.name)
                    val saved = ImageUtils.saveBitmap(filtered, frameFile)
                    if (filtered !== bitmap) filtered.recycle()
                    if (!saved) {
                        Timber.e("Failed to save frame ${spectrum.name} to ${frameFile.absolutePath}")
                    }
                    _capturedBitmap.value?.let { if (!it.isRecycled) it.recycle() }
                    _capturedBitmap.value = bitmap
                    capturedSuccessfully = true
                    break
                } else {
                    if (bitmap != null && !bitmap.isRecycled) bitmap.recycle()
                    if (retry < maxRetries - 1) {
                        Timber.w("Capture attempt ${retry + 1} failed for ${spectrum.name}, retrying...")
                        delay(200)
                    }
                }
            }

            if (!capturedSuccessfully) {
                Timber.w("All $maxRetries capture attempts failed for ${spectrum.name} — using placeholder")
                createPlaceholderBitmap(frameFile, spectrum)
            }

            frames[spectrum] = frameFile
            _capturedFrameSequence.value = _capturedFrameSequence.value + (spectrum to frameFile)
            Timber.d("capturedFrameSequence emitted: ${_capturedFrameSequence.value.size} frames, last=${spectrum.name} -> ${frameFile.name}")

            _currentPhase.value = phase.copy(status = CapturePhase.Status.COMPLETE)
            onProgress(phase.copy(status = CapturePhase.Status.COMPLETE), step, totalSteps)

            if (stepIndex < spectra.size - 1) {
                kotlinx.coroutines.delay(400)
            }
        }

        _faceGuideVisible.value = false
    }
    private fun createPlaceholderBitmap(file: File, spectrum: LightSpectrum) {
        val bitmap = Bitmap.createBitmap(1920, 1080, Bitmap.Config.ARGB_8888)
        try {
            val canvas = android.graphics.Canvas(bitmap)
            canvas.drawColor(android.graphics.Color.DKGRAY)
            val paint = android.graphics.Paint().apply {
                color = android.graphics.Color.WHITE
                textSize = 48f
                textAlign = android.graphics.Paint.Align.CENTER
            }
            canvas.drawText(spectrum.displayName, 960f, 540f, paint)
            val saved = ImageUtils.saveBitmap(bitmap, file)
            if (!saved) {
                Timber.e("Failed to save placeholder bitmap for ${spectrum.name} to ${file.absolutePath}")
            }
        } finally {
            bitmap.recycle()
        }
    }

    private suspend fun tryMlKitFallback(): Boolean {
        return try {
            val frame = cameraManager.captureFrame() ?: return false
            val checkBmp = if (frame.width > 640 || frame.height > 640) {
                val scale = 640f / maxOf(frame.width, frame.height)
                Bitmap.createScaledBitmap(frame,
                    (frame.width * scale).toInt().coerceAtLeast(1),
                    (frame.height * scale).toInt().coerceAtLeast(1), true)
            } else frame
            val faces = faceDetector.detectFaces(checkBmp)
            if (checkBmp !== frame) checkBmp.recycle()
            frame.recycle()
            Timber.d("ML Kit fallback: ${faces.size} face(s)")
            faces.isNotEmpty()
        } catch (e: Exception) {
            Timber.w(e, "ML Kit fallback failed")
            false
        }
    }

    fun reset() {
        _captureState.value = CaptureState.IDLE
        _capturedFrames.value = emptyMap()
        _capturedFrameSequence.value = emptyList()
        _currentPhase.value = null
        _capturedBitmap.value?.let { if (!it.isRecycled) it.recycle() }
        _capturedBitmap.value = null
        _positionScore.value = 0
        _positionMessage.value = ""
        _skinCenterX.value = 0.5f
        _skinCenterY.value = 0.5f
        _countdownValue.value = 0
        _captureFlash.value = false
        _faceGuideVisible.value = false
    }
}
