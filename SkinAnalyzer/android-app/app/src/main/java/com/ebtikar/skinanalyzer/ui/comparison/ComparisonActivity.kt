package com.ebtikar.skinanalyzer.ui.comparison

import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.LinearLayout
import android.widget.TextView
import androidx.activity.enableEdgeToEdge
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityComparisonBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.google.android.material.card.MaterialCardView
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import timber.log.Timber
import kotlin.math.abs

@AndroidEntryPoint
class ComparisonActivity : AppCompatActivity() {

    private lateinit var binding: ActivityComparisonBinding
    private val viewModel: ComparisonViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityComparisonBinding.inflate(layoutInflater)
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
        loadComparisonData()
    }

    private fun loadComparisonData() {
        val beforeId = intent.getStringExtra("before_id")
        val afterId = intent.getStringExtra("after_id")
        if (beforeId != null && afterId != null) {
            viewModel.compareReports(beforeId, afterId)
        }
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.comparisonData.collect { data ->
                        if (data == null) {
                            binding.tvEmptyState.visibility = View.VISIBLE
                            return@collect
                        }
                        binding.tvEmptyState.visibility = View.GONE

                        binding.tvBeforeScore.text = "%.1f".format(data.beforeScore)
                        binding.tvAfterScore.text = "%.1f".format(data.afterScore)
                        binding.tvBeforeDate.text = data.beforeDate
                        binding.tvAfterDate.text = data.afterDate

                        val delta = data.afterScore - data.beforeScore
                        val sign = if (delta >= 0) "+" else ""
                        binding.tvDelta.text = "$sign${"%.1f".format(delta)}"

                        binding.tvDelta.setTextColor(
                            ContextCompat.getColor(this@ComparisonActivity,
                                if (delta >= 0) R.color.severity_excellent else R.color.severity_poor
                            )
                        )
                        binding.tvDeltaLabel.text = when {
                            delta > 5f -> "تحسن ملحوظ في صحة البشرة"
                            delta > 0f -> "تحسن طفيف"
                            delta == 0f -> "لا يوجد تغيير"
                            delta > -5f -> "انخفاض طفيف"
                            else -> "انخفاض ملحوظ — يُنصح بمراجعة الخبير"
                        }

                        if (data.beforeRadarValues.isNotEmpty() && data.afterRadarValues.isNotEmpty()) {
                            binding.tvRadarTitle.visibility = View.VISIBLE
                            binding.cardRadar.visibility = View.VISIBLE
                            binding.radarChartComparison.setComparisonData(
                                data.beforeRadarValues,
                                data.afterRadarValues,
                                data.radarLabels
                            )
                        }

                        populateMetricDeltas(data.metricDeltas)
                    }
                }
            }
        }
    }

    private fun populateMetricDeltas(deltas: List<ComparisonViewModel.MetricDelta>) {
        binding.containerMetricDeltas.removeAllViews()

        for (delta in deltas) {
            val card = MaterialCardView(this).apply {
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
                setPadding(36, 28, 36, 28)
                gravity = android.view.Gravity.CENTER_VERTICAL
            }

            val nameLayout = LinearLayout(this).apply {
                orientation = LinearLayout.VERTICAL
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            }

            val nameText = TextView(this).apply {
                text = getArabicName(delta.type)
                setTextColor(resources.getColor(R.color.text_primary, theme))
                textSize = 14f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
            }
            nameLayout.addView(nameText)

            val severityRow = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                gravity = android.view.Gravity.CENTER_VERTICAL
                setPadding(0, 4, 0, 0)
            }

            val beforeLabel = TextView(this).apply {
                text = "${"%.0f".format(delta.beforeScore)}"
                setTextColor(resources.getColor(R.color.text_muted, theme))
                textSize = 11f
            }
            severityRow.addView(beforeLabel)

            val arrow = TextView(this).apply {
                text = " → "
                setTextColor(resources.getColor(R.color.text_muted, theme))
                textSize = 11f
            }
            severityRow.addView(arrow)

            val afterLabel = TextView(this).apply {
                text = "${"%.0f".format(delta.afterScore)}"
                setTextColor(resources.getColor(R.color.text_primary, theme))
                textSize = 11f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
            }
            severityRow.addView(afterLabel)
            nameLayout.addView(severityRow)

            layout.addView(nameLayout)

            val deltaValue = TextView(this).apply {
                val d = delta.delta
                val sign = if (d >= 0) "+" else ""
                text = "$sign${"%.1f".format(d)}"
                setTextColor(
                    ContextCompat.getColor(this@ComparisonActivity,
                        if (d >= 0) R.color.severity_excellent else R.color.severity_poor
                    )
                )
                textSize = 16f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
            }
            layout.addView(deltaValue)

            card.addView(layout)
            binding.containerMetricDeltas.addView(card)
        }
    }

    private fun getArabicName(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع"
        SkinMetric.Type.VASCULAR -> "الأوعية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس"
        SkinMetric.Type.ACNE -> "الحب"
        SkinMetric.Type.SKIN_TONE -> "اللون"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
