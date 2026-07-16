package com.ebtikar.skinanalyzer.ai

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Canvas
import android.graphics.ColorMatrix
import android.graphics.ColorMatrixColorFilter
import android.graphics.Paint
import android.graphics.PointF
import android.graphics.RectF
import dagger.hilt.android.qualifiers.ApplicationContext
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.MetricTrend
import com.ebtikar.skinanalyzer.model.SkinZone
import java.io.File
import kotlin.math.abs
import kotlin.math.atan2
import kotlin.math.cos
import kotlin.math.sin
import kotlin.math.sqrt
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AdvancedSkinAnalyzer @Inject constructor(
    @ApplicationContext private val context: Context,
    private val faceMeshDetector: FaceMeshDetector
) {
    init {
        faceMeshDetector.initialize()
    }

    data class RegionAnalysis(
        val region: SkinRegion,
        val pixels: List<PointF>,
        val bounds: RectF?,
        val bitmap: Bitmap?,
        val centerX: Float = bounds?.centerX() ?: 0f,
        val centerY: Float = bounds?.centerY() ?: 0f,
        val area: Float = (bounds?.width() ?: 0f) * (bounds?.height() ?: 0f)
    )

    data class CrossSpectrumResult(
        val type: SkinMetric.Type,
        val primaryScore: Float,
        val crossSpectrumBonus: Float,
        val confidence: Float,
        val details: String
    )

    suspend fun analyze(
        frames: Map<LightSpectrum, File>,
        whiteFile: File?
    ): Map<SkinMetric.Type, SkinMetric> = withContext(Dispatchers.Default) {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        val spectrumScores = mutableMapOf<LightSpectrum, MutableMap<SkinMetric.Type, Float>>()

        val whiteBitmap = whiteFile?.let {
            if (it.exists()) CVUtils.decodeSampled(it) else null
        }

        if (whiteBitmap == null || whiteBitmap.width < 10 || whiteBitmap.height < 10) {
            Timber.w("White bitmap too small or null — falling back to simple analysis")
            whiteBitmap?.recycle()
            val fallbackResult = fallbackSimpleAnalysis(frames, null)
            return@withContext fallbackResult
        }

        val faceMesh = try { whiteBitmap?.let { faceMeshDetector.detect(it) } } catch (e: Exception) {
            Timber.e(e, "Face mesh detection threw exception")
            null
        }
        if (faceMesh == null) {
            Timber.w("No face mesh detected — falling back to simple analysis")
            val fallbackResult = fallbackSimpleAnalysis(frames, whiteBitmap)
            whiteBitmap?.recycle()
            return@withContext fallbackResult
        }

        Timber.i("Face mesh: ${faceMesh.landmarks.size} landmarks, confidence=${faceMesh.confidence}")

        val regions = try {
            extractRegions(faceMesh, whiteBitmap)
        } catch (e: Exception) {
            Timber.e(e, "Region extraction failed")
            mapOf()
        }

        for ((spectrum, file) in frames) {
            if (!file.exists()) continue
            val bitmap = CVUtils.decodeSampled(file) ?: continue

            try {
                val spectrumMetrics = analyzeSpectrumAdvanced(
                    spectrum, bitmap, faceMesh, regions, whiteBitmap
                )
                val spectrumScoresMap = mutableMapOf<SkinMetric.Type, Float>()
                for ((type, metric) in spectrumMetrics) {
                    metrics[type] = metric
                    spectrumScoresMap[type] = metric.score
                }
                spectrumScores[spectrum] = spectrumScoresMap
            } catch (e: Exception) {
                Timber.e(e, "Advanced analysis failed for ${spectrum.name}")
            }
            bitmap.recycle()
        }

        if (metrics.isEmpty()) {
            Timber.w("Advanced analysis produced no metrics, falling back to simple analysis")
            val fallbackResult = fallbackSimpleAnalysis(frames, whiteBitmap)
            regions.values.forEach { it.bitmap?.recycle() }
            whiteBitmap?.recycle()
            return@withContext fallbackResult
        }

        val fusedMetrics = crossSpectrumFusion(metrics, spectrumScores)

        regions.values.forEach { it.bitmap?.recycle() }
        whiteBitmap?.recycle()

        fusedMetrics
    }

    private fun extractRegions(
        faceMesh: FaceMeshDetector.FaceMeshResult,
        whiteBitmap: Bitmap?
    ): Map<SkinRegion, RegionAnalysis> {
        val regions = mutableMapOf<SkinRegion, RegionAnalysis>()

        for (region in SkinRegion.entries) {
            if (region == SkinRegion.FULL_FACE) continue
            val bounds = region.getRegionBounds(faceMesh.landmarks)
            val pixels = region.extractRegionPixels(faceMesh.landmarks, whiteBitmap?.width ?: 0, whiteBitmap?.height ?: 0)

            val regionBitmap = if (bounds != null && whiteBitmap != null) {
                extractRegionBitmap(whiteBitmap, bounds)
            } else null

            val centerX = bounds?.centerX() ?: 0f
            val centerY = bounds?.centerY() ?: 0f
            val area = (bounds?.width() ?: 0f) * (bounds?.height() ?: 0f)

            regions[region] = RegionAnalysis(region, pixels, bounds, regionBitmap, centerX, centerY, area)
        }

        return regions
    }

    private fun crossSpectrumFusion(
        allMetrics: Map<SkinMetric.Type, SkinMetric>,
        spectrumData: Map<LightSpectrum, Map<SkinMetric.Type, Float>>
    ): Map<SkinMetric.Type, SkinMetric> {
        val fused = mutableMapOf<SkinMetric.Type, SkinMetric>()
        for ((type, baseMetric) in allMetrics) {
            val spectrumValues = spectrumData.mapNotNull { (spectrum, data) ->
                data[type]?.let { spectrum to it }
            }
            if (spectrumValues.size >= 2) {
                val scores = spectrumValues.map { it.second }
                val mean = scores.average().toFloat()
                val variance = scores.map { (it - mean) * (it - mean) }.average().toFloat()
                val stdDev = kotlin.math.sqrt(variance)
                val confidenceBoost = (stdDev / 30f).coerceIn(0f, 0.15f)
                val fusedScore = (baseMetric.score * 0.6f + mean * 0.4f).coerceIn(5f, 95f)
                val newConfidence = (baseMetric.confidence + confidenceBoost).coerceAtMost(0.95f)
                fused[type] = baseMetric.copy(
                    score = fusedScore,
                    confidence = newConfidence,
                    details = "${baseMetric.details} [fusion: ${spectrumValues.size} spectra]"
                )
            } else {
                fused[type] = baseMetric
            }
        }
        return fused
    }

    private fun safeCrop(bitmap: Bitmap, x: Int, y: Int, w: Int, h: Int): Bitmap? {
        if (bitmap.isRecycled) { Timber.w("safeCrop: source bitmap recycled"); return null }
        return try {
            val sub = Bitmap.createBitmap(bitmap, x, y, w.coerceAtMost(bitmap.width - x), h.coerceAtMost(bitmap.height - y))
            val copy = sub.copy(Bitmap.Config.ARGB_8888, false)
            if (sub !== copy) sub.recycle()
            copy
        } catch (e: Exception) { null }
    }

    private fun extractRegionBitmap(bitmap: Bitmap, bounds: RectF): Bitmap? {
        val x = bounds.left.toInt().coerceIn(0, bitmap.width - 1)
        val y = bounds.top.toInt().coerceIn(0, bitmap.height - 1)
        val w = bounds.width().toInt().coerceAtLeast(1)
        val h = bounds.height().toInt().coerceAtLeast(1)
        return safeCrop(bitmap, x, y, w, h)
    }

    fun cropRect(bitmap: Bitmap, rect: android.graphics.Rect): Bitmap? {
        return safeCrop(bitmap, rect.left, rect.top, rect.width(), rect.height())
    }

    private fun analyzeSpectrumAdvanced(
        spectrum: LightSpectrum,
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>,
        whiteBitmap: Bitmap?
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        when (spectrum) {
            LightSpectrum.WHITE -> {
                metrics.putAll(analyzeWhiteSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.UV365 -> {
                metrics.putAll(analyzeUVSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.POL_P -> {
                metrics.putAll(analyzePolPSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.POL_N -> {
                metrics.putAll(analyzePolNSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.WOODS -> {
                metrics.putAll(analyzeWoodsSpectrum(bitmap, faceMesh, regions, whiteBitmap))
            }
            LightSpectrum.BLUE -> {
                metrics.putAll(analyzeBlueSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.BROWN -> {
                metrics.putAll(analyzeBrownSpectrum(bitmap, faceMesh, regions))
            }
            LightSpectrum.RED -> {
                metrics.putAll(analyzeRedSpectrum(bitmap, faceMesh, regions))
            }
            else -> {}
        }

        return metrics
    }

    private fun analyzeWhiteSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val cheekLeft = regions[SkinRegion.LEFT_CHEEK]?.bitmap
        val cheekRight = regions[SkinRegion.RIGHT_CHEEK]?.bitmap
        val tZone = regions[SkinRegion.NOSE]?.bitmap
        val forehead = regions[SkinRegion.FOREHEAD]?.bitmap
        val chin = regions[SkinRegion.CHIN]?.bitmap

        val textureScore = analyzeTextureAdvanced(cheekLeft, cheekRight, forehead)
        metrics[SkinMetric.Type.TEXTURE] = createMetric(
            SkinMetric.Type.TEXTURE, textureScore, SkinZone.FULL_FACE,
            "تحليل النسيج المتعدد المقياس (Gabor + LBP + Morphology + Cross-Zone)"
        )

        val poreScore = analyzePoresAdvanced(tZone, faceMesh, chin)
        metrics[SkinMetric.Type.PORES] = createMetric(
            SkinMetric.Type.PORES, poreScore, SkinZone.T_ZONE,
            "تحليل كثافة المسام (Morphological + Frequency Domain + Cross-Zone)"
        )

        val toneScore = analyzeSkinToneAdvanced(bitmap, faceMesh, regions)
        metrics[SkinMetric.Type.SKIN_TONE] = createMetric(
            SkinMetric.Type.SKIN_TONE, toneScore, SkinZone.FULL_FACE,
            "تحليل لون البشرة (LAB Color Variance + Histogram + Uniformity)"
        )

        return metrics
    }

    private fun analyzeTextureAdvanced(cheekLeft: Bitmap?, cheekRight: Bitmap?, forehead: Bitmap?): Float {
        val lbp1 = if (cheekLeft != null) CVUtils.localBinaryPattern(cheekLeft, 2) else 0f
        val lbp2 = if (cheekRight != null) CVUtils.localBinaryPattern(cheekRight, 2) else 0f
        val gabor1 = if (cheekLeft != null) CVUtils.gaborTextureEnergy(cheekLeft) else 0f
        val gabor2 = if (cheekRight != null) CVUtils.gaborTextureEnergy(cheekRight) else 0f
        val morph1 = if (cheekLeft != null) CVUtils.morphologicalGradient(cheekLeft) else 0f
        val morph2 = if (cheekRight != null) CVUtils.morphologicalGradient(cheekRight) else 0f
        val avgLbp = (lbp1 + lbp2) / 2f
        val avgGabor = (gabor1 + gabor2) / 2f
        val avgMorph = (morph1 + morph2) / 2f

        val foreheadTexture = if (forehead != null) CVUtils.localBinaryPattern(forehead, 2) else avgLbp
        val zoneConsistency = 1f - kotlin.math.abs(avgLbp - foreheadTexture) / (avgLbp + foreheadTexture + 0.01f)

        val combined = avgLbp * 0.25f + avgGabor * 0.35f + avgMorph * 0.25f + zoneConsistency * 0.15f
        return CVUtils.calibratedScore(combined, 50f, 2f)
    }

    private fun analyzePoresAdvanced(tZone: Bitmap?, faceMesh: FaceMeshDetector.FaceMeshResult, chin: Bitmap?): Float {
        if (tZone == null) return 50f
        val poreDensity = CVUtils.poreDensityEstimate(tZone)
        val morphGrad = CVUtils.morphologicalGradient(tZone, 3)
        val specular = CVUtils.specularHighlightRatio(tZone)
        val chinPores = if (chin != null) CVUtils.poreDensityEstimate(chin) else poreDensity * 0.7f
        val zoneRatio = if (chinPores > 0.01f) poreDensity / chinPores else 1f
        val tZoneEmphasis = (zoneRatio / (zoneRatio + 1f)).coerceIn(0.3f, 0.8f)
        val combined = poreDensity * 0.45f + morphGrad / 100f * 0.25f + (1f - specular) * 0.15f + tZoneEmphasis * 0.15f
        return CVUtils.calibratedScore(combined, 0.8f, 0.05f)
    }

    private fun analyzeSkinToneAdvanced(bitmap: Bitmap, faceMesh: FaceMeshDetector.FaceMeshResult, regions: Map<SkinRegion, RegionAnalysis>): Float {
        val faceBounds = faceMesh.faceRect
        val faceBitmap = CVUtils.extractFaceRegion(bitmap, android.graphics.Rect(
            faceBounds.left.toInt(), faceBounds.top.toInt(),
            faceBounds.right.toInt(), faceBounds.bottom.toInt()
        ))
        val uniformity = if (faceBitmap != null) {
            val labVar = CVUtils.colorVarianceAnalysis(faceBitmap)
            val histEntropy = CVUtils.colorHistogramAnalysis(faceBitmap)
            val labUniformity = CVUtils.labColorUniformity(faceBitmap)
            val cheekL = regions[SkinRegion.LEFT_CHEEK]?.bitmap
            val cheekR = regions[SkinRegion.RIGHT_CHEEK]?.bitmap
            val cheekUniformity = if (cheekL != null && cheekR != null) {
                val u1 = CVUtils.labColorUniformity(cheekL)
                val u2 = CVUtils.labColorUniformity(cheekR)
                (u1 + u2) / 2f
            } else labUniformity
            faceBitmap.recycle()
            (labVar.first + labVar.second) / 2f + histEntropy / 10f + cheekUniformity
        } else 10f
        return CVUtils.calibratedScore(uniformity, 25f, 2f)
    }

    private fun analyzeUVSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh)
        if (faceBitmap == null) {
            metrics[SkinMetric.Type.UV_SPOTS] = createMetric(SkinMetric.Type.UV_SPOTS, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الوجه")
            metrics[SkinMetric.Type.PIGMENTATION] = createMetric(SkinMetric.Type.PIGMENTATION, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الوجه")
            return metrics
        }

        val stats = CVUtils.computePixelStats(faceBitmap)
        val spots = CVUtils.adaptiveThresholdSpots(faceBitmap, 15, 8)
        val pigHetero = CVUtils.pigmentationHeterogeneity(faceBitmap)
        val morphGrad = CVUtils.morphologicalGradient(faceBitmap, 5)

        val uvSpotsScore = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.40f, 0.005f)
        metrics[SkinMetric.Type.UV_SPOTS] = createMetric(
            SkinMetric.Type.UV_SPOTS, uvSpotsScore, SkinZone.FULL_FACE,
            "تحليل البقع فوق البنفسجية (Morphological + Adaptive Threshold)"
        )

        val pigmentation = CVUtils.calibratedScore(stats.contrast * 0.5f + pigHetero * 0.5f, 45f, 3f)
        metrics[SkinMetric.Type.PIGMENTATION] = createMetric(
            SkinMetric.Type.PIGMENTATION, pigmentation, SkinZone.FULL_FACE,
            "تحليل التصبغ (LAB Variance + Histogram Entropy)"
        )

        faceBitmap.recycle()
        return metrics
    }

    private fun analyzePolPSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh) ?: run {
            val default = createMetric(SkinMetric.Type.VASCULAR, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الأوعية الدموية")
            return mapOf(
                SkinMetric.Type.VASCULAR to default,
                SkinMetric.Type.SENSITIVITY to createMetric(SkinMetric.Type.SENSITIVITY, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الحساسية"),
                SkinMetric.Type.ROSACEA to createMetric(SkinMetric.Type.ROSACEA, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الوردية")
            )
        }

        val cheekLeft = regions[SkinRegion.LEFT_CHEEK]?.bitmap
        val cheekRight = regions[SkinRegion.RIGHT_CHEEK]?.bitmap
        val centerFace = regions[SkinRegion.NOSE]?.bitmap

        val rednessLeft = if (cheekLeft != null) CVUtils.hsvRednessIndex(cheekLeft) else 0f
        val rednessRight = if (cheekRight != null) CVUtils.hsvRednessIndex(cheekRight) else 0f
        val cheecksRedness = (rednessLeft + rednessRight) / 2f
        val faceRedness = if (faceBitmap != null) CVUtils.hsvRednessIndex(faceBitmap) else cheecksRedness
        val centerRedness = if (centerFace != null) CVUtils.hsvRednessIndex(centerFace) else cheecksRedness

        val vascularComplexity = if (faceBitmap != null) CVUtils.vascularPatternComplexity(faceBitmap) else 0f
        val inflammatory = if (faceBitmap != null) CVUtils.inflammatoryMarkerDetection(faceBitmap) else 0f

        val vascularScore = CVUtils.calibratedScore(cheecksRedness * 0.5f + vascularComplexity * 0.3f + inflammatory * 0.2f, 0.60f, 0.02f)
        metrics[SkinMetric.Type.VASCULAR] = createMetric(
            SkinMetric.Type.VASCULAR, vascularScore, SkinZone.U_ZONE,
            "تحليل الأوعية الدموية (Vascular Pattern + Inflammatory Markers)"
        )

        val sensitivityScore = CVUtils.calibratedScore(faceRedness * 0.6f + inflammatory * 0.4f, 0.50f, 0.02f)
        metrics[SkinMetric.Type.SENSITIVITY] = createMetric(
            SkinMetric.Type.SENSITIVITY, sensitivityScore, SkinZone.FULL_FACE,
            "تحليل حساسية البشرة (Redness + Inflammatory Detection)"
        )

        val rosaceaScore = CVUtils.calibratedScore((cheecksRedness + centerRedness) / 2f * 0.5f + vascularComplexity * 0.3f + inflammatory * 0.2f, 0.45f, 0.01f)
        metrics[SkinMetric.Type.ROSACEA] = createMetric(
            SkinMetric.Type.ROSACEA, rosaceaScore, SkinZone.U_ZONE,
            "تحليل الوردية (Vascular Complexity + Cheek Redness)"
        )

        faceBitmap.recycle()
        return metrics
    }

    private fun analyzePolNSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh) ?: run {
            return mapOf(SkinMetric.Type.WRINKLES to createMetric(SkinMetric.Type.WRINKLES, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل التجاعيد"))
        }

        val forehead = regions[SkinRegion.FOREHEAD]?.bitmap
        val cheekLeft = regions[SkinRegion.LEFT_CHEEK]?.bitmap
        val cheekRight = regions[SkinRegion.RIGHT_CHEEK]?.bitmap

        val regionBitmaps = listOfNotNull(forehead, cheekLeft, cheekRight).filter { it.width >= 10 && it.height >= 10 }
        val edgeRatio = if (regionBitmaps.isNotEmpty()) {
            regionBitmaps.map { CVUtils.cannyEdgeRatio(it) }.average().toFloat()
        } else 0f

        val wrinkleDepth = if (faceBitmap != null) CVUtils.wrinkleDepthEstimate(faceBitmap) else 0f
        val edgeHist = if (faceBitmap != null) CVUtils.edgeDirectionHistogram(faceBitmap) else 0f
        val depthScore = analyzeDepthFromLandmarks(faceMesh)
        val combined = edgeRatio * 0.3f + wrinkleDepth * 0.3f + edgeHist * 0.2f + depthScore * 0.2f
        val wrinkleScore = CVUtils.calibratedScore(combined, 0.40f, 0.003f)

        metrics[SkinMetric.Type.WRINKLES] = createMetric(
            SkinMetric.Type.WRINKLES, wrinkleScore, SkinZone.FULL_FACE,
            "تحليل التجاعيد (Edge + Gabor Depth + Direction Histogram + 3D)"
        )

        faceBitmap.recycle()
        return metrics
    }

    private fun analyzeDepthFromLandmarks(faceMesh: FaceMeshDetector.FaceMeshResult): Float {
        val landmarks3D = faceMesh.landmarks3D
        if (landmarks3D.isEmpty()) return 0f

        val zValues = landmarks3D.map { it[2] }
        val zRange = zValues.max() - zValues.min()
        val zVariance = zValues.map { (it - zValues.average()).pow(2.0).toFloat() }.average().toFloat()

        return sqrt(zVariance) / (zRange + 1f)
    }

    private fun analyzeWoodsSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>,
        whiteBitmap: Bitmap?
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh) ?: run {
            return mapOf(
                SkinMetric.Type.MOISTURE to createMetric(SkinMetric.Type.MOISTURE, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الرطوبة"),
                SkinMetric.Type.MELASMA to createMetric(SkinMetric.Type.MELASMA, 50f, SkinZone.FULL_FACE, "لا يمكن تحليل الكلف")
            )
        }

        val stats = CVUtils.computePixelStats(faceBitmap)
        val whiteStats = if (whiteBitmap != null) {
            val whiteFace = extractFaceFromMesh(whiteBitmap, faceMesh)
            if (whiteFace != null) {
                val s = CVUtils.computePixelStats(whiteFace)
                whiteFace.recycle()
                s
            } else null
        } else null

        val relativeBright = if (whiteStats != null && whiteStats.brightness > 10f) {
            stats.brightness / whiteStats.brightness
        } else 0.5f

        val skinBarrier = CVUtils.skinBarrierEstimate(faceBitmap)
        val moistureScore = CVUtils.calibratedScoreInverted(relativeBright * 0.6f + skinBarrier * 0.4f, 0.05f, 0.85f)
        metrics[SkinMetric.Type.MOISTURE] = createMetric(
            SkinMetric.Type.MOISTURE, moistureScore, SkinZone.FULL_FACE,
            "تحليل الرطوبة (Relative Brightness + Skin Barrier Estimate)"
        )

        val melasmaSpots = CVUtils.adaptiveThresholdSpots(faceBitmap, 11, 5)
        val pigHetero = CVUtils.pigmentationHeterogeneity(faceBitmap)
        val melasmaScore = CVUtils.calibratedScore(melasmaSpots * 0.6f + pigHetero * 0.4f, 0.40f, 0.003f)
        metrics[SkinMetric.Type.MELASMA] = createMetric(
            SkinMetric.Type.MELASMA, melasmaScore, SkinZone.FULL_FACE,
            "تحليل الميلasma (Adaptive Spots + Pigmentation Heterogeneity)"
        )

        faceBitmap.recycle()
        return metrics
    }

    private fun analyzeRedSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh)
        val redness = CVUtils.hsvRednessIndex(faceBitmap ?: bitmap)

        metrics[SkinMetric.Type.VASCULAR] = createMetric(
            SkinMetric.Type.VASCULAR, CVUtils.calibratedScore(redness, 0.60f, 0.02f), SkinZone.U_ZONE,
            "تحليل الأوعية الدموية بالجهد الأحمر 630nm"
        )

        faceBitmap?.recycle()
        return metrics
    }

    private fun analyzeBlueSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val faceBitmap = extractFaceFromMesh(bitmap, faceMesh) ?: run {
            return mapOf(
                SkinMetric.Type.SEBUM to createMetric(SkinMetric.Type.SEBUM, 50f, SkinZone.T_ZONE, "لا يمكن تحليل الدهون"),
                SkinMetric.Type.ACNE to createMetric(SkinMetric.Type.ACNE, 50f, SkinZone.T_ZONE, "لا يمكن تحليل حب الشباب"),
                SkinMetric.Type.BLACKHEADS to createMetric(SkinMetric.Type.BLACKHEADS, 50f, SkinZone.T_ZONE, "لا يمكن تحليل الرؤوس السوداء")
            )
        }

        val stats = CVUtils.computePixelStats(faceBitmap)
        val spots = CVUtils.adaptiveThresholdSpots(faceBitmap, 13, 6)
        val (sebumDist, sebumUniformity) = CVUtils.sebumDistributionAnalysis(faceBitmap)
        val morphGrad = CVUtils.morphologicalGradient(faceBitmap, 5)

        val sebumScore = CVUtils.calibratedScoreInverted(stats.meanB / 255f * 0.5f + sebumDist * 0.3f + morphGrad / 100f * 0.2f, 0.15f, 0.55f)
        metrics[SkinMetric.Type.SEBUM] = createMetric(
            SkinMetric.Type.SEBUM, sebumScore, SkinZone.T_ZONE,
            "تحليل الدهون (Blue Channel + Distribution + Morphology)"
        )

        val acneScore = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.40f, 0.003f)
        metrics[SkinMetric.Type.ACNE] = createMetric(
            SkinMetric.Type.ACNE, acneScore, SkinZone.T_ZONE,
            "تحليل حبشباب (Adaptive Threshold + Morphological Gradient)"
        )

        val blackheadScore = CVUtils.calibratedScore(spots * 0.5f + (1f - sebumUniformity / 50f) * 0.3f + morphGrad / 100f * 0.2f, 0.38f, 0.005f)
        metrics[SkinMetric.Type.BLACKHEADS] = createMetric(
            SkinMetric.Type.BLACKHEADS, blackheadScore, SkinZone.T_ZONE,
            "تحليل الرؤوس السوداء (Spots + Texture Uniformity + Morphology)"
        )

        faceBitmap.recycle()
        return metrics
    }

    private fun analyzeBrownSpectrum(
        bitmap: Bitmap,
        faceMesh: FaceMeshDetector.FaceMeshResult,
        regions: Map<SkinRegion, RegionAnalysis>
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val periorbitalLeft = regions[SkinRegion.PERIORBITAL_LEFT]?.bitmap
        val periorbitalRight = regions[SkinRegion.PERIORBITAL_RIGHT]?.bitmap

        val spotsLeft = if (periorbitalLeft != null) CVUtils.adaptiveThresholdSpots(periorbitalLeft, 11, 4) else 0f
        val spotsRight = if (periorbitalRight != null) CVUtils.adaptiveThresholdSpots(periorbitalRight, 11, 4) else 0f
        val avgSpots = (spotsLeft + spotsRight) / 2f

        val textureLeft = if (periorbitalLeft != null) CVUtils.localBinaryPattern(periorbitalLeft, 3) else 0f
        val textureRight = if (periorbitalRight != null) CVUtils.localBinaryPattern(periorbitalRight, 3) else 0f
        val avgTexture = (textureLeft + textureRight) / 2f

        val morphLeft = if (periorbitalLeft != null) CVUtils.morphologicalGradient(periorbitalLeft, 3) else 0f
        val morphRight = if (periorbitalRight != null) CVUtils.morphologicalGradient(periorbitalRight, 3) else 0f
        val avgMorph = (morphLeft + morphRight) / 2f

        val darkCircleScore = CVUtils.calibratedScore(avgSpots * 0.5f + avgTexture * 0.3f + avgMorph / 100f * 0.2f, 0.38f, 0.005f)
        metrics[SkinMetric.Type.DARK_CIRCLES] = createMetric(
            SkinMetric.Type.DARK_CIRCLES, darkCircleScore, SkinZone.EYE_AREA,
            "تحليل الهالات السوداء (Spots + LBP Texture + Morphology)"
        )

        return metrics
    }

    private fun extractFaceFromMesh(bitmap: Bitmap, faceMesh: FaceMeshDetector.FaceMeshResult): Bitmap? {
        val rect = faceMesh.faceRect
        if (rect.width() < 10 || rect.height() < 10) return null

        val x = (rect.left - rect.width() * 0.1f).toInt().coerceIn(0, bitmap.width - 1)
        val y = (rect.top - rect.height() * 0.1f).toInt().coerceIn(0, bitmap.height - 1)
        val w = (rect.width() * 1.2f).toInt().coerceAtMost(bitmap.width - x)
        val h = (rect.height() * 1.2f).toInt().coerceAtMost(bitmap.height - y)

        return safeCrop(bitmap, x, y, w, h)
    }

    private fun createMetric(
        type: SkinMetric.Type,
        score: Float,
        zone: SkinZone,
        details: String
    ): SkinMetric {
        val clamped = score.coerceIn(5f, 95f)
        val severity = when {
            clamped >= 72f -> MetricSeverity.EXCELLENT
            clamped >= 55f -> MetricSeverity.GOOD
            clamped >= 35f -> MetricSeverity.FAIR
            clamped >= 20f -> MetricSeverity.POOR
            else -> MetricSeverity.CRITICAL
        }

        return SkinMetric(
            type = type,
            score = clamped,
            severity = severity,
            zone = zone,
            details = details,
            confidence = if (score in 1f..5f || score in 95f..99f) 0.4f else 0.82f
        )
    }

    private fun estimateFaceRegions(skinRect: android.graphics.Rect): Map<String, android.graphics.Rect> {
        val x = skinRect.left; val y = skinRect.top
        val w = skinRect.width(); val h = skinRect.height()
        return mapOf(
            "T_ZONE" to android.graphics.Rect(x + w/4, y, x + 3*w/4, y + h/2),
            "CHEEKS" to android.graphics.Rect(x, y + h/4, x + w, y + 3*h/4),
            "FOREHEAD" to android.graphics.Rect(x + w/6, y, x + 5*w/6, y + h/3),
            "EYE_AREA" to android.graphics.Rect(x + w/8, y + h/4, x + 7*w/8, y + h/2 + h/8),
            "FULL_FACE" to skinRect
        )
    }

    private fun fallbackSimpleAnalysis(
        frames: Map<LightSpectrum, File>,
        whiteBitmap: Bitmap?
    ): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val skinRect = whiteBitmap?.let { CVUtils.findLargestSkinRegion(it) }
        val regions = skinRect?.let { estimateFaceRegions(it) } ?: emptyMap()
        Timber.i("fallbackSimpleAnalysis: skinRect=$skinRect, regions=${regions.keys}, whiteBitmap=${whiteBitmap?.width}x${whiteBitmap?.height}")

        for ((spectrum, file) in frames) {
            if (!file.exists()) continue
            val bitmap = CVUtils.decodeSampled(file) ?: continue

            try {
                val analyzeBitmap = if (skinRect != null && bitmap.width > skinRect.right && bitmap.height > skinRect.bottom) {
                    safeCrop(bitmap, skinRect.left, skinRect.top, skinRect.width(), skinRect.height()) ?: bitmap
                } else bitmap
                val useFull = analyzeBitmap === bitmap

                val stats = CVUtils.computePixelStats(analyzeBitmap)
                val texture = CVUtils.laplacianVariance(analyzeBitmap)
                val spots = CVUtils.adaptiveThresholdSpots(analyzeBitmap)
                val redness = CVUtils.hsvRednessIndex(analyzeBitmap)
                val specular = CVUtils.specularHighlightRatio(analyzeBitmap)
                val uniformity = CVUtils.labColorUniformity(analyzeBitmap)
                val edgeRatio = CVUtils.cannyEdgeRatio(analyzeBitmap)

                fun regionVal(rect: android.graphics.Rect?, compute: (Bitmap) -> Float, fallback: Float): Float {
                    if (rect == null || useFull) return fallback
                    val crop = safeCrop(analyzeBitmap, rect.left, rect.top, rect.width(), rect.height())
                    return crop?.let { compute(it).also { crop.recycle() } } ?: fallback
                }

                val tRect = regions["T_ZONE"]
                val cRect = regions["CHEEKS"]
                val fRect = regions["FOREHEAD"]
                val eRect = regions["EYE_AREA"]

                val tZoneTex = regionVal(tRect, { CVUtils.laplacianVariance(it) }, texture)
                val cheeksEdge = regionVal(cRect, { CVUtils.cannyEdgeRatio(it) }, edgeRatio)
                val foreheadEdge = regionVal(fRect, { CVUtils.cannyEdgeRatio(it) }, edgeRatio)
                val tZoneSpots = regionVal(tRect, { CVUtils.adaptiveThresholdSpots(it) }, spots)
                val eyeSpots = regionVal(eRect, { CVUtils.adaptiveThresholdSpots(it, 11, 4) }, spots)
                val cheeksRed = regionVal(cRect, { CVUtils.hsvRednessIndex(it) }, redness)

                when (spectrum) {
                    LightSpectrum.WHITE -> {
                        val textureScore = CVUtils.calibratedScore(texture, 65f, 2f)
                        val poreScore = CVUtils.calibratedScore(tZoneTex * 0.7f + (1f - specular) * 30f * 0.3f, 65f, 3f)
                        val toneScore = CVUtils.calibratedScore(uniformity, 25f, 2f)
                        metrics[SkinMetric.Type.TEXTURE] = createMetric(SkinMetric.Type.TEXTURE, textureScore, SkinZone.FULL_FACE, "تحليل النسيج (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.PORES] = createMetric(SkinMetric.Type.PORES, poreScore, SkinZone.T_ZONE, "تحليل المسام (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.SKIN_TONE] = createMetric(SkinMetric.Type.SKIN_TONE, toneScore, SkinZone.FULL_FACE, "تحليل لون البشرة (بدون شبكة الوجه)")
                    }
                    LightSpectrum.UV365 -> {
                        val uvSpotsScore = CVUtils.calibratedScore(spots, 0.40f, 0.003f)
                        val pigmentationScore = CVUtils.calibratedScore(stats.contrast, 35f, 2f)
                        metrics[SkinMetric.Type.UV_SPOTS] = createMetric(SkinMetric.Type.UV_SPOTS, uvSpotsScore, SkinZone.FULL_FACE, "تحليل البقع فوق البنفسجية (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.PIGMENTATION] = createMetric(SkinMetric.Type.PIGMENTATION, pigmentationScore, SkinZone.FULL_FACE, "تحليل التصبغ (بدون شبكة الوجه)")
                    }
                    LightSpectrum.POL_P -> {
                        Timber.d("POL_P: cheeksRed=$cheeksRed, fullRedness=$redness, spots=$spots")
                        val vascularScore = CVUtils.calibratedScore(cheeksRed, 0.60f, 0.01f)
                        val sensitivityScore = CVUtils.calibratedScore(redness, 0.50f, 0.01f)
                        val rosaceaScore = CVUtils.calibratedScore((cheeksRed + spots) / 2f, 0.45f, 0.005f)
                        metrics[SkinMetric.Type.VASCULAR] = createMetric(SkinMetric.Type.VASCULAR, vascularScore, SkinZone.U_ZONE, "تحليل الأوعية الدموية (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.SENSITIVITY] = createMetric(SkinMetric.Type.SENSITIVITY, sensitivityScore, SkinZone.FULL_FACE, "تحليل الحساسية (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.ROSACEA] = createMetric(SkinMetric.Type.ROSACEA, rosaceaScore, SkinZone.U_ZONE, "تحليل الوردية (بدون شبكة الوجه)")
                    }
                    LightSpectrum.POL_N -> {
                        Timber.d("POL_N: foreheadEdge=$foreheadEdge")
                        val wrinkleScore = CVUtils.calibratedScore(foreheadEdge, 0.50f, 0.005f)
                        metrics[SkinMetric.Type.WRINKLES] = createMetric(SkinMetric.Type.WRINKLES, wrinkleScore, SkinZone.FULL_FACE, "تحليل التجاعيد (بدون شبكة الوجه)")
                    }
                    LightSpectrum.WOODS -> {
                        val whiteCrop = if (skinRect != null && whiteBitmap != null && whiteBitmap.width > skinRect.right && whiteBitmap.height > skinRect.bottom) {
                            safeCrop(whiteBitmap, skinRect.left, skinRect.top, skinRect.width(), skinRect.height())
                        } else null
                        val whiteStats = whiteCrop?.let { CVUtils.computePixelStats(it).also { whiteCrop.recycle() } } ?: whiteBitmap?.let { CVUtils.computePixelStats(it) }
                        val rawBrightness = stats.brightness
                        val whiteBright = whiteStats?.brightness ?: 128f
                        Timber.d("MOISTURE: woodsBright=$rawBrightness, whiteBright=$whiteBright")
                        val relativeBright = if (whiteBright > 10f) rawBrightness / whiteBright else 0.5f
                        val moistureScore = CVUtils.calibratedScoreInverted(relativeBright, 0.05f, 0.90f)
                        val melasmaScore = CVUtils.calibratedScore(spots, 0.45f, 0.003f)
                        metrics[SkinMetric.Type.MOISTURE] = createMetric(SkinMetric.Type.MOISTURE, moistureScore, SkinZone.FULL_FACE, "تحليل الرطوبة (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.MELASMA] = createMetric(SkinMetric.Type.MELASMA, melasmaScore, SkinZone.FULL_FACE, "تحليل الكلف (بدون شبكة الوجه)")
                    }
                    LightSpectrum.BLUE -> {
                        val sebumScore = CVUtils.calibratedScoreInverted(stats.meanB / 255f, 0.15f, 0.50f)
                        val tZoneSpotScore = if (!useFull) CVUtils.calibratedScore(tZoneSpots, 0.40f, 0.003f) else CVUtils.calibratedScore(spots, 0.40f, 0.003f)
                        val tZoneBlackhead = if (!useFull) CVUtils.calibratedScore(tZoneSpots, 0.38f, 0.005f) else CVUtils.calibratedScore(spots, 0.38f, 0.005f)
                        metrics[SkinMetric.Type.SEBUM] = createMetric(SkinMetric.Type.SEBUM, sebumScore, SkinZone.T_ZONE, "تحليل الدهون (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.ACNE] = createMetric(SkinMetric.Type.ACNE, tZoneSpotScore, SkinZone.T_ZONE, "تحليل حب الشباب (بدون شبكة الوجه)")
                        metrics[SkinMetric.Type.BLACKHEADS] = createMetric(SkinMetric.Type.BLACKHEADS, tZoneBlackhead, SkinZone.T_ZONE, "تحليل الرؤوس السوداء (بدون شبكة الوجه)")
                    }
                    LightSpectrum.RED -> {
                        val vascularScore = CVUtils.calibratedScore(redness, 0.60f, 0.02f)
                        metrics[SkinMetric.Type.VASCULAR] = createMetric(SkinMetric.Type.VASCULAR, vascularScore, SkinZone.FULL_FACE, "تحليل الأوعية الدموية (الجهد الأحمر)")
                    }
                    LightSpectrum.BROWN -> {
                        val darkCircleScore = CVUtils.calibratedScore(eyeSpots, 0.38f, 0.005f)
                        metrics[SkinMetric.Type.DARK_CIRCLES] = createMetric(SkinMetric.Type.DARK_CIRCLES, darkCircleScore, SkinZone.EYE_AREA, "تحليل الهالات السوداء (بدون شبكة الوجه)")
                    }
                    else -> {}
                }
                if (analyzeBitmap !== bitmap) analyzeBitmap.recycle()
            } catch (e: Exception) {
                Timber.e(e, "fallbackSimpleAnalysis: spectrum ${spectrum.name} failed")
            }
            bitmap.recycle()
        }

        return metrics
    }

    override fun toString(): String = "AdvancedSkinAnalyzer"

    private fun Double.pow(n: Double): Double {
        return Math.pow(this, n)
    }
}
