package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View

class FaceGridOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val gridLinePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#40FFFFFF")
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    private val faceOvalPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FF00D4FF")
        style = Paint.Style.STROKE
        strokeWidth = 2.5f
    }

    private val faceOvalGlowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#3300D4FF")
        style = Paint.Style.STROKE
        strokeWidth = 10f
    }

    private val faceOvalFillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#0D00D4FF")
        style = Paint.Style.FILL
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#AAFFFFFF")
        textSize = 11f
        textAlign = Paint.Align.CENTER
    }

    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#6600D4FF")
        style = Paint.Style.FILL
    }

    private val faceRect = RectF()
    private var faceDetected = false

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        super.onSizeChanged(w, h, oldw, oldh)
        val density = resources.displayMetrics.density
        val faceW = (220f * density).coerceAtMost(w * 0.55f)
        val faceH = (300f * density).coerceAtMost(h * 0.7f)
        val left = (w - faceW) / 2f
        val top = (h - faceH) / 2f
        faceRect.set(left, top, left + faceW, top + faceH)
    }

    override fun onDraw(canvas: Canvas) {
        drawGrid(canvas)
        drawFaceOval(canvas)
        drawCenterCrosshair(canvas)
        drawAlignmentDots(canvas)
        drawLabels(canvas)
    }

    private fun drawGrid(canvas: Canvas) {
        val w = width.toFloat()
        val h = height.toFloat()

        // 3x3 grid lines
        val thirdW = w / 3f
        val thirdH = h / 3f

        gridLinePaint.alpha = 50
        // Vertical
        canvas.drawLine(thirdW, 0f, thirdW, h, gridLinePaint)
        canvas.drawLine(thirdW * 2, 0f, thirdW * 2, h, gridLinePaint)
        // Horizontal
        canvas.drawLine(0f, thirdH, w, thirdH, gridLinePaint)
        canvas.drawLine(0f, thirdH * 2, w, thirdH * 2, gridLinePaint)

        // Subtle center cross
        gridLinePaint.alpha = 25
        canvas.drawLine(w / 2f, 0f, w / 2f, h, gridLinePaint)
        canvas.drawLine(0f, h / 2f, w, h / 2f, gridLinePaint)
    }

    private fun drawFaceOval(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val rx = faceRect.width() / 2f
        val ry = faceRect.height() / 2f

        canvas.drawOval(faceRect, faceOvalFillPaint)
        canvas.drawOval(faceRect, faceOvalGlowPaint)
        canvas.drawOval(faceRect, faceOvalPaint)

        // Eye-line guides
        val eyeY = cy - ry * 0.15f
        val guidePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#33FFD4AF37")
            style = Paint.Style.STROKE
            strokeWidth = 1f
            pathEffect = android.graphics.DashPathEffect(floatArrayOf(8f, 6f), 0f)
        }
        canvas.drawLine(cx - rx * 0.6f, eyeY, cx + rx * 0.6f, eyeY, guidePaint)

        // Nose-line guide
        val noseX = cx
        canvas.drawLine(noseX, cy - ry * 0.3f, noseX, cy + ry * 0.4f, guidePaint)
    }

    private fun drawCenterCrosshair(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val size = 12f

        val chPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#88FFD4AF37")
            style = Paint.Style.STROKE
            strokeWidth = 1.5f
        }
        canvas.drawLine(cx - size, cy, cx + size, cy, chPaint)
        canvas.drawLine(cx, cy - size, cx, cy + size, chPaint)
    }

    private fun drawAlignmentDots(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val rx = faceRect.width() / 2f
        val ry = faceRect.height() / 2f

        // Top, bottom, left, right alignment dots
        val dots = listOf(
            cx to faceRect.top,
            cx to faceRect.bottom,
            faceRect.left to cy,
            faceRect.right to cy
        )
        for ((x, y) in dots) {
            canvas.drawCircle(x, y, 4f, dotPaint)
        }

        // Center dot
        canvas.drawCircle(cx, cy, 3f, Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#FFFFD4AF37")
            style = Paint.Style.FILL
        })
    }

    private fun drawLabels(canvas: Canvas) {
        val cx = faceRect.centerX()
        labelPaint.textSize = 10f * resources.displayMetrics.density
        labelPaint.alpha = 120
        canvas.drawText("ضع وجهك هنا", cx, faceRect.bottom + 20f * resources.displayMetrics.density, labelPaint)
    }

    fun setFacePosition(centerX: Float, centerY: Float) {
        if (centerX < 0f || centerX > 1f || centerY < 0f || centerY > 1f) return
        val density = resources.displayMetrics.density
        val faceW = (220f * density).coerceAtMost(width * 0.55f)
        val faceH = (300f * density).coerceAtMost(height * 0.7f)
        val cx = centerX * width
        val cy = centerY * height
        val left = (cx - faceW / 2f).coerceIn(0f, (width - faceW).toFloat())
        val top = (cy - faceH / 2f).coerceIn(0f, (height - faceH).toFloat())
        faceRect.set(left, top, left + faceW, top + faceH)
        faceDetected = true
        faceOvalPaint.color = Color.parseColor("#FF00D4FF")
        invalidate()
    }

    fun setDetected(detected: Boolean) {
        faceDetected = detected
        faceOvalPaint.color = if (detected) Color.parseColor("#FF00E676") else Color.parseColor("#FF00D4FF")
        invalidate()
    }

    fun getFaceRect(): RectF = RectF(faceRect)
}
