package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator
import kotlin.math.abs
import kotlin.random.Random

class DigitalMeshOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private data class MeshNode(
        val ox: Float, val oy: Float,
        val baseAlpha: Int = 180,
        val pulsePhase: Float = 0f
    )

    private val nodes = mutableListOf<MeshNode>()
    private var nodePulse = 0f
    private var time = 0f
    private var pulseAnimator: ValueAnimator? = null
    private var faceX = 0.5f
    private var faceY = 0.45f
    private var faceScale = 1.0f
    private var offsetX = 0f
    private var offsetY = 0f

    private val linePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 1.5f
        strokeCap = Paint.Cap.ROUND
    }
    private val nodePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val nodeGlowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val cyanLinePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    init {
        setLayerType(View.LAYER_TYPE_HARDWARE, null)
        startAnimations()
    }

    private fun startAnimations() {
        pulseAnimator = ValueAnimator.ofFloat(0f, 1f).apply {
            duration = 3000L
            repeatCount = ValueAnimator.INFINITE
            interpolator = LinearInterpolator()
            addUpdateListener {
                time = it.animatedValue as Float
                nodePulse = (abs((time * 2f) % 2f - 1f))
                invalidate()
            }
            start()
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        pulseAnimator?.cancel()
        pulseAnimator = null
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        nodes.clear()
        val density = resources.displayMetrics.density
        val seed = Random(System.currentTimeMillis())
        val faceW = w * 0.35f
        val faceH = h * 0.5f

        val cols = 12
        val rows = 14
        val spacingX = faceW / cols
        val spacingY = faceH / rows

        for (row in 0..rows) {
            for (col in 0..cols) {
                val ox = (col - cols / 2f) * spacingX + seed.nextFloat() * 6f * density
                val oy = (row - rows / 2f) * spacingY + seed.nextFloat() * 6f * density
                if (ox * ox / (faceW * 0.3f) + oy * oy / (faceH * 0.3f) < 0.9f) {
                    nodes.add(MeshNode(ox, oy, seed.nextInt(120, 220), seed.nextFloat() * 3f))
                }
            }
        }
    }

    fun updateFacePosition(x: Float, y: Float, scale: Float = 1.0f) {
        faceX = x.coerceIn(0f, 1f)
        faceY = y.coerceIn(0f, 1f)
        faceScale = scale.coerceIn(0.5f, 2.0f)
        offsetX = (faceX - 0.5f) * width
        offsetY = (faceY - 0.45f) * height
        invalidate()
    }

    private fun nodeX(node: MeshNode): Float = width / 2f + node.ox * faceScale + offsetX
    private fun nodeY(node: MeshNode): Float = height * 0.45f + node.oy * faceScale + offsetY

    override fun onDraw(canvas: Canvas) {
        if (nodes.isEmpty()) return
        val density = resources.displayMetrics.density
        val cx = width * faceX
        val cy = height * faceY

        // Draw mesh lines (golden)
        linePaint.color = Color.parseColor("#33D4AF37")
        linePaint.strokeWidth = 1.2f * density
        val connectionDist = 70f * density

        for (i in nodes.indices) {
            for (j in i + 1 until nodes.size) {
                val dx = nodeX(nodes[i]) - nodeX(nodes[j])
                val dy = nodeY(nodes[i]) - nodeY(nodes[j])
                val dist = dx * dx + dy * dy
                if (dist < connectionDist * connectionDist && dist > 0) {
                    val alpha = ((1f - dist / (connectionDist * connectionDist)) * 80).toInt().coerceIn(20, 80)
                    linePaint.alpha = alpha
                    canvas.drawLine(nodeX(nodes[i]), nodeY(nodes[i]), nodeX(nodes[j]), nodeY(nodes[j]), linePaint)
                }
            }
        }

        // Draw cyan accent lines (neural network style)
        cyanLinePaint.color = Color.parseColor("#1A00D4FF")
        cyanLinePaint.strokeWidth = 0.8f * density
        for (i in nodes.indices step 3) {
            if (i + 3 < nodes.size) {
                val dx = nodeX(nodes[i]) - nodeX(nodes[i + 3])
                val dy = nodeY(nodes[i]) - nodeY(nodes[i + 3])
                if (dx * dx + dy * dy < connectionDist * connectionDist * 2) {
                    cyanLinePaint.alpha = 40
                    canvas.drawLine(nodeX(nodes[i]), nodeY(nodes[i]), nodeX(nodes[i + 3]), nodeY(nodes[i + 3]), cyanLinePaint)
                }
            }
        }

        // Draw glowing nodes
        for (node in nodes) {
            val pulse = ((node.pulsePhase + nodePulse) % 1f)
            val alpha = (node.baseAlpha * (0.6f + 0.4f * pulse)).toInt().coerceIn(80, 220)
            val nx = nodeX(node)
            val ny = nodeY(node)

            // Glow
            nodeGlowPaint.color = Color.parseColor("#1AD4AF37")
            nodeGlowPaint.alpha = (alpha * 0.3f).toInt()
            canvas.drawCircle(nx, ny, 8f * density * (0.8f + 0.3f * pulse), nodeGlowPaint)

            // Core dot
            val distFromCenter = abs(nx - cx) / (width * 0.4f)
            val isGold = distFromCenter > 0.3f
            nodePaint.color = if (isGold) Color.parseColor("#FFD4AF37") else Color.parseColor("#FF00D4FF")
            nodePaint.alpha = alpha
            val r = (2.5f * density * (0.8f + 0.3f * pulse)).coerceAtLeast(1.5f)
            canvas.drawCircle(nx, ny, r, nodePaint)
        }

        // Draw connection lines from lens to nodes
        linePaint.color = Color.parseColor("#1AD4AF37")
        linePaint.strokeWidth = 1f * density
        for (i in 0 until minOf(5, nodes.size)) {
            val idx = i * nodes.size / 5
            linePaint.alpha = (80 - i * 12).coerceIn(20, 80)
            canvas.drawLine(cx, cy, nodeX(nodes[idx]), nodeY(nodes[idx]), linePaint)
        }
    }
}
