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
import androidx.recyclerview.widget.LinearLayoutManager
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityCalibrationBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class CalibrationActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCalibrationBinding
    private val viewModel: CalibrationViewModel by viewModels()
    private lateinit var stepAdapter: CalibrationStepAdapter

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

        setupRecyclerView()
        setupUI()
        observeViewModel()
    }

    private fun setupRecyclerView() {
        stepAdapter = CalibrationStepAdapter()
        binding.rvCalibrationSteps.apply {
            layoutManager = LinearLayoutManager(this@CalibrationActivity)
            adapter = stepAdapter
        }
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
                    viewModel.calibrationSteps.collect { steps ->
                        stepAdapter.submitList(steps)
                    }
                }

                launch {
                    viewModel.isRunning.collect { running ->
                        binding.btnStartCalibration.isEnabled = !running
                        binding.btnStartCalibration.text = if (running)
                            "جاري المعايرة..."
                        else
                            "بدء المعايرة"
                    }
                }

                launch {
                    viewModel.calibrationStatus.collect { status ->
                        binding.tvCalibrationStatus.text = status
                    }
                }

                launch {
                    viewModel.lastCalibration.collect { last ->
                        binding.tvLastCalibration.text = last
                    }
                }

                launch {
                    viewModel.currentStep.collect { step ->
                        binding.tvCurrentStep.text = step
                    }
                }

                launch {
                    viewModel.progress.collect { progress ->
                        binding.progressCalibration.progress = progress
                    }
                }

                launch {
                    viewModel.calibrationLog.collect { log ->
                        binding.tvCalibrationLog.text = log
                    }
                }
            }
        }
    }
}
