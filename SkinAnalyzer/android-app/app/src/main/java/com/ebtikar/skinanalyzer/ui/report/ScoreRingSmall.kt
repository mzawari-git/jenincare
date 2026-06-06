package com.ebtikar.skinanalyzer.ui.report

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.SweepGradient
import android.util.AttributeSet
import android.view.View
import android.view.animation.DecelerateInterpolator
import kotlin.math.min

class ScoreRingSmall @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val trackPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
        color = Color.parseColor("#1AFFFFFF")
    }

    private val arcPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val scorePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
        color = Color.parseColor("#FFFFFFFF")
    }

    private var currentScore = 0f
    private var animator: ValueAnimator? = null

    fun setScore(score: Float, animate: Boolean = true) {
        val target = score.coerceIn(0f, 100f)
        if (animate) {
            animator?.cancel()
            animator = ValueAnimator.ofFloat(currentScore, target).apply {
                duration = 800L
                interpolator = DecelerateInterpolator(2f)
                addUpdateListener {
                    currentScore = it.animatedValue as Float
                    updateColor()
                    invalidate()
                }
                start()
            }
        } else {
            currentScore = target
            updateColor()
            invalidate()
        }
    }

    private fun updateColor() {
        val color = when {
            currentScore >= 85f -> "#FF10B981"
            currentScore >= 70f -> "#FF34D399"
            currentScore >= 55f -> "#FFF59E0B"
            currentScore >= 35f -> "#FFF97316"
            else -> "#FFF43F5E"
        }
        arcPaint.color = Color.parseColor(color)
        scorePaint.color = Color.parseColor(color)
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        val stroke = 3f * resources.displayMetrics.density
        trackPaint.strokeWidth = stroke
        arcPaint.strokeWidth = stroke
        scorePaint.textSize = min(w, h) * 0.32f
        updateColor()
    }

    override fun onDraw(canvas: Canvas) {
        val cx = width / 2f
        val cy = height / 2f
        val radius = min(cx, cy) - trackPaint.strokeWidth
        val fill = (currentScore / 100f) * 360f

        canvas.drawCircle(cx, cy, radius, trackPaint)

        if (fill > 0f) {
            canvas.drawArc(
                cx - radius, cy - radius, cx + radius, cy + radius,
                -90f, fill, false, arcPaint
            )
        }

        canvas.drawText("${currentScore.toInt()}", cx, cy + scorePaint.textSize * 0.35f, scorePaint)
    }
}
