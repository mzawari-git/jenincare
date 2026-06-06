package com.ebtikar.skinanalyzer.ui.calibration

import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.databinding.ActivityCalibrationBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class CalibrationActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCalibrationBinding
    private val viewModel: CalibrationViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityCalibrationBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.updatePadding(
                left = systemBars.left,
                top = systemBars.top,
                right = systemBars.right,
                bottom = systemBars.bottom
            )
            insets
        }

        setupUI()
        observeViewModel()
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
        binding.btnStartCalibration.setOnClickListener {
            viewModel.startCalibration()
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.calibrationResults.collect { results ->
                        updateCalibrationUI(results)
                    }
                }

                launch {
                    viewModel.isRunning.collect { running ->
                        binding.btnStartCalibration.isEnabled = !running
                        binding.btnStartCalibration.text = if (running)
                            getString(com.ebtikar.skinanalyzer.R.string.calibration_running)
                        else
                            getString(com.ebtikar.skinanalyzer.R.string.calibration_start)
                    }
                }
            }
        }
    }

    private fun updateCalibrationUI(results: Map<String, CalibrationViewModel.CalibrationResult>) {
        val sb = StringBuilder()
        for ((name, result) in results) {
            val status = when (result.status) {
                CalibrationViewModel.TestStatus.PENDING -> "⏳"
                CalibrationViewModel.TestStatus.RUNNING -> "🔄"
                CalibrationViewModel.TestStatus.PASS -> "✅"
                CalibrationViewModel.TestStatus.FAIL -> "❌"
            }
            sb.appendLine("$status $name: ${result.message}")
        }
        binding.tvCalibrationLog.text = sb.toString()
    }
}
