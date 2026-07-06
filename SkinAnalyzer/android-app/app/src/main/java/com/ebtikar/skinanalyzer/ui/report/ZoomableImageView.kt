package com.ebtikar.skinanalyzer.ui.report

import android.content.Context
import android.graphics.Matrix
import android.graphics.PointF
import android.util.AttributeSet
import android.view.MotionEvent
import androidx.appcompat.widget.AppCompatImageView
import kotlin.math.max
import kotlin.math.min
import kotlin.math.sqrt

class ZoomableImageView @JvmOverloads constructor(
    context: Context, attrs: AttributeSet? = null, defStyleAttr: Int = 0
) : AppCompatImageView(context, attrs, defStyleAttr) {

    private val baseMatrix = Matrix()
    private val displayMatrix = Matrix()
    private val touchMatrix = Matrix()
    private var mode = NONE

    private val start = PointF()
    private val mid = PointF()
    private var oldDist = 1f

    private var zoomLevel = 0f
    private var minZoom = -6f
    private var maxZoom = 6f

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        super.onSizeChanged(w, h, oldw, oldh)
        scaleType = ScaleType.MATRIX
    }

    override fun onLayout(changed: Boolean, left: Int, top: Int, right: Int, bottom: Int) {
        super.onLayout(changed, left, top, right, bottom)
        if (changed && drawable != null) {
            computeInitialMatrix()
        }
    }

    override fun setImageBitmap(bm: android.graphics.Bitmap?) {
        super.setImageBitmap(bm)
        if (bm != null && width > 0 && height > 0) {
            computeInitialMatrix()
        }
    }

    private fun computeInitialMatrix() {
        val dw = drawable?.intrinsicWidth ?: return
        val dh = drawable?.intrinsicHeight ?: return
        val vw = width.toFloat()
        val vh = height.toFloat()
        baseMatrix.reset()
        val scale = min(vw / dw, vh / dh)
        val dx = (vw - dw * scale) / 2f
        val dy = (vh - dh * scale) / 2f
        baseMatrix.postScale(scale, scale)
        baseMatrix.postTranslate(dx, dy)
        displayMatrix.set(baseMatrix)
        touchMatrix.reset()
        zoomLevel = 0f
        setImageMatrix(displayMatrix)
    }

    fun resetZoom() {
        touchMatrix.reset()
        displayMatrix.set(baseMatrix)
        zoomLevel = 0f
        setImageMatrix(displayMatrix)
        mode = NONE
    }

    private fun zoomToScale(level: Float): Float {
        return if (level >= 0f) 1f + level * (5f / 6f)
        else 1f / (1f - level * (5f / 6f))
    }

    private fun scaleToZoom(scale: Float): Float {
        return if (scale >= 1f) (scale - 1f) * 6f / 5f
        else (1f - 1f / scale) * 6f / 5f
    }

    private fun applyMatrix() {
        val tmp = Matrix(baseMatrix)
        tmp.postConcat(touchMatrix)
        displayMatrix.set(tmp)
        setImageMatrix(displayMatrix)
    }

    override fun onTouchEvent(event: MotionEvent): Boolean {
        when (event.action and MotionEvent.ACTION_MASK) {
            MotionEvent.ACTION_DOWN -> {
                mode = DRAG
                start.set(event.x, event.y)
            }
            MotionEvent.ACTION_POINTER_DOWN -> {
                oldDist = spacing(event)
                if (oldDist > 10f) {
                    mode = ZOOM
                    midPoint(mid, event)
                }
            }
            MotionEvent.ACTION_MOVE -> {
                if (mode == ZOOM) {
                    val newDist = spacing(event)
                    if (newDist > 10f) {
                        val gestureScale = newDist / oldDist
                        val currentEffective = zoomToScale(zoomLevel)
                        val newEffective = currentEffective * gestureScale
                        val newLevel = scaleToZoom(newEffective)
                        if (newLevel in minZoom..maxZoom) {
                            zoomLevel = newLevel
                            touchMatrix.postScale(gestureScale, gestureScale, mid.x, mid.y)
                            oldDist = newDist
                        }
                    }
                } else if (mode == DRAG) {
                    touchMatrix.postTranslate(event.x - start.x, event.y - start.y)
                    start.set(event.x, event.y)
                }
                applyMatrix()
            }
            MotionEvent.ACTION_UP, MotionEvent.ACTION_POINTER_UP -> {
                if (mode == ZOOM || mode == DRAG) {
                    clampTranslation()
                }
                mode = NONE
            }
        }
        return true
    }

    private fun clampTranslation() {
        val vals = FloatArray(9)
        displayMatrix.getValues(vals)
        val scaleX = vals[Matrix.MSCALE_X]
        val scaleY = vals[Matrix.MSCALE_Y]
        val transX = vals[Matrix.MTRANS_X]
        val transY = vals[Matrix.MTRANS_Y]
        val dw = drawable?.intrinsicWidth ?: 0
        val dh = drawable?.intrinsicHeight ?: 0
        val vw = width.toFloat()
        val vh = height.toFloat()
        val imageW = dw * scaleX
        val imageH = dh * scaleY
        var adjX = transX
        var adjY = transY
        if (imageW > vw) {
            adjX = max(vw - imageW, min(0f, transX))
        } else {
            adjX = (vw - imageW) / 2f
        }
        if (imageH > vh) {
            adjY = max(vh - imageH, min(0f, transY))
        } else {
            adjY = (vh - imageH) / 2f
        }
        if (adjX != transX || adjY != transY) {
            touchMatrix.postTranslate(adjX - transX, adjY - transY)
            applyMatrix()
        }
    }

    private fun spacing(event: MotionEvent): Float {
        val dx = event.getX(0) - event.getX(1)
        val dy = event.getY(0) - event.getY(1)
        return sqrt(dx * dx + dy * dy)
    }

    private fun midPoint(point: PointF, event: MotionEvent) {
        point.set(
            (event.getX(0) + event.getX(1)) / 2f,
            (event.getY(0) + event.getY(1)) / 2f
        )
    }

    companion object {
        private const val NONE = 0
        private const val DRAG = 1
        private const val ZOOM = 2
    }
}
