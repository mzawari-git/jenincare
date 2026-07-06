package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View
import android.view.animation.DecelerateInterpolator

class HealthIndicatorView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    data class Indicator(val label: String, var value: Float, val color: Int, val maxValue: Float = 100f)

    private val indicators = mutableListOf(
        Indicator("الترطيب", 0f, Color.parseColor("#FF00D4FF")),
        Indicator("المسام", 0f, Color.parseColor("#FF7C3AED")),
        Indicator("الاحمرار", 0f, Color.parseColor("#FFD95353")),
        Indicator("الملمس", 0f, Color.parseColor("#FF52B788")),
        Indicator("حب الشباب", 0f, Color.parseColor("#FFFF6B6B")),
        Indicator("الحساسية", 0f, Color.parseColor("#FFFFB347")),
    )

    private val displayValues = FloatArray(indicators.size) { 0f }
    private val animators = mutableListOf<ValueAnimator?>()

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFFFFFFF")
        textSize = 28f
    }

    private val valuePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99FFFFFF")
        textSize = 22f
    }

    private val barBgPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#1AFFFFFF")
        style = Paint.Style.FILL
    }

    private val barPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private val barGlowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private val headerPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#66FFFFFF")
        textSize = 22f
        isFakeBoldText = true
    }

    init {
        for (i in indicators.indices) {
            animators.add(null)
        }
    }

    private val sectionHeight: Float
        get() {
            val density = resources.displayMetrics.density
            return 42f * density
        }

    private val barHeight: Float
        get() {
            val density = resources.displayMetrics.density
            return 14f * density
        }

    private val barMargin: Float
        get() {
            val density = resources.displayMetrics.density
            return 2f * density
        }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val desiredHeight = (60f + sectionHeight * indicators.size).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    fun setValue(index: Int, value: Float) {
        if (index < 0 || index >= indicators.size) return
        val oldTarget = indicators[index].value
        indicators[index] = indicators[index].copy(value = value.coerceIn(0f, 100f))

        animators[index]?.cancel()
        val anim = ValueAnimator.ofFloat(displayValues[index], indicators[index].value).apply {
            duration = 800L
            interpolator = DecelerateInterpolator()
            addUpdateListener {
                displayValues[index] = it.animatedValue as Float
                postInvalidate()
            }
            start()
        }
        animators[index] = anim
    }

    fun setHydration(v: Float) { setValue(0, v) }
    fun setPores(v: Float) { setValue(1, v) }
    fun setRedness(v: Float) { setValue(2, v) }
    fun setTexture(v: Float) { setValue(3, v) }
    fun setAcne(v: Float) { setValue(4, v) }
    fun setSensitivity(v: Float) { setValue(5, v) }

    fun estimateFromPositionScore(score: Int) {
        val base = score.coerceIn(0, 100).toFloat()
        setHydration(base * 0.8f)
        setPores(base * 0.6f)
        setRedness(100f - base * 0.7f)
        setTexture(base * 0.75f)
        setAcne(100f - base * 0.5f)
        setSensitivity(100f - base * 0.6f)
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val leftPadding = 16f * density
        val rightPadding = 16f * density
        val topStart = 30f * density
        val barWidth = width - leftPadding - rightPadding
        val barTop = 30f * density

        if (indicators.isEmpty()) return

        for (i in indicators.indices) {
            val ind = indicators[i]
            val y = barTop + i * sectionHeight

            labelPaint.color = Color.parseColor("#FFFFFFFF")
            labelPaint.textSize = 26f * density
            canvas.drawText(ind.label, leftPadding, y, labelPaint)

            val displayVal = displayValues[i]
            val valText = "${displayVal.toInt()}%"
            valuePaint.color = ind.color
            valuePaint.textSize = 22f * density
            val tw = valuePaint.measureText(valText)
            canvas.drawText(valText, width - rightPadding - tw, y, valuePaint)

            val bgTop = y + 6f * density
            val bgRect = RectF(leftPadding, bgTop, leftPadding + barWidth, bgTop + barHeight)
            barBgPaint.alpha = 40
            canvas.drawRoundRect(bgRect, 7f, 7f, barBgPaint)

            val fillW = barWidth * (displayVal / 100f).coerceIn(0f, 1f)
            if (fillW > 0f) {
                val fillRect = RectF(leftPadding, bgTop, leftPadding + fillW, bgTop + barHeight)
                barPaint.color = ind.color
                barPaint.alpha = 200
                canvas.drawRoundRect(fillRect, 7f, 7f, barPaint)

                barGlowPaint.color = ind.color
                barGlowPaint.alpha = 40
                val glowRect = RectF(leftPadding, bgTop - 2f * density,
                    (leftPadding + fillW + 8f * density).coerceAtMost(leftPadding + barWidth),
                    bgTop + barHeight + 2f * density)
                canvas.drawRoundRect(glowRect, 9f, 9f, barGlowPaint)
            }
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        animators.forEach { it?.cancel() }
    }
}
