package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Context
import android.graphics.*
import android.util.AttributeSet
import android.view.View
import androidx.core.content.ContextCompat
import com.ebtikar.skinanalyzer.R

class FaceMeshOverlay @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private val meshPaint = Paint().apply {
        color = Color.parseColor("#00D4FF") // Cyan Sci-Fi
        style = Paint.Style.STROKE
        strokeWidth = 2f
        alpha = 180
        isAntiAlias = true
    }

    private val nodePaint = Paint().apply {
        color = Color.parseColor("#00D4FF")
        style = Paint.Style.FILL
        isAntiAlias = true
    }

    private var facePath = Path()
    private val points = mutableListOf<PointF>()

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)
        
        if (points.isEmpty()) return

        // Draw dynamic connecting lines (The Mesh)
        for (i in 0 until points.size) {
            val p1 = points[i]
            // Connect to nearby points to form a grid effect
            if (i + 1 < points.size) {
                val p2 = points[i+1]
                canvas.drawLine(p1.x, p1.y, p2.x, p2.y, meshPaint)
            }
            
            // Draw glowing nodes at key landmarks
            canvas.drawCircle(p1.x, p1.y, 4f, nodePaint)
        }
    }

    fun updateFacePoints(newPoints: List<PointF>) {
        points.clear()
        points.addAll(newPoints)
        invalidate()
    }
}
