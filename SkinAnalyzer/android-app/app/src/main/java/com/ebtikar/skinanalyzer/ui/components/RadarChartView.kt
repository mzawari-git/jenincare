package com.ebtikar.skinanalyzer.ui.components

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Path
import android.graphics.PointF
import android.util.AttributeSet
import android.view.View
import android.view.animation.DecelerateInterpolator
import com.ebtikar.skinanalyzer.R
import kotlin.math.PI
import kotlin.math.cos
import kotlin.math.min
import kotlin.math.sin

class RadarChartView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val gridPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.GRAY
        style = Paint.Style.STROKE
        strokeWidth = 1f
        alpha = 80
    }

    private val axisPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.GRAY
        style = Paint.Style.STROKE
        strokeWidth = 1f
        alpha = 60
    }

    private val fillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.primary)
        style = Paint.Style.FILL
        alpha = 60
    }

    private val strokePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.primary)
        style = Paint.Style.STROKE
        strokeWidth = 3f
    }

    private val pointPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.primary)
        style = Paint.Style.FILL
    }

    private val comparisonFillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.accent_cyan)
        style = Paint.Style.FILL
        alpha = 40
    }

    private val comparisonStrokePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.accent_cyan)
        style = Paint.Style.STROKE
        strokeWidth = 2f
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = context.getColor(R.color.text_secondary)
        textSize = 28f
        textAlign = Paint.Align.CENTER
    }

    private var data: List<Float> = emptyList()
    private var labels: List<String> = emptyList()
    private var comparisonData: List<Float>? = null
    private var animatedValues: List<Float> = emptyList()
    private var animationProgress = 1f
    private var animator: ValueAnimator? = null

    private val gridLevels = 5

    fun setData(values: List<Float>, labels: List<String>) {
        this.data = values
        this.labels = labels
        animateData()
    }

    fun setComparisonData(before: List<Float>, after: List<Float>, labels: List<String> = this.labels) {
        this.comparisonData = before
        this.labels = labels
        this.data = after
        animateData()
    }

    private fun animateData() {
        animationProgress = 0f
        val targetValues = data.toList()
        val startValues = if (animatedValues.isNotEmpty()) animatedValues.toList() else List(data.size) { 0f }

        animator?.cancel()
        animator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 600
            interpolator = DecelerateInterpolator()
            addUpdateListener { animator ->
                val fraction = animator.animatedValue as Float
                animatedValues = startValues.zip(targetValues).map { (start, target) ->
                    start + (target - start) * fraction
                }
                animationProgress = fraction
                invalidate()
            }
            start()
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        animator?.cancel()
        animator = null
    }

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)

        val centerX = width / 2f
        val centerY = height / 2f
        val radius = min(centerX, centerY) * 0.7f

        if (data.isEmpty()) return

        val numAxes = data.size
        val angleStep = 2.0 * PI / numAxes

        drawGrid(canvas, centerX, centerY, radius, numAxes, angleStep)
        drawAxes(canvas, centerX, centerY, radius, numAxes, angleStep)

        comparisonData?.let { before ->
            drawDataPolygon(canvas, centerX, centerY, radius, before, angleStep, comparisonFillPaint, comparisonStrokePaint)
        }

        drawDataPolygon(canvas, centerX, centerY, radius, animatedValues, angleStep, fillPaint, strokePaint)
        drawDataPoints(canvas, centerX, centerY, radius, animatedValues, angleStep)
        drawLabels(canvas, centerX, centerY, radius, numAxes, angleStep)
    }

    private fun drawGrid(canvas: Canvas, cx: Float, cy: Float, radius: Float, numAxes: Int, angleStep: Double) {
        for (level in 1..gridLevels) {
            val levelRadius = radius * level / gridLevels
            val path = Path()
            for (i in 0 until numAxes) {
                val angle = angleStep * i - PI / 2
                val x = cx + (levelRadius * cos(angle)).toFloat()
                val y = cy + (levelRadius * sin(angle)).toFloat()
                if (i == 0) path.moveTo(x, y) else path.lineTo(x, y)
            }
            path.close()
            canvas.drawPath(path, gridPaint)
        }
    }

    private fun drawAxes(canvas: Canvas, cx: Float, cy: Float, radius: Float, numAxes: Int, angleStep: Double) {
        for (i in 0 until numAxes) {
            val angle = angleStep * i - PI / 2
            val x = cx + (radius * cos(angle)).toFloat()
            val y = cy + (radius * sin(angle)).toFloat()
            canvas.drawLine(cx, cy, x, y, axisPaint)
        }
    }

    private fun drawDataPolygon(canvas: Canvas, cx: Float, cy: Float, radius: Float, values: List<Float>, angleStep: Double, fill: Paint, stroke: Paint) {
        val path = Path()
        for (i in values.indices) {
            val angle = angleStep * i - PI / 2
            val valueRadius = radius * (values[i] / 100f)
            val x = cx + (valueRadius * cos(angle)).toFloat()
            val y = cy + (valueRadius * sin(angle)).toFloat()
            if (i == 0) path.moveTo(x, y) else path.lineTo(x, y)
        }
        path.close()
        canvas.drawPath(path, fill)
        canvas.drawPath(path, stroke)
    }

    private fun drawDataPoints(canvas: Canvas, cx: Float, cy: Float, radius: Float, values: List<Float>, angleStep: Double) {
        for (i in values.indices) {
            val angle = angleStep * i - PI / 2
            val valueRadius = radius * (values[i] / 100f)
            val x = cx + (valueRadius * cos(angle)).toFloat()
            val y = cy + (valueRadius * sin(angle)).toFloat()
            canvas.drawCircle(x, y, 6f, pointPaint)
        }
    }

    private fun drawLabels(canvas: Canvas, cx: Float, cy: Float, radius: Float, numAxes: Int, angleStep: Double) {
        if (labels.isEmpty()) return
        val labelRadius = radius + 30f
        for (i in 0 until min(numAxes, labels.size)) {
            val angle = angleStep * i - PI / 2
            val x = cx + (labelRadius * cos(angle)).toFloat()
            val y = cy + (labelRadius * sin(angle)).toFloat()
            canvas.drawText(labels[i], x, y + 8f, labelPaint)
        }
    }
}
