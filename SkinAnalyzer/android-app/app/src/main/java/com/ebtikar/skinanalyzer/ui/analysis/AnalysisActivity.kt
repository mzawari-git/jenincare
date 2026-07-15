package com.ebtikar.skinanalyzer.ui.analysis

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Matrix
import android.media.ExifInterface
import android.graphics.SurfaceTexture
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.provider.Settings
import com.ebtikar.skinanalyzer.ai.CVUtils
import java.io.File
import android.os.Bundle
import android.view.Surface
import android.view.TextureView
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.core.content.ContextCompat
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.BaseCameraActivity
import com.ebtikar.skinanalyzer.camera.CameraSettings
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.databinding.ActivityAnalysisBinding
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.ui.result.ResultActivity
import com.ebtikar.skinanalyzer.ui.scan.AnalysisMarker
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.VoiceGuideManager
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.guava.await
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class AnalysisActivity : BaseCameraActivity() {

    private val viewModel: AnalysisViewModel by viewModels()

    @Inject lateinit var cameraManager: USBCameraManager
    @Inject lateinit var capturePipeline: FrameCapturePipeline
    @Inject lateinit var preferencesManager: PreferencesManager
    @Inject lateinit var fiseGpioController: FiseGpioController
    @Inject lateinit var serialBusManager: SerialBusManager
    @Inject lateinit var voiceGuide: VoiceGuideManager

    private var isScanning = false
    private var analysisInitialized = false
    private var analysisSurface: Surface? = null  // Track surface for proper cleanup

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val diagnosisMode = intent.getStringExtra("diagnosis_mode") ?: Constants.DIAGNOSIS_ALL
        viewModel.setDiagnosisMode(diagnosisMode)
        voiceGuide.initialize()
        lifecycleScope.launch {
            voiceGuide.setEnabled(preferencesManager.voiceGuideFlow.first())
        }

        lifecycleScope.launch {
            val savedRotation = preferencesManager.cameraRotationFlow.first()
            val savedZoomProgress = preferencesManager.cameraZoomProgressFlow.first()
            val zoomRatio = 1.0f + (savedZoomProgress / 120f) * (cameraManager.maxZoom - 1.0f)
            cameraManager.cameraSettings = CameraSettings(
                userRotationOffset = savedRotation,
                zoomRatio = zoomRatio
            )
        }

        setupUI()
        observeViewModel()

        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
            Timber.i("Camera permission not granted, requesting...")
            binding.tvScanInstruction.text = "مطلوب إذن الكاميرا للمسح"
            cameraPermissionLauncher.launch(Manifest.permission.CAMERA)
        } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R && !Environment.isExternalStorageManager()) {
            Timber.i("MANAGE_EXTERNAL_STORAGE not granted, requesting...")
            binding.tvScanInstruction.text = "مطلوب إذن التخزين لحفظ الصور"
            storagePermissionLauncher.launch(Intent(Settings.ACTION_MANAGE_APP_ALL_FILES_ACCESS_PERMISSION).apply {
                data = Uri.parse("package:$packageName")
            })
        } else {
            Timber.i("All permissions already granted")
            setupCameraPreview()
        }
    }

    private val cameraPermissionLauncher = registerForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
        if (granted) {
            Timber.i("Camera permission granted by user")
            binding.tvScanInstruction.text = "جاري تهيئة الكاميرا..."
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R && !Environment.isExternalStorageManager()) {
                storagePermissionLauncher.launch(Intent(Settings.ACTION_MANAGE_APP_ALL_FILES_ACCESS_PERMISSION).apply {
                    data = Uri.parse("package:$packageName")
                })
            } else {
                setupCameraPreview()
            }
        } else {
            Timber.w("Camera permission denied by user")
            binding.tvScanInstruction.text = "لا يمكن بدء المسح بدون إذن الكاميرا"
        }
    }

    private val storagePermissionLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R && Environment.isExternalStorageManager()) {
            Timber.i("MANAGE_EXTERNAL_STORAGE granted")
            setupCameraPreview()
        } else {
            Timber.w("MANAGE_EXTERNAL_STORAGE denied, using internal storage fallback")
            setupCameraPreview()
        }
    }

    private fun setupCameraPreview() {
        if (binding.cameraPreview.isAvailable) {
            Timber.i("TextureView surface already available, handling immediately")
            val surface = binding.cameraPreview.surfaceTexture
            if (surface != null) {
                handleSurfaceAvailable(surface, binding.cameraPreview.width, binding.cameraPreview.height)
            } else {
                Timber.w("isAvailable=true but surfaceTexture is null, setting listener")
                binding.cameraPreview.surfaceTextureListener = object : TextureView.SurfaceTextureListener {
                    override fun onSurfaceTextureAvailable(surface: SurfaceTexture, width: Int, height: Int) {
                        handleSurfaceAvailable(surface, width, height)
                    }
                    override fun onSurfaceTextureSizeChanged(surface: SurfaceTexture, width: Int, height: Int) {
                        binding.cameraPreview.post { cameraManager.rotateTextureView(binding.cameraPreview) }
                    }
                    override fun onSurfaceTextureDestroyed(surface: SurfaceTexture): Boolean = true
                    override fun onSurfaceTextureUpdated(surface: SurfaceTexture) {}
                }
            }
        } else {
            Timber.i("TextureView surface not yet available, setting listener")
            binding.cameraPreview.surfaceTextureListener = object : TextureView.SurfaceTextureListener {
                override fun onSurfaceTextureAvailable(surface: SurfaceTexture, width: Int, height: Int) {
                    handleSurfaceAvailable(surface, width, height)
                }
                override fun onSurfaceTextureSizeChanged(surface: SurfaceTexture, width: Int, height: Int) {
                    binding.cameraPreview.post { cameraManager.rotateTextureView(binding.cameraPreview) }
                }
                override fun onSurfaceTextureDestroyed(surface: SurfaceTexture): Boolean = true
                override fun onSurfaceTextureUpdated(surface: SurfaceTexture) {}
            }
        }
    }

    private fun handleSurfaceAvailable(surface: SurfaceTexture, width: Int, height: Int) {
        if (isScanning && analysisInitialized) {
            Timber.i("Scan already in progress and analysis initialized, ignoring surface callback")
            return
        }
        isScanning = true
        analysisInitialized = false
        cameraManager.isDisplayPortrait = height > width
        cameraManager.setTextureView(binding.cameraPreview)
        Timber.i("TextureView available: ${width}x${height}, portrait=${height > width}")
        capturePipeline.reset()
        lifecycleScope.launch {
            releaseCameraX()
            delay(1500)
            val currentSurface = binding.cameraPreview.surfaceTexture
            if (currentSurface == null || binding.cameraPreview.width == 0) {
                Timber.w("TextureView surface no longer valid after delay, aborting analysis init")
                isScanning = false
                return@launch
            }
            val newSurface: Surface
            try {
                analysisSurface?.release()
                Surface(currentSurface).also { newSurface = it; analysisSurface = it }
            } catch (e: Exception) {
                Timber.w(e, "Failed to create Surface from texture")
                isScanning = false
                return@launch
            }
            analysisInitialized = true
            runOnUiThread {
                checkLightingHardware {
                    viewModel.initializeAnalysis(newSurface)
                }
            }
        }
        binding.cameraPreview.post { cameraManager.rotateTextureView(binding.cameraPreview) }
    }

    /**
     * Checks lighting hardware before starting the capture pipeline.
     * Always auto-continues — shows no blocking dialogs.
     */
    private fun checkLightingHardware(onReady: () -> Unit) {
        val gpioAvailable = fiseGpioController.isAvailable
        val serialAvailable = serialBusManager.isConnected

        Timber.i("Hardware pre-check: gpio=$gpioAvailable, serial=$serialAvailable")

        if (!gpioAvailable && !serialAvailable) {
            Timber.w("No lighting hardware detected — continuing without LEDs")
        } else if (gpioAvailable && !serialAvailable) {
            Timber.i("GPIO available, serial not connected — BLUE/RED/BROWN will be dark frames")
        } else {
            Timber.i("All lighting hardware ready (gpio=$gpioAvailable, serial=$serialAvailable)")
        }

        onReady()
    }

    private fun setupUI() {
        capturePipeline.reset()
        binding.btnCancelScan.setOnClickListener {
            android.app.AlertDialog.Builder(this)
                .setTitle("إلغاء الفحص")
                .setMessage("هل أنت متأكد من إلغاء الفحص الحالي؟ لن يتم حفظ النتائج.")
                .setPositiveButton("نعم، إلغاء") { _, _ ->
                    viewModel.abortAnalysis()
                    finish()
                }
                .setNegativeButton("استمرار", null)
                .show()
        }
        binding.btnViewReport.setOnClickListener {
            navigateToReport()
        }
        lifecycleScope.launch {
            applyOverlayPreferences()
        }
    }

    private suspend fun applyOverlayPreferences() {
        binding.digitalMesh.visibility = if (preferencesManager.showFaceMeshFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.medicalLens.visibility = if (preferencesManager.showMedicalLensFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.scanGrid.visibility = if (preferencesManager.showScanGridFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.scanRings.visibility = if (preferencesManager.showScanRingsFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.topCard.visibility = if (preferencesManager.showSpectralGraphFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.leftPanel.visibility = if (preferencesManager.showMedicalIndicatorsFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
        binding.rightPanel.visibility = if (preferencesManager.showScanDataPanelFlow.first()) android.view.View.VISIBLE else android.view.View.GONE
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.currentPhase.collect { phase ->
                        if (phase != null) {
                            updatePhaseUI(phase)
                            if (phase.status == CapturePhase.Status.FAILED) {
                                binding.tvScanInstruction.text = "⚠️ أضواء التشخيص غير متصلة — سيتم استخدام الإضاءة الرقمية فقط"
                            }
                        }
                    }
                }

                launch {
                    viewModel.progress.collect { progress ->
                        binding.progressScan.progress = progress
                    }
                }

                launch {
                    viewModel.currentStep.collect { step ->
                        binding.tvScanStep.text = "$step"
                    }
                }

                launch {
                    viewModel.totalSteps.collect { total ->
                        binding.tvScanTotal.text = " / $total"
                    }
                }

                launch {
                    viewModel.statusMessage.collect { message ->
                        binding.tvScanInstruction.text = message
                    }
                }

                launch {
                    viewModel.isComplete.collect { complete ->
                        if (complete) {
                            isScanning = false
                            analysisInitialized = false
                            binding.progressScan.progress = 100
                            binding.tvScanInstruction.text = getString(R.string.analysis_complete)
                            voiceGuide.speakAnalysisComplete()
                            binding.btnViewReport.visibility = android.view.View.VISIBLE
                            binding.medicalLens.visibility = android.view.View.GONE
                            binding.digitalMesh.visibility = android.view.View.GONE
                            binding.faceGridOverlay.visibility = android.view.View.GONE
                            binding.tvScanInstruction.visibility = android.view.View.GONE
                            binding.tvCurrentSpectrum.visibility = android.view.View.GONE
                            
                            showAnalysisMarkers()
                            val rv = viewModel.radarValues.value
                            val rl = viewModel.radarLabels.value
                            if (rv.isNotEmpty() && rl.isNotEmpty()) {
                                binding.radarChart.setData(rv, rl)
                                binding.radarChart.visibility = android.view.View.VISIBLE
                            }
                            binding.analysisHistory.visibility = android.view.View.VISIBLE
                        }
                    }
                }

                launch {
                    viewModel.error.collect { error ->
                        if (error != null) {
                            isScanning = false
                            analysisInitialized = false
                            binding.tvScanInstruction.text = error
                            binding.medicalLens.visibility = android.view.View.GONE
                        }
                    }
                }

                launch {
                    viewModel.currentSpectrumName.collect { name ->
                        binding.tvCurrentSpectrum.text = name
                    }
                }

                launch {
                    viewModel.ledTestProgress.collect { status ->
                        if (status.isNotEmpty()) {
                            binding.tvLedStatus.text = status
                            binding.tvLedStatus.visibility = android.view.View.VISIBLE
                        } else {
                            binding.tvLedStatus.visibility = android.view.View.GONE
                        }
                    }
                }

                launch {
                    viewModel.ledHardwareStatus.collect { hwStatus ->
                        if (hwStatus.isNotEmpty()) {
                            val gpioCount = hwStatus.values.count { it == "GPIO" }
                            val serialCount = hwStatus.values.count { it == "Serial" }
                            val unavailableCount = hwStatus.values.count { it == "Unavailable" }
                            val detail = buildString {
                                if (gpioCount > 0) append("GPIO:$gpioCount")
                                if (serialCount > 0) { if (isNotEmpty()) append(" | "); append("Serial:$serialCount") }
                                if (unavailableCount > 0) { if (isNotEmpty()) append(" | "); append("⚠:$unavailableCount") }
                            }
                            if (detail.isNotEmpty()) {
                                binding.tvLedStatus.text = detail
                                binding.tvLedStatus.visibility = android.view.View.VISIBLE
                            }
                        }
                    }
                }

                launch {
                    capturePipeline.capturedFrameSequence.collect { frames ->
                        populateThumbnails(frames)
                    }
                }

                launch {
                    capturePipeline.positionScore.collect { score ->
                        viewModel.updateTrackingData(score, score > 0)
                        binding.scanDataPanel.setConfidence(score.toFloat())
                        binding.rightPanel.setConfidence(score.toFloat())
                        binding.scanDataPanel.setTrackingAccuracy(score)
                        binding.rightPanel.setTrackingAccuracy(score)
                    }
                }

                launch {
                    capturePipeline.positionMessage.collect { msg ->
                        if (msg.isNotEmpty() && viewModel.error.value == null) {
                            binding.tvScanInstruction.text = msg
                            if (msg.contains("تم التحقق من وضع الوجه") || msg.contains("تم التحقق بواسطة الذكاء الاصطناعي")) {
                                voiceGuide.speakFaceDetected()
                            } else if (msg.contains("ارفع") || msg.contains("اقترب") || msg.contains("ابتعد") ||
                                msg.contains("تمركز") || msg.contains("لم يتم") || msg.contains("اضبط")) {
                                voiceGuide.speakPositionGuideDebounced(msg)
                            } else if (msg.contains("اكتمل التحليل")) {
                                voiceGuide.speakAnalysisComplete()
                            }
                        }
                    }
                }

                launch {
                    viewModel.trackingAccuracy.collect { accuracy ->
                        binding.scanDataPanel.setTrackingAccuracy(accuracy)
                        binding.rightPanel.setTrackingAccuracy(accuracy)
                    }
                }

                launch {
                    viewModel.faceDetected.collect { detected ->
                        binding.scanDataPanel.setFaceDetected(detected)
                        binding.rightPanel.setFaceDetected(detected)
                        binding.digitalMesh.visibility = if (detected) android.view.View.VISIBLE else android.view.View.INVISIBLE
                    }
                }

                launch {
                    viewModel.scanArea.collect { area ->
                        binding.scanDataPanel.setScanArea(area)
                        binding.rightPanel.setScanArea(area)
                    }
                }

                launch { 
                    viewModel.hydration.collect { 
                        binding.medicalIndicator.setHydration(it)
                        binding.leftPanel.setHydration(it)
                    } 
                }
                launch { 
                    viewModel.pores.collect { 
                        binding.medicalIndicator.setPores(it)
                        binding.leftPanel.setPores(it)
                    } 
                }
                launch { 
                    viewModel.redness.collect { 
                        binding.medicalIndicator.setRedness(it)
                        binding.leftPanel.setRedness(it)
                    } 
                }
                launch { 
                    viewModel.texture.collect { 
                        binding.medicalIndicator.setTexture(it)
                        binding.leftPanel.setTexture(it)
                    } 
                }
                launch { 
                    viewModel.acne.collect { 
                        binding.medicalIndicator.setAcne(it)
                        binding.leftPanel.setAcne(it)
                    } 
                }
                launch { 
                    viewModel.sensitivity.collect { 
                        binding.medicalIndicator.setSensitivity(it)
                        binding.leftPanel.setSensitivity(it)
                    } 
                }
                launch { 
                    viewModel.pigmentation.collect { 
                        binding.medicalIndicator.setPigmentation(it)
                        binding.leftPanel.setPigmentation(it)
                    } 
                }

                launch {
                    viewModel.recentScans.collect { entries ->
                        binding.analysisHistory.setHistory(entries)
                    }
                }

                launch {
                    viewModel.recommendations.collect { recs ->
                        binding.analysisHistory.setRecommendations(recs)
                        binding.tvRecommendation.text = recs.firstOrNull() ?: "بانتظار نتائج التحليل..."
                        binding.tvAiRecommendations.text = recs.joinToString("\n• ") { it }.let { if (it.isEmpty()) "سيتم عرض التوصيات بعد اكتمال التحليل" else "• $it" }
                    }
                }

                launch {
                    capturePipeline.skinCenterX.combine(capturePipeline.skinCenterY) { cx, cy -> Pair(cx, cy) }
                        .collect { (cx, cy) ->
                            val detected = cx in 0f..1f && cy in 0f..1f
                            binding.scanDataPanel.setFaceDetected(detected)
                            binding.rightPanel.setFaceDetected(detected)
                            binding.digitalMesh.updateFacePosition(cx, cy)
                            binding.faceGuideOverlay.setFacePosition(cx, cy)
                            binding.faceGridOverlay.setFacePosition(cx, cy)
                        }
                }

                launch {
                    capturePipeline.countdownValue.collect { value ->
                        if (value > 0) {
                            binding.countdownOverlay.visibility = android.view.View.VISIBLE
                            binding.tvCountdown.text = "$value"
                            binding.tvCountdown.alpha = 1f
                            voiceGuide.speakCountdown(value)
                            binding.tvCountdown.animate().scaleX(1.4f).scaleY(1.4f).setDuration(200).withEndAction {
                                binding.tvCountdown.animate().scaleX(1f).scaleY(1f).setDuration(150).start()
                            }.start()
                        } else {
                            binding.countdownOverlay.visibility = android.view.View.GONE
                        }
                    }
                }

                launch {
                    capturePipeline.captureFlash.collect { flash ->
                        if (flash) {
                            binding.captureFlashOverlay.visibility = android.view.View.VISIBLE
                            binding.captureFlashOverlay.alpha = 0.7f
                            binding.captureFlashOverlay.animate().alpha(0f).setDuration(200).start()
                        } else {
                            binding.captureFlashOverlay.visibility = android.view.View.GONE
                        }
                    }
                }

                launch {
                    capturePipeline.faceGuideVisible.collect { visible ->
                        binding.faceGuideOverlay.visibility = if (visible) android.view.View.VISIBLE else android.view.View.GONE
                        binding.faceGridOverlay.visibility = if (visible) android.view.View.VISIBLE else android.view.View.GONE
                    }
                }
            }
        }
    }

    private fun populateThumbnails(frames: List<Pair<LightSpectrum, File>>) {
        Timber.d("populateThumbnails called with ${frames.size} frames")
        if (frames.isEmpty()) {
            binding.containerAnalysisThumbnails.visibility = android.view.View.GONE
            binding.cardThumbnails.visibility = android.view.View.GONE
            Timber.d("populateThumbnails: frames empty, hiding container")
            return
        }
        binding.containerAnalysisThumbnails.visibility = android.view.View.VISIBLE
        binding.cardThumbnails.visibility = android.view.View.VISIBLE
        binding.containerAnalysisThumbnails.removeAllViews()
        Timber.d("populateThumbnails: container visible, building ${frames.size} thumbnails")

        val density = resources.displayMetrics.density
        val size = (48 * density).toInt()
        val margin = (4 * density).toInt()

        lifecycleScope.launch {
            val decodedFrames = withContext(Dispatchers.IO) {
                frames.map { (spectrum, file) ->
                    spectrum to try { loadBitmapWithRotation(file) } catch (_: Exception) { null }
                }
            }
            for ((spectrum, bitmap) in decodedFrames) {
                val card = layoutInflater.inflate(R.layout.item_spectrum_thumbnail, null) as ViewGroup
                val imageView = card.findViewById<ImageView>(R.id.ivSpectrumThumb)
                val label = card.findViewById<TextView>(R.id.tvSpectrumLabel)

                label.text = spectrum.displayNameAr
                label.setTextColor(android.graphics.Color.WHITE)
                label.setBackgroundColor(android.graphics.Color.parseColor(spectrum.colorHex))

                if (bitmap != null) {
                    imageView.setImageBitmap(bitmap)
                } else {
                    imageView.setBackgroundColor(android.graphics.Color.DKGRAY)
                }

                val lp = LinearLayout.LayoutParams(size, size)
                lp.marginEnd = margin
                binding.containerAnalysisThumbnails.addView(card, lp)
            }
        }
    }

    private fun updatePhaseUI(phase: CapturePhase) {
        binding.tvCurrentSpectrum.text = phase.spectrum.displayNameAr

        val statusText = when (phase.status) {
            CapturePhase.Status.ACTIVATING -> "جاري تفعيل ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.SETTLING -> "ثبت الإضاءة — ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.CAPTURING -> {
                voiceGuide.speakCaptureReady()
                "جاري التقاط الصورة — ${phase.spectrum.displayNameAr}..."
            }
            CapturePhase.Status.PROCESSING -> "معالجة ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.COMPLETE -> "تم — ${phase.spectrum.displayNameAr} ✓"
            CapturePhase.Status.FAILED -> "فشل ${phase.spectrum.displayNameAr} — سيتم المحاولة التالية"
            CapturePhase.Status.PENDING -> ""
        }
        binding.tvScanStatus.setText(statusText)

        val spectrumColor = try {
            android.graphics.Color.parseColor(phase.spectrum.colorHex)
        } catch (_: Exception) { android.graphics.Color.WHITE }
        binding.tvCurrentSpectrum.setTextColor(spectrumColor)
    }

    private fun navigateToReport() {
        val intent = Intent(this, ResultActivity::class.java).apply {
            putExtra("report_id", viewModel.getReportId())
        }
        startActivity(intent)
        finish()
    }

    private fun loadBitmapWithRotation(file: File): Bitmap? {
        val bitmap = CVUtils.decodeSampled(file, 640) ?: return null
        if (bitmap.isRecycled) { Timber.w("loadBitmapWithRotation: decoded bitmap already recycled"); return null }
        Timber.d("loadBitmapWithRotation: decoded ${bitmap.width}x${bitmap.height} from ${file.name}")
        try {
            val exif = ExifInterface(file.absolutePath)
            val orientation = exif.getAttributeInt(
                ExifInterface.TAG_ORIENTATION,
                ExifInterface.ORIENTATION_NORMAL
            )
            val degree = when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> 90f
                ExifInterface.ORIENTATION_ROTATE_180 -> 180f
                ExifInterface.ORIENTATION_ROTATE_270 -> 270f
                else -> 0f
            }
            if (degree != 0f) {
                val matrix = Matrix().apply { postRotate(degree) }
                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                if (rotated !== bitmap) bitmap.recycle()
                return rotated
            }
            if (bitmap.width > bitmap.height) {
                val matrix = Matrix().apply { postRotate(270f) }
                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                if (rotated !== bitmap) bitmap.recycle()
                return rotated
            }
        } catch (_: Throwable) { }
        return bitmap
    }

    private suspend fun releaseCameraX() {
        try {
            val cameraProvider = ProcessCameraProvider.getInstance(this).await()
            cameraProvider.unbindAll()
            Timber.i("CameraX unbound successfully")
        } catch (e: Exception) {
            Timber.w(e, "Failed to unbind CameraX")
        }
    }

    override fun onCapturePhaseStarted(phase: CapturePhase) {
        runOnUiThread {
            updatePhaseUI(phase)
            voiceGuide.speakSpectrumActivation(phase.spectrum.displayNameAr)
        }
    }

    override fun onCapturePhaseComplete(phase: CapturePhase) {
        runOnUiThread {
            updatePhaseUI(phase.copy(status = CapturePhase.Status.COMPLETE))
            voiceGuide.speakCaptureComplete(phase.spectrum.displayNameAr)
        }
    }

    private fun showAnalysisMarkers() {
        val markers = mutableListOf<AnalysisMarker>()

        val hydration = viewModel.hydration.value
        val pores = viewModel.pores.value
        val redness = viewModel.redness.value
        val texture = viewModel.texture.value
        val acne = viewModel.acne.value
        val sensitivity = viewModel.sensitivity.value
        val pigmentation = viewModel.pigmentation.value

        if (pigmentation > 0f) {
            markers.add(AnalysisMarker(0.5f, 0.3f, AnalysisMarker.MarkerType.PIGMENTATION, "التصبغ", pigmentation / 100f))
        }
        if (acne > 0f) {
            markers.add(AnalysisMarker(0.4f, 0.55f, AnalysisMarker.MarkerType.ACNE, "حب الشباب", acne / 100f))
            markers.add(AnalysisMarker(0.6f, 0.5f, AnalysisMarker.MarkerType.ACNE, "حب الشباب", acne / 100f * 0.8f))
        }
        if (pores > 60f) {
            markers.add(AnalysisMarker(0.45f, 0.45f, AnalysisMarker.MarkerType.OILY, "الدهون", pores / 100f))
        }
        if (texture < 50f) {
            markers.add(AnalysisMarker(0.55f, 0.35f, AnalysisMarker.MarkerType.DRYNESS, "الجفاف", (100f - texture) / 100f))
        }
        if (redness > 50f) {
            markers.add(AnalysisMarker(0.35f, 0.4f, AnalysisMarker.MarkerType.ACNE, "الاحمرار", redness / 100f))
        }

        markers.add(AnalysisMarker(0.5f, 0.65f, AnalysisMarker.MarkerType.DARK_CIRCLES, "الهالات", 0.7f))

        if (markers.isNotEmpty()) {
            binding.analysisMarkersOverlay.visibility = android.view.View.VISIBLE
            val faceRect = binding.faceGuideOverlay.getFaceRect()
            binding.analysisMarkersOverlay.setMarkers(markers, faceRect)
            Timber.i("Analysis markers displayed: ${markers.size} markers")
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        voiceGuide.shutdown()
        analysisSurface?.release()
        analysisSurface = null
        viewModel.abortAnalysis()
        binding.cameraPreview.surfaceTextureListener = null
        Timber.i("AnalysisActivity destroyed, resources cleaned up")
    }
}
