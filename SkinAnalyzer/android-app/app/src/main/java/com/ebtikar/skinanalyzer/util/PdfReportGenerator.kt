package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.DashPathEffect
import android.graphics.Paint
import android.graphics.Path
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
import com.ebtikar.skinanalyzer.model.SkinZone
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

        private const val BLUE = 0xFF06B6D4.toInt()
        private const val BLUE_DARK = 0xFF0891B2.toInt()
        private const val BLUE_LIGHT = 0x3306B6D4.toInt()
        private const val BLUE_VERY_LIGHT = 0x1006B6D4.toInt()
        private const val DARK = 0xFF1A1A2E.toInt()
        private const val GRAY = 0xFF6B7280.toInt()
        private const val LIGHT_GRAY = 0xFF9CA3AF.toInt()
        private const val VERY_LIGHT_GRAY = 0xFFF3F4F6.toInt()
        private const val GREEN = 0xFF10B981.toInt()
        private const val GREEN_LIGHT = 0xFF34D399.toInt()
        private const val ORANGE = 0xFFF59E0B.toInt()
        private const val RED = 0xFFEF4444.toInt()
        private const val RED_DARK = 0xFFDC2626.toInt()
        private const val WHITE = 0xFFFFFFFF.toInt()
        private const val BG = 0xFFF8FAFC.toInt()
        private const val CARD_BG = 0xFFFFFFFF.toInt()

        private val ZONE_NAMES_AR = mapOf(
            SkinZone.T_ZONE to "منطقة T — الجبهة والأنف والذقن",
            SkinZone.U_ZONE to "الخدود والوجنتين",
            SkinZone.EYE_AREA to "منطقة حول العين",
            SkinZone.O_ZONE to "المنطقة الخارجية للوجه",
            SkinZone.FULL_FACE to "الوجه بالكامل"
        )
        private val ZONE_EMOJI = mapOf(
            SkinZone.T_ZONE to "T",
            SkinZone.U_ZONE to "C",
            SkinZone.EYE_AREA to "E",
            SkinZone.O_ZONE to "O",
            SkinZone.FULL_FACE to "F"
        )
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
            drawPage1_ClinicalSummary(pdfDocument, report, 1)
            drawPage2_ZoneAnalysis(pdfDocument, report, 2)
            drawPage3_TreatmentPlan(pdfDocument, report, 3)
            drawPage4_Images(pdfDocument, capturedImages, 4, bitmapsToRecycle)

            if (!outputDir.exists()) outputDir.mkdirs()
            val file = File(outputDir, "DERMA_AI_Report_${report.id.take(8)}.pdf")
            FileOutputStream(file).use { fos ->
                pdfDocument.writeTo(fos)
            }
            Timber.i("PDF generated: ${file.absolutePath} (${file.length()} bytes)")
            file
        } catch (e: Exception) {
            Timber.e(e, "Failed to generate PDF")
            null
        } finally {
            try { pdfDocument.close() } catch (_: Exception) {}
            for (b in bitmapsToRecycle) { try { if (!b.isRecycled) b.recycle() } catch (_: Exception) {} }
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 1 — Clinical Summary with Face Diagram
    // ═══════════════════════════════════════════════════════════

    private fun drawPage1_ClinicalSummary(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas
        canvas.drawColor(BG)
        var y = 0f

        y = drawBlueHeader(canvas, y, report)
        y = drawExecutiveSummary(canvas, y, report)
        y = drawFaceDiagramWithZones(canvas, y, report)
        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    private fun drawBlueHeader(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY
        val barPaint = Paint().apply { color = BLUE; isAntiAlias = true }
        canvas.drawRect(0f, y, PAGE_WIDTH.toFloat(), y + 12f, barPaint)
        y += 12f

        val dateFormat = SimpleDateFormat("dd MMMM yyyy — hh:mm a", Locale("ar"))
        val dateStr = dateFormat.format(Date(report.timestamp))

        val logoPaint = Paint().apply { color = BLUE_VERY_LIGHT; isAntiAlias = true }
        canvas.drawCircle(MARGIN + 40f, y + 60f, 45f, logoPaint)
        val logoBorder = Paint().apply { color = BLUE; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 3f }
        canvas.drawCircle(MARGIN + 40f, y + 60f, 45f, logoBorder)
        val dPaint = TextPaint().apply { color = BLUE; textSize = 48f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER }
        canvas.drawText("D", MARGIN + 40f, y + 78f, dPaint)

        val titlePaint = TextPaint().apply { color = BLUE; textSize = 64f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD); letterSpacing = 0.06f }
        canvas.drawText("DERMA AI", MARGIN + 110f, y + 55f, titlePaint)
        val subPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; letterSpacing = 0.12f }
        canvas.drawText("INTEGRATED UNIFIED DIAGNOSIS", MARGIN + 110f, y + 90f, subPaint)

        val datePaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 28f; isAntiAlias = true; textAlign = Paint.Align.RIGHT }
        canvas.drawText(dateStr, PAGE_WIDTH - MARGIN, y + 55f, datePaint)

        val enginePaint = TextPaint().apply { color = BLUE; textSize = 26f; isAntiAlias = true; textAlign = Paint.Align.RIGHT }
        canvas.drawText(report.providerName.replace("_", " ").take(30), PAGE_WIDTH - MARGIN, y + 85f, enginePaint)

        y += 110f
        val linePaint = Paint().apply { color = BLUE; strokeWidth = 2f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y, PAGE_WIDTH - MARGIN, y, linePaint)

        val arTitle = TextPaint().apply { color = DARK; textSize = 72f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText("تقرير تحليل البشرة", MARGIN, y + 80f, arTitle)
        val enSub = TextPaint().apply { color = LIGHT_GRAY; textSize = 34f; isAntiAlias = true; letterSpacing = 0.05f }
        canvas.drawText("Unified Clinical Report — Comprehensive Skin Analysis", MARGIN, y + 120f, enSub)

        return y + 150f
    }

    private fun drawExecutiveSummary(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "الملخص التنفيذي", "EXECUTIVE SUMMARY")
        y += 40f

        val cardPaint = Paint().apply { color = CARD_BG; isAntiAlias = true }
        val cardRect = RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + 420f)
        canvas.drawRoundRect(cardRect, 20f, 20f, cardPaint)
        val borderPaint = Paint().apply { color = BLUE_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f }
        canvas.drawRoundRect(cardRect, 20f, 20f, borderPaint)

        val accentPaint = Paint().apply { color = BLUE; isAntiAlias = true }
        canvas.drawRect(MARGIN, y + 20f, MARGIN + 6f, y + 400f, accentPaint)

        val score = report.overallScore
        val scoreColor = getScoreColor(score)
        val scoreLabel = getScoreLabel(score)

        val scorePaint = TextPaint().apply { color = scoreColor; textSize = 100f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText("%.0f".format(score), MARGIN + 40f, y + 100f, scorePaint)
        val scoreLabelPaint = TextPaint().apply { color = scoreColor; textSize = 36f; isAntiAlias = true }
        canvas.drawText("/100 — $scoreLabel", MARGIN + 40f + scorePaint.measureText("%.0f".format(score)) + 15f, y + 100f, scoreLabelPaint)

        val skinType = report.skinProfile?.skinTypeAr ?: "غير محدد"
        val typePaint = TextPaint().apply { color = DARK; textSize = 36f; isAntiAlias = true }
        canvas.drawText("نوع البشرة: $skinType", MARGIN + 40f, y + 155f, typePaint)

        val excellent = report.metrics.count { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }
        val fair = report.metrics.count { it.severity == MetricSeverity.FAIR }
        val poor = report.metrics.count { it.severity == MetricSeverity.POOR }
        val critical = report.metrics.count { it.severity == MetricSeverity.CRITICAL }

        val statPaint = TextPaint().apply { color = GRAY; textSize = 34f; isAntiAlias = true }
        canvas.drawText("المؤشرات: ✅ $excellent جيدة | ⚠️ $fair متوسطة | 🔴 $poor تحتاج عناية | ❌ $critical حرجة", MARGIN + 40f, y + 210f, statPaint)

        val urgent = report.metrics.filter { it.severity == MetricSeverity.CRITICAL || it.severity == MetricSeverity.POOR }
            .sortedBy { it.score }.take(3)
        if (urgent.isNotEmpty()) {
            val urgentPaint = TextPaint().apply { color = RED; textSize = 34f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
            canvas.drawText("🔴 أعلى إلحاحاً: ${urgent.joinToString("، ") { "${getArabicName(it.type)} (${ "%.0f".format(it.score) })" }}", MARGIN + 40f, y + 265f, urgentPaint)
        }

        val summaryText = report.aiAnalysisTextAr ?: report.aiAnalysisText ?: ""
        if (summaryText.isNotEmpty()) {
            val textPaint = TextPaint().apply { color = GRAY; textSize = 30f; isAntiAlias = true }
            val lines = summaryText.lines().take(5).joinToString("\n")
            val staticLayout = StaticLayout.Builder.obtain(lines, 0, lines.length, textPaint, CONTENT_WIDTH.toInt() - 80)
                .setAlignment(Layout.Alignment.ALIGN_NORMAL).setLineSpacing(4f, 1.15f).build()
            canvas.save()
            canvas.translate(MARGIN + 40f, y + 290f)
            staticLayout.draw(canvas)
            canvas.restore()
        }

        return y + 450f
    }

    private fun drawFaceDiagramWithZones(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "التحليل حسب المنطقة", "ZONE-BASED ANALYSIS")
        y += 40f

        val metricsByZone = report.metrics.groupBy { it.zone }
        val zoneOrder = listOf(SkinZone.T_ZONE, SkinZone.U_ZONE, SkinZone.EYE_AREA, SkinZone.O_ZONE, SkinZone.FULL_FACE)

        val faceWidth = 500f
        val faceX = MARGIN + faceWidth / 2f
        val faceY = y + 200f
        drawFaceOutline(canvas, faceX, faceY, 180f)

        var rightX = MARGIN + faceWidth + 80f
        val rightWidth = CONTENT_WIDTH - faceWidth - 80f

        for (zone in zoneOrder) {
            val zoneMetrics = metricsByZone[zone] ?: continue
            if (zoneMetrics.isEmpty()) continue
            if (y + 200f > PAGE_HEIGHT - 200f) break

            val zoneLabel = ZONE_NAMES_AR[zone] ?: continue
            val emoji = ZONE_EMOJI[zone] ?: "•"

            val zoneBg = Paint().apply { color = BLUE_VERY_LIGHT; isAntiAlias = true }
            canvas.drawRoundRect(RectF(rightX, y, rightX + rightWidth, y + 35f), 8f, 8f, zoneBg)
            val zoneLabelPaint = TextPaint().apply { color = BLUE_DARK; textSize = 30f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
            canvas.drawText("$emoji $zoneLabel", rightX + 15f, y + 26f, zoneLabelPaint)
            y += 45f

            for (m in zoneMetrics.sortedBy { it.score }) {
                if (y + 60f > PAGE_HEIGHT - 200f) break
                val namePaint = TextPaint().apply { color = DARK; textSize = 28f; isAntiAlias = true }
                val scoreColor = getScoreColor(m.score)
                val scorePaint = TextPaint().apply { color = scoreColor; textSize = 28f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
                val severityPaint = TextPaint().apply { color = scoreColor; textSize = 24f; isAntiAlias = true }

                canvas.drawText(getArabicName(m.type), rightX + 15f, y + 22f, namePaint)
                canvas.drawText("${ "%.0f".format(m.score) }/100 (${m.severity.displayAr})", rightX + 15f, y + 50f, scorePaint)
                y += 60f
            }
            y += 15f
        }

        return y.coerceAtLeast(startY + 250f)
    }

    private fun drawFaceOutline(canvas: Canvas, cx: Float, cy: Float, radius: Float) {
        val facePaint = Paint().apply { color = 0xFFE2E8F0.toInt(); isAntiAlias = true; style = Paint.Style.FILL }
        canvas.drawOval(cx - radius * 0.7f, cy - radius, cx + radius * 0.7f, cy + radius, facePaint)

        val outlinePaint = Paint().apply { color = BLUE; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 3f }
        canvas.drawOval(cx - radius * 0.7f, cy - radius, cx + radius * 0.7f, cy + radius, outlinePaint)

        val zonePaint = Paint().apply { color = BLUE; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f; pathEffect = DashPathEffect(floatArrayOf(10f, 6f), 0f) }

        val tZonePath = Path()
        tZonePath.moveTo(cx - 60f, cy - radius + 30f)
        tZonePath.lineTo(cx + 60f, cy - radius + 30f)
        tZonePath.lineTo(cx + 40f, cy + 40f)
        tZonePath.lineTo(cx - 40f, cy + 40f)
        tZonePath.close()
        canvas.drawPath(tZonePath, zonePaint)

        val leftEye = Path()
        leftEye.addOval(cx - 55f, cy - 30f, cx - 15f, cy + 10f, Path.Direction.CW)
        canvas.drawPath(leftEye, zonePaint)
        val rightEye = Path()
        rightEye.addOval(cx + 15f, cy - 30f, cx + 55f, cy + 10f, Path.Direction.CW)
        canvas.drawPath(rightEye, zonePaint)

        val labelPaint = TextPaint().apply { color = BLUE; textSize = 22f; isAntiAlias = true; textAlign = Paint.Align.CENTER }
        canvas.drawText("T", cx, cy - radius + 55f, labelPaint)
        canvas.drawText("E", cx - 35f, cy - 5f, labelPaint)
        canvas.drawText("E", cx + 35f, cy - 5f, labelPaint)
        canvas.drawText("C", cx - 80f, cy + 10f, labelPaint)
        canvas.drawText("C", cx + 80f, cy + 10f, labelPaint)
        canvas.drawText("O", cx - 95f, cy - 20f, labelPaint)
        canvas.drawText("O", cx + 95f, cy - 20f, labelPaint)
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 2 — Zone-Grouped Metrics Detail
    // ═══════════════════════════════════════════════════════════

    private fun drawPage2_ZoneAnalysis(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas
        canvas.drawColor(BG)
        var y = 0f

        y = drawPageHeaderCompact(canvas, y, "تفاصيل المؤشرات حسب المنطقة", "METRICS BY ZONE")
        y += 20f

        val metricsByZone = report.metrics.groupBy { it.zone }
        val zoneOrder = listOf(SkinZone.T_ZONE, SkinZone.U_ZONE, SkinZone.EYE_AREA, SkinZone.O_ZONE, SkinZone.FULL_FACE)

        for (zone in zoneOrder) {
            val zoneMetrics = metricsByZone[zone] ?: continue
            if (zoneMetrics.isEmpty()) continue
            if (y > PAGE_HEIGHT - 400f) break

            val zoneLabel = ZONE_NAMES_AR[zone] ?: "الوجه بالكامل"

            val zoneHeaderBg = Paint().apply { color = BLUE_VERY_LIGHT; isAntiAlias = true }
            canvas.drawRoundRect(RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + 55f), 12f, 12f, zoneHeaderBg)
            val zoneHeaderText = TextPaint().apply { color = BLUE_DARK; textSize = 38f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
            canvas.drawText("📍 $zoneLabel", MARGIN + 20f, y + 40f, zoneHeaderText)
            y += 70f

            y = drawMetricTableHeader(canvas, y)

            for ((index, metric) in zoneMetrics.sortedBy { it.score }.withIndex()) {
                if (y > PAGE_HEIGHT - 150f) break
                y = drawMetricRow(canvas, y, metric, index)
            }
            y += 25f
        }

        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    private fun drawPageHeaderCompact(canvas: Canvas, startY: Float, titleAr: String, titleEn: String): Float {
        var y = startY + 40f
        val arPaint = TextPaint().apply { color = DARK; textSize = 60f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText(titleAr, MARGIN, y, arPaint)
        val enPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 30f; isAntiAlias = true; letterSpacing = 0.08f }
        canvas.drawText(titleEn, MARGIN, y + 40f, enPaint)
        val linePaint = Paint().apply { color = BLUE; strokeWidth = 3f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y + 55f, MARGIN + 140f, y + 55f, linePaint)
        return y + 70f
    }

    private fun drawMetricTableHeader(canvas: Canvas, startY: Float): Float {
        val y = startY
        val headerBg = Paint().apply { color = 0x0D06B6D4.toInt(); isAntiAlias = true }
        canvas.drawRect(MARGIN, y, PAGE_WIDTH - MARGIN, y + 42f, headerBg)

        val thPaint = TextPaint().apply { color = BLUE_DARK; textSize = 32f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD); letterSpacing = 0.06f }
        canvas.drawText("المؤشر", MARGIN + 20f, y + 30f, thPaint)
        canvas.drawText("النتيجة", MARGIN + 550f, y + 30f, thPaint)
        canvas.drawText("الحالة", MARGIN + 900f, y + 30f, thPaint)
        canvas.drawText("المستوى", MARGIN + 1300f, y + 30f, thPaint)

        val linePaint = Paint().apply { color = BLUE; strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y + 42f, PAGE_WIDTH - MARGIN, y + 42f, linePaint)
        return y + 52f
    }

    private fun drawMetricRow(canvas: Canvas, startY: Float, metric: SkinMetric, index: Int): Float {
        val y = startY
        val rowHeight = 78f

        if (index % 2 == 0) {
            val rowBg = Paint().apply { color = 0x04000000; isAntiAlias = true }
            canvas.drawRect(MARGIN, y, PAGE_WIDTH - MARGIN, y + rowHeight, rowBg)
        }

        val namePaint = TextPaint().apply { color = DARK; textSize = 36f; isAntiAlias = true }
        canvas.drawText(getArabicName(metric.type), MARGIN + 20f, y + 45f, namePaint)

        val scoreColor = getScoreColor(metric.score)
        val scorePaint = TextPaint().apply { color = scoreColor; textSize = 38f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText("%.0f".format(metric.score), MARGIN + 550f, y + 45f, scorePaint)

        val statusPaint = TextPaint().apply { color = scoreColor; textSize = 34f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText(metric.severity.displayAr, MARGIN + 900f, y + 45f, statusPaint)

        val barX = MARGIN + 1300f
        val barY = y + 20f
        val barWidth = 400f
        val barHeight = 28f
        val barBg = Paint().apply { color = 0xFFE5E7EB.toInt(); isAntiAlias = true }
        canvas.drawRoundRect(RectF(barX, barY, barX + barWidth, barY + barHeight), 14f, 14f, barBg)

        val fillWidth = (metric.score / 100f * barWidth).coerceIn(0f, barWidth)
        if (fillWidth > 0) {
            val barFill = Paint().apply { color = scoreColor; isAntiAlias = true }
            canvas.drawRoundRect(RectF(barX, barY, barX + fillWidth, barY + barHeight), 14f, 14f, barFill)
        }

        val pctPaint = TextPaint().apply { color = WHITE; textSize = 22f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD); textAlign = Paint.Align.CENTER }
        if (fillWidth > 50f) {
            canvas.drawText("%.0f%%".format(metric.score), barX + fillWidth / 2, barY + 20f, pctPaint)
        }

        return y + rowHeight
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 3 — Treatment Plan
    // ═══════════════════════════════════════════════════════════

    private fun drawPage3_TreatmentPlan(pdfDocument: PdfDocument, report: SkinAnalysisReport, pageNum: Int) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas
        canvas.drawColor(BG)
        var y = 0f

        y = drawPageHeaderCompact(canvas, y, "خطة العلاج والتوصيات", "TREATMENT PLAN & RECOMMENDATIONS")
        y += 20f

        y = drawTreatmentTimeline(canvas, y, report)
        y += 20f
        y = drawExpertTipsCompact(canvas, y, report)
        y += 20f
        y = drawProductCards(canvas, y, report)
        y += 30f
        drawDisclaimer(canvas, y)

        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    private fun drawTreatmentTimeline(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        var y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "خطة العلاج المقترحة", "PROPOSED TREATMENT PLAN")
        y += 40f

        val urgent = report.metrics.filter { it.severity == MetricSeverity.CRITICAL || it.severity == MetricSeverity.POOR }.sortedBy { it.score }.take(3)
        val fair = report.metrics.filter { it.severity == MetricSeverity.FAIR }.take(3)

        val phases = mutableListOf<Triple<String, String, Int>>()
        if (urgent.isNotEmpty()) {
            phases.add(Triple("🔴 فوري (هذا الأسبوع)", "IMMEDIATE", RED))
        }
        if (fair.isNotEmpty()) {
            phases.add(Triple("🟡 قصير المدى (هذا الشهر)", "SHORT-TERM", ORANGE))
        }
        phases.add(Triple("🟢 طويل المدى (٣-٦ شهور)", "LONG-TERM", GREEN))

        for ((phaseIdx, phase) in phases.withIndex()) {
            if (y > PAGE_HEIGHT - 300f) break

            val phaseBg = Paint().apply { color = 0x0D06B6D4.toInt(); isAntiAlias = true }
            canvas.drawRoundRect(RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + 45f), 10f, 10f, phaseBg)
            val phasePaint = TextPaint().apply { color = phase.third; textSize = 36f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
            canvas.drawText(phase.first, MARGIN + 20f, y + 35f, phasePaint)
            y += 60f

            val tips = if (phaseIdx == 0) urgent
                else if (phaseIdx == 1) fair
                else report.metrics.filter { it.severity != MetricSeverity.EXCELLENT && it.severity != MetricSeverity.GOOD }.take(4)

            var stepNum = 1
            for (m in tips) {
                if (y > PAGE_HEIGHT - 150f) break
                val namePaint = TextPaint().apply { color = DARK; textSize = 32f; isAntiAlias = true }
                val detailPaint = TextPaint().apply { color = GRAY; textSize = 28f; isAntiAlias = true }

                canvas.drawText("${stepNum}.", MARGIN + 30f, y + 25f, namePaint)
                canvas.drawText("${getArabicName(m.type)}: ${m.severity.displayAr}", MARGIN + 65f, y + 25f, namePaint)

                val desc = when (m.severity) {
                    MetricSeverity.CRITICAL -> "يحتاج عناية فورية ومتواصلة"
                    MetricSeverity.POOR -> "يحتاج عناية مركزة هذا الأسبوع"
                    MetricSeverity.FAIR -> "يحتاج متابعة وتحسين تدريجي"
                    else -> "في حالة جيدة — حافظي عليها"
                }
                canvas.drawText(desc, MARGIN + 65f, y + 55f, detailPaint)
                stepNum++
                y += 68f
            }
            y += 15f
        }

        return y
    }

    private fun drawExpertTipsCompact(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val tips = report.expertTipsAr
        if (tips.isEmpty()) return startY

        var y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "نصائح الخبراء", "EXPERT TIPS")
        y += 40f

        val tipTextPaint = TextPaint().apply { color = GRAY; textSize = 32f; isAntiAlias = true }
        val tipNumberPaint = TextPaint().apply { color = BLUE; textSize = 38f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }

        for ((index, tip) in tips.take(4).withIndex()) {
            if (y > PAGE_HEIGHT - 200f) break
            val estimatedLines = (tip.length / 40f).toInt().coerceIn(1, 3)
            val cardHeight = (55f + estimatedLines * 40f).coerceAtMost(200f)

            val cardPaint = Paint().apply { color = CARD_BG; isAntiAlias = true }
            canvas.drawRoundRect(RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + cardHeight), 14f, 14f, cardPaint)

            val numBg = Paint().apply { color = BLUE_VERY_LIGHT; isAntiAlias = true }
            canvas.drawCircle(MARGIN + 35f, y + 30f, 24f, numBg)
            canvas.drawText("${index + 1}", MARGIN + 26f, y + 42f, tipNumberPaint)

            val staticLayout = StaticLayout.Builder.obtain(tip, 0, tip.length, tipTextPaint, CONTENT_WIDTH.toInt() - 100)
                .setAlignment(Layout.Alignment.ALIGN_NORMAL).setLineSpacing(3f, 1.1f).build()
            canvas.save()
            canvas.translate(MARGIN + 75f, y + 15f)
            staticLayout.draw(canvas)
            canvas.restore()

            y += cardHeight + 12f
        }

        return y + 10f
    }

    private fun drawProductCards(canvas: Canvas, startY: Float, report: SkinAnalysisReport): Float {
        val products = report.productRecommendations
        if (products.isEmpty()) return startY

        var y = startY + 10f
        drawSectionTitle(canvas, MARGIN, y, "المنتجات المقترحة", "RECOMMENDED PRODUCTS")
        y += 40f

        val namePaint = TextPaint().apply { color = DARK; textSize = 38f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        val descPaint = TextPaint().apply { color = GRAY; textSize = 30f; isAntiAlias = true }
        val pricePaint = TextPaint().apply { color = BLUE; textSize = 34f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        val matchPaint = TextPaint().apply { color = GREEN; textSize = 30f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }

        for (product in products.take(4)) {
            if (y > PAGE_HEIGHT - 200f) break
            val cardHeight = 160f
            val cardPaint = Paint().apply { color = CARD_BG; isAntiAlias = true }
            canvas.drawRoundRect(RectF(MARGIN, y, PAGE_WIDTH - MARGIN, y + cardHeight), 14f, 14f, cardPaint)

            val accentPaint = Paint().apply { color = BLUE; isAntiAlias = true }
            canvas.drawRect(MARGIN, y + 15f, MARGIN + 6f, y + cardHeight - 15f, accentPaint)

            canvas.drawText(product.nameAr.ifEmpty { product.name }, MARGIN + 30f, y + 45f, namePaint)

            if (product.matchScore > 0) {
                canvas.drawText("تطابق: %.0f%%".format(product.matchScore * 100), PAGE_WIDTH - MARGIN - 350f, y + 45f, matchPaint)
            }

            val reason = product.reasonAr.ifEmpty { product.reason }
            if (reason.isNotEmpty()) {
                val staticLayout = StaticLayout.Builder.obtain(reason, 0, reason.length, descPaint, CONTENT_WIDTH.toInt() - 80)
                    .setAlignment(Layout.Alignment.ALIGN_NORMAL).setLineSpacing(2f, 1.1f).build()
                canvas.save()
                canvas.translate(MARGIN + 30f, y + 65f)
                staticLayout.draw(canvas)
                canvas.restore()
            }

            if (product.price > 0) {
                canvas.drawText("%.0f %s".format(product.price, product.currency), MARGIN + 30f, y + cardHeight - 18f, pricePaint)
            }
            val urlPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 24f; isAntiAlias = true }
            canvas.drawText("jenincare.shop", PAGE_WIDTH - MARGIN - 280f, y + cardHeight - 18f, urlPaint)

            y += cardHeight + 12f
        }

        return y + 10f
    }

    private fun drawDisclaimer(canvas: Canvas, startY: Float) {
        val y = startY + 30f
        val linePaint = Paint().apply { color = 0xFFE5E7EB.toInt(); strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, y, PAGE_WIDTH - MARGIN, y, linePaint)

        val disclaimerPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 28f; isAntiAlias = true }
        val disclaimerText = "هذا التقرير تم إنشاؤه بواسطة الذكاء الاصطناعي (DERMA AI) ولا يغني عن استشارة الطبيب المختص. النتائج مبنية على تحليل بصري للصور الملتقطة."
        val staticLayout = StaticLayout.Builder.obtain(disclaimerText, 0, disclaimerText.length, disclaimerPaint, CONTENT_WIDTH.toInt())
            .setAlignment(Layout.Alignment.ALIGN_CENTER).build()
        canvas.save()
        canvas.translate(MARGIN, y + 15f)
        staticLayout.draw(canvas)
        canvas.restore()
    }

    // ═══════════════════════════════════════════════════════════
    //  PAGE 4 — Captured Spectral Images
    // ═══════════════════════════════════════════════════════════

    private fun drawPage4_Images(pdfDocument: PdfDocument, capturedImages: Map<LightSpectrum, File>, pageNum: Int, bitmapsToRecycle: MutableList<Bitmap>) {
        val pageInfo = PdfDocument.PageInfo.Builder(PAGE_WIDTH, PAGE_HEIGHT, pageNum).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas = page.canvas
        canvas.drawColor(BG)
        var y = 0f

        y = drawPageHeaderCompact(canvas, y, "الصور الملتقطة", "CAPTURED SPECTRAL IMAGES")
        y += 30f

        val spectra = LightSpectrum.CAPTURE_SEQUENCE.filter { capturedImages.containsKey(it) }
        if (spectra.isEmpty()) {
            val emptyPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 48f; isAntiAlias = true; textAlign = Paint.Align.CENTER }
            canvas.drawText("لا توجد صور ملتقطة", PAGE_WIDTH / 2f, y + 100f, emptyPaint)
            drawFooter(canvas, pageNum, 4)
            pdfDocument.finishPage(page)
            return
        }

        val cols = 2
        val cellSpacing = 25f
        val labelHeight = 80f
        val cellWidth = (CONTENT_WIDTH - cellSpacing * (cols - 1)) / cols
        val rows = (spectra.size + cols - 1) / cols
        val cellHeight = ((PAGE_HEIGHT - y - 250f - labelHeight * rows - cellSpacing * (rows + 1)) / rows).coerceAtMost(500f)

        for ((index, spectrum) in spectra.withIndex()) {
            val file = capturedImages[spectrum] ?: continue
            val col = index % cols
            val row = index / cols

            val cellX = MARGIN + col * (cellWidth + cellSpacing)
            val cellY = y + cellSpacing + row * (cellHeight + labelHeight + cellSpacing)

            val cardPaint = Paint().apply { color = CARD_BG; isAntiAlias = true }
            val cardRect = RectF(cellX, cellY, cellX + cellWidth, cellY + cellHeight)
            canvas.drawRoundRect(cardRect, 16f, 16f, cardPaint)

            val borderPaint = Paint().apply { color = BLUE_LIGHT; isAntiAlias = true; style = Paint.Style.STROKE; strokeWidth = 2f }
            canvas.drawRoundRect(cardRect, 16f, 16f, borderPaint)

            try {
                val options = BitmapFactory.Options().apply { inSampleSize = 4 }
                val bitmap = BitmapFactory.decodeFile(file.absolutePath, options)
                if (bitmap != null) {
                    val padding = 12f
                    val imgRect = RectF(cellX + padding, cellY + padding, cellX + cellWidth - padding, cellY + cellHeight - padding)
                    canvas.drawBitmap(bitmap, null, imgRect, null)
                    bitmapsToRecycle.add(bitmap)
                }
            } catch (e: Exception) {
                Timber.w(e, "Failed to decode image for ${spectrum.name}")
            }

            val spectrumColor = try { Color.parseColor(spectrum.colorHex) } catch (_: Exception) { BLUE }
            val indicatorPaint = Paint().apply { color = spectrumColor; isAntiAlias = true }
            canvas.drawCircle(cellX + cellWidth - 24f, cellY + 24f, 12f, indicatorPaint)

            val nameArPaint = TextPaint().apply { color = DARK; textSize = 40f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
            canvas.drawText(spectrum.displayNameAr, cellX + 16f, cellY + cellHeight + 45f, nameArPaint)

            val nameEnPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 28f; isAntiAlias = true; letterSpacing = 0.04f }
            canvas.drawText(spectrum.displayName, cellX + 16f, cellY + cellHeight + 75f, nameEnPaint)
        }

        drawFooter(canvas, pageNum, 4)
        pdfDocument.finishPage(page)
    }

    // ═══════════════════════════════════════════════════════════
    //  UTILITY
    // ═══════════════════════════════════════════════════════════

    private fun drawSectionTitle(canvas: Canvas, x: Float, y: Float, titleAr: String, titleEn: String) {
        val arPaint = TextPaint().apply { color = DARK; textSize = 46f; isAntiAlias = true; typeface = Typeface.create("sans-serif", Typeface.BOLD) }
        canvas.drawText(titleAr, x, y, arPaint)
        val enPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 28f; isAntiAlias = true; letterSpacing = 0.08f }
        canvas.drawText(titleEn, x + arPaint.measureText(titleAr) + 25f, y, enPaint)
        val linePaint = Paint().apply { color = BLUE; strokeWidth = 3f; isAntiAlias = true }
        canvas.drawLine(x, y + 10f, x + 100f, y + 10f, linePaint)
    }

    private fun drawFooter(canvas: Canvas, pageNum: Int, totalPages: Int) {
        val footerY = PAGE_HEIGHT - 70f
        val linePaint = Paint().apply { color = 0xFFE5E7EB.toInt(); strokeWidth = 1f; isAntiAlias = true }
        canvas.drawLine(MARGIN, footerY, PAGE_WIDTH - MARGIN, footerY, linePaint)

        val leftPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 26f; isAntiAlias = true; letterSpacing = 0.04f }
        canvas.drawText("DERMA AI — Integrated Unified Diagnosis System", MARGIN, footerY + 30f, leftPaint)

        val rightPaint = TextPaint().apply { color = LIGHT_GRAY; textSize = 26f; isAntiAlias = true; textAlign = Paint.Align.RIGHT }
        canvas.drawText("صفحة $pageNum من $totalPages", PAGE_WIDTH - MARGIN, footerY + 30f, rightPaint)
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
        score >= 35f -> "متوسط"
        score >= 20f -> "يحتاج عناية"
        else -> "يحتاج عناية مركزة"
    }

    private fun getArabicName(type: SkinMetric.Type): String = when (type) {
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
