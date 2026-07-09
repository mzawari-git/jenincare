package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View

data class AnalysisMarker(
    val x: Float,
    val y: Float,
    val type: MarkerType,
    val label: String,
    val confidence: Float = 0.8f,
    val radius: Float = 0.03f
) {
    enum class MarkerType {
        PIGMENTATION,
        ACNE,
        OILY,
        DRYNESS,
        DARK_CIRCLES
    }
}

class AnalysisMarkersOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val markers = mutableListOf<AnalysisMarker>()
    private var imageRect: RectF = RectF()

    private val colorMap = mapOf(
        AnalysisMarker.MarkerType.PIGMENTATION to Color.parseColor("#FF8D6E63"),
        AnalysisMarker.MarkerType.ACNE to Color.parseColor("#FFEF5350"),
        AnalysisMarker.MarkerType.OILY to Color.parseColor("#FFFFCA28"),
        AnalysisMarker.MarkerType.DRYNESS to Color.parseColor("#FF42A5F5"),
        AnalysisMarker.MarkerType.DARK_CIRCLES to Color.parseColor("#FFAB47BC")
    )

    private val colorLabelMap = mapOf(
        AnalysisMarker.MarkerType.PIGMENTATION to "الكلف والتصبغات",
        AnalysisMarker.MarkerType.ACNE to "حبوب والتهاب",
        AnalysisMarker.MarkerType.OILY to "دهون والمعان",
        AnalysisMarker.MarkerType.DRYNESS to "جفاف والتجاعيد",
        AnalysisMarker.MarkerType.DARK_CIRCLES to "هالات سوداء"
    )

    private val strokePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 3f
    }

    private val fillPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private val labelBgPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
        color = Color.parseColor("#CC000000")
    }

    private val labelTextPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.WHITE
        textSize = 11f
        textAlign = Paint.Align.LEFT
    }

    private val confidencePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#AAFFFFFF")
        textSize = 9f
        textAlign = Paint.Align.CENTER
    }

    private val connectorPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeWidth = 1.5f
        color = Color.parseColor("#66FFFFFF")
    }

    private var animationProgress = 0f
    private var animationRunning = false

    fun setMarkers(newMarkers: List<AnalysisMarker>, targetRect: RectF? = null) {
        markers.clear()
        markers.addAll(newMarkers)
        if (targetRect != null) {
            imageRect = targetRect
        }
        startAnimation()
    }

    fun clear() {
        markers.clear()
        animationRunning = false
        invalidate()
    }

    private fun startAnimation() {
        animationProgress = 0f
        animationRunning = true
        post(object : Runnable {
            override fun run() {
                animationProgress += 0.08f
                if (animationProgress >= 1f) {
                    animationProgress = 1f
                    animationRunning = false
                }
                invalidate()
                if (animationRunning) {
                    postDelayed(this, 16)
                }
            }
        })
    }

    override fun onDraw(canvas: Canvas) {
        if (markers.isEmpty()) return

        for (marker in markers) {
            val cx = imageRect.left + marker.x.coerceIn(0f, 1f) * imageRect.width()
            val cy = imageRect.top + marker.y.coerceIn(0f, 1f) * imageRect.height()
            val baseRadius = (30f + marker.radius * 200f) * resources.displayMetrics.density
            val radius = baseRadius * animationProgress
            val color = colorMap[marker.type] ?: Color.WHITE

            // Glow
            val glowPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
                this.color = color
                alpha = (40 * animationProgress).toInt()
                style = Paint.Style.FILL
            }
            canvas.drawCircle(cx, cy, radius * 1.5f, glowPaint)

            // Stroke
            strokePaint.color = color
            strokePaint.alpha = (200 * animationProgress).toInt()
            strokePaint.strokeWidth = 3f * resources.displayMetrics.density
            canvas.drawCircle(cx, cy, radius, strokePaint)

            // Inner fill
            fillPaint.color = color
            fillPaint.alpha = (30 * animationProgress).toInt()
            canvas.drawCircle(cx, cy, radius, fillPaint)

            // Label with connector line
            if (animationProgress > 0.5f) {
                val labelAlpha = ((animationProgress - 0.5f) * 2 * 255).toInt().coerceIn(0, 255)
                val labelText = colorLabelMap[marker.type] ?: marker.label
                val density = resources.displayMetrics.density

                val labelW = labelTextPaint.measureText(labelText) + 16f * density
                val labelH = 20f * density
                val labelX = cx + radius + 8f * density
                val labelY = cy - labelH / 2f

                // Connector line
                connectorPaint.alpha = labelAlpha / 2
                canvas.drawLine(cx + radius, cy, labelX, cy, connectorPaint)

                // Label background
                labelBgPaint.alpha = (labelAlpha * 0.8f / 255f * 255).toInt()
                val labelRect = RectF(labelX, labelY, labelX + labelW, labelY + labelH)
                canvas.drawRoundRect(labelRect, 4f * density, 4f * density, labelBgPaint)

                // Label text
                labelTextPaint.alpha = labelAlpha
                labelTextPaint.textSize = 11f * density
                canvas.drawText(labelText, labelX + 8f * density, labelY + labelH * 0.7f, labelTextPaint)

                // Confidence percentage
                confidencePaint.alpha = labelAlpha
                confidencePaint.textSize = 9f * density
                val confText = "${(marker.confidence * 100).toInt()}%"
                canvas.drawText(confText, labelX + labelW / 2f, labelY + labelH + 12f * density, confidencePaint)
            }
        }
    }
}
