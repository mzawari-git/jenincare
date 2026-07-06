package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Path
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator
import kotlin.math.sin
import kotlin.random.Random

class SpectralGraphView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var animOffset = 0f
    private var waveAnimator: ValueAnimator? = null
    private val gridPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }
    private val redWavePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f
        strokeCap = Paint.Cap.ROUND
    }
    private val greenWavePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f
        strokeCap = Paint.Cap.ROUND
    }
    private val blueWavePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f
        strokeCap = Paint.Cap.ROUND
    }
    private val fillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
    }

    init {
        startAnimations()
    }

    private fun startAnimations() {
        waveAnimator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 4000L
            repeatCount = ValueAnimator.INFINITE
            interpolator = LinearInterpolator()
            addUpdateListener { animOffset = it.animatedValue as Float; invalidate() }
            start()
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        waveAnimator?.cancel()
        waveAnimator = null
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val left = 8f * density
        val top = 8f * density
        val w = width.toFloat() - 16f * density
        val h = height.toFloat() - 16f * density

        if (w <= 0 || h <= 0) return

        // Grid lines
        gridPaint.color = Color.parseColor("#1AFFFFFF")
        gridPaint.strokeWidth = 0.5f * density
        val gridSteps = 6
        for (i in 1 until gridSteps) {
            val y = top + h * i / gridSteps
            canvas.drawLine(left, y, left + w, y, gridPaint)
        }

        // Draw spectrum waves
        drawWave(canvas, left, top, w, h, 0f, Color.parseColor("#66D95353"), Color.parseColor("#FFD95353"))
        drawWave(canvas, left, top, w, h, 0.3f, Color.parseColor("#6652B788"), Color.parseColor("#FF52B788"))
        drawWave(canvas, left, top, w, h, 0.6f, Color.parseColor("#6600D4FF"), Color.parseColor("#FF00D4FF"))

        // Labels
        labelPaint.textSize = 9f * density
        labelPaint.color = Color.parseColor("#88FFFFFF")
        canvas.drawText("R", left, top + h + 10f * density, labelPaint)
        canvas.drawText("G", left + w * 0.5f, top + h + 10f * density, labelPaint)
        canvas.drawText("B", left + w, top + h + 10f * density, labelPaint)

        labelPaint.textSize = 8f * density
        labelPaint.color = Color.parseColor("#66FFFFFF")
        canvas.drawText("RGB SPECTRUM", left + w / 2f, top - 4f * density, labelPaint)
    }

    private fun drawWave(canvas: Canvas, left: Float, top: Float, w: Float, h: Float, phaseOffset: Float, fillColor: Int, strokeColor: Int) {
        val seed = Random(hashCode() + phaseOffset.toInt())
        val path = Path()
        val fillPath = Path()
        val points = 30
        val baseY = top + h * (0.4f + phaseOffset * 0.2f)

        var started = false
        for (i in 0..points) {
            val x = left + w * i / points
            val t = i.toFloat() / points + animOffset + phaseOffset
            val amplitude = h * 0.08f * (0.5f + 0.5f * sin(i * 0.3f + phaseOffset * 6f))
            val y = baseY + amplitude.toFloat() * sin((t * 8f + phaseOffset * 4f).toDouble()).toFloat()

            if (!started) {
                path.moveTo(x, y)
                fillPath.moveTo(x, y)
                started = true
            } else {
                path.lineTo(x, y)
                fillPath.lineTo(x, y)
            }
        }

        redWavePaint.color = strokeColor
        redWavePaint.alpha = 200
        canvas.drawPath(path, redWavePaint)

        fillPaint.color = fillColor
        fillPaint.alpha = 30
        fillPath.lineTo(left + w, top + h)
        fillPath.lineTo(left, top + h)
        fillPath.close()
        canvas.drawPath(fillPath, fillPaint)
    }
}
