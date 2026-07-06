package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.CornerPathEffect
import android.graphics.Paint
import android.graphics.Path
import android.graphics.PointF
import android.util.AttributeSet
import android.view.View
import android.view.animation.DecelerateInterpolator
import android.view.animation.LinearInterpolator
import kotlin.random.Random

class ScanGridView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val gridPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 1f * resources.displayMetrics.density
        color = Color.parseColor("#1A00D4FF")
    }

    private val scanLinePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f * resources.displayMetrics.density
        strokeCap = Paint.Cap.ROUND
        color = Color.parseColor("#FFD4AF37")
        pathEffect = CornerPathEffect(4f)
    }

    private val scanLineGlow = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 10f * resources.displayMetrics.density
        strokeCap = Paint.Cap.ROUND
        color = Color.parseColor("#33D4AF37")
    }

    private val faceRectPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f * resources.displayMetrics.density
        color = Color.parseColor("#FF00D4FF")
    }

    private val bracketPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 3f * resources.displayMetrics.density
        strokeCap = Paint.Cap.ROUND
        color = Color.parseColor("#FFD4AF37")
    }

    private val crosshairPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 2f * resources.displayMetrics.density
        color = Color.parseColor("#FFD4AF37")
    }

    private val particlePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private var scanLineY = 0f
    private var crosshairScale = 1f
    private var trackingScore = 50

    private data class Particle(
        var x: Float, var y: Float,
        var speedX: Float, var speedY: Float,
        var alpha: Int, val size: Float,
        val color: Int
    )

    private val particles = mutableListOf<Particle>()
    private var particleAnimator: ValueAnimator? = null
    private var scanLineAnimator: ValueAnimator? = null
    private var crosshairAnimator: ValueAnimator? = null

    private val faceRect = android.graphics.RectF()
    private var gridColumns = 12
    private var gridRows = 8

    init {
        startAnimations()
    }

    private fun startAnimations() {
        scanLineAnimator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 3000L
            repeatCount = ValueAnimator.INFINITE
            repeatMode = ValueAnimator.REVERSE
            interpolator = LinearInterpolator()
            addUpdateListener {
                scanLineY = it.animatedValue as Float
                invalidate()
            }
            start()
        }

        crosshairAnimator = ValueAnimator.ofFloat(1f, 1.2f, 1f).apply {
            duration = 2000L
            repeatCount = ValueAnimator.INFINITE
            interpolator = DecelerateInterpolator()
            addUpdateListener {
                crosshairScale = it.animatedValue as Float
                invalidate()
            }
            start()
        }

        particleAnimator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 2000L
            repeatCount = ValueAnimator.INFINITE
            interpolator = LinearInterpolator()
            addUpdateListener {
                updateParticles()
                invalidate()
            }
            start()
        }
    }

    private fun generateParticles() {
        particles.clear()
        val density = resources.displayMetrics.density
        val seed = Random(System.currentTimeMillis())
        repeat(20) {
            particles.add(Particle(
                x = seed.nextFloat() * width,
                y = seed.nextFloat() * height,
                speedX = (seed.nextFloat() - 0.5f) * 2f,
                speedY = (seed.nextFloat() - 0.5f) * 1.5f,
                alpha = (seed.nextInt(100) + 80).coerceIn(0, 180),
                size = (seed.nextFloat() * 3f + 2f) * density,
                color = if (seed.nextBoolean())
                    Color.parseColor("#FF00D4FF") else Color.parseColor("#FFD4AF37")
            ))
        }
    }

    private fun updateParticles() {
        for (p in particles) {
            p.x += p.speedX
            p.y += p.speedY
            if (p.x < 0 || p.x > width) { p.speedX *= -1; p.x = p.x.coerceIn(0f, width.toFloat()) }
            if (p.y < 0 || p.y > height) { p.speedY *= -1; p.y = p.y.coerceIn(0f, height.toFloat()) }
            p.alpha = ((p.alpha + Random.nextInt(-5, 6))).coerceIn(60, 200)
        }
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        super.onSizeChanged(w, h, oldw, oldh)
        val density = resources.displayMetrics.density
        val faceW = (260f * density).coerceAtMost(w * 0.65f)
        val faceH = (320f * density).coerceAtMost(h * 0.75f)
        val left = (w - faceW) / 2f
        val top = (h - faceH) / 2f
        faceRect.set(left, top, left + faceW, top + faceH)

        val spacing = 40f * density
        gridColumns = (w / spacing).toInt().coerceAtLeast(6)
        gridRows = (h / spacing).toInt().coerceAtLeast(4)

        generateParticles()
    }

    fun setTrackingScore(score: Int) {
        trackingScore = score.coerceIn(0, 100)
        invalidate()
    }

    fun setFaceRect(rect: android.graphics.RectF) {
        faceRect.set(rect)
        invalidate()
    }

    override fun onDraw(canvas: Canvas) {
        drawGrid(canvas)
        drawFaceRect(canvas)
        drawBrackets(canvas)
        drawScanLine(canvas)
        drawParticles(canvas)
        drawCrosshair(canvas)
    }

    private fun drawGrid(canvas: Canvas) {
        val cx = width / 2f
        val cy = height / 2f
        val spacingX = width.toFloat() / (gridColumns + 1)
        val spacingY = height.toFloat() / (gridRows + 1)

        gridPaint.color = Color.parseColor("#1A00D4FF")

        for (col in 1..gridColumns) {
            val x = col * spacingX
            val distFromCenter = Math.abs(x - cx) / (width / 2f)
            val alpha = ((1f - distFromCenter * 0.7f) * 0.15f * 255f).toInt().coerceIn(15, 60)
            gridPaint.alpha = alpha
            canvas.drawLine(x, 0f, x, height.toFloat(), gridPaint)
        }

        for (row in 1..gridRows) {
            val y = row * spacingY
            val distFromCenter = Math.abs(y - cy) / (height / 2f)
            val alpha = ((1f - distFromCenter * 0.7f) * 0.15f * 255f).toInt().coerceIn(15, 60)
            gridPaint.alpha = alpha
            canvas.drawLine(0f, y, width.toFloat(), y, gridPaint)
        }
    }

    private fun drawFaceRect(canvas: Canvas) {
        val glow = Paint(faceRectPaint).apply {
            strokeWidth = 8f * resources.displayMetrics.density
            alpha = 30 + (trackingScore * 0.7f).toInt().coerceIn(0, 60)
            color = Color.parseColor("#3300D4FF")
        }
        canvas.drawRoundRect(faceRect, 24f, 24f, glow)

        faceRectPaint.alpha = 180 + (trackingScore * 0.7f).toInt().coerceIn(0, 75)
        canvas.drawRoundRect(faceRect, 24f, 24f, faceRectPaint)
    }

    private fun drawBrackets(canvas: Canvas) {
        val bracketLen = 40f * resources.displayMetrics.density
        val inset = 4f * resources.displayMetrics.density
        bracketPaint.alpha = 200

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

    private fun drawScanLine(canvas: Canvas) {
        val y = scanLineY * height
        val left = faceRect.left
        val right = faceRect.right

        scanLineGlow.alpha = 40
        canvas.drawLine(left, y, right, y, scanLineGlow)
        scanLinePaint.alpha = 200
        canvas.drawLine(left, y, right, y, scanLinePaint)

        val dot = Paint(Paint.ANTI_ALIAS_FLAG).apply {
            style = Paint.Style.FILL
            color = Color.parseColor("#FFD4AF37")
        }
        canvas.drawCircle(left, y, 6f, dot)
        canvas.drawCircle(right, y, 6f, dot)
    }

    private fun drawParticles(canvas: Canvas) {
        for (p in particles) {
            particlePaint.color = p.color
            particlePaint.alpha = p.alpha
            canvas.drawCircle(p.x, p.y, p.size, particlePaint)
        }
    }

    private fun drawCrosshair(canvas: Canvas) {
        val cx = faceRect.centerX()
        val cy = faceRect.centerY()
        val size = (12f * resources.displayMetrics.density) * crosshairScale

        crosshairPaint.alpha = 180
        canvas.drawLine(cx - size, cy, cx + size, cy, crosshairPaint)
        canvas.drawLine(cx, cy - size, cx, cy + size, crosshairPaint)
        canvas.drawCircle(cx, cy, size * 0.5f, crosshairPaint)
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        scanLineAnimator?.cancel()
        crosshairAnimator?.cancel()
        particleAnimator?.cancel()
    }
}
