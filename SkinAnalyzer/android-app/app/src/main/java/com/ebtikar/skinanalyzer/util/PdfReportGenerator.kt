package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.Typeface
import android.graphics.pdf.PdfDocument
import android.text.Layout
import android.text.StaticLayout
import android.text.TextPaint
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
        private const val PAGE_WIDTH = 2480
        private const val PAGE_HEIGHT = 3508
        private const val MARGIN = 120f
        private const val CONTENT_WIDTH = PAGE_WIDTH - 2 * MARGIN

        private const val GOLD = 0xFFD4AF37.toInt()
        private const val GOLD_DARK = 0xFFB8942E.toInt()
        private const val GOLD_LIGHT = 0x33D4AF37.toInt()
        private const val GOLD_VERY_LIGHT = 0x10D4AF37.toInt()
        private const val DARK = 0xFF1A1A1A.toInt()
        private const val GRAY = 0xFF6B6B6B.toInt()
        private const val LIGHT_GRAY = 0xFFA0A0A0.toInt()
        private const val VERY_LIGHT_GRAY = 0xFFF5F0EB.toInt()
        private const val GREEN = 0xFF2ECC71.toInt()
        private const val GREEN_LIGHT = 0xFF27AE60.toInt()
        private const val ORANGE = 0xFFF39C12.toInt()
        private const val RED = 0xFFE74C3C.toInt()
        private const val RED_DARK = 0xFFC0392B.toInt()
        private const val WHITE = 0xFFFFFFFF.toInt()
        private const val BG_CREAM = 0xFFFCFAF7.toInt()
        private const val CARD_SHADOW = 0x0D000000.toInt()
    }

    fun generate(
        context: Context,
        report: SkinAnalysisReport,
        outputDir: File,
        capturedImages: Map<LightSpectrum, File> = emptyMap()
    ): File? {
        val pdfDocument = PdfDocument()
        val bitmapsToRecycle = mutableListOf<Bitmap>()
        return try {
            var pageNumber = 1
            drawPage1_Summary(pdfDocument, report, pageNumber)
            pageNumber++
            drawPage2_Metrics(pdfDocument, report, pageNumber)
            pageNumber++
            drawPage3_Recommendations(pdfDocument, report, pageNumber)
            pageNumber++
            if (capturedImages.isNotEmpty()) {
                drawPage4_Images(pdfDocument, capturedImages, pageNumber, bitmapsToRecycle)
            }

            if (!outputDir.exists()) outputDir.mkdirs()
            val file = File(outputDir, "DERMA_AI_Report_${report.id.take(8)}.pdf")
            FileOutputStream(file).use { fos ->
                pdfDocument.writeTo(fos)
            }
            Timber.i("PDF generated (300 DPI A4): ${file.absolutePath} (${file.length()} bytes)")
            file
        } catch (e: Exception) {
            Timber.e(e, "Failed to generate PDF")
            null
        } finally {
            try { pdfDocument.close() } catch (_: Exception) {}
            for (b in bitmapsToRecycle) {
                try { if (!b.isRecycled) b.recycle() } catch (_: Exception) {}
            }
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 1 — Header, Score Circle, AI Summary, Top Concerns
    // ═══════════════════════════════════════════════════════════

    private fun drawPage1_Summary(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)
        var y = 0f

        // ── Top gold accent bar ──
        y = drawGoldBar(canvas, y)

        // ── Logo + Title ──
        y = drawHeader(canvas, y, report)

        // ── Info cards (date, time, engine) ──
        y = drawInfoCards(canvas, y, report)

        // ── Score circle ──
        y = drawScoreCircle(canvas, y, report)

        // ── Skin type card ──
        y = drawSkinTypeCard(canvas, y, report)

        // ── AI Analysis Summary ──
        y = drawAiSummary(canvas, y, report)

        // ── Top Concerns ──
        drawTopConcerns(canvas, y, report)

        // ── Footer ──
        drawFooter(canvas, pageNum, 4)

        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 2 — Detailed Metrics Table
    // ═══════════════════════════════════════════════════════════

    private fun drawPage2_Metrics(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)
        var y = 0f

        y = drawGoldBar(canvas, y)
        y = drawPageHeader(canvas, y, "تفاصيل المؤشرات", "METRICS DETAIL")
        y += 40f

        // ── Table header ──
        y = drawTableHeader(canvas, y)

        // ── Metric rows ──
        y = drawMetricRows(canvas, y, report)

        // ── Summary card ──
        drawSummaryCard(canvas, y, report)

        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 3 — Expert Tips & Product Recommendations
    // ═══════════════════════════════════════════════════════════

    private fun drawPage3_Recommendations(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)
        var y = 0f

        y = drawGoldBar(canvas, y)
        y = drawPageHeader(canvas, y, "التوصيات والتغذية الراجعة", "RECOMMENDATIONS & FEEDBACK")
        y += 40f

        // ── Expert Tips ──
        y = drawExpertTips(canvas, y, report)

        // ── Product Recommendations ──
        y = drawProductRecommendations(canvas, y, report)

        // ── Disclaimer ──
        drawDisclaimer(canvas, y)

        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 4 — Captured Spectral Images
    // ═══════════════════════════════════════════════════════════

    private fun drawPage4_Images(
        pdfDocument: PdfDocument,
        capturedImages: Map<LightSpectrum, File>,
        pageNum: Int,
        bitmapsToRecycle: MutableList<Bitmap>
    ) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas

        canvas.drawColor(BG_CREAM)
        var y = 0f

        y = drawGoldBar(canvas, y)
        y = drawPageHeader(canvas, y, "الصور الملتقطة", "CAPTURED SPECTRAL IMAGES")
        y += 50f

        val spectra = LightSpectrum.CAPTURE_SEQUENCE.filter { capturedImages.containsKey(it) }
        if (spectra.isEmpty()) {
            val emptyPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 56f; isAntiAlias = true }
            canvas.drawText("لا توجد صور ملتقطة", MARGIN, y + 100f, emptyPaint)
            drawFooter(canvas, pageNum, 4)
            pdfDocument.finishPage(page)
            return
        }

        // 2 columns x 4 rows grid
        val cols = 2
        val rows = (spectra.size + cols - 1) / cols
        val cellSpacing = 30f
        val labelHeight = 90f
        val cellWidth = (CONTENT_WIDTH - cellSpacing * (cols - 1)) / cols
        val cellHeight = (PAGE_HEIGHT - y - 300f - labelHeight * rows - cellSpacing * (rows + 1)) / rows

        for ((index, spectrum) in spectra.withIndex()) {
            val file = capturedImages[spectrum] ?: continue
            val col = index % cols
            val row = index / cols

            val cellX = MARGIN + col * (cellWidth + cellSpacing)
            val cellY = y + cellSpacing + row * (cellHeight + labelHeight + cellSpacing)

            // Card background
            val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val cardRect = RectF(cellX, cellY, cellX + cellWidth, cellY + cellHeight)
            canvas.drawRoundRect(cardRect, 20f, 20f, cardPaint)

            val borderPaint = Paint().apply {
                color = GOLD_LIGHT; isAntiAlias = true
                style = Paint.Style.STROKE; strokeWidth = 3f
            }
            canvas.drawRoundRect(cardRect, 20f, 20f, borderPaint)

            // Decode and draw image
            try {
                val options = BitmapFactory.Options().apply { inSampleSize = 4 }
                val bitmap = BitmapFactory.decodeFile(file.absolutePath, options)
                if (bitmap != null) {
                    val padding = 16f
                    val imgRect = RectF(
                        cellX + padding, cellY + padding,
                        cellX + cellWidth - padding, cellY + cellHeight - padding
                    )
                    canvas.drawBitmap(bitmap, null, imgRect, null)
                    bitmapsToRecycle.add(bitmap)
                } else {
                    val errPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 44f; isAntiAlias = true; textAlign = Paint.Align.CENTER }
                    canvas.drawText("صورة غير متاحة", cellX + cellWidth / 2, cellY + cellHeight / 2, errPaint)
                }
            } catch (e: Exception) {
                Timber.w(e, "Failed to decode image for ${spectrum.name}")
                val errPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 44f; isAntiAlias = true; textAlign = Paint.Align.CENTER }
                canvas.drawText("صورة غير متاحة", cellX + cellWidth / 2, cellY + cellHeight / 2, errPaint)
            }

            // Spectrum color indicator
            val spectrumColor = try { Color.parseColor(spectrum.colorHex) } catch (_: Exception) { DARK }
            val indicatorPaint = Paint().apply { color = spectrumColor; isAntiAlias = true }
            canvas.drawCircle(cellX + cellWidth - 28f, cellY + 28f, 14f, indicatorPaint)

            // Labels
            val nameArPaint = TextPaint().apply {
                color = DARK; textSize = 46f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }
            canvas.drawText(spectrum.displayNameAr, cellX + 20f, cellY + cellHeight + 50f, nameArPaint)

            val nameEnPaint = TextPaint().apply {
                color = LIGHT_GRAY; textSize = 32f; isAntiAlias = true; letterSpacing = 0.05f
            }
            canvas.drawText(spectrum.displayName, cellX + 20f, cellY + cellHeight + 85f, nameEnPaint)
        }

        drawFooter(canvas, pageNum, spectra.size.coerceAtLeast(1) / 2 + if (spectra.size % 2 == 1) 1 else 0 + 1)
        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════════
    //  REUSABLE DRAWING COMPONENTS
    // ═══════════════════════════════════════════════════════════

    private fun drawGoldBar(canvas: Canvas, startY: Float): Float {
        val paint = Paint().apply { color = GOLD; isAntiAlias = true }
        canvas.drawRect(0f, startY, PAGE_WIDTH.toFloat(), startY + 12f, paint)
        return startY + 12f
    }

    private fun drawHeader(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY + 50f

        // Logo circle
        val logoCx = MARGIN + 40f
        val logoCy = y + 10f
        val logoPaint = Paint().apply { color = GOLD_VERY_LIGHT; isAntiAlias = true; style = Paint.Style.FILL }
        canvas.drawCircle(logoCx, logoCy, 50f, logoPaint)
        val logoBorderPaint = Paint().apply { color = GOLD; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 4f }
        canvas.drawCircle(logoCx, logoCy, 50f, logoBorderPaint)

        // "D" letter inside logo
        val dPaint = TextPaint().apply {
            color = GOLD; textSize = 52f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER
        }
        canvas.drawText("D", logoCx, logoCy + 18f, dPaint)

        // DERMA AI title
        val titlePaint = TextPaint().apply {
            color = GOLD; textSize = 72f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD); letterSpacing = 0.08f
        }
        canvas.drawText("DERMA AI", MARGIN + 120f, y + 10f, titlePaint)

        val subPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 36f; isAntiAlias = true; letterSpacing = 0.12f
        }
        canvas.drawText("ADVANCED SKIN ANALYSIS SYSTEM", MARGIN + 120f, y + 55f, subPaint)

        // Gold divider
        val divPaint = Paint().apply { color = GOLD; strokeWidth = 3f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y + 80f, PAGE_WIDTH - MARGIN, y + 80f, divPaint)

        // Report title Arabic
        val arTitlePaint = TextPaint().apply {
            color = DARK; textSize = 76f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("تقرير تحليل البشرة", MARGIN, y + 170f, arTitlePaint)

        val enTitlePaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 38f; isAntiAlias = true; letterSpacing = 0.05f
        }
        canvas.drawText("Skin Analysis Report", MARGIN, y + 220f, enTitlePaint)

        return y + 260f
    }

    private fun drawInfoCards(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val y = startY + 20f
        val dateFormat = SimpleDateFormat("dd MMMM yyyy", Locale("ar"))
        val timeFormat = SimpleDateFormat("hh:mm a", Locale("ar"))
        val dateStr = dateFormat.format(Date(report.timestamp))
        val timeStr = timeFormat.format(Date(report.timestamp))

        val cardWidth = (CONTENT_WIDTH - 40f) / 3
        val cardHeight = 130f

        drawInfoCard(canvas, MARGIN, y, cardWidth, cardHeight, "التاريخ", dateStr, "DATE")
        drawInfoCard(canvas, MARGIN + cardWidth + 20f, y, cardWidth, cardHeight, "الوقت", timeStr, "TIME")
        drawInfoCard(canvas, MARGIN + (cardWidth + 20f) * 2, y, cardWidth, cardHeight, "المحرك",
            report.providerName.replace("_", " ").take(16), "ENGINE")

        return y + cardHeight + 20f
    }

    private fun drawInfoCard(canvas: Canvas, x: Float, y: Float, w: Float, h: Float,
                             labelAr: String, value: String, labelEn: String) {
        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val rect = RectF(x, y, x + w, y + h)
        canvas.drawRoundRect(rect, 24f, 24f, cardPaint)

        val borderPaint = Paint().apply {
            color = GOLD_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f
        }
        canvas.drawRoundRect(rect, 24f, 24f, borderPaint)

        val enLabelPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; letterSpacing = 0.1f
        }
        canvas.drawText(labelEn, x + 28f, y + 40f, enLabelPaint)

        val valuePaint = TextPaint().apply {
            color = DARK; textSize = 42f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(value, x + 28f, y + 85f, valuePaint)

        val arLabelPaint = TextPaint().apply { color = GOLD; textSize = 34f; isAntiAlias = true }
        canvas.drawText(labelAr, x + 28f, y + 118f, arLabelPaint)
    }

    private fun drawScoreCircle(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val y = startY + 30f
        val centerX = PAGE_WIDTH / 2f
        val centerY = y + 180f
        val radius = 180f

        // Background circle
        val bgPaint = Paint().apply { color = VERY_LIGHT_GRAY; isAntiAlias = true }
        canvas.drawCircle(centerX, centerY, radius, bgPaint)

        // Outer ring
        val ringPaint = Paint().apply {
            color = 0xFFE8E8E8.toInt(); isAntiAlias = true
            style = Paint.Style.STROKE; strokeWidth = 16f
        }
        canvas.drawCircle(centerX, centerY, radius, ringPaint)

        // Score arc
        val scoreColor = getScoreColor(report.overallScore)
        val arcPaint = Paint().apply {
            color = scoreColor; isAntiAlias = true
            style = Paint.Style.STROKE; strokeWidth = 22f; strokeCap = Paint.Cap.ROUND
        }
        val arcRect = RectF(centerX - radius, centerY - radius, centerX + radius, centerY + radius)
        val sweepAngle = (report.overallScore / 100f) * 360f
        canvas.drawArc(arcRect, -90f, sweepAngle, false, arcPaint)

        // Score value
        val scorePaint = TextPaint().apply {
            color = scoreColor; textSize = 120f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER
        }
        canvas.drawText("%.1f".format(report.overallScore), centerX, centerY + 30f, scorePaint)

        // Score label Arabic
        val labelArPaint = TextPaint().apply {
            color = GRAY; textSize = 44f; isAntiAlias = true; textAlign = Paint.Align.CENTER
        }
        canvas.drawText(getScoreLabel(report.overallScore), centerX, centerY + 75f, labelArPaint)

        // Score label English
        val labelEnPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 32f; isAntiAlias = true
            textAlign = Paint.Align.CENTER; letterSpacing = 0.12f
        }
        canvas.drawText("OVERALL SCORE", centerX, centerY + 115f, labelEnPaint)

        return y + 400f
    }

    private fun drawSkinTypeCard(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "نوع البشرة", "SKIN TYPE")

        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val rect = RectF(MARGIN, y + 30f, PAGE_WIDTH - MARGIN, y + 130f)
        canvas.drawRoundRect(rect, 24f, 24f, cardPaint)

        val borderPaint = Paint().apply {
            color = GOLD_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f
        }
        canvas.drawRoundRect(rect, 24f, 24f, borderPaint)

        // Gold accent
        val accentPaint = Paint().apply { color = GOLD; isAntiAlias = true }
        canvas.drawRect(MARGIN, y + 50f, MARGIN + 8f, y + 110f, accentPaint)

        val skinTypeText = report.skinProfile?.skinTypeAr ?: "غير محدد"
        val textPaint = TextPaint().apply {
            color = DARK; textSize = 52f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(skinTypeText, MARGIN + 35f, y + 95f, textPaint)

        return y + 160f
    }

    private fun drawAiSummary(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "ملخص الذكاء الاصطناعي", "AI ANALYSIS SUMMARY")

        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val rect = RectF(MARGIN, y + 30f, PAGE_WIDTH - MARGIN, y + 380f)
        canvas.drawRoundRect(rect, 24f, 24f, cardPaint)

        val borderPaint = Paint().apply {
            color = GOLD_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f
        }
        canvas.drawRoundRect(rect, 24f, 24f, borderPaint)

        val summaryText = report.aiAnalysisText
            ?: "تم التحليل بنجاح. النتائج متوفرة في الصفحات التالية."

        val textPaint = TextPaint().apply {
            color = GRAY; textSize = 40f; isAntiAlias = true
        }

        val staticLayout = StaticLayout.Builder.obtain(
            summaryText, 0, summaryText.length, textPaint, CONTENT_WIDTH.toInt() - 80
        )
            .setAlignment(Layout.Alignment.ALIGN_NORMAL)
            .setLineSpacing(6f, 1.2f)
            .build()

        canvas.save()
        canvas.translate(MARGIN + 40f, y + 55f)
        staticLayout.draw(canvas)
        canvas.restore()

        return y + 400f
    }

    private fun drawTopConcerns(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "المخاوف الرئيسية", "TOP CONCERNS")

        val topConcerns = report.metrics.sortedBy { it.score }.take(4)
        val chipWidth = (CONTENT_WIDTH - 30f * (topConcerns.size - 1)) / topConcerns.size
        val chipHeight = 90f
        var x = MARGIN

        for (metric in topConcerns) {
            val bgColor = getSeverityBgColor(metric.severity)
            val chipPaint = Paint().apply { color = bgColor; isAntiAlias = true }
            val rect = RectF(x, y + 30f, x + chipWidth, y + 30f + chipHeight)
            canvas.drawRoundRect(rect, 20f, 20f, chipPaint)

            val borderColor = getSeverityColor(metric.severity)
            val borderPaint = Paint().apply {
                color = borderColor; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 3f
            }
            canvas.drawRoundRect(rect, 20f, 20f, borderPaint)

            val namePaint = TextPaint().apply {
                color = borderColor; textSize = 38f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD)
            }
            val label = getArabicMetricName(metric.type)
            canvas.drawText(label, x + 20f, y + 70f, namePaint)

            val scorePaint = TextPaint().apply {
                color = borderColor; textSize = 34f; isAntiAlias = true
            }
            canvas.drawText("%.0f%%".format(metric.score), x + 20f, y + 108f, scorePaint)

            x += chipWidth + 30f
        }

        return y + 160f
    }

    // ── Page 2 helpers ──

    private fun drawPageHeader(canvas: Canvas, startY: Float, titleAr: String, titleEn: String): Float {
        var y = startY + 50f

        val arPaint = TextPaint().apply {
            color = DARK; textSize = 68f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(titleAr, MARGIN, y, arPaint)

        val enPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 34f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText(titleEn, MARGIN, y + 45f, enPaint)

        val divPaint = Paint().apply { color = GOLD; strokeWidth = 4f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y + 65f, MARGIN + 160f, y + 65f, divPaint)

        return y + 80f
    }

    private fun drawTableHeader(canvas: Canvas, startY: Float): Float {
        val y = startY + 10f
        val headerBg = Paint().apply { color = GOLD_VERY_LIGHT; isAntiAlias = true }
        canvas.drawRect(MARGIN, y - 10f, PAGE_WIDTH - MARGIN, y + 45f, headerBg)

        val thPaint = TextPaint().apply {
            color = GOLD_DARK; textSize = 38f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD); letterSpacing = 0.08f
        }

        val colMetric = MARGIN + 20f
        val colScore = MARGIN + 700f
        val colStatus = MARGIN + 1100f
        val colBar = MARGIN + 1500f

        canvas.drawText("المؤشر", colMetric, y + 28f, thPaint)
        canvas.drawText("النتيجة", colScore, y + 28f, thPaint)
        canvas.drawText("الحالة", colStatus, y + 28f, thPaint)
        canvas.drawText("المستوى", colBar, y + 28f, thPaint)

        val linePaint = Paint().apply { color = GOLD; strokeWidth = 2f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y + 45f, PAGE_WIDTH - MARGIN, y + 45f, linePaint)

        return y + 65f
    }

    private fun drawMetricRows(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY
        val rowHeight = 90f

        val namePaint = TextPaint().apply {
            color = DARK; textSize = 42f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.NORMAL)
        }
        val scorePaint = TextPaint().apply {
            textSize = 44f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val statusPaint = TextPaint().apply {
            textSize = 38f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val barBgPaint = Paint().apply { color = 0xFFE8E8E8.toInt(); isAntiAlias = true }
        val barFillPaint = Paint().apply { isAntiAlias = true; style = Paint.Style.FILL }

        val colMetric = MARGIN + 20f
        val colScore = MARGIN + 700f
        val colStatus = MARGIN + 1100f
        val colBar = MARGIN + 1500f

        for ((index, metric) in report.metrics.withIndex()) {
            // Alternating row background
            if (index % 2 == 0) {
                val rowBg = Paint().apply { color = 0x06000000; isAntiAlias = true }
                canvas.drawRect(MARGIN, y - 5f, PAGE_WIDTH - MARGIN, y + rowHeight - 10f, rowBg)
            }

            // Metric name
            canvas.drawText(getArabicMetricName(metric.type), colMetric, y + 40f, namePaint)

            // Score
            scorePaint.color = getScoreColor(metric.score)
            canvas.drawText("%.0f".format(metric.score), colScore, y + 40f, scorePaint)

            // Status
            statusPaint.color = getSeverityColor(metric.severity)
            canvas.drawText(getSeverityLabel(metric.severity), colStatus, y + 40f, statusPaint)

            // Progress bar
            val barX = colBar
            val barYPos = y + 22f
            val barWidth = 360f
            val barHeight = 30f
            val barRect = RectF(barX, barYPos, barX + barWidth, barYPos + barHeight)
            canvas.drawRoundRect(barRect, 15f, 15f, barBgPaint)

            val fillWidth = (metric.score / 100f * barWidth).coerceIn(0f, barWidth)
            if (fillWidth > 0) {
                barFillPaint.color = getScoreColor(metric.score)
                val fillRect = RectF(barX, barYPos, barX + fillWidth, barYPos + barHeight)
                canvas.drawRoundRect(fillRect, 15f, 15f, barFillPaint)
            }

            // Percentage inside bar
            val pctPaint = TextPaint().apply {
                color = WHITE; textSize = 26f; isAntiAlias = true
                typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER
            }
            if (fillWidth > 60f) {
                canvas.drawText("%.0f%%".format(metric.score), barX + fillWidth / 2, barYPos + 22f, pctPaint)
            }

            y += rowHeight
        }

        return y + 20f
    }

    private fun drawSummaryCard(canvas: Canvas, startY: Float, report: SkinAnalysisReport) {
        val y = startY + 20f
        val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
        val rect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + 220f)
        canvas.drawRoundRect(rect, 24f, 24f, cardPaint)

        val borderPaint = Paint().apply {
            color = GOLD_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f
        }
        canvas.drawRoundRect(rect, 24f, 24f, borderPaint)

        val titlePaint = TextPaint().apply {
            color = DARK; textSize = 48f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("ملخص النتائج", MARGIN + 40f, y + 55f, titlePaint)

        val excellent = report.metrics.count { it.severity == MetricSeverity.EXCELLENT }
        val good = report.metrics.count { it.severity == MetricSeverity.GOOD }
        val fair = report.metrics.count { it.severity == MetricSeverity.FAIR }
        val poor = report.metrics.count { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }

        val statPaint = TextPaint().apply { color = GRAY; textSize = 40f; isAntiAlias = true }
        canvas.drawText("ممتاز: $excellent  |  جيد: $good  |  مقبول: $fair  |  ضعيف: $poor",
            MARGIN + 40f, y + 110f, statPaint)

        val totalPaint = TextPaint().apply {
            color = GOLD; textSize = 42f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText("إجمالي المؤشرات: ${report.metrics.size}", MARGIN + 40f, y + 170f, totalPaint)
    }

    // ── Page 3 helpers ──

    private fun drawExpertTips(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val tips = report.expertTipsAr
        if (tips.isEmpty()) return startY

        drawSectionTitle(canvas, MARGIN, startY, "نصائح الخبراء", "EXPERT TIPS")
        var y = startY + 40f

        val tipTextPaint = TextPaint().apply {
            color = GRAY; textSize = 38f; isAntiAlias = true
        }
        val tipNumberPaint = TextPaint().apply {
            color = GOLD; textSize = 44f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }

        for ((index, tip) in tips.withIndex()) {
            // Estimate card height based on text length
            val estimatedLines = (tip.length / 35f).toInt().coerceIn(1, 3)
            val cardHeight = (70f + estimatedLines * 45f).coerceAtMost(250f)

            val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val rect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + cardHeight)
            canvas.drawRoundRect(rect, 20f, 20f, cardPaint)

            val borderPaint = Paint().apply {
                color = 0x15000000.toInt(); isAntiAlias = true
                style = Paint.Style.STROKE; strokeWidth = 2f
            }
            canvas.drawRoundRect(rect, 20f, 20f, borderPaint)

            // Number circle
            val numCx = MARGIN + 40f
            val numCy = y + 38f
            val numBgPaint = Paint().apply { color = GOLD_VERY_LIGHT; isAntiAlias = true }
            canvas.drawCircle(numCx, numCy, 28f, numBgPaint)
            canvas.drawText("${index + 1}", numCx - 10f, numCy + 16f, tipNumberPaint)

            // Tip text with StaticLayout
            val staticLayout = StaticLayout.Builder.obtain(
                tip, 0, tip.length, tipTextPaint, CONTENT_WIDTH.toInt() - 120
            )
                .setAlignment(Layout.Alignment.ALIGN_NORMAL)
                .setLineSpacing(4f, 1.1f)
                .build()

            canvas.save()
            canvas.translate(MARGIN + 90f, y + 20f)
            staticLayout.draw(canvas)
            canvas.restore()

            y += cardHeight + 16f
        }

        return y + 20f
    }

    private fun drawProductRecommendations(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val products = report.productRecommendations
        if (products.isEmpty()) return startY

        drawSectionTitle(canvas, MARGIN, startY, "المنتجات المقترحة", "RECOMMENDED PRODUCTS")
        var y = startY + 40f

        val namePaint = TextPaint().apply {
            color = DARK; textSize = 42f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val descPaint = TextPaint().apply { color = GRAY; textSize = 34f; isAntiAlias = true }
        val pricePaint = TextPaint().apply {
            color = GOLD; textSize = 38f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        val matchPaint = TextPaint().apply {
            color = GREEN; textSize = 34f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }

        for (product in products) {
            val cardHeight = 180f
            val cardPaint = Paint().apply { color = WHITE; isAntiAlias = true }
            val rect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + cardHeight)
            canvas.drawRoundRect(rect, 20f, 20f, cardPaint)

            val borderPaint = Paint().apply {
                color = 0x15000000.toInt(); isAntiAlias = true
                style = Paint.Style.STROKE; strokeWidth = 2f
            }
            canvas.drawRoundRect(rect, 20f, 20f, borderPaint)

            // Gold accent bar
            val accentPaint = Paint().apply { color = GOLD; isAntiAlias = true }
            canvas.drawRect(MARGIN, y + 20f, MARGIN + 8f, y + cardHeight - 20f, accentPaint)

            // Product name
            canvas.drawText(product.name, MARGIN + 35f, y + 50f, namePaint)

            // Match score
            if (product.matchScore > 0) {
                canvas.drawText("نسبة التطابق: %.0f%%".format(product.matchScore * 100),
                    PAGE_WIDTH - MARGIN - 500f, y + 50f, matchPaint)
            }

            // Reason text
            val reason = product.reasonAr.ifEmpty { product.reason }
            if (reason.isNotEmpty()) {
                val staticLayout = StaticLayout.Builder.obtain(
                    reason, 0, reason.length, descPaint, CONTENT_WIDTH.toInt() - 80
                )
                    .setAlignment(Layout.Alignment.ALIGN_NORMAL)
                    .setLineSpacing(2f, 1.1f)
                    .build()

                canvas.save()
                canvas.translate(MARGIN + 35f, y + 70f)
                staticLayout.draw(canvas)
                canvas.restore()
            }

            // Price
            if (product.price > 0) {
                val priceText = "%.0f %s".format(product.price, product.currency)
                canvas.drawText(priceText, MARGIN + 35f, y + cardHeight - 20f, pricePaint)
            }

            // jenincare.shop link
            val urlPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 28f; isAntiAlias = true; letterSpacing = 0.05f }
            canvas.drawText("jenincare.shop", PAGE_WIDTH - MARGIN - 350f, y + cardHeight - 20f, urlPaint)

            y += cardHeight + 16f
        }

        return y + 20f
    }

    private fun drawDisclaimer(canvas: Canvas, startY: Float) {
        val y = startY + 30f
        val disclaimerPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true }

        val disclaimerText = "هذا التقرير تم إنشاؤه بواسطة الذكاء الاصطناعي ولا يغني عن استشارة الطبيب المختص."
        val staticLayout = StaticLayout.Builder.obtain(
            disclaimerText, 0, disclaimerText.length, disclaimerPaint, CONTENT_WIDTH.toInt()
        )
            .setAlignment(Layout.Alignment.ALIGN_CENTER)
            .build()

        canvas.save()
        canvas.translate(MARGIN, y)
        staticLayout.draw(canvas)
        canvas.restore()
    }

    // ═══════════════════════════════════════════════════════════
    //  UTILITY METHODS
    // ═══════════════════════════════════════════════════════════

    private fun drawSectionTitle(canvas: Canvas, x: Float, y: Float, titleAr: String, titleEn: String) {
        val arPaint = TextPaint().apply {
            color = DARK; textSize = 50f; isAntiAlias = true
            typeface = Typeface.create("sans-serif", Typeface.BOLD)
        }
        canvas.drawText(titleAr, x, y, arPaint)

        val enPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; letterSpacing = 0.08f
        }
        canvas.drawText(titleEn, x + arPaint.measureText(titleAr) + 30f, y, enPaint)

        val linePaint = Paint().apply { color = GOLD; strokeWidth = 4f; isAntiAlias = true }
        canvas.drawLine(x, y + 10f, x + 120f, y + 10f, linePaint)
    }

    private fun drawFooter(canvas: Canvas, pageNum: Int, totalPages: Int) {
        val footerY = PAGE_HEIGHT - 80f
        val linePaint = Paint().apply { color = GOLD; strokeWidth = 2f; isAntiAlias = true }
        canvas.drawLine(MARGIN, footerY, PAGE_WIDTH - MARGIN, footerY, linePaint)

        val leftPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; letterSpacing = 0.05f
        }
        canvas.drawText("DERMA AI — Advanced Skin Analysis System", MARGIN, footerY + 35f, leftPaint)

        val rightPaint = TextPaint().apply {
            color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; textAlign = Paint.Align.RIGHT
        }
        canvas.drawText("صفحة $pageNum من $totalPages", PAGE_WIDTH - MARGIN, footerY + 35f, rightPaint)
    }

    private fun getScoreColor(score: Float): Int = when {
        score >= 72f -> GREEN
        score >= 55f -> 0xFF74C69D.toInt()
        score >= 35f -> ORANGE
        score >= 20f -> 0xFFFF9800.toInt()
        else -> RED
    }

    private fun getScoreLabel(score: Float): String = when {
        score >= 72f -> "ممتاز"
        score >= 55f -> "جيد"
        score >= 35f -> "مقبول"
        score >= 20f -> "ضعيف"
        else -> "حرج"
    }

    private fun getSeverityColor(severity: MetricSeverity): Int = when (severity) {
        MetricSeverity.EXCELLENT -> GREEN
        MetricSeverity.GOOD -> 0xFF74C69D.toInt()
        MetricSeverity.FAIR -> ORANGE
        MetricSeverity.POOR -> RED
        MetricSeverity.CRITICAL -> RED_DARK
    }

    private fun getSeverityBgColor(severity: MetricSeverity): Int = when (severity) {
        MetricSeverity.EXCELLENT -> 0x1A2ECC71.toInt()
        MetricSeverity.GOOD -> 0x1A74C69D.toInt()
        MetricSeverity.FAIR -> 0x1AF39C12.toInt()
        MetricSeverity.POOR -> 0x1AE74C3C.toInt()
        MetricSeverity.CRITICAL -> 0x1AC0392B.toInt()
    }

    private fun getSeverityLabel(severity: MetricSeverity): String = when (severity) {
        MetricSeverity.EXCELLENT -> "ممتاز"
        MetricSeverity.GOOD -> "جيد"
        MetricSeverity.FAIR -> "مقبول"
        MetricSeverity.POOR -> "ضعيف"
        MetricSeverity.CRITICAL -> "حرج"
    }

    private fun getArabicMetricName(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "الرطوبة المائية"
        SkinMetric.Type.PORES -> "حجم المسام"
        SkinMetric.Type.SEBUM -> "إفرازات الزيوت"
        SkinMetric.Type.WRINKLES -> "التجاعيد والخطوط"
        SkinMetric.Type.TEXTURE -> "ملمس البشرة"
        SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        SkinMetric.Type.PIGMENTATION -> "التصبغات"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات السوداء"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        SkinMetric.Type.ACNE -> "حب الشباب"
        SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        SkinMetric.Type.SENSITIVITY -> "حساسية البشرة"
        SkinMetric.Type.ROSACEA -> "حب الشباب الوردي"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
