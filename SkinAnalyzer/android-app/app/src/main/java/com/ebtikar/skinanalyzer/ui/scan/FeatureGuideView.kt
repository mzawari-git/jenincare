package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Path
import android.graphics.PathDashPathEffect
import android.util.AttributeSet
import android.view.View

class FeatureGuideView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    data class Feature(
        val icon: String,
        val title: String,
        val description: String,
        val color: Int,
        val targetX: Float = 0.3f,
        val targetY: Float = 0.5f
    )

    private val features = listOf(
        Feature("◉", "الماسح المركزي", "مسح ثلاثي الأبعاد للوجه", Color.parseColor("#FF00D4FF")),
        Feature("◉", "الشبكة الذكية", "تتبع دقيق لملامح الوجه", Color.parseColor("#FF7C3AED")),
        Feature("◉", "لوحة التحليل", "بيانات فورية لصحة الجلد", Color.parseColor("#FFD4AF37")),
        Feature("◉", "التوصيات", "نصائح عناية مخصصة", Color.parseColor("#FF52B788")),
    )

    private val titlePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#66FFFFFF")
        textSize = 22f
        isFakeBoldText = true
    }

    private val iconPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
        textSize = 28f
        textAlign = Paint.Align.CENTER
    }

    private val featureTitlePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#FFFFFFFF")
        textSize = 20f
        isFakeBoldText = true
    }

    private val descPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99FFFFFF")
        textSize = 17f
        isAntiAlias = true
    }

    private val linePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#1AFFFFFF")
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val density = resources.displayMetrics.density
        val lines = features.size * 2 + 2
        val desiredHeight = (lines * 24f * density + 30f * density).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val leftPad = 16f * density
        var y = 28f * density

        titlePaint.textSize = 22f * density
        canvas.drawText("دليل ميزات الواجهة", leftPad, y, titlePaint)
        y += 8f * density

        for (feature in features) {
            y += 28f * density

            iconPaint.color = feature.color
            iconPaint.textSize = 26f * density
            canvas.drawText(feature.icon, leftPad + 14f * density, y, iconPaint)

            featureTitlePaint.textSize = 20f * density
            featureTitlePaint.color = feature.color
            canvas.drawText(feature.title, leftPad + 34f * density, y, featureTitlePaint)

            descPaint.textSize = 17f * density
            descPaint.color = Color.parseColor("#99FFFFFF")
            canvas.drawText(feature.description, leftPad + 34f * density, y + 22f * density, descPaint)

            linePaint.color = Color.parseColor("#1AFFFFFF")
            linePaint.strokeWidth = 1f * density

            val lineStartX = leftPad + 14f * density
            val lineStartY = y - 14f * density
            val lineEndX = width * 0.85f
            val lineEndY = height * (features.indexOf(feature).toFloat() / features.size.toFloat() + 0.3f)

            val path = Path()
            path.moveTo(lineStartX, lineStartY)
            val ctrlX = (lineStartX + lineEndX) / 2f
            path.cubicTo(ctrlX, lineStartY, ctrlX, lineEndY, lineEndX, lineEndY)

            val dotPattern = Path()
            dotPattern.addCircle(0f, 0f, 2f * density, Path.Direction.CW)
            linePaint.pathEffect = PathDashPathEffect(dotPattern, 8f * density, 0f, PathDashPathEffect.Style.TRANSLATE)
            linePaint.alpha = 60
            canvas.drawPath(path, linePaint)
            linePaint.pathEffect = null
            linePaint.alpha = 255
        }
    }
}
