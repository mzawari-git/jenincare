package com.ebtikar.skinanalyzer.ai

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.ColorMatrix
import android.graphics.ColorMatrixColorFilter
import android.graphics.Paint
import android.graphics.Canvas
import android.graphics.Rect
import dagger.hilt.android.qualifiers.ApplicationContext
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.google.mlkit.vision.common.InputImage
import com.google.mlkit.vision.face.FaceDetection
import com.google.mlkit.vision.face.FaceDetector
import com.google.mlkit.vision.face.FaceDetectorOptions
import java.io.File
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class OpenCVSkinAnalyzer @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private val faceDetector: FaceDetector by lazy {
        val options = FaceDetectorOptions.Builder()
            .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_FAST)
            .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_ALL)
            .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_NONE)
            .setContourMode(FaceDetectorOptions.CONTOUR_MODE_NONE)
            .build()
        FaceDetection.getClient(options)
    }

    suspend fun analyze(
        frames: Map<LightSpectrum, File>,
        whiteFile: File?
    ): Map<SkinMetric.Type, SkinMetric> {
        val whiteBitmap = whiteFile?.let {
            if (it.exists()) CVUtils.decodeSampled(it) else null
        }

        var faceRect: Rect? = null
        var whiteBrightness = 128f

        if (whiteBitmap != null) {
            faceRect = detectFace(whiteBitmap)
            if (faceRect == null) {
                val enhanced = enhanceBrightness(whiteBitmap)
                if (enhanced != null) {
                    faceRect = detectFace(enhanced)
                    enhanced.recycle()
                }
            }
            if (faceRect == null) {
                faceRect = CVUtils.findLargestSkinRegion(whiteBitmap)
                Timber.w("No ML face detected, using HSV skin region: ${faceRect}")
            }
            if (faceRect != null) {
                Timber.i("Face region: ${faceRect}")
            } else {
                Timber.w("No face or skin region found, falling back to full image analysis")
            }
            whiteBrightness = CVUtils.computePixelStats(whiteBitmap).brightness
        }

        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        for ((spectrum, file) in frames) {
            if (!file.exists()) continue
            val bitmap = try { CVUtils.decodeSampled(file) } catch (e: Exception) { Timber.e(e, "decode failed for ${file.name}"); null } ?: continue
            try {
                val stats = CVUtils.computePixelStats(bitmap)

                val scoreMap = analyzeSpectrum(spectrum, bitmap, faceRect, null, stats, whiteBrightness)
                for ((type, score) in scoreMap) {
                val severity = when {
                    score >= 70f -> MetricSeverity.EXCELLENT
                    score >= 55f -> MetricSeverity.GOOD
                    score >= 35f -> MetricSeverity.FAIR
                    score >= 20f -> MetricSeverity.POOR
                    else -> MetricSeverity.CRITICAL
                }
                    metrics[type] = SkinMetric(
                        type = type,
                        score = score,
                        severity = severity,
                        details = "Analyzed via ${spectrum.displayName}"
                    )
                }
            } catch (e: Exception) {
                Timber.e(e, "OpenCV analysis: spectrum ${spectrum.name} failed")
            }
            bitmap.recycle()
        }

        whiteBitmap?.recycle()

        return metrics
    }

    private suspend fun detectFace(bitmap: Bitmap): Rect? {
        return withContext(Dispatchers.IO) {
            try {
                val image = InputImage.fromBitmap(bitmap, 0)
                val tasks = com.google.android.gms.tasks.Tasks.await(
                    faceDetector.process(image), 5000, java.util.concurrent.TimeUnit.MILLISECONDS
                )
                tasks.firstOrNull()?.boundingBox
            } catch (e: Exception) {
                Timber.w(e, "Face detection failed")
                null
            }
        }
    }

    private fun enhanceBrightness(bitmap: Bitmap): Bitmap? {
        return try {
            val out = Bitmap.createBitmap(bitmap.width, bitmap.height, bitmap.config ?: Bitmap.Config.ARGB_8888)
            val canvas = Canvas(out)
            val paint = Paint().apply {
                colorFilter = ColorMatrixColorFilter(
                    ColorMatrix(floatArrayOf(
                        1.4f, 0f, 0f, 0f, 30f,
                        0f, 1.4f, 0f, 0f, 30f,
                        0f, 0f, 1.4f, 0f, 30f,
                        0f, 0f, 0f, 1f, 0f
                    ))
                )
            }
            canvas.drawBitmap(bitmap, 0f, 0f, paint)
            out
        } catch (e: Exception) {
            Timber.w(e, "Brightness enhancement failed")
            null
        }
    }

    private fun analyzeSpectrum(
        spectrum: LightSpectrum,
        bitmap: Bitmap,
        faceRect: Rect?,
        skinMask: Bitmap?,
        stats: CVUtils.PixelStats,
        whiteBrightness: Float
    ): Map<SkinMetric.Type, Float> {
        val cropBitmap = faceRect?.let { CVUtils.extractFaceRegion(bitmap, it) }
        val analyzeBitmap = cropBitmap ?: bitmap

        val result = when (spectrum) {
            LightSpectrum.WHITE -> {
                mapOf(
                    SkinMetric.Type.TEXTURE to analyzeCheekTexture(analyzeBitmap),
                    SkinMetric.Type.PORES to analyzePores(analyzeBitmap),
                    SkinMetric.Type.SKIN_TONE to analyzeSkinTone(analyzeBitmap)
                )
            }
            LightSpectrum.UV365 -> {
                val spots = CVUtils.adaptiveThresholdSpots(analyzeBitmap, 15, 8)
                val morphGrad = CVUtils.morphologicalGradient(analyzeBitmap, 5)
                val pigHetero = CVUtils.pigmentationHeterogeneity(analyzeBitmap)
                val uvSpots = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 50f, 5f)
                val pigmentation = CVUtils.calibratedScore(stats.contrast * 0.5f + pigHetero * 0.5f, 30f, 3f)
                mapOf(
                    SkinMetric.Type.UV_SPOTS to uvSpots,
                    SkinMetric.Type.PIGMENTATION to pigmentation
                )
            }
            LightSpectrum.POL_P -> {
                val rednessRatio = CVUtils.hsvRednessIndex(analyzeBitmap)
                val vascularComplexity = CVUtils.vascularPatternComplexity(analyzeBitmap)
                val inflammatory = CVUtils.inflammatoryMarkerDetection(analyzeBitmap)
                mapOf(
                    SkinMetric.Type.VASCULAR to CVUtils.calibratedScore(rednessRatio * 0.5f + vascularComplexity * 0.3f + inflammatory * 0.2f, 0.50f, 0.01f),
                    SkinMetric.Type.SENSITIVITY to CVUtils.calibratedScore(rednessRatio * 0.6f + inflammatory * 0.4f, 0.40f, 0.01f),
                    SkinMetric.Type.ROSACEA to CVUtils.calibratedScore((rednessRatio + vascularComplexity) / 2f, 0.35f, 0.005f)
                )
            }
            LightSpectrum.POL_N -> {
                mapOf(
                    SkinMetric.Type.WRINKLES to analyzeWrinkles(analyzeBitmap)
                )
            }
            LightSpectrum.WOODS -> {
                val relativeBright = if (whiteBrightness > 10f) stats.brightness / whiteBrightness else 0.5f
                val melasmaSpots = CVUtils.adaptiveThresholdSpots(analyzeBitmap)
                val skinBarrier = CVUtils.skinBarrierEstimate(analyzeBitmap)
                val pigHetero = CVUtils.pigmentationHeterogeneity(analyzeBitmap)
                mapOf(
                    SkinMetric.Type.MOISTURE to CVUtils.calibratedScoreInverted(relativeBright * 0.6f + skinBarrier * 0.4f, 0.05f, 0.90f),
                    SkinMetric.Type.MELASMA to CVUtils.calibratedScore(melasmaSpots * 0.6f + pigHetero * 0.4f, 0.12f, 0.003f)
                )
            }
            LightSpectrum.BLUE -> {
                val spots = CVUtils.adaptiveThresholdSpots(analyzeBitmap)
                val (sebumDist, sebumUniformity) = CVUtils.sebumDistributionAnalysis(analyzeBitmap)
                val morphGrad = CVUtils.morphologicalGradient(analyzeBitmap, 5)
                val sebumBlue = CVUtils.calibratedScoreInverted(stats.meanB / 255f * 0.5f + sebumDist * 0.3f + morphGrad / 100f * 0.2f, 0.2f, 0.5f)
                mapOf(
                    SkinMetric.Type.SEBUM to sebumBlue,
                    SkinMetric.Type.ACNE to CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.15f, 0.003f),
                    SkinMetric.Type.BLACKHEADS to CVUtils.calibratedScore(spots * 0.5f + (1f - sebumUniformity / 50f) * 0.3f + morphGrad / 100f * 0.2f, 0.12f, 0.005f)
                )
            }
            LightSpectrum.RED -> {
                val redness = CVUtils.hsvRednessIndex(analyzeBitmap)
                val vascularComplexity = CVUtils.vascularPatternComplexity(analyzeBitmap)
                mapOf(
                    SkinMetric.Type.VASCULAR to CVUtils.calibratedScore(redness * 0.6f + vascularComplexity * 0.4f, 0.25f, 0.02f)
                )
            }
            LightSpectrum.BROWN -> {
                val spots = CVUtils.adaptiveThresholdSpots(analyzeBitmap)
                val texture = CVUtils.localBinaryPattern(analyzeBitmap, 3)
                val morphGrad = CVUtils.morphologicalGradient(analyzeBitmap, 3)
                mapOf(
                    SkinMetric.Type.DARK_CIRCLES to CVUtils.calibratedScore(spots * 0.5f + texture * 0.3f + morphGrad / 100f * 0.2f, 0.12f, 0.005f)
                )
            }
            else -> emptyMap()
        }
        if (cropBitmap != null) cropBitmap.recycle()
        return result
    }

    private fun analyzeCheekTexture(bitmap: Bitmap): Float {
        return try {
            val cheekLeft = CVUtils.extractRegion(bitmap, 0f, 0.2f, 0.35f, 0.65f)
            val cheekRight = CVUtils.extractRegion(bitmap, 0.65f, 0.2f, 1f, 0.65f)

            val v1 = if (cheekLeft != null) CVUtils.laplacianVariance(cheekLeft) else 0f
            val v2 = if (cheekRight != null) CVUtils.laplacianVariance(cheekRight) else 0f
            val gabor1 = if (cheekLeft != null) CVUtils.gaborTextureEnergy(cheekLeft) else 0f
            val gabor2 = if (cheekRight != null) CVUtils.gaborTextureEnergy(cheekRight) else 0f
            val lbp1 = if (cheekLeft != null) CVUtils.localBinaryPattern(cheekLeft, 2) else 0f
            val lbp2 = if (cheekRight != null) CVUtils.localBinaryPattern(cheekRight, 2) else 0f
            cheekLeft?.recycle(); cheekRight?.recycle()

            val avgLap = (v1 + v2) / 2f
            val avgGabor = (gabor1 + gabor2) / 2f
            val avgLbp = (lbp1 + lbp2) / 2f
            val combined = avgLap * 0.3f + avgGabor * 0.4f + avgLbp * 0.3f
            CVUtils.calibratedScore(combined, 65f, 0f)
        } catch (e: Exception) {
            Timber.w(e, "analyzeCheekTexture failed")
            50f
        }
    }

    private fun analyzeWrinkles(bitmap: Bitmap): Float {
        return try {
            val forehead = CVUtils.extractRegion(bitmap, 0.15f, 0f, 0.85f, 0.25f)
            val leftCheek = CVUtils.extractRegion(bitmap, 0f, 0.2f, 0.4f, 0.6f)
            val rightCheek = CVUtils.extractRegion(bitmap, 0.6f, 0.2f, 1f, 0.6f)

            val regions = listOfNotNull(forehead, leftCheek, rightCheek).filter { it.width >= 10 && it.height >= 10 }

            val edgeRatio = if (regions.isNotEmpty()) {
                regions.map { CVUtils.cannyEdgeRatio(it) }.average().toFloat()
            } else 0f

            val wrinkleDepth = if (regions.isNotEmpty()) {
                regions.map { CVUtils.wrinkleDepthEstimate(it) }.average().toFloat()
            } else 0f

            val edgeHist = if (regions.isNotEmpty()) {
                regions.map { CVUtils.edgeDirectionHistogram(it) }.average().toFloat()
            } else 0f

            regions.forEach { it.recycle() }

            val combined = edgeRatio * 0.4f + wrinkleDepth * 0.3f + edgeHist * 0.3f
            CVUtils.calibratedScore(combined, 0.40f, 0.005f)
        } catch (e: Exception) {
            Timber.w(e, "analyzeWrinkles failed")
            50f
        }
    }

    private fun analyzePores(bitmap: Bitmap): Float {
        return try {
            val tZone = CVUtils.extractRegion(bitmap, 0.2f, 0f, 0.8f, 0.45f)
            val lapVar = if (tZone != null) {
                val v = CVUtils.laplacianVariance(tZone)
                tZone.recycle()
                v
            } else 10f
            val poreDensity = CVUtils.poreDensityEstimate(tZone ?: bitmap)
            val combined = lapVar * 0.5f + poreDensity * 0.5f
            CVUtils.calibratedScore(combined, 35f, 0f)
        } catch (e: Exception) {
            Timber.w(e, "analyzePores failed")
            50f
        }
    }

    private fun analyzeSkinTone(bitmap: Bitmap): Float {
        val uniformity = CVUtils.labColorUniformity(bitmap)
        val (varA, varB) = CVUtils.colorVarianceAnalysis(bitmap)
        val histEntropy = CVUtils.colorHistogramAnalysis(bitmap)
        val combined = uniformity + (varA + varB) / 2f + histEntropy / 10f
        return CVUtils.calibratedScore(combined, 20f, 0f)
    }
}
