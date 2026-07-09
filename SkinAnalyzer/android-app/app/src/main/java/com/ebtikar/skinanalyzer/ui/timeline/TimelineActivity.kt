package com.ebtikar.skinanalyzer.ui.timeline

import android.content.Intent
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
import com.ebtikar.skinanalyzer.databinding.ActivityTimelineBinding
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.ui.report.ReportActivity
import com.github.mikephil.charting.components.XAxis
import com.github.mikephil.charting.data.Entry
import com.github.mikephil.charting.data.LineData
import com.github.mikephil.charting.data.LineDataSet
import com.github.mikephil.charting.formatter.IndexAxisValueFormatter
import com.google.android.material.card.MaterialCardView
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import timber.log.Timber
import java.text.SimpleDateFormat
import java.util.Locale

@AndroidEntryPoint
class TimelineActivity : AppCompatActivity() {

    private lateinit var binding: ActivityTimelineBinding
    private val viewModel: TimelineViewModel by viewModels()
    private val dateFormat = SimpleDateFormat("MM/dd", Locale.getDefault())
    private val metricColors = mapOf(
        SkinMetric.Type.MOISTURE to Color.parseColor("#3498DB"),
        SkinMetric.Type.PORES to Color.parseColor("#E67E22"),
        SkinMetric.Type.SEBUM to Color.parseColor("#F1C40F"),
        SkinMetric.Type.WRINKLES to Color.parseColor("#E74C3C"),
        SkinMetric.Type.TEXTURE to Color.parseColor("#1ABC9C"),
        SkinMetric.Type.UV_SPOTS to Color.parseColor("#9B59B6"),
        SkinMetric.Type.VASCULAR to Color.parseColor("#E74C3C"),
        SkinMetric.Type.PIGMENTATION to Color.parseColor("#D35400"),
        SkinMetric.Type.DARK_CIRCLES to Color.parseColor("#8E44AD"),
        SkinMetric.Type.BLACKHEADS to Color.parseColor("#34495E"),
        SkinMetric.Type.ACNE to Color.parseColor("#E74C3C"),
        SkinMetric.Type.SKIN_TONE to Color.parseColor("#F39C12"),
        SkinMetric.Type.SENSITIVITY to Color.parseColor("#E91E63"),
        SkinMetric.Type.ROSACEA to Color.parseColor("#FF5722"),
        SkinMetric.Type.MELASMA to Color.parseColor("#795548")
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityTimelineBinding.inflate(layoutInflater)
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
        viewModel.loadTimeline()
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }

