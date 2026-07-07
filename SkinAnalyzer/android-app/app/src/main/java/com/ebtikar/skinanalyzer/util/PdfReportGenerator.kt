package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.graphics.BitmapFactory
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Path
import android.graphics.RectF
import android.graphics.Typeface
import android.graphics.pdf.PdfDocument
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
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
        private const val MARGIN = 45f
        private const val LINE_HEIGHT = 18f
        private const val GOLD = 0xFFD4AF37.toInt()
        private const val GOLD_LIGHT = 0x1AD4AF37.toInt()
        private const val DARK = 0xFF1A1A1A.toInt()
        private const val GRAY = 0xFF6B6B6B.toInt()
        private const val LIGHT_GRAY = 0xFFA0A0A0.toInt()
        private const val VERY_LIGHT_GRAY = 0xFFF5F0EB.toInt()
        private const val GREEN = 0xFF52B788.toInt()
        private const val ORANGE = 0xFFE8A838.toInt()
        private const val RED = 0xFFE07070.toInt()
        private const val WHITE = 0xFFFFFFFF.toInt()
        private const val BG_CREAM = 0xFFFAF8F6.toInt()
    }

    fun generate(context: Context, report: SkinAnalysisReport, outputDir: File, capturedImages: Map<LightSpectrum, File> = emptyMap()): File? {
        return try {
            val pdfDocument = PdfDocument()

            drawPage1_Header(pdfDocument, report)
            drawPage2_Metrics(pdfDocument, report)
            drawPage3_Recommendations(pdfDocument, report)
            if (capturedImages.isNotEmpty()) {
                drawPage4_CapturedImages(pdfDocument, capturedImages)
            }

            if (!outputDir.exists()) outputDir.mkdirs()
            val file = File(outputDir, "DERMA_AI_Report_${report.id.take(8)}.pdf")
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

    // ═══════════════════════════════════════════════════════
    // PAGE 1 — Header, Score, AI Summary
    // ═══════════════════════════════════════════════════════
    private fun drawPage1_Header(pdfDocument: PdfDocument, report: SkinAnalysisReport) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, 1).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        // Background
        canvas.drawColor(BG_CREAM)

        // Top gold bar
        val barPaint = Paint().apply { color = GOLD; isAntiAlias = true }
        canvas.drawRect(0f, 0f, PAGE_WIDTH.toFloat(), 6f, barPaint)

        // DERMA AI Logo text
        val logoPaint = Paint().apply {
            color = GOLD; textSize = 22f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
            letterSpacing = 0.08f
        }
        canvas.drawText("DERMA AI", MARGIN, 45f, logoPaint)

        val subPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 9f; isAntiAlias = true
            letterSpacing = 0.12f
        }
        canvas.drawText("ADVANCED SKIN ANALYSIS SYSTEM", MARGIN, 60f, subPaint)

        // Divider line
        val dividerPaint = Paint().apply { color = GOLD; strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, 72f, PAGE_WIDTH - MARGIN, 72f, dividerPaint)

        // Report Title
        val titlePaint = Paint().apply {
            color = DARK; textSize = 20f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("تقرير تحليل البشرة", MARGIN, 105f, titlePaint)

        val titleEnPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 10f; isAntiAlias = true
            letterSpacing = 0.05f
        }
        canvas.drawText("Skin Analysis Report", MARGIN, 120f, titleEnPaint)

        // Info cards
        val dateFormat = SimpleDateFormat("dd MMMM yyyy", Locale("ar"))
        val timeFormat = SimpleDateFormat("hh:mm a", Locale("ar"))
        val dateStr = dateFormat.format(Date(report.timestamp))
        val timeStr = timeFormat.format(Date(report.timestamp))

        val cardY = 145f
        drawInfoCard(canvas, MARGIN, cardY, "التاريخ", dateStr, "Date")
        drawInfoCard(canvas, MARGIN + 170f, cardY, "الوقت", timeStr, "Time")
        drawInfoCard(canvas, MARGIN + 340f, cardY, "المحرك", report.providerName.replace("_", " ").take(12), "Engine")

        // Score Circle
        val scoreCenterX = PAGE_WIDTH / 2f
        val scoreCenterY = 310f
        val scoreRadius = 70f

        // Score background circle
        val bgCirclePaint = Paint().apply {
            color = VERY_LIGHT_GRAY; isAntiAlias = true; style = Paint.Style.FILL
        }
        canvas.drawCircle(scoreCenterX, scoreCenterY, scoreRadius, bgCirclePaint)

        // Score arc
        val scorePaint = Paint().apply {
            color = getScoreColor(report.overallScore); isAntiAlias = true
            style = Paint.Style.STROKE; strokeWidth = 10f; strokeCap = Paint.Cap.ROUND
        }
        val scoreRect = RectF(
            scoreCenterX - scoreRadius, scoreCenterY - scoreRadius,
            scoreCenterX + scoreRadius, scoreCenterY + scoreRadius
        )
        val sweepAngle = (report.overallScore / 100f) * 360f
        canvas.drawArc(scoreRect, -90f, sweepAngle, false, scorePaint)

        // Score text
        val scoreTextPaint = Paint().apply {
            color = getScoreColor(report.overallScore); textSize = 36f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER
        }
        canvas.drawText("%.1f".format(report.overallScore), scoreCenterX, scoreCenterY + 12f, scoreTextPaint)

        val scoreLabelPaint = Paint().apply {
            color = GRAY; textSize = 11f; isAntiAlias = true; textAlign = Paint.Align.CENTER
        }
        canvas.drawText(getScoreLabel(report.overallScore), scoreCenterX, scoreCenterY + 28f, scoreLabelPaint)

        // Score label below
        val scoreNamePaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 9f; isAntiAlias = true; textAlign = Paint.Align.CENTER
            letterSpacing = 0.1f
        }
        canvas.drawText("OVERALL SCORE", scoreCenterX, scoreCenterY + 42f, scoreNamePaint)

        // Skin Type Card
        val skinTypeY = 420f
        drawSectionTitle(canvas, MARGIN, skinTypeY, "نوع البشرة", "Skin Type")

        val skinTypeCardPaint = Paint().apply {
            color = WHITE; isAntiAlias = true; style = Paint.Style.FILL
        }
        val skinTypeRect = RectF(MARGIN, skinTypeY + 15f, PAGE_WIDTH - MARGIN, skinTypeY + 65f)
        canvas.drawRoundRect(skinTypeRect, 12f, 12f, skinTypeCardPaint)

        val skinTypeText = report.skinProfile?.skinTypeAr ?: "غير محدد"
        val skinTypePaint = Paint().apply {
            color = DARK; textSize = 14f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(skinTypeText, MARGIN + 20f, skinTypeY + 45f, skinTypePaint)

        // AI Summary Section
        val summaryY = 510f
        drawSectionTitle(canvas, MARGIN, summaryY, "ملخص الذكاء الاصطناعي", "AI Analysis Summary")

        val summaryCardPaint = Paint().apply {
            color = WHITE; isAntiAlias = true; style = Paint.Style.FILL
        }
        val summaryRect = RectF(MARGIN, summaryY + 15f, PAGE_WIDTH - MARGIN, summaryY + 135f)
        canvas.drawRoundRect(summaryRect, 12f, 12f, summaryCardPaint)

        val summaryText = report.aiAnalysisText ?: "تم التحليل بنجاح. النتائج متوفرة في الصفحات التالية."
        val summaryBodyPaint = Paint().apply {
            color = GRAY; textSize = 11f; isAntiAlias = true
        }
        drawWrappedText(canvas, summaryText, MARGIN + 20f, summaryY + 40f, PAGE_WIDTH - MARGIN * 2 - 40f, summaryBodyPaint, 6)

        // Top Concerns
        val concernsY = 670f
        drawSectionTitle(canvas, MARGIN, concernsY, "المخاوف الرئيسية", "Top Concerns")

        val topConcerns = report.metrics
            .sortedBy { it.score }
            .take(3)

        var cx = MARGIN
        for (metric in topConcerns) {
            val chipPaint = Paint().apply {
                color = getSeverityBgColor(metric.severity); isAntiAlias = true; style = Paint.Style.FILL
            }
            val chipTextPaint = Paint().apply {
                color = getSeverityColor(metric.severity); textSize = 10f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }
            val chipWidth = 130f
            val chipRect = RectF(cx, concernsY + 15f, cx + chipWidth, concernsY + 45f)
            canvas.drawRoundRect(chipRect, 8f, 8f, chipPaint)

            val label = metric.type.name.replace("_", " ")
            val scoreText = "%.0f".format(metric.score)
            canvas.drawText("$label  $scoreText", cx + 10f, concernsY + 33f, chipTextPaint)

            cx += chipWidth + 10f
        }

        // Footer
        drawFooter(canvas, 1, "DERMA AI v${android.os.Build.VERSION.RELEASE}")

        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════
    // PAGE 2 — Metrics Table
    // ═══════════════════════════════════════════════════════
    private fun drawPage2_Metrics(pdfDocument: PdfDocument, report: SkinAnalysisReport) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, 2).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)

        // Top gold bar
        val barPaint = Paint().apply { color = GOLD; isAntiAlias = true }
        canvas.drawRect(0f, 0f, PAGE_WIDTH.toFloat(), 6f, barPaint)

        // Header
        val headerPaint = Paint().apply {
            color = DARK; textSize = 16f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("تفاصيل المؤشرات", MARGIN, 40f, headerPaint)

        val headerSubPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 9f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText("METRICS DETAIL", MARGIN, 54f, headerSubPaint)

        // Divider
        val dividerPaint = Paint().apply { color = GOLD; strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, 62f, PAGE_WIDTH - MARGIN, 62f, dividerPaint)

        // Table header
        val tableY = 80f
        val colMetric = MARGIN
        val colScore = MARGIN + 200f
        val colStatus = MARGIN + 280f
        val colBar = MARGIN + 370f

        val thPaint = Paint().apply {
            color = GOLD; textSize = 10f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
            letterSpacing = 0.08f
        }
        canvas.drawText("METRIC", colMetric, tableY, thPaint)
        canvas.drawText("SCORE", colScore, tableY, thPaint)
        canvas.drawText("STATUS", colStatus, tableY, thPaint)
        canvas.drawText("LEVEL", colBar, tableY, thPaint)

        // Header underline
        val linePaint = Paint().apply { color = GOLD_LIGHT; strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, tableY + 5f, PAGE_WIDTH - MARGIN, tableY + 5f, linePaint)

        // Metrics rows
        val rowHeight = 28f
        var y = tableY + 22f

        val metricNamePaint = Paint().apply {
            color = DARK; textSize = 11f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.NORMAL)
        }
        val metricScorePaint = Paint().apply {
            color = DARK; textSize = 12f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val metricStatusPaint = Paint().apply {
            textSize = 10f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val barBgPaint = Paint().apply {
            color = 0xFFE8E8E8.toInt(); isAntiAlias = true; style = Paint.Style.FILL
        }
        val barFillPaint = Paint().apply {
            isAntiAlias = true; style = Paint.Style.FILL
        }

        for ((index, metric) in report.metrics.withIndex()) {
            // Alternating row background
            if (index % 2 == 0) {
                val rowBgPaint = Paint().apply {
                    color = 0x08000000; isAntiAlias = true
                }
                canvas.drawRect(MARGIN, y - 12f, PAGE_WIDTH - MARGIN, y + rowHeight - 10f, rowBgPaint)
            }

            // Metric name
            val nameAr = getArabicMetricName(metric.type)
            canvas.drawText(nameAr, colMetric, y, metricNamePaint)

            // Score
            metricScorePaint.color = getScoreColor(metric.score)
            canvas.drawText("%.0f".format(metric.score), colScore, y, metricScorePaint)

            // Status
            val statusText = getSeverityLabel(metric.severity)
            metricStatusPaint.color = getSeverityColor(metric.severity)
            canvas.drawText(statusText, colStatus, y, metricStatusPaint)

            // Progress bar
            val barX = colBar
            val barY = y - 8f
            val barWidth = 120f
            val barHeight = 10f
            val barRect = RectF(barX, barY, barX + barWidth, barY + barHeight)
            canvas.drawRoundRect(barRect, 5f, 5f, barBgPaint)

            val fillWidth = (metric.score / 100f * barWidth).coerceIn(0f, barWidth)
            if (fillWidth > 0) {
                barFillPaint.color = getScoreColor(metric.score)
                val fillRect = RectF(barX, barY, barX + fillWidth, barY + barHeight)
                canvas.drawRoundRect(fillRect, 5f, 5f, barFillPaint)
            }

            y += rowHeight
        }

        // Summary card at bottom
        val summaryY = y + 20f
        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val cardRect = RectF(MARGIN, summaryY, PAGE_WIDTH - MARGIN, summaryY + 80f)
        canvas.drawRoundRect(cardRect, 12f, 12f, cardPaint)

        val cardBorderPaint = Paint().apply {
            color = GOLD_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 1f
        }
        canvas.drawRoundRect(cardRect, 12f, 12f, cardBorderPaint)

        val excellentCount = report.metrics.count { it.severity == MetricSeverity.EXCELLENT }
        val goodCount = report.metrics.count { it.severity == MetricSeverity.GOOD }
        val fairCount = report.metrics.count { it.severity == MetricSeverity.FAIR }
        val poorCount = report.metrics.count { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }

        val cardTitlePaint = Paint().apply {
            color = DARK; textSize = 12f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("ملخص النتائج", MARGIN + 20f, summaryY + 25f, cardTitlePaint)

        val statPaint = Paint().apply { color = GRAY; textSize = 10f; isAntiAlias = true }
        val stats = "ممتاز: $excellentCount  |  جيد: $goodCount  |  مقبول: $fairCount  |  ضعيف: $poorCount"
        canvas.drawText(stats, MARGIN + 20f, summaryY + 45f, statPaint)

        val totalPaint = Paint().apply {
            color = GOLD; textSize = 11f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("إجمالي المؤشرات: ${report.metrics.size}", MARGIN + 20f, summaryY + 65f, totalPaint)

        // Footer
        drawFooter(canvas, 2, "DERMA AI v${android.os.Build.VERSION.RELEASE}")

        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════
    // PAGE 3 — Recommendations & Tips
    // ═══════════════════════════════════════════════════════
    private fun drawPage3_Recommendations(pdfDocument: PdfDocument, report: SkinAnalysisReport) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, 3).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)

        // Top gold bar
        val barPaint = Paint().apply { color = GOLD; isAntiAlias = true }
        canvas.drawRect(0f, 0f, PAGE_WIDTH.toFloat(), 6f, barPaint)

        // Header
        val headerPaint = Paint().apply {
            color = DARK; textSize = 16f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("التوصيات والتغذية الراجعة", MARGIN, 40f, headerPaint)

        val headerSubPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 9f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText("RECOMMENDATIONS & FEEDBACK", MARGIN, 54f, headerSubPaint)

        val dividerPaint = Paint().apply { color = GOLD; strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, 62f, PAGE_WIDTH - MARGIN, 62f, dividerPaint)

        var y = 85f

        // Expert Tips
        val tips = report.expertTipsAr
        if (tips.isNotEmpty()) {
            drawSectionTitle(canvas, MARGIN, y, "نصائح الخبراء", "Expert Tips")
            y += 22f

            val tipCardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val tipTextPaint = Paint().apply { color = GRAY; textSize = 11f; isAntiAlias = true }
            val tipNumberPaint = Paint().apply {
                color = GOLD; textSize = 12f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }

            for ((index, tip) in tips.withIndex()) {
                val cardHeight = 40f
                val cardRect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + cardHeight)
                canvas.drawRoundRect(cardRect, 8f, 8f, tipCardPaint)

                val cardBorderPaint = Paint().apply {
                    color = 0x1A000000.toInt(); isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 0.5f
                }
                canvas.drawRoundRect(cardRect, 8f, 8f, cardBorderPaint)

                canvas.drawText("${index + 1}", MARGIN + 15f, y + 25f, tipNumberPaint)
                drawWrappedText(canvas, tip, MARGIN + 35f, y + 15f, PAGE_WIDTH - MARGIN * 2 - 50f, tipTextPaint, 2)

                y += cardHeight + 8f
            }
        }

        y += 15f

        // Product Recommendations
        val products = report.productRecommendations
        if (products.isNotEmpty()) {
            drawSectionTitle(canvas, MARGIN, y, "المنتجات المقترحة", "Recommended Products")
            y += 22f

            val productCardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val productNamePaint = Paint().apply {
                color = DARK; textSize = 11f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }
            val productDescPaint = Paint().apply { color = GRAY; textSize = 9f; isAntiAlias = true }

            for (product in products) {
                val cardRect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + 50f)
                canvas.drawRoundRect(cardRect, 8f, 8f, productCardPaint)

                val cardBorderPaint = Paint().apply {
                    color = 0x1A000000.toInt(); isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 0.5f
                }
                canvas.drawRoundRect(cardRect, 8f, 8f, cardBorderPaint)

                // Gold accent line on left
                val accentPaint = Paint().apply { color = GOLD; isAntiAlias = true }
                canvas.drawRect(MARGIN, y + 8f, MARGIN + 4f, y + 42f, accentPaint)

                canvas.drawText(product.name, MARGIN + 15f, y + 22f, productNamePaint)
                drawWrappedText(canvas, product.reason, MARGIN + 15f, y + 35f, PAGE_WIDTH - MARGIN * 2 - 30f, productDescPaint, 1)

                y += 58f
            }
        }

        y += 20f

        // Device Info
        val devicePaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 8f; isAntiAlias = true; letterSpacing = 0.05f
        }
        canvas.drawText("Device: ${report.deviceModel}  |  Processing: ${report.executionTimeMs}ms  |  Engine: ${report.providerName}", MARGIN, y, devicePaint)

        // Disclaimer
        y += 15f
        val disclaimerPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 7f; isAntiAlias = true
        }
        canvas.drawText("هذا التقرير تم إنشاؤه بواسطة الذكاء الاصطناعي ولا يغني عن استشارة الطبيب المختص.", MARGIN, y, disclaimerPaint)

        // Footer
        drawFooter(canvas, 3, "DERMA AI v${android.os.Build.VERSION.RELEASE}")

        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════
    // PAGE 4 — Captured Spectral Images
    // ═══════════════════════════════════════════════════════
    private fun drawPage4_CapturedImages(pdfDocument: PdfDocument, capturedImages: Map<LightSpectrum, File>) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, 4).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)

        val titlePaint = Paint().apply {
            color = DARK; textSize = 18f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("الصور الملتقطة", MARGIN, 45f, titlePaint)

        val subtitlePaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 9f; isAntiAlias = true; letterSpacing = 0.1f
        }
        canvas.drawText("CAPTURED SPECTRAL IMAGES", MARGIN, 60f, subtitlePaint)

        val linePaint = Paint().apply { color = GOLD; strokeWidth = 2f; isAntiAlias = true }
        canvas.drawLine(MARGIN, 68f, MARGIN + 35f, 68f, linePaint)

        val spectra = LightSpectrum.CAPTURE_SEQUENCE.filter { capturedImages.containsKey(it) }
        if (spectra.isEmpty()) {
            val emptyPaint = Paint().apply { color = LIGHT_GRAY; textSize = 12f; isAntiAlias = true }
            canvas.drawText("لا توجد صور ملتقطة", MARGIN, 120f, emptyPaint)
            drawFooter(canvas, 4, "DERMA AI v${android.os.Build.VERSION.RELEASE}")
            pdfDocument.finishPage(page)
            return
        }

        val cellWidth = (PAGE_WIDTH - MARGIN * 2 - 12f) / 2
        val cellHeight = 220f
        val labelHeight = 25f
        val spacing = 8f
        var x = MARGIN
        var y = 85f

        for ((index, spectrum) in spectra.withIndex()) {
            val file = capturedImages[spectrum] ?: continue
            val col = index % 2
            val row = index / 2

            if (col == 0 && index > 0) {
                y += cellHeight + labelHeight + spacing
            }
            x = MARGIN + col * (cellWidth + spacing)

            val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val cardRect = RectF(x, y, x + cellWidth, y + cellHeight)
            canvas.drawRoundRect(cardRect, 8f, 8f, cardPaint)

            val borderPaint = Paint().apply {
                color = 0x1A000000.toInt(); isAntiAlias = true
                style = Paint.Style.STROKE; strokeWidth = 0.5f
            }
            canvas.drawRoundRect(cardRect, 8f, 8f, borderPaint)

            try {
                val bitmap = BitmapFactory.decodeFile(file.absolutePath)
                if (bitmap != null) {
                    val padding = 6f
                    val imageRect = RectF(x + padding, y + padding, x + cellWidth - padding, y + cellHeight - padding)
                    canvas.drawBitmap(bitmap, null, imageRect, null)
                    bitmap.recycle()
                }
            } catch (e: Exception) {
                Timber.w(e, "Failed to decode image for ${spectrum.name} in PDF")
                val errorPaint = Paint().apply { color = LIGHT_GRAY; textSize = 10f; isAntiAlias = true }
                canvas.drawText("صورة غير متاحة", x + cellWidth / 2 - 40f, y + cellHeight / 2, errorPaint)
            }

            val spectrumColor = try { Color.parseColor(spectrum.colorHex) } catch (_: Exception) { DARK }
            val indicatorPaint = Paint().apply { color = spectrumColor; isAntiAlias = true }
            canvas.drawCircle(x + cellWidth - 12f, y + 12f, 5f, indicatorPaint)

            val nameLabelPaint = Paint().apply {
                color = DARK; textSize = 10f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }
            canvas.drawText(spectrum.displayNameAr, x + 8f, y + cellHeight + 14f, nameLabelPaint)

            val nameEnPaint = Paint().apply {
                color = LIGHT_GRAY; textSize = 7f; isAntiAlias = true; letterSpacing = 0.05f
            }
            canvas.drawText(spectrum.name, x + 8f, y + cellHeight + 24f, nameEnPaint)
        }

        drawFooter(canvas, 4, "DERMA AI v${android.os.Build.VERSION.RELEASE}")
        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════

    private fun drawInfoCard(canvas: Canvas, x: Float, y: Float, labelAr: String, value: String, labelEn: String) {
        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val cardRect = RectF(x, y, x + 155f, y + 55f)
        canvas.drawRoundRect(cardRect, 10f, 10f, cardPaint)

        val cardBorderPaint = Paint().apply {
            color = 0x1A000000.toInt(); isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 0.5f
        }
        canvas.drawRoundRect(cardRect, 10f, 10f, cardBorderPaint)

        val labelPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 8f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText(labelEn.uppercase(), x + 12f, y + 18f, labelPaint)

        val valuePaint = Paint().apply {
            color = DARK; textSize = 11f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(value, x + 12f, y + 35f, valuePaint)

        val arLabelPaint = Paint().apply {
            color = GOLD; textSize = 9f; isAntiAlias = true
        }
        canvas.drawText(labelAr, x + 12f, y + 48f, arLabelPaint)
    }

    private fun drawSectionTitle(canvas: Canvas, x: Float, y: Float, titleAr: String, titleEn: String) {
        val titlePaint = Paint().apply {
            color = DARK; textSize = 13f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(titleAr, x, y, titlePaint)

        val subPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 8f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText(titleEn.uppercase(), x + titlePaint.measureText(titleAr) + 15f, y, subPaint)

        // Gold underline
        val linePaint = Paint().apply { color = GOLD; strokeWidth = 2f; isAntiAlias = true }
        canvas.drawLine(x, y + 5f, x + 30f, y + 5f, linePaint)
    }

    private fun drawWrappedText(canvas: Canvas, text: String, x: Float, y: Float, maxWidth: Float, paint: Paint, maxLines: Int): Float {
        val words = text.split(" ")
        var line = ""
        var currentY = y
        var lineCount = 0

        for (word in words) {
            val testLine = if (line.isEmpty()) word else "$line $word"
            if (paint.measureText(testLine) > maxWidth && line.isNotEmpty()) {
                if (lineCount >= maxLines - 1) {
                    canvas.drawText("${line.take(40)}...", x, currentY, paint)
                    return currentY + paint.textSize
                }
                canvas.drawText(line, x, currentY, paint)
                line = word
                currentY += paint.textSize + 4f
                lineCount++
            } else {
                line = testLine
            }
        }
        if (line.isNotEmpty()) {
            canvas.drawText(line, x, currentY, paint)
        }
        return currentY + paint.textSize
    }

    private fun drawFooter(canvas: Canvas, pageNum: Int, appName: String) {
        val footerY = PAGE_HEIGHT - 30f
        val footerLinePaint = Paint().apply { color = GOLD; strokeWidth = 0.5f; isAntiAlias = true }
        canvas.drawLine(MARGIN, footerY, PAGE_WIDTH - MARGIN, footerY, footerLinePaint)

        val footerPaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 8f; isAntiAlias = true; letterSpacing = 0.05f
        }
        canvas.drawText(appName, MARGIN, footerY + 12f, footerPaint)

        val pagePaint = Paint().apply {
            color = LIGHT_GRAY; textSize = 8f; isAntiAlias = true; textAlign = Paint.Align.RIGHT
        }
        canvas.drawText("Page $pageNum of 3", PAGE_WIDTH - MARGIN, footerY + 12f, pagePaint)
    }

    private fun getScoreColor(score: Float): Int {
        return when {
            score >= 72f -> GREEN
            score >= 55f -> 0xFF74C69D.toInt()
            score >= 35f -> ORANGE
            score >= 20f -> 0xFFFF9800.toInt()
            else -> RED
        }
    }

    private fun getScoreLabel(score: Float): String {
        return when {
            score >= 72f -> "ممتاز"
            score >= 55f -> "جيد"
            score >= 35f -> "مقبول"
            score >= 20f -> "ضعيف"
            else -> "حرج"
        }
    }

    private fun getSeverityColor(severity: MetricSeverity): Int {
        return when (severity) {
            MetricSeverity.EXCELLENT -> GREEN
            MetricSeverity.GOOD -> 0xFF74C69D.toInt()
            MetricSeverity.FAIR -> ORANGE
            MetricSeverity.POOR -> RED
            MetricSeverity.CRITICAL -> 0xFFD95353.toInt()
        }
    }

    private fun getSeverityBgColor(severity: MetricSeverity): Int {
        return when (severity) {
            MetricSeverity.EXCELLENT -> 0x1A52B788.toInt()
            MetricSeverity.GOOD -> 0x1A74C69D.toInt()
            MetricSeverity.FAIR -> 0x1AE8A838.toInt()
            MetricSeverity.POOR -> 0x1AE07070.toInt()
            MetricSeverity.CRITICAL -> 0x1AD95353.toInt()
        }
    }

    private fun getSeverityLabel(severity: MetricSeverity): String {
        return when (severity) {
            MetricSeverity.EXCELLENT -> "ممتاز"
            MetricSeverity.GOOD -> "جيد"
            MetricSeverity.FAIR -> "مقبول"
            MetricSeverity.POOR -> "ضعيف"
            MetricSeverity.CRITICAL -> "حرج"
        }
    }

    private fun getArabicMetricName(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        SkinMetric.Type.ACNE -> "حب الشباب"
        SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
