package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.LinearGradient
import android.graphics.Paint
import android.graphics.Path
import android.graphics.Shader
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator
import kotlin.math.abs

class DigitalMeshOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private data class MeshNode(
        val ox: Float, val oy: Float,
        val baseAlpha: Int = 180,
        val pulsePhase: Float = 0f,
        val feature: Boolean = false
    )

    private val nodes = mutableListOf<MeshNode>()
    private val featureLines = mutableListOf<Pair<Int, Int>>()
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
    private val contourPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
        strokeJoin = Paint.Join.ROUND
    }
    private val contourPath = Path()

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
        featureLines.clear()
        buildAnatomicalMesh(w, h)
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

        drawFaceContours(canvas, density)

        linePaint.color = Color.parseColor("#33D4AF37")
        linePaint.strokeWidth = 1.2f * density
        val connectionDist = 58f * density

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

        cyanLinePaint.color = Color.parseColor("#6600D4FF")
        cyanLinePaint.strokeWidth = 1.15f * density
        for ((a, b) in featureLines) {
            if (a in nodes.indices && b in nodes.indices) {
                cyanLinePaint.alpha = 110
                canvas.drawLine(nodeX(nodes[a]), nodeY(nodes[a]), nodeX(nodes[b]), nodeY(nodes[b]), cyanLinePaint)
            }
        }

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

        for (node in nodes) {
            val pulse = ((node.pulsePhase + nodePulse) % 1f)
            val alpha = (node.baseAlpha * (0.65f + 0.35f * pulse)).toInt().coerceIn(70, 230)
            val nx = nodeX(node)
            val ny = nodeY(node)

            nodeGlowPaint.color = if (node.feature) Color.parseColor("#2600D4FF") else Color.parseColor("#1AD4AF37")
            nodeGlowPaint.alpha = (alpha * 0.32f).toInt()
            canvas.drawCircle(nx, ny, if (node.feature) 10f * density else 7f * density, nodeGlowPaint)

            val distFromCenter = abs(nx - cx) / (width * 0.4f)
            val isGold = distFromCenter > 0.34f
            nodePaint.color = when {
                node.feature -> Color.parseColor("#FF00D4FF")
                isGold -> Color.parseColor("#FFE8D5A3")
                else -> Color.parseColor("#FFD4AF37")
            }
            nodePaint.alpha = alpha
            val r = ((if (node.feature) 3.2f else 2.1f) * density * (0.85f + 0.25f * pulse)).coerceAtLeast(1.4f)
            canvas.drawCircle(nx, ny, r, nodePaint)
        }

        linePaint.color = Color.parseColor("#1AD4AF37")
        linePaint.strokeWidth = 1f * density
        for (i in 0 until minOf(5, nodes.size)) {
            val idx = i * nodes.size / 5
            linePaint.alpha = (80 - i * 12).coerceIn(20, 80)
            canvas.drawLine(cx, cy, nodeX(nodes[idx]), nodeY(nodes[idx]), linePaint)
        }
    }

    private fun buildAnatomicalMesh(w: Int, h: Int) {
        val faceW = w * 0.36f
        val faceH = h * 0.56f
        val rx = faceW / 2f
        val ry = faceH / 2f
        val rows = 15
        val cols = 11

        for (row in 0..rows) {
            val yNorm = -1f + row * 2f / rows
            val rowWidth = kotlin.math.sqrt((1f - yNorm * yNorm * 0.86f).coerceAtLeast(0.05f))
            for (col in 0..cols) {
                val xNorm = -1f + col * 2f / cols
                if (abs(xNorm) <= rowWidth) {
                    val cheekCurve = 1f - 0.08f * abs(yNorm)
                    val ox = xNorm * rx * 0.78f * rowWidth * cheekCurve
                    val oy = yNorm * ry * 0.88f
                    val feature = (row in 5..10 && col in 4..7) || row == 6 || row == 11
                    nodes.add(MeshNode(ox, oy, if (feature) 210 else 145, ((row * 13 + col * 7) % 100) / 100f, feature))
                }
            }
        }

        val leftEye = addFeatureNode(-rx * 0.32f, -ry * 0.22f, 0.12f)
        val rightEye = addFeatureNode(rx * 0.32f, -ry * 0.22f, 0.24f)
        val noseTop = addFeatureNode(0f, -ry * 0.12f, 0.36f)
        val noseTip = addFeatureNode(0f, ry * 0.12f, 0.48f)
        val mouthL = addFeatureNode(-rx * 0.24f, ry * 0.38f, 0.60f)
        val mouthR = addFeatureNode(rx * 0.24f, ry * 0.38f, 0.72f)
        val chin = addFeatureNode(0f, ry * 0.74f, 0.84f)
        val browL = addFeatureNode(-rx * 0.34f, -ry * 0.36f, 0.18f)
        val browR = addFeatureNode(rx * 0.34f, -ry * 0.36f, 0.30f)

        featureLines.addAll(listOf(
            leftEye to noseTop,
            rightEye to noseTop,
            noseTop to noseTip,
            noseTip to mouthL,
            noseTip to mouthR,
            mouthL to mouthR,
            mouthL to chin,
            mouthR to chin,
            browL to leftEye,
            browR to rightEye
        ))
    }

    private fun addFeatureNode(ox: Float, oy: Float, phase: Float): Int {
        nodes.add(MeshNode(ox, oy, 230, phase, true))
        return nodes.lastIndex
    }

    private fun drawFaceContours(canvas: Canvas, density: Float) {
        val cx = width * faceX
        val cy = height * faceY
        val rx = width * 0.18f * faceScale
        val ry = height * 0.28f * faceScale

        contourPaint.strokeWidth = 1.4f * density
        contourPaint.alpha = 120
        contourPaint.shader = LinearGradient(cx - rx, cy, cx + rx, cy,
            intArrayOf(Color.parseColor("#00D4AF37"), Color.parseColor("#BBD4AF37"), Color.parseColor("#6600D4FF")),
            null,
            Shader.TileMode.CLAMP
        )

        contourPath.reset()
        contourPath.addOval(cx - rx, cy - ry, cx + rx, cy + ry, Path.Direction.CW)
        canvas.drawPath(contourPath, contourPaint)

        contourPath.reset()
        contourPath.moveTo(cx - rx * 0.55f, cy - ry * 0.18f)
        contourPath.cubicTo(cx - rx * 0.30f, cy - ry * 0.28f, cx - rx * 0.10f, cy - ry * 0.20f, cx, cy - ry * 0.05f)
        contourPath.cubicTo(cx + rx * 0.10f, cy - ry * 0.20f, cx + rx * 0.30f, cy - ry * 0.28f, cx + rx * 0.55f, cy - ry * 0.18f)
        canvas.drawPath(contourPath, contourPaint)

        contourPath.reset()
        contourPath.moveTo(cx - rx * 0.38f, cy + ry * 0.34f)
        contourPath.cubicTo(cx - rx * 0.12f, cy + ry * 0.43f, cx + rx * 0.12f, cy + ry * 0.43f, cx + rx * 0.38f, cy + ry * 0.34f)
        canvas.drawPath(contourPath, contourPaint)
        contourPaint.shader = null
    }
}
