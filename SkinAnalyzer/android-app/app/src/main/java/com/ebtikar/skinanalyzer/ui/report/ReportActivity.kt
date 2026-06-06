package com.ebtikar.skinanalyzer.ui.report

import android.graphics.Color
import android.os.Bundle
import android.view.LayoutInflater
import android.widget.LinearLayout
import android.widget.TextView
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
import androidx.recyclerview.widget.LinearLayoutManager
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityReportBinding
import com.google.android.material.chip.Chip
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class ReportActivity : AppCompatActivity() {

    private lateinit var binding: ActivityReportBinding
    private val viewModel: ReportViewModel by viewModels()
    private lateinit var metricsAdapter: ReportMetricAdapter
    private lateinit var productAdapter: ProductAdapter

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

        setupRecyclerViews()
        setupUI()
        observeViewModel()
        viewModel.loadReport(reportId)
    }

    private fun setupRecyclerViews() {
        metricsAdapter = ReportMetricAdapter()
        binding.rvMetrics.apply {
            layoutManager = GridLayoutManager(this@ReportActivity, 4)
            adapter = metricsAdapter
        }

        productAdapter = ProductAdapter()
        binding.rvProducts.apply {
            layoutManager = LinearLayoutManager(this@ReportActivity)
            adapter = productAdapter
        }
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
        binding.btnShare.setOnClickListener { viewModel.shareReport() }
        binding.btnSave.setOnClickListener { viewModel.saveReport() }
        binding.btnNewAnalysis.setOnClickListener { finish() }
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
                    viewModel.aiAnalysisText.collect { text ->
                        binding.tvAiAnalysis.text = text
                    }
                }

                launch {
                    viewModel.skinProfile.collect { profile ->
                        binding.tvSkinType.text = profile.skinTypeAr
                        binding.tvFitzpatrick.text = "Fitzpatrick ${profile.fitzpatrickLevel}"
                    }
                }

                launch {
                    viewModel.expertTips.collect { tips ->
                        populateTips(tips)
                    }
                }

                launch {
                    viewModel.productRecommendations.collect { products ->
                        productAdapter.submitList(products)
                    }
                }

                launch {
                    viewModel.radarValues.collect { values ->
                        if (values.isNotEmpty()) {
                            binding.radarChart.setData(values, viewModel.radarLabels.value)
                        }
                    }
                }

                launch {
                    viewModel.topConcerns.collect { concerns ->
                        populateConcernChips(concerns.map { getArabicName(it.type) })
                    }
                }

                launch {
                    viewModel.providerName.collect { name ->
                    }
                }

                launch {
                    viewModel.analysisTime.collect { time ->
                        binding.tvAnalysisTime.text = if (time >= 1000) "${"%.1f".format(time / 1000f)}s" else "${time}ms"
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

    private fun populateTips(tips: List<String>) {
        binding.containerTips.removeAllViews()
        tips.forEachIndexed { index, tip ->
            val cardView = com.google.android.material.card.MaterialCardView(this).apply {
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply {
                    bottomMargin = resources.getDimensionPixelSize(R.dimen.space_8)
                }
                setCardBackgroundColor(resources.getColor(R.color.surface_card, theme))
                radius = resources.getDimension(R.dimen.corner_lg)
                strokeWidth = 1
                setStrokeColor(resources.getColor(R.color.border_card, theme))
                cardElevation = 0f
            }

            val layout = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                setPadding(36, 24, 36, 24)
                gravity = android.view.Gravity.CENTER_VERTICAL
            }

            val numberView = TextView(this).apply {
                text = "${index + 1}"
                setTextColor(Color.parseColor("#FF00D4FF"))
                textSize = 14f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply {
                    marginEnd = resources.getDimensionPixelSize(R.dimen.space_12)
                }
            }

            val tipView = TextView(this).apply {
                text = tip
                setTextColor(resources.getColor(R.color.text_secondary, theme))
                textSize = 12f
                setLineSpacing(0f, 1.5f)
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            }

            layout.addView(numberView)
            layout.addView(tipView)
            cardView.addView(layout)
            binding.containerTips.addView(cardView)
        }
    }

    private fun populateConcernChips(concerns: List<String>) {
        binding.chipGroupConcerns.removeAllViews()
        concerns.forEach { concern ->
            val chip = Chip(this).apply {
                text = concern
                setTextColor(Color.parseColor("#FFF43F5E"))
                chipBackgroundColor = android.content.res.ColorStateList.valueOf(Color.parseColor("#1AF43F5E"))
                chipCornerRadius = resources.getDimension(R.dimen.corner_md)
                textSize = 12f
                isClickable = false
                isCheckable = false
            }
            binding.chipGroupConcerns.addView(chip)
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

    private fun getArabicName(type: com.ebtikar.skinanalyzer.model.SkinMetric.Type): String = when (type) {
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.MOISTURE -> "الرطوبة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.PORES -> "المسام"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SEBUM -> "الدهنية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.WRINKLES -> "التجاعيد"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.TEXTURE -> "الملمس"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.PIGMENTATION -> "التصبغ"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.ACNE -> "حب الشباب"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.COLLAGEN -> "الكولاجين"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SENSITIVITY -> "الحساسية"
    }
}
