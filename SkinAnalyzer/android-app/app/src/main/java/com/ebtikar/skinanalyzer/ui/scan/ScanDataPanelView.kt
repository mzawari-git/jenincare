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

class ScanDataPanelView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var confidence = 0f
    private var displayConfidence = 0f
    private var trackingAccuracy = 0f
    private var displayTracking = 0f
    private var scanArea = 0f
    private var faceDetected = false
    private var processingTime = 0L
    private var lightingBalance = 0f

    private val confAnimator = MutableAnimator()
    private val trackAnimator = MutableAnimator()

    private class MutableAnimator {
        var animator: ValueAnimator? = null
        fun cancel() { animator?.cancel() }
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val valuePaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val subLabelPaint = Paint(Paint.ANTI_ALIAS_FLAG)
    private val arcPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }
    private val trackPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }
    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val density = resources.displayMetrics.density
        val desiredHeight = (220f * density).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val cx = width / 2f
        val topY = 24f * density

        // AI Confidence circular gauge
        val gaugeR = 36f * density
        val gaugeCx = 48f * density
        val gaugeCy = topY + gaugeR + 8f * density

        trackPaint.color = Color.parseColor("#1AFFFFFF")
        trackPaint.strokeWidth = 6f * density
        canvas.drawCircle(gaugeCx, gaugeCy, gaugeR, trackPaint)

        val confSweep = (displayConfidence / 100f) * 360f
        val confColor = when {
            displayConfidence >= 80f -> Color.parseColor("#FF52B788")
            displayConfidence >= 50f -> Color.parseColor("#FFFFB347")
            else -> Color.parseColor("#FFD95353")
        }
        arcPaint.color = confColor
        arcPaint.strokeWidth = 6f * density
        val arcRect = RectF(gaugeCx - gaugeR, gaugeCy - gaugeR, gaugeCx + gaugeR, gaugeCy + gaugeR)
        canvas.drawArc(arcRect, -90f, confSweep, false, arcPaint)

        valuePaint.color = confColor
        valuePaint.textSize = 16f * density
        valuePaint.isFakeBoldText = true
        valuePaint.textAlign = Paint.Align.CENTER
        canvas.drawText("${displayConfidence.toInt()}%", gaugeCx, gaugeCy + 6f * density, valuePaint)

        labelPaint.color = Color.parseColor("#88FFFFFF")
        labelPaint.textSize = 10f * density
        labelPaint.isFakeBoldText = false
        labelPaint.textAlign = Paint.Align.CENTER
        canvas.drawText("AI Confidence", gaugeCx, gaugeCy + gaugeR + 16f * density, labelPaint)

        // Tracking info on the right
        val infoX = 104f * density
        var infoY = topY + 10f * density

        // Tracking Accuracy
        labelPaint.textAlign = Paint.Align.LEFT
        labelPaint.color = Color.parseColor("#88FFFFFF")
        labelPaint.textSize = 11f * density
        canvas.drawText("دقة التتبع", infoX, infoY, labelPaint)

        val trackColor = when {
            displayTracking >= 70f -> Color.parseColor("#FF52B788")
            displayTracking >= 40f -> Color.parseColor("#FFFFB347")
            else -> Color.parseColor("#FFD95353")
        }
        valuePaint.color = trackColor
        valuePaint.textSize = 16f * density
        valuePaint.isFakeBoldText = true
        valuePaint.textAlign = Paint.Align.LEFT
        canvas.drawText("${displayTracking.toInt()}%", infoX, infoY + 18f * density, valuePaint)

        infoY += 44f * density

        // Face detection status
        val faceText = if (faceDetected) "✓ الوجه: مكتشف" else "✗ الوجه: غير مكتشف"
        val faceColor = if (faceDetected) Color.parseColor("#FF52B788") else Color.parseColor("#FFD95353")
        labelPaint.color = faceColor
        labelPaint.textSize = 11f * density
        canvas.drawText(faceText, infoX, infoY, labelPaint)

        infoY += 20f * density

        // Scan area
        labelPaint.color = Color.parseColor("#88FFFFFF")
        canvas.drawText("مساحة المسح: ${scanArea.toInt()}%", infoX, infoY, labelPaint)

        infoY += 20f * density

        // Image Quality
        labelPaint.color = Color.parseColor("#FF52B788")
        canvas.drawText("جودة الصورة: ممتازة", infoX, infoY, labelPaint)

        infoY += 20f * density

        // Processing time
        val timeStr = if (processingTime > 0) "${processingTime}ms" else "—"
        labelPaint.color = Color.parseColor("#88FFFFFF")
        canvas.drawText("وقت المعالجة: $timeStr", infoX, infoY, labelPaint)

        infoY += 20f * density

        // Lighting balance bar
        labelPaint.color = Color.parseColor("#88FFFFFF")
        canvas.drawText("توازن الإضاءة", infoX, infoY, labelPaint)
        val barLeft = infoX + 90f * density
        val barTop2 = infoY - 4f * density
        val barW = 70f * density
        val barH = 6f * density

        trackPaint.color = Color.parseColor("#1AFFFFFF")
        trackPaint.strokeWidth = barH
        canvas.drawLine(barLeft, barTop2, barLeft + barW, barTop2, trackPaint)

        val lightSweep = (lightingBalance / 100f) * barW
        arcPaint.color = Color.parseColor("#FFD4AF37")
        arcPaint.strokeWidth = barH
        canvas.drawLine(barLeft, barTop2, barLeft + lightSweep, barTop2, arcPaint)

        dotPaint.color = Color.parseColor("#FFD4AF37")
        canvas.drawCircle(barLeft + lightSweep, barTop2, 4f * density, dotPaint)
    }

    fun setConfidence(value: Float) {
        confidence = value.coerceIn(0f, 100f)
        confAnimator.cancel()
        confAnimator.animator = ValueAnimator.ofFloat(displayConfidence, confidence).apply {
            duration = 800L
            interpolator = DecelerateInterpolator()
            addUpdateListener {
                displayConfidence = it.animatedValue as Float
                postInvalidate()
            }
            start()
        }
    }

    fun setTrackingAccuracy(value: Int) {
        trackingAccuracy = value.coerceIn(0, 100).toFloat()
        trackAnimator.cancel()
        trackAnimator.animator = ValueAnimator.ofFloat(displayTracking, trackingAccuracy).apply {
            duration = 800L
            interpolator = DecelerateInterpolator()
            addUpdateListener {
                displayTracking = it.animatedValue as Float
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

    fun setProcessingTime(ms: Long) {
        processingTime = ms
        postInvalidate()
    }

    fun setLightingBalance(value: Float) {
        lightingBalance = value.coerceIn(0f, 100f)
        postInvalidate()
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        confAnimator.cancel()
        trackAnimator.cancel()
    }
}
