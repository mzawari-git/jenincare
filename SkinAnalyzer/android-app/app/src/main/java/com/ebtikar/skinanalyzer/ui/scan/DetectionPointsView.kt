package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.util.AttributeSet
import android.view.View

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

    private var points = listOf<PointF>()

    data class PointF(val x: Float, val y: Float)

    fun setDetectionPoints(pts: List<PointF>) {
        points = pts
        invalidate()
    }

    fun clearPoints() {
        points = emptyList()
        invalidate()
    }

    override fun onDraw(canvas: Canvas) {
        for (point in points) {
            val px = point.x * width
            val py = point.y * height
            canvas.drawCircle(px, py, 6f, dotPaint)
            canvas.drawCircle(px, py, 16f, ringPaint)
        }
    }
}
