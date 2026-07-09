package com.ebtikar.skinanalyzer.ui.result

import android.graphics.Color
import android.graphics.Typeface
import android.os.Bundle
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
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityResultBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class ResultActivity : AppCompatActivity() {

    private lateinit var binding: ActivityResultBinding
    private val viewModel: ResultViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityResultBinding.inflate(layoutInflater)
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

        setupUI()
        observeViewModel()
        viewModel.loadReport(reportId)
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
        binding.btnNewScan.setOnClickListener {
            finish()
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.overallScore.collect { score ->
                        binding.tvScoreValue.text = "%.1f".format(score)
                        binding.tvScoreLabel.text = getScoreLabel(score)
                    }
                }

                launch {
                    viewModel.metrics.collect { metrics ->
                        populateMetricsTable(metrics)
                    }
                }
            }
        }
    }

    private fun populateMetricsTable(metrics: List<SkinMetric>) {
        binding.containerMetrics.removeAllViews()
        if (metrics.isEmpty()) return

        val density = resources.displayMetrics.density
        val rowHeight = (48 * density).toInt()
        val paddingH = (16 * density).toInt()
        val paddingV = (12 * density).toInt()

        for ((index, metric) in metrics.withIndex()) {
            val row = LinearLayout(this).apply {
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    rowHeight
                )
                orientation = LinearLayout.HORIZONTAL
                gravity = android.view.Gravity.CENTER_VERTICAL
                setPadding(paddingH, 0, paddingH, 0)
                if (index % 2 == 0) {
                    setBackgroundColor(Color.parseColor("#0DFFFFFF"))
                } else {
                    setBackgroundColor(Color.TRANSPARENT)
                }
            }

            // Metric name
            val nameView = TextView(this).apply {
                text = getArabicName(metric.type)
                setTextColor(resources.getColor(R.color.text_primary, theme))
                textSize = 13f
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 3f)
            }

            // Percentage
            val percentView = TextView(this).apply {
                text = "%.0f%%".format(metric.score)
                setTextColor(getScoreColor(metric.score))
                textSize = 14f
                typeface = Typeface.DEFAULT_BOLD
                gravity = android.view.Gravity.CENTER
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 2f)
            }

            // Status/severity
            val statusView = TextView(this).apply {
                text = metric.severity.displayAr
                setTextColor(getSeverityColor(metric.severity))
                textSize = 12f
                typeface = Typeface.DEFAULT_BOLD
                gravity = android.view.Gravity.CENTER
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 2f)
            }

            row.addView(nameView)
            row.addView(percentView)
            row.addView(statusView)
            binding.containerMetrics.addView(row)
        }
    }

    private fun getScoreColor(score: Float): Int = when {
        score >= 72f -> Color.parseColor("#FF00E676")
        score >= 55f -> Color.parseColor("#FF00D4FF")
        score >= 35f -> Color.parseColor("#FFFFD740")
        else -> Color.parseColor("#FFFF5252")
    }

    private fun getSeverityColor(severity: MetricSeverity): Int = when (severity) {
        MetricSeverity.EXCELLENT -> Color.parseColor("#FF00E676")
        MetricSeverity.GOOD -> Color.parseColor("#FF00D4FF")
        MetricSeverity.FAIR -> Color.parseColor("#FFFFD740")
        MetricSeverity.POOR -> Color.parseColor("#FFFF6E40")
        MetricSeverity.CRITICAL -> Color.parseColor("#FFFF5252")
    }

    private fun getScoreLabel(score: Float): String = when {
        score >= 72f -> "ممتاز"
        score >= 55f -> "جيد"
        score >= 35f -> "متوسط"
        score >= 20f -> "ضعيف"
        else -> "يحتاج عناية مركزة"
    }

    private fun getArabicName(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        SkinMetric.Type.ACNE -> "حب الشباب"
        SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
