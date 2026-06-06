package com.jenincare.skinanalyzer.util

import android.content.ContentValues
import android.content.Context
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.Typeface
import android.os.Build
import android.os.Environment
import android.provider.MediaStore
import com.jenincare.skinanalyzer.domain.model.ScanReport
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.io.File
import java.io.FileOutputStream

object PdfReportGenerator {

    suspend fun generate(context: Context, report: ScanReport): Result<String> = withContext(Dispatchers.IO) {
        try {
            val widthPx = 595
            val heightPx = 842
            val margin = 40f
            var yPos = margin

            val titlePaint = Paint().apply {
                color = Color.parseColor("#C9956B")
                textSize = 24f
                typeface = Typeface.create(Typeface.DEFAULT, Typeface.BOLD)
                isAntiAlias = true
            }
            val headingPaint = Paint().apply {
                color = Color.parseColor("#2DD4BF")
                textSize = 16f
                typeface = Typeface.create(Typeface.DEFAULT, Typeface.BOLD)
                isAntiAlias = true
            }
            val bodyPaint = Paint().apply {
                color = Color.parseColor("#EDEDED")
                textSize = 12f
                isAntiAlias = true
            }
            val smallPaint = Paint().apply {
                color = Color.parseColor("#B0B8C1")
                textSize = 10f
                isAntiAlias = true
            }
            val bgPaint = Paint().apply {
                color = Color.parseColor("#0D1117")
                style = Paint.Style.FILL
            }
            val cardPaint = Paint().apply {
                color = Color.parseColor("#161B24")
                style = Paint.Style.FILL
            }
            val borderPaint = Paint().apply {
                color = Color.parseColor("#4DC9956B")
                style = Paint.Style.STROKE
                strokeWidth = 1f
            }
            val barBgPaint = Paint().apply {
                color = Color.parseColor("#2D3748")
            }

            val pages = mutableListOf<Bitmap>()
            var bmp = Bitmap.createBitmap(widthPx, heightPx, Bitmap.Config.ARGB_8888)
            var canvas = Canvas(bmp)
            canvas.drawRect(0f, 0f, widthPx.toFloat(), heightPx.toFloat(), bgPaint)

            yPos = drawHeader(canvas, yPos, widthPx, margin, titlePaint, smallPaint)

            yPos = drawCard(canvas, yPos, widthPx, margin, cardPaint, borderPaint) { cardYStart ->
                var cy = cardYStart + 15f
                canvas.drawText("Skin Health Score", margin + 15f, cy, headingPaint)
                cy += 35f
                val scoreColor = when {
                    report.scan.overallScore >= 80 -> Color.parseColor("#10B981")
                    report.scan.overallScore >= 60 -> Color.parseColor("#F59E0B")
                    else -> Color.parseColor("#EF4444")
                }
                val scorePaint = Paint().apply {
                    color = scoreColor
                    textSize = 48f
                    typeface = Typeface.create(Typeface.DEFAULT, Typeface.BOLD)
                    isAntiAlias = true
                }
                canvas.drawText("${report.scan.overallScore}", margin + 15f, cy + 40f, scorePaint)
                val maxLbl = Paint().apply { color = Color.parseColor("#B0B8C1"); textSize = 14f }
                canvas.drawText("/ 100", margin + 100f, cy + 40f, maxLbl)
                cy += 55f
                val label = when {
                    report.scan.overallScore >= 80 -> "Excellent"
                    report.scan.overallScore >= 60 -> "Good"
                    report.scan.overallScore >= 40 -> "Fair"
                    else -> "Needs Care"
                }
                val labelPaint = Paint().apply { color = scoreColor; textSize = 14f; typeface = Typeface.create(Typeface.DEFAULT, Typeface.BOLD) }
                canvas.drawText(label, margin + 15f, cy + 10f, labelPaint)
                cy += 25f
                canvas.drawText("Date: ${report.scan.createdAt}", margin + 15f, cy + 10f, smallPaint)
                cy + 25f
            }
            yPos += 10f

            yPos = drawCard(canvas, yPos, widthPx, margin, cardPaint, borderPaint) { cardYStart ->
                var cy = cardYStart + 15f
                canvas.drawText("Metrics Analysis", margin + 15f, cy, headingPaint)
                cy += 25f
                report.radarMetrics.forEach { metric ->
                    val pct = (metric.value * 100).toInt()
                    canvas.drawText(metric.nameEn, margin + 15f, cy + 12f, bodyPaint)
                    canvas.drawText("$pct%", widthPx - margin - 50f, cy + 12f, Paint().apply {
                        color = when { pct >= 70 -> Color.parseColor("#10B981"); pct >= 50 -> Color.parseColor("#F59E0B"); else -> Color.parseColor("#EF4444") }
                        textSize = 12f; typeface = Typeface.create(Typeface.DEFAULT, Typeface.BOLD)
                    })
                    val barLeft = margin + 120f
                    val barRight = widthPx - margin - 60f
                    val barTop = cy + 4f
                    val barH = 10f
                    canvas.drawRoundRect(RectF(barLeft, barTop, barRight, barTop + barH), 5f, 5f, barBgPaint)
                    val fillW = (barRight - barLeft) * metric.value
                    val fillColor = when { pct >= 70 -> Color.parseColor("#10B981"); pct >= 50 -> Color.parseColor("#F59E0B"); else -> Color.parseColor("#EF4444") }
                    canvas.drawRoundRect(RectF(barLeft, barTop, barLeft + fillW, barTop + barH), 5f, 5f, Paint().apply { color = fillColor })
                    cy += 28f
                }
                cy
            }
            yPos += 10f

            if (report.advancedMetrics.isNotEmpty()) {
                yPos = drawCard(canvas, yPos, widthPx, margin, cardPaint, borderPaint) { cardYStart ->
                    var cy = cardYStart + 15f
                    canvas.drawText("Advanced Metrics", margin + 15f, cy, headingPaint)
                    cy += 25f
                    report.advancedMetrics.forEach { (key, value) ->
                        val label = when (key) {
                            "brightness" -> "Brightness"; "texture" -> "Texture"; "redness" -> "Redness"
                            "sensitivity" -> "Sensitivity"; "oiliness" -> "Oiliness"; else -> key
                        }
                        canvas.drawText(label, margin + 15f, cy + 12f, bodyPaint)
                        canvas.drawText("$value%", widthPx - margin - 50f, cy + 12f, bodyPaint)
                        cy += 22f
                    }
                    cy
                }
                yPos += 10f
            }

            if (report.defects.isNotEmpty()) {
                yPos = drawCard(canvas, yPos, widthPx, margin, cardPaint, borderPaint) { cardYStart ->
                    var cy = cardYStart + 15f
                    canvas.drawText("Skin Concerns", margin + 15f, cy, headingPaint)
                    cy += 25f
                    report.defects.forEach { defect ->
                        canvas.drawText("${defect.nameEn} - Severity: ${(defect.severity * 100).toInt()}%", margin + 15f, cy + 12f, bodyPaint)
                        cy += 18f
                        canvas.drawText("Tip: ${defect.tipEn}", margin + 25f, cy + 10f, smallPaint)
                        cy += 22f
                    }
                    cy
                }
                yPos += 10f
            }

            if (report.tips.isNotEmpty()) {
                yPos = drawCard(canvas, yPos, widthPx, margin, cardPaint, borderPaint) { cardYStart ->
                    var cy = cardYStart + 15f
                    canvas.drawText("Recommendations", margin + 15f, cy, headingPaint)
                    cy += 25f
                    report.tips.forEachIndexed { idx, tip ->
                        canvas.drawText("${idx + 1}. $tip", margin + 15f, cy + 12f, bodyPaint)
                        cy += 22f
                    }
                    cy
                }
                yPos += 10f
            }

            drawFooter(canvas, heightPx, margin, smallPaint)
            pages.add(bmp)

            val fileName = "SkinReport_${report.scan.id}_${System.currentTimeMillis()}.pdf"
            val resolver = context.contentResolver
            val contentValues = ContentValues().apply {
                put(MediaStore.MediaColumns.DISPLAY_NAME, fileName)
                put(MediaStore.MediaColumns.MIME_TYPE, "application/pdf")
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                    put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_DOCUMENTS + "/JeninCare/")
                    put(MediaStore.MediaColumns.IS_PENDING, 1)
                }
            }
            val uri = resolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, contentValues)
                ?: return@withContext Result.failure(Exception("Cannot create file"))

            resolver.openOutputStream(uri)?.use { outputStream ->
                val pdfDocument = android.graphics.pdf.PdfDocument()
                pages.forEach { pageBitmap ->
                    val pageInfo = android.graphics.pdf.PdfDocument.PageInfo.Builder(widthPx, heightPx, 0).create()
                    val page = pdfDocument.startPage(pageInfo)
                    pageBitmap.let { page.canvas.drawBitmap(it, 0f, 0f, null) }
                    pdfDocument.finishPage(page)
                }
                pdfDocument.writeTo(outputStream)
                pdfDocument.close()
            }

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                contentValues.clear()
                contentValues.put(MediaStore.MediaColumns.IS_PENDING, 0)
                resolver.update(uri, contentValues, null, null)
            }

            Result.success(fileName)
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    private fun drawHeader(canvas: Canvas, yPos: Float, widthPx: Int, margin: Float, titlePaint: Paint, smallPaint: Paint): Float {
        var y = yPos
        val accentPaint = Paint().apply { color = Color.parseColor("#C9956B"); style = Paint.Style.STROKE; strokeWidth = 3f }
        canvas.drawLine(margin, y, margin, y + 40f, accentPaint)
        canvas.drawText("Jenin Care", margin + 15f, y + 18f, titlePaint)
        canvas.drawText("Skin Analysis Report", margin + 15f, y + 36f, smallPaint)
        val datePaint = Paint().apply { color = Color.parseColor("#B0B8C1"); textSize = 9f; textAlign = Paint.Align.RIGHT }
        canvas.drawText("Generated: ${java.text.SimpleDateFormat("yyyy-MM-dd HH:mm").format(java.util.Date())}", widthPx - margin.toFloat(), y + 18f, datePaint)
        y += 55f
        val dividerPaint = Paint().apply { color = Color.parseColor("#4DC9956B"); style = Paint.Style.STROKE; strokeWidth = 0.5f }
        canvas.drawLine(margin, y, widthPx - margin, y, dividerPaint)
        y += 15f
        return y
    }

    private fun drawFooter(canvas: Canvas, heightPx: Int, margin: Float, smallPaint: Paint) {
        val footerPaint = Paint(smallPaint).apply { textAlign = Paint.Align.CENTER; color = Color.parseColor("#4A5568") }
        canvas.drawText("Jenin Care - Professional Skin Analysis", (margin + (canvas.width - margin)) / 2f, heightPx - 20f, footerPaint)
    }

    private fun drawCard(
        canvas: Canvas,
        yPos: Float,
        widthPx: Int,
        margin: Float,
        cardPaint: Paint,
        borderPaint: Paint,
        drawContent: (Float) -> Float
    ): Float {
        val estimatedHeight = 200f
        val cardRect = RectF(margin, yPos, widthPx - margin, yPos + estimatedHeight)
        canvas.drawRoundRect(cardRect, 8f, 8f, cardPaint)
        canvas.drawRoundRect(cardRect, 8f, 8f, borderPaint)
        val endY = drawContent(yPos)
        val actualHeight = endY - yPos + 15f
        return yPos + actualHeight.coerceAtLeast(estimatedHeight)
    }
}
