package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Intent
import android.graphics.SurfaceTexture
import android.os.Bundle
import android.view.Surface
import android.view.TextureView
import androidx.activity.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.BaseCameraActivity
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.databinding.ActivityAnalysisBinding
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.ui.report.ReportActivity
import com.ebtikar.skinanalyzer.util.Constants
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class AnalysisActivity : BaseCameraActivity() {

    private val viewModel: AnalysisViewModel by viewModels()

    @Inject lateinit var cameraManager: USBCameraManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val diagnosisMode = intent.getStringExtra("diagnosis_mode") ?: Constants.DIAGNOSIS_CROSS
        viewModel.setDiagnosisMode(diagnosisMode)

        setupUI()
        observeViewModel()

        binding.cameraPreview.surfaceTextureListener = object : TextureView.SurfaceTextureListener {
            override fun onSurfaceTextureAvailable(surface: SurfaceTexture, width: Int, height: Int) {
                cameraManager.isDisplayPortrait = height > width
                Timber.i("TextureView available: ${width}x${height}, portrait=${height > width}")
                val previewSurface = Surface(surface)
                viewModel.initializeAnalysis(previewSurface)
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
            }
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
