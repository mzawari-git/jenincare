package com.ebtikar.skinanalyzer.ui.report

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.SweepGradient
import android.util.AttributeSet
import android.view.View
import android.view.animation.DecelerateInterpolator
import androidx.core.content.ContextCompat
import com.ebtikar.skinanalyzer.R
import kotlin.math.min

/**
 * ScoreGaugeView — Premium animated arc gauge for Derma AI
 *
 * Renders a 270° arc gauge with:
 *  - Gradient stroke (cyan → purple)
 *  - Dark track background
 *  - Animated score fill on [setScore]
 *  - Glow shadow effect
 *  - Score text + label in center
 */
class ScoreGaugeView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    companion object {
        private const val START_ANGLE = 135f   // degrees — starts at bottom-left
        private const val SWEEP_ANGLE = 270f   // full arc span
        private const val STROKE_DP  = 16f
        private const val ANIM_MS    = 1200L
    }

    // ── Colors ──
    private val colorCyan    = Color.parseColor("#FF00D4FF")
    private val colorPurple  = Color.parseColor("#FF7C3AED")
    private val colorGold    = Color.parseColor("#FFF59E0B")
    private val colorRose    = Color.parseColor("#FFF43F5E")
    private val colorTrack   = Color.parseColor("#1AFFFFFF")
    private val colorTextPri = Color.parseColor("#FFFFFFFF")
    private val colorTextSec = Color.parseColor("#FF94A3B8")

    // ── State ──
    private var targetScore  = 0f
    private var currentScore = 0f   // animated value

    // ── Paints ──
    private val trackPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
        color = colorTrack
    }

    private val arcPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val glowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val scorePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
        color = colorTextPri
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
        color = colorTextSec
    }

    private val oval = RectF()
    private var animator: ValueAnimator? = null

    // ── Public API ──

    /**
     * Set score (0–100) and animate the gauge fill.
     */
    fun setScore(score: Float, animate: Boolean = true) {
        targetScore = score.coerceIn(0f, 100f)
        if (animate) animateTo(targetScore) else {
            currentScore = targetScore
            rebuildShader()
            invalidate()
        }
    }

    // ── Drawing ──

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        val stroke = STROKE_DP * resources.displayMetrics.density
        trackPaint.strokeWidth = stroke
        arcPaint.strokeWidth   = stroke
        glowPaint.strokeWidth  = stroke * 2.2f

        val inset = stroke * 1.5f
        oval.set(inset, inset, w - inset, h - inset)

        scorePaint.textSize = min(w, h) * 0.28f
        labelPaint.textSize = min(w, h) * 0.12f

        rebuildShader()
    }

    override fun onDraw(canvas: Canvas) {
        val cx = width / 2f
        val cy = height / 2f
        val fill = (currentScore / 100f) * SWEEP_ANGLE

        // 1. Track (background arc)
        canvas.drawArc(oval, START_ANGLE, SWEEP_ANGLE, false, trackPaint)

        if (fill > 0f) {
            // 2. Glow (wider, very transparent)
            glowPaint.alpha = 40
            canvas.drawArc(oval, START_ANGLE, fill, false, glowPaint)

            // 3. Gradient arc
            canvas.drawArc(oval, START_ANGLE, fill, false, arcPaint)
        }

        // 4. Score number
        val scoreInt = currentScore.toInt()
        canvas.drawText("$scoreInt", cx, cy + scorePaint.textSize * 0.38f, scorePaint)

        // 5. Label beneath score
        val label = when {
            currentScore >= 85 -> "ممتاز"
            currentScore >= 70 -> "جيد"
            currentScore >= 55 -> "متوسط"
            else               -> "يحتاج عناية"
        }
        canvas.drawText(label, cx, cy + scorePaint.textSize * 0.38f + labelPaint.textSize * 1.4f, labelPaint)
    }

    // ── Internals ──

    private fun rebuildShader() {
        val arcColor = when {
            currentScore >= 85 -> intArrayOf(colorCyan, colorCyan)
            currentScore >= 70 -> intArrayOf(colorCyan, colorPurple)
            currentScore >= 55 -> intArrayOf(colorGold, colorGold)
            else               -> intArrayOf(colorRose, colorRose)
        }
        val cx = width / 2f
        val cy = height / 2f
        val gradient = SweepGradient(cx, cy, arcColor, null)
        arcPaint.shader = gradient
        glowPaint.shader = gradient
    }

    private fun animateTo(target: Float) {
        animator?.cancel()
        animator = ValueAnimator.ofFloat(currentScore, target).apply {
            duration = ANIM_MS
            interpolator = DecelerateInterpolator(2f)
            addUpdateListener { anim ->
                currentScore = anim.animatedValue as Float
                rebuildShader()
                invalidate()
            }
            start()
        }
    }
}
