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

class TrackingAccuracyView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var trackingAccuracy = 0f
    private var displayAccuracy = 0f
    private var scanArea = 0f
    private var faceDetected = false

    private var accuracyAnimator: ValueAnimator? = null

    private val arcPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val trackPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        color = Color.parseColor("#1A00D4FF")
        strokeCap = Paint.Cap.ROUND
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFFFFFFF")
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
    }

    private val valuePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
    }

    private val subLabelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99FFFFFF")
        textAlign = Paint.Align.CENTER
    }

    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private val bgPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#1A0D1F3A")
        style = Paint.Style.FILL
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val cx = width / 2f
        val cy = height * 0.38f
        val radius = (minOf(width, height) * 0.3f).coerceAtMost(80f * density)
        val strokeWidth = 10f * density

        trackPaint.strokeWidth = strokeWidth
        canvas.drawCircle(cx, cy, radius, trackPaint)

        val sweepAngle = (displayAccuracy / 100f) * 360f
        val color = when {
            displayAccuracy >= 70f -> Color.parseColor("#FF52B788")
            displayAccuracy >= 40f -> Color.parseColor("#FFFFB347")
            else -> Color.parseColor("#FFD95353")
        }
        arcPaint.color = color
        arcPaint.strokeWidth = strokeWidth

        val arcRect = RectF(cx - radius, cy - radius, cx + radius, cy + radius)
        canvas.drawArc(arcRect, -90f, sweepAngle, false, arcPaint)

        val pctText = "${displayAccuracy.toInt()}%"
        valuePaint.color = color
        valuePaint.textSize = 36f * density
        valuePaint.isFakeBoldText = true
        canvas.drawText(pctText, cx, cy + 12f * density, valuePaint)

        labelPaint.textSize = 20f * density
        canvas.drawText("دقة التتبع", cx, cy + radius + 30f * density, labelPaint)

        subLabelPaint.textSize = 18f * density
        val areaText = "مساحة المسح: ${scanArea.toInt()}%"
        canvas.drawText(areaText, cx, cy + radius + 56f * density, subLabelPaint)

        val faceText: String
        val faceColor: Int
        if (faceDetected) {
            faceText = "✓ الوجه: مكتشف"
            faceColor = Color.parseColor("#FF52B788")
        } else {
            faceText = "✗ الوجه: غير مكتشف"
            faceColor = Color.parseColor("#FFD95353")
        }
        subLabelPaint.color = faceColor
        subLabelPaint.textSize = 18f * density
        canvas.drawText(faceText, cx, cy + radius + 80f * density, subLabelPaint)
        subLabelPaint.color = Color.parseColor("#99FFFFFF")

        dotPaint.color = color
        dotPaint.alpha = 120
        canvas.drawCircle(cx, cy + radius + 16f * density, 3f * density, dotPaint)
    }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val density = resources.displayMetrics.density
        val desiredHeight = (200f * density).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    fun setTrackingAccuracy(value: Int) {
        trackingAccuracy = value.coerceIn(0, 100).toFloat()
        accuracyAnimator?.cancel()
        accuracyAnimator = ValueAnimator.ofFloat(displayAccuracy, trackingAccuracy).apply {
            duration = 1000L
            interpolator = DecelerateInterpolator()
            addUpdateListener {
                displayAccuracy = it.animatedValue as Float
                postInvalidate()
            }
            start()
        }
    }

    fun setScanArea(value: Int) {
        scanArea = value.coerceIn(0, 100).toFloat()
        postInvalidate()
    }

    fun setFaceDetected(detected: Boolean) {
        faceDetected = detected
        postInvalidate()
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        accuracyAnimator?.cancel()
    }
}
