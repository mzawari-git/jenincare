package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.DashPathEffect
import android.graphics.Paint
import android.graphics.Path
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View
import kotlin.math.sin

class FaceGridOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var animationPhase = 0f

    private val gridLinePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#2AFFFFFF")
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    private val faceOvalPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFE8D5A3")
        style = Paint.Style.STROKE
        strokeWidth = 2.5f
    }

    private val faceOvalGlowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#33D4AF37")
        style = Paint.Style.STROKE
        strokeWidth = 14f
    }

    private val faceOvalFillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#0800D4FF")
        style = Paint.Style.FILL
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#DFFFFFFF")
        textSize = 11f
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
    }

    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#CC00D4FF")
        style = Paint.Style.FILL
    }

    private val featurePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99D4AF37")
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
        strokeJoin = Paint.Join.ROUND
    }

    private val path = Path()
    private val faceRect = RectF()
    private var faceDetected = false

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        super.onSizeChanged(w, h, oldw, oldh)
        val density = resources.displayMetrics.density
        val faceW = (310f * density).coerceAtMost(w * 0.58f)
        val faceH = (400f * density).coerceAtMost(h * 0.74f)
        val left = (w - faceW) / 2f
        val top = (h - faceH) * 0.46f
        faceRect.set(left, top, left + faceW, top + faceH)
    }

    override fun onDraw(canvas: Canvas) {
        animationPhase = (animationPhase + 0.018f) % 1f
        drawGrid(canvas)
        drawFaceOval(canvas)
        drawFeatureGuides(canvas)
        drawCenterCrosshair(canvas)
        drawAlignmentDots(canvas)
        drawLabels(canvas)
        postInvalidateOnAnimation()
    }

    private fun drawGrid(canvas: Canvas) {
        val w = width.toFloat()
        val h = height.toFloat()

        val thirdW = w / 3f
        val thirdH = h / 3f

        gridLinePaint.alpha = 42
        gridLinePaint.pathEffect = DashPathEffect(floatArrayOf(12f, 14f), animationPhase * 26f)
        canvas.drawLine(thirdW, 0f, thirdW, h, gridLinePaint)
        canvas.drawLine(thirdW * 2, 0f, thirdW * 2, h, gridLinePaint)
        canvas.drawLine(0f, thirdH, w, thirdH, gridLinePaint)
        canvas.drawLine(0f, thirdH * 2, w, thirdH * 2, gridLinePaint)

        gridLinePaint.pathEffect = null
        gridLinePaint.alpha = 28
        canvas.drawLine(w / 2f, 0f, w / 2f, h, gridLinePaint)
        canvas.drawLine(0f, h / 2f, w, h / 2f, gridLinePaint)
    }

    private fun drawFaceOval(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val rx = faceRect.width() / 2f
        val ry = faceRect.height() / 2f

        faceOvalGlowPaint.alpha = (45 + 40 * sin(animationPhase * Math.PI * 2).toFloat()).toInt().coerceIn(24, 90)
        faceOvalPaint.color = if (faceDetected) Color.parseColor("#FF52B788") else Color.parseColor("#FFE8D5A3")
        canvas.drawOval(faceRect, faceOvalFillPaint)
        canvas.drawOval(faceRect, faceOvalGlowPaint)
        canvas.drawOval(faceRect, faceOvalPaint)

        val eyeY = cy - ry * 0.15f
        val guidePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#55D4AF37")
            style = Paint.Style.STROKE
            strokeWidth = 1.2f * resources.displayMetrics.density
            pathEffect = DashPathEffect(floatArrayOf(10f, 8f), animationPhase * 18f)
        }
        canvas.drawLine(cx - rx * 0.6f, eyeY, cx + rx * 0.6f, eyeY, guidePaint)

        val noseX = cx
        canvas.drawLine(noseX, cy - ry * 0.3f, noseX, cy + ry * 0.4f, guidePaint)
    }

    private fun drawFeatureGuides(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val rx = faceRect.width() / 2f
        val ry = faceRect.height() / 2f

        featurePaint.strokeWidth = 1.6f * density
        featurePaint.alpha = 125

        path.reset()
        path.addOval(cx - rx * 0.46f, cy - ry * 0.27f, cx - rx * 0.16f, cy - ry * 0.12f, Path.Direction.CW)
        path.addOval(cx + rx * 0.16f, cy - ry * 0.27f, cx + rx * 0.46f, cy - ry * 0.12f, Path.Direction.CW)
        canvas.drawPath(path, featurePaint)

        path.reset()
        path.moveTo(cx, cy - ry * 0.18f)
        path.cubicTo(cx - rx * 0.10f, cy + ry * 0.02f, cx - rx * 0.08f, cy + ry * 0.18f, cx, cy + ry * 0.24f)
        path.cubicTo(cx + rx * 0.08f, cy + ry * 0.18f, cx + rx * 0.10f, cy + ry * 0.02f, cx, cy - ry * 0.18f)
        canvas.drawPath(path, featurePaint)

        path.reset()
        path.moveTo(cx - rx * 0.34f, cy + ry * 0.38f)
        path.cubicTo(cx - rx * 0.12f, cy + ry * 0.48f, cx + rx * 0.12f, cy + ry * 0.48f, cx + rx * 0.34f, cy + ry * 0.38f)
        canvas.drawPath(path, featurePaint)
    }

    private fun drawCenterCrosshair(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val density = resources.displayMetrics.density
        val size = 16f * density

        val chPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#AAD4AF37")
            style = Paint.Style.STROKE
            strokeWidth = 1.5f * density
        }
        canvas.drawLine(cx - size, cy, cx + size, cy, chPaint)
        canvas.drawLine(cx, cy - size, cx, cy + size, chPaint)
    }

    private fun drawAlignmentDots(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val density = resources.displayMetrics.density

        val dots = listOf(
            cx to faceRect.top,
            cx to faceRect.bottom,
            faceRect.left to cy,
            faceRect.right to cy
        )
        for ((x, y) in dots) {
            canvas.drawCircle(x, y, 5f * density, dotPaint)
        }

        canvas.drawCircle(cx, cy, 3.5f * density, Paint(Paint.ANTI_ALIAS_FLAG).apply {
            color = Color.parseColor("#FFD4AF37")
            style = Paint.Style.FILL
        })
    }

    private fun drawLabels(canvas: Canvas) {
        val cx = faceRect.centerX()
        val density = resources.displayMetrics.density
        labelPaint.textSize = 11f * density
        labelPaint.alpha = 180
        val text = if (faceDetected) "FACE LOCKED" else "ALIGN FACE"
        canvas.drawText(text, cx, faceRect.bottom + 24f * density, labelPaint)
    }

    fun setFacePosition(centerX: Float, centerY: Float) {
        if (centerX < 0f || centerX > 1f || centerY < 0f || centerY > 1f) return
        val density = resources.displayMetrics.density
        val faceW = (310f * density).coerceAtMost(width * 0.58f)
        val faceH = (400f * density).coerceAtMost(height * 0.74f)
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
        invalidate()
    }

    fun getFaceRect(): RectF = RectF(faceRect)
}
