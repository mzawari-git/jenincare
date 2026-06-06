package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Intent
import android.os.Bundle
import android.view.View
import androidx.activity.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.BaseCameraActivity
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.databinding.ActivityAnalysisBinding
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.ui.report.ReportActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class AnalysisActivity : BaseCameraActivity() {

    private val viewModel: AnalysisViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        setupUI()
        observeViewModel()
        viewModel.initializeAnalysis()
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
                        binding.tvScanPercent.text = "$progress%"
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
        binding.tvCurrentSpectrum.text = phase.spectrum.displayName

        val statusText = when (phase.status) {
            CapturePhase.Status.ACTIVATING -> getString(R.string.phase_activating)
            CapturePhase.Status.SETTLING -> getString(R.string.phase_settling)
            CapturePhase.Status.CAPTURING -> getString(R.string.phase_capturing)
            CapturePhase.Status.COMPLETE -> getString(R.string.phase_complete)
            CapturePhase.Status.FAILED -> getString(R.string.phase_failed)
            CapturePhase.Status.PENDING -> ""
        }
        binding.tvScanInstruction.text = statusText
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
