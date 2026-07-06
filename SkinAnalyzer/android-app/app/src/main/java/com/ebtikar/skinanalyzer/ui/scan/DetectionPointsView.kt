package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.util.AttributeSet
import android.view.View
import kotlin.math.sqrt

class DetectionPointsView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FF00D4FF")
        style = Paint.Style.FILL
    }

    private val ringPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#4000D4FF")
        style = Paint.Style.STROKE
        strokeWidth = 2f * resources.displayMetrics.density
    }

    private val linePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#2000D4FF")
        style = Paint.Style.STROKE
        strokeWidth = 1f * resources.displayMetrics.density
    }

    private var points = listOf<PointF>()
    private var maxConnectDist = 0.3f

    data class PointF(val x: Float, val y: Float)

    fun setDetectionPoints(pts: List<PointF>) {
        points = pts
        invalidate()
    }

    fun clearPoints() {
        points = emptyList()
        invalidate()
    }

    private fun distance(a: PointF, b: PointF): Float {
        return sqrt((a.x - b.x) * (a.x - b.x) + (a.y - b.y) * (a.y - b.y))
    }

    override fun onDraw(canvas: Canvas) {
        if (points.isEmpty()) return

        for (i in points.indices) {
            for (j in i + 1 until points.size) {
                val dist = distance(points[i], points[j])
                if (dist < maxConnectDist) {
                    val alpha = ((maxConnectDist - dist) / maxConnectDist * 80).toInt().coerceIn(10, 80)
                    linePaint.alpha = alpha
                    canvas.drawLine(
                        points[i].x * width, points[i].y * height,
                        points[j].x * width, points[j].y * height,
                        linePaint
                    )
                }
            }
        }

        val density = resources.displayMetrics.density
        for (point in points) {
            val px = point.x * width
            val py = point.y * height

            ringPaint.alpha = 60
            canvas.drawCircle(px, py, 14f * density, ringPaint)

            dotPaint.alpha = 220
            canvas.drawCircle(px, py, 5f * density, dotPaint)
        }
    }
}
