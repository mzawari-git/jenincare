package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.DashPathEffect
import android.graphics.LinearGradient
import android.graphics.Paint
import android.graphics.Path
import android.graphics.PorterDuff
import android.graphics.PorterDuffXfermode
import android.graphics.RectF
import android.graphics.Shader
import android.util.AttributeSet
import android.view.View
import kotlin.math.sin

class FaceGuideOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var pulse = 0f

    private val overlayPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#B0000000")
        style = Paint.Style.FILL
    }

    private val clearPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        xfermode = PorterDuffXfermode(PorterDuff.Mode.CLEAR)
    }

    private val borderPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFE8D5A3")
        style = Paint.Style.STROKE
        strokeWidth = 2.5f
        isAntiAlias = true
    }

    private val glowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#40D4AF37")
        style = Paint.Style.STROKE
        strokeWidth = 16f
        isAntiAlias = true
    }

    private val bracketPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFD4AF37")
        style = Paint.Style.STROKE
        strokeWidth = 3.5f
        strokeCap = Paint.Cap.ROUND
        isAntiAlias = true
    }

    private val gridPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#2200D4FF")
        style = Paint.Style.STROKE
        strokeWidth = 1f
        isAntiAlias = true
    }

    private val markPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#80E8D5A3")
        style = Paint.Style.STROKE
        strokeWidth = 1f
        isAntiAlias = true
    }

    private val scanPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#E6FFFFFF")
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
    }

    private val faceRect = RectF()
    private val facePath = Path()
    private val scanPath = Path()

    init {
        setLayerType(View.LAYER_TYPE_SOFTWARE, null)
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        val density = resources.displayMetrics.density
        val faceW = (310f * density).coerceAtMost(w * 0.58f)
        val faceH = (400f * density).coerceAtMost(h * 0.74f)
        val left = (w - faceW) / 2f
        val top = (h - faceH) * 0.46f
        faceRect.set(left, top, left + faceW, top + faceH)
        updateFacePath()
    }

    override fun onDraw(canvas: Canvas) {
        pulse = (pulse + 0.018f) % 1f
        val sc = canvas.saveLayer(0f, 0f, width.toFloat(), height.toFloat(), null)

        canvas.drawRect(0f, 0f, width.toFloat(), height.toFloat(), overlayPaint)

        canvas.drawPath(facePath, clearPaint)
        canvas.restoreToCount(sc)

        drawInnerGrid(canvas)

        val pulseAlpha = (70 + 55 * sin(pulse * Math.PI * 2).toFloat()).toInt().coerceIn(35, 125)
        glowPaint.alpha = pulseAlpha
        borderPaint.alpha = 235
        canvas.drawOval(faceRect, glowPaint)
        canvas.drawOval(faceRect, borderPaint)

        drawCornerBrackets(canvas)
        drawMeasurementMarks(canvas)
        drawScanLine(canvas)
        drawStatusLabel(canvas)
        postInvalidateOnAnimation()
    }

    private fun drawInnerGrid(canvas: Canvas) {
        val density = resources.displayMetrics.density
        gridPaint.pathEffect = DashPathEffect(floatArrayOf(8f * density, 10f * density), 0f)
        gridPaint.alpha = 62

        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val rx = faceRect.width() / 2f
        val ry = faceRect.height() / 2f

        for (i in -2..2) {
            val x = cx + i * rx / 3f
            scanPath.reset()
            scanPath.moveTo(x, cy - ry * 0.82f)
            scanPath.cubicTo(x - i * 8f * density, cy - ry * 0.25f, x + i * 8f * density, cy + ry * 0.25f, x, cy + ry * 0.82f)
            canvas.drawPath(scanPath, gridPaint)
        }

        for (i in -3..3) {
            val y = cy + i * ry / 4.5f
            val ratio = kotlin.math.sqrt((1f - ((y - cy) / ry) * ((y - cy) / ry)).coerceAtLeast(0f))
            canvas.drawLine(cx - rx * ratio * 0.82f, y, cx + rx * ratio * 0.82f, y, gridPaint)
        }
        gridPaint.pathEffect = null
    }

    private fun drawCornerBrackets(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val bracketLen = 42f * density
        val inset = 12f * density

        bracketPaint.alpha = 220

        val corners = listOf(
            faceRect.left - inset to faceRect.top - inset,
            faceRect.right + inset to faceRect.top - inset,
            faceRect.left - inset to faceRect.bottom + inset,
            faceRect.right + inset to faceRect.bottom + inset
        )

        for ((x, y) in corners) {
            val isLeft = x < width / 2f
            val isTop = y < height / 2f
            val signX = if (isLeft) 1f else -1f
            val signY = if (isTop) 1f else -1f

            canvas.drawLine(x, y, x + signX * bracketLen, y, bracketPaint)
            canvas.drawLine(x, y, x, y + signY * bracketLen, bracketPaint)
        }
    }

    private fun drawMeasurementMarks(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val markLen = 8f * density
        val step = 42f * density

        var pos = faceRect.top + step
        while (pos < faceRect.bottom) {
            markPaint.alpha = 120
            canvas.drawLine(faceRect.left - markLen, pos, faceRect.left, pos, markPaint)
            canvas.drawLine(faceRect.right, pos, faceRect.right + markLen, pos, markPaint)
            pos += step
        }

        pos = faceRect.left + step
        while (pos < faceRect.right) {
            markPaint.alpha = 120
            canvas.drawLine(pos, faceRect.top - markLen, pos, faceRect.top, markPaint)
            canvas.drawLine(pos, faceRect.bottom, pos, faceRect.bottom + markLen, markPaint)
            pos += step
        }
    }

    private fun drawScanLine(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val y = faceRect.top + faceRect.height() * pulse
        val cy = faceRect.centerY()
        val ry = faceRect.height() / 2f
        val rx = faceRect.width() / 2f
        val ratio = kotlin.math.sqrt((1f - ((y - cy) / ry) * ((y - cy) / ry)).coerceAtLeast(0f))
        val left = faceRect.centerX() - rx * ratio * 0.86f
        val right = faceRect.centerX() + rx * ratio * 0.86f
        scanPaint.strokeWidth = 3f * density
        scanPaint.shader = LinearGradient(left, y, right, y,
            intArrayOf(Color.TRANSPARENT, Color.parseColor("#FF00D4FF"), Color.parseColor("#FFD4AF37"), Color.TRANSPARENT),
            floatArrayOf(0f, 0.35f, 0.65f, 1f),
            Shader.TileMode.CLAMP
        )
        canvas.drawLine(left, y, right, y, scanPaint)
        scanPaint.shader = null
    }

    private fun drawStatusLabel(canvas: Canvas) {
        val density = resources.displayMetrics.density
        labelPaint.textSize = 13f * density
        canvas.drawText("FACE POSITIONING", faceRect.centerX(), faceRect.top - 28f * density, labelPaint)
    }

    private fun updateFacePath() {
        facePath.reset()
        facePath.addOval(faceRect, Path.Direction.CW)
    }

    fun getFaceRect(): RectF = RectF(faceRect)
    fun getFaceCenterY(): Float = faceRect.centerY()

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
        updateFacePath()
        invalidate()
    }
}
