package com.ebtikar.skinanalyzer.ui.report

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
import androidx.recyclerview.widget.GridLayoutManager
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityReportBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class ReportActivity : AppCompatActivity() {

    private lateinit var binding: ActivityReportBinding
    private val viewModel: ReportViewModel by viewModels()
    private lateinit var metricsAdapter: ReportMetricAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityReportBinding.inflate(layoutInflater)
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

        val reportId = intent.getStringExtra("report_id") ?: run {
            finish()
            return
        }

        setupRecyclerView()
        setupUI()
        observeViewModel()
        viewModel.loadReport(reportId)
    }

    private fun setupRecyclerView() {
        metricsAdapter = ReportMetricAdapter()
        binding.rvMetrics.apply {
            layoutManager = GridLayoutManager(this@ReportActivity, 4)
            adapter = metricsAdapter
        }
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
        binding.btnShare.setOnClickListener { viewModel.shareReport() }
        binding.btnSave.setOnClickListener { viewModel.saveReport() }
        binding.btnNewAnalysis.setOnClickListener {
            finish()
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.overallScore.collect { score ->
                        binding.tvOverallScore.text = "%.1f".format(score)
                        binding.chipScoreLabel.text = getScoreLabel(score)
                        binding.gaugeOverallScore.setScore(score)
                    }
                }

                launch {
                    viewModel.metrics.collect { metrics ->
                        metricsAdapter.submitList(metrics)
                        binding.tvMetricCount.text = "${metrics.size}/14"
                    }
                }

                launch {
                    viewModel.providerName.collect { name ->
                        binding.tvProvider.text = name
                    }
                }

                launch {
                    viewModel.analysisTime.collect { time ->
                        binding.tvAnalysisTime.text = "${time}ms"
                    }
                }

                launch {
                    viewModel.reportDate.collect { date ->
                        binding.tvReportDate.text = date
                    }
                }
            }
        }
    }

    private fun getScoreLabel(score: Float): String {
        return when {
            score >= 85f -> getString(R.string.score_excellent)
            score >= 70f -> getString(R.string.score_good)
            score >= 55f -> getString(R.string.score_fair)
            score >= 35f -> getString(R.string.score_poor)
            else -> getString(R.string.score_critical)
        }
    }
}
