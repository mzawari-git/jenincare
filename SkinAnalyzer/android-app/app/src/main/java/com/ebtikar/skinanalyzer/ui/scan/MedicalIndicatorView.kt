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

class MedicalIndicatorView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    data class Indicator(
        val label: String,
        val labelEn: String,
        var value: Float,
        val color: Int,
        val icon: String = ""
    )

    private val indicators = mutableListOf(
        Indicator("الترطيب", "Hydration", 0f, Color.parseColor("#FF00D4FF")),
        Indicator("المسام", "Pores", 0f, Color.parseColor("#FF7C3AED")),
        Indicator("الاحمرار", "Redness", 0f, Color.parseColor("#FFD95353")),
        Indicator("الملمس", "Texture", 0f, Color.parseColor("#FF52B788")),
        Indicator("حب الشباب", "Acne", 0f, Color.parseColor("#FFFFB347")),
        Indicator("الحساسية", "Sensitivity", 0f, Color.parseColor("#FF7C3AED")),
        Indicator("التصبغ", "Pigmentation", 0f, Color.parseColor("#FFD4AF37")),
    )

    private val displayValues = FloatArray(indicators.size) { 0f }
    private val animators = mutableListOf<ValueAnimator?>()

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val valuePaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val barBgPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val barPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val barGlowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val statusPaint = Paint(Paint.ANTI_ALIAS_FLAG)

    init {
        for (i in indicators.indices) animators.add(null)
    }

    private val sectionHeight: Float get() = 36f * resources.displayMetrics.density
    private val barHeight: Float get() = 10f * resources.displayMetrics.density

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val desiredHeight = (40f + sectionHeight * indicators.size).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    private fun setValue(index: Int, value: Float) {
        if (index < 0 || index >= indicators.size) return
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
    fun setPigmentation(v: Float) { setValue(6, v) }

    fun resetAll() {
        for (i in indicators.indices) {
            indicators[i] = indicators[i].copy(value = 0f)
            displayValues[i] = 0f
        }
        postInvalidate()
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val leftPad = 12f * density
        val rightPad = 12f * density
        val barWidth = width - leftPad - rightPad
        val topStart = 24f * density

        for (i in indicators.indices) {
            val ind = indicators[i]
            val y = topStart + i * sectionHeight
            val displayVal = displayValues[i]

            // Label
            labelPaint.color = Color.parseColor("#FFFFFFFF")
            labelPaint.textSize = 20f * density
            labelPaint.isFakeBoldText = false
            canvas.drawText(ind.label, leftPad, y, labelPaint)

            // Value
            val valText = "${displayVal.toInt()}"
            valuePaint.color = ind.color
            valuePaint.textSize = 18f * density
            valuePaint.isFakeBoldText = true
            val tw = valuePaint.measureText(valText)
            canvas.drawText(valText, width - rightPad - tw, y, valuePaint)

            // Percentage sign
            valuePaint.color = Color.parseColor("#66FFFFFF")
            valuePaint.textSize = 11f * density
            valuePaint.isFakeBoldText = false
            canvas.drawText("%", width - rightPad, y, valuePaint)

            // Status text
            val statusText = when {
                displayVal >= 78f -> "ممتاز"
                displayVal >= 60f -> "جيد"
                displayVal >= 40f -> "متوسط"
                displayVal >= 22f -> "ضعيف"
                else -> ""
            }
            if (statusText.isNotEmpty() && displayVal > 0f) {
                val statusColor = when {
                    displayVal >= 78f -> Color.parseColor("#FF52B788")
                    displayVal >= 60f -> Color.parseColor("#FF74C69D")
                    displayVal >= 40f -> Color.parseColor("#FFFFB347")
                    else -> Color.parseColor("#FFD95353")
                }
                statusPaint.color = statusColor
                statusPaint.textSize = 11f * density
                statusPaint.isFakeBoldText = true
                canvas.drawText(statusText, width - rightPad - tw - 40f * density, y, statusPaint)
            }

            // Bar background
            val bgTop = y + 6f * density
            val bgRect = RectF(leftPad, bgTop, leftPad + barWidth, bgTop + barHeight)
            barBgPaint.color = Color.parseColor("#1AFFFFFF")
            canvas.drawRoundRect(bgRect, 5f, 5f, barBgPaint)

            // Bar fill
            val fillW = barWidth * (displayVal / 100f).coerceIn(0f, 1f)
            if (fillW > 0f) {
                val fillRect = RectF(leftPad, bgTop, leftPad + fillW, bgTop + barHeight)
                barPaint.color = ind.color
                barPaint.alpha = 200
                canvas.drawRoundRect(fillRect, 5f, 5f, barPaint)

                barGlowPaint.color = ind.color
                barGlowPaint.alpha = 30
                val glowRect = RectF(leftPad, bgTop - 1f * density,
                    (leftPad + fillW + 4f * density).coerceAtMost(leftPad + barWidth),
                    bgTop + barHeight + 1f * density)
                canvas.drawRoundRect(glowRect, 6f, 6f, barGlowPaint)
            }
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        animators.forEach { it?.cancel() }
    }
}
