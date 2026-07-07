package com.ebtikar.skinanalyzer.ui.components

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View
import com.ebtikar.skinanalyzer.model.HeatmapPoint

class HeatmapOverlayView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var points: List<HeatmapPoint> = emptyList()
    private var imageRect: RectF = RectF()

    private val goodPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.argb(120, 82, 183, 136)
        style = Paint.Style.FILL
    }

    private val moderatePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.argb(120, 232, 168, 56)
        style = Paint.Style.FILL
    }

    private val concernPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.argb(120, 224, 112, 112)
        style = Paint.Style.FILL
    }

    private val borderPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.argb(60, 255, 255, 255)
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.WHITE
        textSize = 24f
        textAlign = Paint.Align.CENTER
        setShadowLayer(2f, 1f, 1f, Color.BLACK)
    }

    fun setHeatmapData(heatmapPoints: List<HeatmapPoint>, imgRect: RectF) {
        this.points = heatmapPoints
        this.imageRect = imgRect
        invalidate()
    }

    fun clear() {
        points = emptyList()
        invalidate()
    }

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)
        if (points.isEmpty() || imageRect.isEmpty) return

        for (point in points) {
            val cx = imageRect.left + point.x.coerceIn(0f, 1f) * imageRect.width()
            val cy = imageRect.top + point.y.coerceIn(0f, 1f) * imageRect.height()
            val radius = 30f + point.value.coerceIn(0f, 1f) * 40f

            val paint = when {
                point.value < 0.33f -> goodPaint
                point.value < 0.66f -> moderatePaint
                else -> concernPaint
            }

            canvas.drawCircle(cx, cy, radius, paint)
            canvas.drawCircle(cx, cy, radius, borderPaint)

            point.label?.let { label ->
                canvas.drawText(label, cx, cy - radius - 5f, labelPaint)
            }
        }
    }
}
