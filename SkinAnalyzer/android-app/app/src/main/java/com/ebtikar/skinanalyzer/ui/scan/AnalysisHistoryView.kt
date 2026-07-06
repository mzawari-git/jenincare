package com.ebtikar.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.util.AttributeSet
import android.view.View

class AnalysisHistoryView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private data class HistoryEntry(val date: String, val score: Int)

    private val history = mutableListOf<HistoryEntry>()
    private val recommendations = mutableListOf<String>()

    private val headerPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#66FFFFFF")
        textSize = 22f
        isFakeBoldText = true
    }

    private val historyPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99FFFFFF")
        textSize = 20f
    }

    private val recPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#99FFFFFF")
        textSize = 18f
        isAntiAlias = true
    }

    private val recTitlePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#66FFFFFF")
        textSize = 20f
        isFakeBoldText = true
    }

    private val dotPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }

    private val separatorPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        color = Color.parseColor("#1AFFFFFF")
        style = Paint.Style.STROKE
        strokeWidth = 1f
    }

    override fun onMeasure(widthMeasureSpec: Int, heightMeasureSpec: Int) {
        val density = resources.displayMetrics.density
        val lines = history.size + recommendations.size + 4
        val desiredHeight = (lines * 28f * density + 40f * density).toInt()
        setMeasuredDimension(
            MeasureSpec.getSize(widthMeasureSpec),
            resolveSize(desiredHeight, heightMeasureSpec)
        )
    }

    fun setHistory(entries: List<Pair<String, Int>>) {
        history.clear()
        for ((date, score) in entries) {
            history.add(HistoryEntry(date, score))
        }
        postInvalidate()
    }

    fun setRecommendations(recs: List<String>) {
        recommendations.clear()
        recommendations.addAll(recs)
        postInvalidate()
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val leftPad = 16f * density
        var y = 28f * density

        headerPaint.textSize = 22f * density
        canvas.drawText("سجل التحليل الأخير", leftPad, y, headerPaint)
        y += 8f * density

        if (history.isEmpty()) {
            historyPaint.textSize = 18f * density
            historyPaint.color = Color.parseColor("#66FFFFFF")
            canvas.drawText("لا توجد فحوصات سابقة", leftPad, y + 24f * density, historyPaint)
            y += 56f * density
        } else {
            historyPaint.textSize = 20f * density
            for (entry in history) {
                y += 26f * density
                val scoreColor = when {
                    entry.score >= 70 -> Color.parseColor("#FF52B788")
                    entry.score >= 50 -> Color.parseColor("#FFFFB347")
                    else -> Color.parseColor("#FFD95353")
                }
                dotPaint.color = scoreColor
                canvas.drawCircle(leftPad + 5f * density, y - 5f * density, 4f * density, dotPaint)
                historyPaint.color = Color.parseColor("#99FFFFFF")
                val text = "${entry.date} — ${entry.score}/100"
                canvas.drawText(text, leftPad + 18f * density, y, historyPaint)
            }
            y += 10f * density
        }

        canvas.drawLine(leftPad, y, width - 16f * density, y, separatorPaint)
        y += 20f * density

        recTitlePaint.textSize = 20f * density
        canvas.drawText("توصيات شخصية", leftPad, y, recTitlePaint)
        y += 8f * density

        if (recommendations.isEmpty()) {
            recPaint.textSize = 18f * density
            recPaint.color = Color.parseColor("#66FFFFFF")
            canvas.drawText("سيتم اقتراح توصيات بعد التحليل", leftPad, y + 24f * density, recPaint)
        } else {
            recPaint.textSize = 18f * density
            for (rec in recommendations) {
                y += 24f * density
                recPaint.color = Color.parseColor("#99FFFFFF")
                val text = "• $rec"
                val maxW = width - leftPad - 16f * density
                val tw = recPaint.measureText(text)
                if (tw > maxW) {
                    val parts = splitText(text, recPaint, maxW)
                    canvas.drawText(parts.first, leftPad, y, recPaint)
                    if (parts.second.isNotEmpty()) {
                        y += 22f * density
                        canvas.drawText(parts.second, leftPad + 10f * density, y, recPaint)
                    }
                } else {
                    canvas.drawText(text, leftPad, y, recPaint)
                }
            }
        }
    }

    private fun splitText(text: String, paint: Paint, maxWidth: Float): Pair<String, String> {
        for (i in text.length - 1 downTo 1) {
            val first = text.substring(0, i)
            if (paint.measureText(first) <= maxWidth) {
                return Pair(first, text.substring(i))
            }
        }
        return Pair(text, "")
    }
}