        binding.chipGroupDays.setOnCheckedStateChangeListener { _, checkedIds ->
            val days = when {
                checkedIds.contains(R.id.chip7Days) -> 7
                checkedIds.contains(R.id.chip30Days) -> 30
                checkedIds.contains(R.id.chip90Days) -> 90
                else -> 0
            }
            viewModel.loadTimeline(days)
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.timelinePoints.collect { points ->
                        if (points.isEmpty()) {
                            binding.tvEmptyState.visibility = View.VISIBLE
                            binding.lineChartOverall.visibility = View.GONE
                            binding.lineChartMetrics.visibility = View.GONE
                            binding.tvMetricTitle.visibility = View.GONE
                            binding.containerReports.visibility = View.GONE
                            binding.containerMetricLegend.visibility = View.GONE
                            return@collect
                        }
                        binding.tvEmptyState.visibility = View.GONE
                        binding.lineChartOverall.visibility = View.VISIBLE
                        binding.lineChartMetrics.visibility = View.VISIBLE
                        binding.tvMetricTitle.visibility = View.VISIBLE
                        binding.containerReports.visibility = View.VISIBLE

                        setupOverallChart(points)
                        setupMetricsChart(points)
                        populateReportList(points)
                    }
                }
            }
        }
    }

    private fun setupOverallChart(points: List<TimelinePoint>) {
        val entries = points.mapIndexed { index, point ->
            Entry(index.toFloat(), point.overallScore)
        }
        val labels = points.map { it.dateLabel }

        val dataSet = LineDataSet(entries, "النتيجة الإجمالية").apply {
            color = ContextCompat.getColor(this@TimelineActivity, R.color.severity_excellent)
            setCircleColor(ContextCompat.getColor(this@TimelineActivity, R.color.severity_excellent))
            lineWidth = 2.5f
            circleRadius = 4f
            valueTextSize = 10f
            valueTextColor = ContextCompat.getColor(this@TimelineActivity, R.color.text_primary)
            mode = LineDataSet.Mode.CUBIC_BEZIER
            setDrawFilled(true)
            fillColor = ContextCompat.getColor(this@TimelineActivity, R.color.severity_excellent)
            fillAlpha = 30
        }

        binding.lineChartOverall.apply {
            data = LineData(dataSet)
            description.isEnabled = false
            legend.isEnabled = false
            setTouchEnabled(true)
            isDragEnabled = true
            setScaleEnabled(false)
            setPinchZoom(false)
            setDrawGridBackground(false)
            axisRight.isEnabled = false
            xAxis.apply {
                position = XAxis.XAxisPosition.BOTTOM
                valueFormatter = IndexAxisValueFormatter(labels)
                granularity = 1f
                labelRotationAngle = -45f
                textColor = ContextCompat.getColor(this@TimelineActivity, R.color.text_muted)
                setDrawGridLines(false)
            }
            axisLeft.apply {
                axisMinimum = 0f
                axisMaximum = 100f
                textColor = ContextCompat.getColor(this@TimelineActivity, R.color.text_muted)
                setDrawGridLines(true)
                gridColor = ContextCompat.getColor(this@TimelineActivity, R.color.border_card)
            }
            animateX(500)
            invalidate()
        }
    }

    private fun setupMetricsChart(points: List<TimelinePoint>) {
        val allMetricTypes = points.flatMap { it.metricScores.keys }.distinct().sortedBy { it.name }
        if (allMetricTypes.isEmpty()) return

        val dataSets = allMetricTypes.map { type ->
            val entries = points.mapIndexedNotNull { index, point ->
                point.metricScores[type]?.let { Entry(index.toFloat(), it) }
            }
            LineDataSet(entries, getArabicName(type)).apply {
                color = metricColors[type] ?: Color.GRAY
                setCircleColor(metricColors[type] ?: Color.GRAY)
                lineWidth = 1.5f
                circleRadius = 2.5f
                valueTextSize = 0f
                mode = LineDataSet.Mode.CUBIC_BEZIER
                setDrawFilled(false)
            }
        }

        val labels = points.map { it.dateLabel }

        binding.lineChartMetrics.apply {
            data = LineData(dataSets)
            description.isEnabled = false
            legend.isEnabled = false
            setTouchEnabled(true)
            isDragEnabled = true
            setScaleEnabled(false)
            setPinchZoom(false)
            setDrawGridBackground(false)
            axisRight.isEnabled = false
            xAxis.apply {
                position = XAxis.XAxisPosition.BOTTOM
                valueFormatter = IndexAxisValueFormatter(labels)
                granularity = 1f
                labelRotationAngle = -45f
                textColor = ContextCompat.getColor(this@TimelineActivity, R.color.text_muted)
                setDrawGridLines(false)
            }
            axisLeft.apply {
                axisMinimum = 0f
                axisMaximum = 100f
                textColor = ContextCompat.getColor(this@TimelineActivity, R.color.text_muted)
                setDrawGridLines(true)
                gridColor = ContextCompat.getColor(this@TimelineActivity, R.color.border_card)
            }
            animateX(500)
            invalidate()
        }

        populateMetricLegend(allMetricTypes)
    }

    private fun populateMetricLegend(metricTypes: List<SkinMetric.Type>) {
        binding.containerMetricLegend.removeAllViews()
        for (type in metricTypes) {
            val chip = com.google.android.material.chip.Chip(this).apply {
                text = getArabicName(type)
                isCheckable = true
                isChecked = true
                textSize = 10f
                setTextColor(ContextCompat.getColor(this@TimelineActivity, R.color.text_primary))
                chipBackgroundColor = android.content.res.ColorStateList.valueOf(
                    ContextCompat.getColor(this@TimelineActivity, R.color.surface_card)
                )
                setOnCheckedChangeListener { _, _ ->
                    toggleMetricLine(type, isChecked)
                }
            }
            binding.containerMetricLegend.addView(chip)
        }
    }

    private fun toggleMetricLine(type: SkinMetric.Type, visible: Boolean) {
        val data = binding.lineChartMetrics.data ?: return
        val index = data.dataSets.indexOfFirst { it.label == getArabicName(type) }
        if (index >= 0) {
            data.dataSets[index].isVisible = visible
            binding.lineChartMetrics.invalidate()
        }
    }

    private fun populateReportList(points: List<TimelinePoint>) {
        binding.containerReports.removeAllViews()
        for (point in points.reversed()) {
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
                isClickable = true
                isFocusable = true
                setOnClickListener {
                    val intent = Intent(this@TimelineActivity, ReportActivity::class.java).apply {
                        putExtra("report_id", point.reportId)
                    }
                    startActivity(intent)
                }
            }

            val layout = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                setPadding(36, 28, 36, 28)
                gravity = android.view.Gravity.CENTER_VERTICAL
            }

            val leftLayout = LinearLayout(this).apply {
                orientation = LinearLayout.VERTICAL
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            }

            val dateText = TextView(this).apply {
                text = point.dateLabel
                setTextColor(resources.getColor(R.color.text_muted, theme))
                textSize = 12f
            }
            leftLayout.addView(dateText)

            val scoreLabel = TextView(this).apply {
                text = "${"%.1f".format(point.overallScore)}/100"
                setTextColor(resources.getColor(R.color.text_primary, theme))
                textSize = 18f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
                setPadding(0, 4, 0, 0)
            }
            leftLayout.addView(scoreLabel)

            layout.addView(leftLayout)

            val scoreColor = when {
                point.overallScore >= 72f -> R.color.severity_excellent
                point.overallScore >= 55f -> R.color.severity_good
                point.overallScore >= 35f -> R.color.severity_fair
                else -> R.color.severity_poor
            }
            val scoreDot = View(this).apply {
                layoutParams = android.view.ViewGroup.LayoutParams(12, 12)
                background = android.graphics.drawable.GradientDrawable().apply {
                    shape = android.graphics.drawable.GradientDrawable.OVAL
                    setColor(ContextCompat.getColor(this@TimelineActivity, scoreColor))
                }
            }
            layout.addView(scoreDot)

            card.addView(layout)
            binding.containerReports.addView(card)
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
