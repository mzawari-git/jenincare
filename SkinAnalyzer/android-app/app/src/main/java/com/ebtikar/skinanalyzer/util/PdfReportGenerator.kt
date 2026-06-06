package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Typeface
import android.graphics.pdf.PdfDocument
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import timber.log.Timber
import java.io.File
import java.io.FileOutputStream
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PdfReportGenerator @Inject constructor() {

    companion object {
        private const val PAGE_WIDTH = 595
        private const val PAGE_HEIGHT = 842
        private const val MARGIN = 40f
        private const val LINE_HEIGHT = 20f
    }

    fun generate(context: Context, report: SkinAnalysisReport, outputDir: File): File? {
        return try {
            val pdfDocument = PdfDocument()
            val paint = Paint()
            val titlePaint = Paint().apply {
                color = Color.parseColor("#212121")
                textSize = 24f
                typeface = Typeface.DEFAULT_BOLD
                isAntiAlias = true
            }
            val bodyPaint = Paint().apply {
                color = Color.parseColor("#424242")
                textSize = 14f
                isAntiAlias = true
            }
            val scorePaint = Paint().apply {
                textSize = 48f
                typeface = Typeface.DEFAULT_BOLD
                isAntiAlias = true
            }

            val page = pdfDocument.startPage(PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, 1).create())
            val canvas = page.canvas

            var y = MARGIN + 30f

            canvas.drawText("Skin Analysis Report", MARGIN, y, titlePaint)
            y += LINE_HEIGHT * 2

            val dateFormat = SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault())
            canvas.drawText("Date: ${dateFormat.format(Date(report.timestamp))}", MARGIN, y, bodyPaint)
            y += LINE_HEIGHT
            canvas.drawText("Engine: ${report.providerName.replace("_", " ")}", MARGIN, y, bodyPaint)
            y += LINE_HEIGHT
            canvas.drawText("Processing Time: ${report.executionTimeMs}ms", MARGIN, y, bodyPaint)
            y += LINE_HEIGHT * 2

            scorePaint.color = getScoreColor(report.overallScore)
            canvas.drawText("%.1f".format(report.overallScore), MARGIN, y + 40f, scorePaint)
            y += LINE_HEIGHT * 4

            canvas.drawText("Metrics", MARGIN, y, titlePaint.apply { textSize = 18f })
            y += LINE_HEIGHT * 1.5f

            for (metric in report.metrics) {
                canvas.drawText("${metric.type.name.replace("_", " ")}: %.0f (%s)".format(metric.score, metric.severity.name), MARGIN + 10f, y, bodyPaint)
                y += LINE_HEIGHT
            }

            y += LINE_HEIGHT
            canvas.drawText("Device: ${report.deviceModel}", MARGIN, y, bodyPaint.apply { textSize = 10f; color = Color.GRAY })

            pdfDocument.finishPage(page)

            if (!outputDir.exists()) outputDir.mkdirs()
            val file = File(outputDir, "report_${report.id}.pdf")
            FileOutputStream(file).use { fos ->
                pdfDocument.writeTo(fos)
            }
            pdfDocument.close()

            Timber.i("PDF report generated: ${file.absolutePath}")
            file
        } catch (e: Exception) {
            Timber.e(e, "Failed to generate PDF report")
            null
        }
    }

    private fun getScoreColor(score: Float): Int {
        return when {
            score >= 85f -> Color.parseColor("#4CAF50")
            score >= 70f -> Color.parseColor("#8BC34A")
            score >= 55f -> Color.parseColor("#FFC107")
            score >= 35f -> Color.parseColor("#FF9800")
            else -> Color.parseColor("#F44336")
        }
    }
}
