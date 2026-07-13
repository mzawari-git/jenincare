package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Intent
import android.graphics.BitmapFactory
import android.graphics.SurfaceTexture
import java.io.File
import android.os.Bundle
import android.view.Surface
import android.view.TextureView
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.activity.viewModels
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.BaseCameraActivity
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.databinding.ActivityAnalysisBinding
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.ui.report.ReportActivity
import com.ebtikar.skinanalyzer.util.Constants
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.delay
import kotlinx.coroutines.guava.await
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class AnalysisActivity : BaseCameraActivity() {

    private val viewModel: AnalysisViewModel by viewModels()

    @Inject lateinit var cameraManager: USBCameraManager
    @Inject lateinit var capturePipeline: FrameCapturePipeline

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val diagnosisMode = intent.getStringExtra("diagnosis_mode") ?: Constants.DIAGNOSIS_ALL
        viewModel.setDiagnosisMode(diagnosisMode)

        setupUI()
        observeViewModel()

        binding.cameraPreview.surfaceTextureListener = object : TextureView.SurfaceTextureListener {
            override fun onSurfaceTextureAvailable(surface: SurfaceTexture, width: Int, height: Int) {
                cameraManager.isDisplayPortrait = height > width
                Timber.i("TextureView available: ${width}x${height}, portrait=${height > width}")
                val previewSurface = Surface(surface)
                lifecycleScope.launch {
                    releaseCameraX()
                    delay(200)
                    viewModel.initializeAnalysis(previewSurface)
                }
                binding.cameraPreview.post {
                    cameraManager.rotateTextureView(binding.cameraPreview)
                }
            }
            override fun onSurfaceTextureSizeChanged(surface: SurfaceTexture, width: Int, height: Int) {
                binding.cameraPreview.post {
                    cameraManager.rotateTextureView(binding.cameraPreview)
                }
            }
            override fun onSurfaceTextureDestroyed(surface: SurfaceTexture): Boolean = true
            override fun onSurfaceTextureUpdated(surface: SurfaceTexture) {}
        }
    }

    private fun setupUI() {
        binding.btnCancelScan.setOnClickListener {
            viewModel.abortAnalysis()
            finish()
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.currentPhase.collect { phase ->
                        if (phase != null) {
                            updatePhaseUI(phase)
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
                            binding.progressScan.progress = 100
                            binding.tvScanInstruction.text = getString(R.string.analysis_complete)
                            navigateToReport()
                        }
                    }
                }

                launch {
                    viewModel.error.collect { error ->
                        if (error != null) {
                            binding.tvScanInstruction.text = error
                            binding.btnCancelScan.text = getString(R.string.action_close)
                        }
                    }
                }

                launch {
                    viewModel.currentSpectrumName.collect { name ->
                        binding.tvCurrentSpectrum.text = name
                    }
                }

                launch {
                    capturePipeline.capturedFrameSequence.collect { frames ->
                        populateThumbnails(frames)
                    }
                }
            }
        }
    }

    private fun populateThumbnails(frames: List<Pair<LightSpectrum, File>>) {
        Timber.d("populateThumbnails called with ${frames.size} frames")
        if (frames.isEmpty()) {
            binding.containerAnalysisThumbnails.visibility = android.view.View.GONE
            Timber.d("populateThumbnails: frames empty, hiding container")
            return
        }
        binding.containerAnalysisThumbnails.visibility = android.view.View.VISIBLE
        binding.containerAnalysisThumbnails.removeAllViews()
        Timber.d("populateThumbnails: container visible, building ${frames.size} thumbnails")

        val density = resources.displayMetrics.density
        val size = (48 * density).toInt()
        val margin = (4 * density).toInt()

        for ((spectrum, file) in frames) {
            val card = layoutInflater.inflate(R.layout.item_spectrum_thumbnail, null) as ViewGroup
            val imageView = card.findViewById<ImageView>(R.id.ivSpectrumThumb)
            val label = card.findViewById<TextView>(R.id.tvSpectrumLabel)

            label.text = spectrum.displayNameAr
            label.setTextColor(android.graphics.Color.WHITE)
            label.setBackgroundColor(android.graphics.Color.parseColor(spectrum.colorHex))

            try {
                val bm = BitmapFactory.decodeFile(file.absolutePath)
                if (bm != null) {
                    imageView.setImageBitmap(bm)
                } else {
                    imageView.setBackgroundColor(android.graphics.Color.DKGRAY)
                }
            } catch (e: Exception) {
                imageView.setBackgroundColor(android.graphics.Color.DKGRAY)
            }

            val lp = LinearLayout.LayoutParams(size, size)
            lp.marginEnd = margin
            binding.containerAnalysisThumbnails.addView(card, lp)
        }
    }

    private fun updatePhaseUI(phase: CapturePhase) {
        binding.tvCurrentSpectrum.text = phase.spectrum.displayNameAr
        binding.tvSpectrumMode.text = phase.spectrum.displayNameAr
        binding.tvScanPercent.text = "${phase.index + 1}/${phase.spectrum.let { 8 }}"

        val dotColor = try {
            android.graphics.Color.parseColor(phase.spectrum.colorHex)
        } catch (e: Exception) {
            getColor(R.color.primary)
        }
        binding.dotSpectrum.background.setTint(dotColor)

        val statusText = when (phase.status) {
            CapturePhase.Status.ACTIVATING -> "تفعيل ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.SETTLING -> "تثبيت ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.CAPTURING -> "التقاط ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.PROCESSING -> "معالجة ${phase.spectrum.displayNameAr}..."
            CapturePhase.Status.COMPLETE -> "${phase.spectrum.displayNameAr} ✓"
            CapturePhase.Status.FAILED -> "فشل ${phase.spectrum.displayNameAr}"
            CapturePhase.Status.PENDING -> ""
        }
        binding.tvScanStatus.setText(statusText)
    }

    private fun navigateToReport() {
        val intent = Intent(this, ReportActivity::class.java).apply {
            putExtra("report_id", viewModel.getReportId())
        }
        startActivity(intent)
        finish()
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
        }
    }

    override fun onCapturePhaseComplete(phase: CapturePhase) {
        runOnUiThread {
            updatePhaseUI(phase.copy(status = CapturePhase.Status.COMPLETE))
        }
    }
}
