package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.DashPathEffect
import android.graphics.Paint
import android.graphics.SweepGradient
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator
import kotlin.math.min

class ScanRingView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val ringPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val trackPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        color = Color.parseColor("#1A00D4FF")
        strokeWidth = 2f * resources.displayMetrics.density
    }

    private val laserPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
        color = Color.parseColor("#FF00D4FF")
    }

    private var rotationAngle = 0f
    private var pulseAlpha = 128
    private var ringAnimator: ValueAnimator? = null
    private var pulseAnimator: ValueAnimator? = null
    private val dashEffect = DashPathEffect(floatArrayOf(12f, 8f), 0f)

    init {
        startAnimation()
    }

    private fun startAnimation() {
        ringAnimator = ValueAnimator.ofFloat(0f, 360f).apply {
            duration = 4000L
            repeatCount = ValueAnimator.INFINITE
            interpolator = LinearInterpolator()
            addUpdateListener {
                rotationAngle = it.animatedValue as Float
                invalidate()
            }
            start()
        }

        pulseAnimator = ValueAnimator.ofInt(60, 180, 60).apply {
            duration = 2000L
            repeatCount = ValueAnimator.INFINITE
            addUpdateListener {
                pulseAlpha = it.animatedValue as Int
                invalidate()
            }
            start()
        }
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        val stroke = 3f * resources.displayMetrics.density
        ringPaint.strokeWidth = stroke
        laserPaint.strokeWidth = stroke * 1.5f
    }

    override fun onDraw(canvas: Canvas) {
        val cx = width / 2f
        val cy = height / 2f

        val outerR = min(cx, cy) - 20f
        val middleR = outerR - 30f
        val innerR = middleR - 30f

        trackPaint.alpha = pulseAlpha / 3
        canvas.drawCircle(cx, cy, outerR, trackPaint)
        canvas.drawCircle(cx, cy, middleR, trackPaint)
        canvas.drawCircle(cx, cy, innerR, trackPaint)

        ringPaint.color = Color.parseColor("#FF00D4FF")
        ringPaint.alpha = pulseAlpha
        canvas.save()
        canvas.rotate(rotationAngle, cx, cy)
        canvas.drawArc(cx - outerR, cy - outerR, cx + outerR, cy + outerR, -30f, 60f, false, ringPaint)
        canvas.restore()

        ringPaint.color = Color.parseColor("#FF7C3AED")
        canvas.save()
        canvas.rotate(-rotationAngle * 0.7f, cx, cy)
        canvas.drawArc(cx - middleR, cy - middleR, cx + middleR, cy + middleR, 90f, 45f, false, ringPaint)
        canvas.restore()

        laserPaint.alpha = pulseAlpha
        canvas.save()
        canvas.rotate(rotationAngle * 1.5f, cx, cy)
        canvas.drawLine(cx, cy - innerR, cx, cy + innerR, laserPaint)
        canvas.restore()
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        ringAnimator?.cancel()
        pulseAnimator?.cancel()
    }
}
