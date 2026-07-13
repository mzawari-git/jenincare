package com.ebtikar.skinanalyzer.data.repository

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import androidx.core.graphics.blue
import androidx.core.graphics.green
import androidx.core.graphics.red
import com.ebtikar.skinanalyzer.ai.FeatureExtractor
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.data.local.SkinReportDao
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.remote.CloudUploadService
import com.ebtikar.skinanalyzer.data.remote.MockAnalysisEngine
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinProfile
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton
import kotlin.math.sqrt

@Singleton
class SkinAnalysisRepositoryImpl @Inject constructor(
    @ApplicationContext private val context: Context,
    private val capturePipeline: FrameCapturePipeline,
    private val providerManager: AnalysisProviderManager,
    private val reportDao: SkinReportDao,
    private val mockEngine: MockAnalysisEngine,
    private val cloudUploadService: CloudUploadService,
    private val featureExtractor: FeatureExtractor
) : SkinAnalysisRepository {

    private val json = Json { ignoreUnknownKeys = true; encodeDefaults = true }

    private val _analysisState = MutableStateFlow<AnalysisState>(AnalysisState.Idle)
    override fun getAnalysisState(): StateFlow<AnalysisState> = _analysisState.asStateFlow()

    override suspend fun startAnalysis(
        outputDir: File,
        diagnosisMode: String,
        previewSurface: android.view.Surface?
    ): Result<Map<LightSpectrum, File>> {
        return try {
            _analysisState.value = AnalysisState.Initializing

            val spectra = LightSpectrum.getSpectraForDiagnosisMode(diagnosisMode)
            val total = spectra.size

            val result = capturePipeline.startCaptureSequence(
                outputDir = outputDir,
                spectra = spectra,
                previewSurface = previewSurface,
                onStateChanged = { state ->
                    when (state) {
                        FrameCapturePipeline.CaptureState.WAITING_FOR_FACE -> {
                            _analysisState.value = AnalysisState.WaitingForFace
                        }
                        else -> { /* Other states */ }
                    }
                },
                onProgress = { phase, step, totalSteps ->
                    val progress = if (totalSteps > 0) (step * 100 / totalSteps) else 0
                    _analysisState.value = AnalysisState.Capturing(
                        phase = phase.spectrum,
                        progress = progress,
                        step = step,
                        totalSteps = totalSteps,
                        spectrumDisplayAr = phase.spectrum.displayNameAr
                    )
                }
            )

            if (result.isSuccess) {
                val frames = result.getOrThrow()
                _analysisState.value = AnalysisState.Capturing(LightSpectrum.OFF, 100, total, total, "")
                Result.success(frames)
            } else {
                _analysisState.value = AnalysisState.Error(result.exceptionOrNull()?.message ?: "Capture failed")
                result
            }
        } catch (e: Exception) {
            _analysisState.value = AnalysisState.Error(e.message ?: "Unknown error")
            Result.failure(e)
        }
    }

    override suspend fun analyzeImages(frames: Map<LightSpectrum, File>, mode: String): Result<SkinAnalysisReport> {
        return try {
            val useCloud = mode == Constants.ANALYSIS_CLOUD || mode == Constants.ANALYSIS_AUTO
            val useLocal = mode == Constants.ANALYSIS_LOCAL || mode == Constants.ANALYSIS_AUTO

            if (useCloud) {
                val apiResult = cloudUploadService.uploadAndAnalyze(frames)
                if (apiResult.isSuccess) {
                    _analysisState.value = AnalysisState.Analyzing("Cloud_API")
                    Timber.i("Cloud API analysis successful")
                    return apiResult
                }
                if (mode == Constants.ANALYSIS_CLOUD) {
                    return Result.failure(apiResult.exceptionOrNull() ?: Exception("Cloud analysis failed"))
                }
                Timber.w("Cloud API failed, falling back to local analysis: ${apiResult.exceptionOrNull()?.message}")
            }

            if (useLocal) {
                _analysisState.value = AnalysisState.Analyzing("Local_TFLite")
                val localMetrics = performLocalTFLiteAnalysis(frames)
                if (localMetrics.isNotEmpty()) {
                    val metricsList = SkinMetric.ALL_TYPES.map { type ->
                        localMetrics[type] ?: SkinMetric(type = type, score = 60f, severity = MetricSeverity.FAIR, details = "No spectral data")
                    }
                    val metricsMap = metricsList.associateBy { it.type }
                    val expertTips = mockEngine.generateExpertTips(metricsMap)
                    val products = mockEngine.generateProductRecommendations(metricsMap)
                    val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                    val report = SkinAnalysisReport(
                        providerName = "Local_TFLite_Engine",
                        overallScore = metricsList.map { it.score }.average().toFloat(),
                        metrics = metricsList, executionTimeMs = 1500,
                        aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                        expertTipsAr = expertTips, productRecommendations = products,
                        skinProfile = skinProfile, confidence = 0.88f
                    )
                    return Result.success(report)
                }
                Timber.w("TFLite analysis returned no metrics, falling back to pixel analysis")
            }

            _analysisState.value = AnalysisState.Analyzing("Pixel_Analysis_Engine")

            val pixelMetrics = performPixelAnalysis(frames)

            if (pixelMetrics.isEmpty()) {
                val mockResult = mockEngine.generateMockResult("Fallback_Mock")
                val metrics = SkinMetric.ALL_TYPES.map { type ->
                    mockResult.metrics[type] ?: SkinMetric(type = type, score = 0f, severity = MetricSeverity.CRITICAL, details = "No data")
                }
                val metricsMap = metrics.associateBy { it.type }
                val expertTips = mockEngine.generateExpertTips(metricsMap)
                val products = mockEngine.generateProductRecommendations(metricsMap)
                val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                val report = SkinAnalysisReport(
                    providerName = mockResult.providerName,
                    overallScore = metrics.map { it.score }.average().toFloat(),
                    metrics = metrics, executionTimeMs = mockResult.executionTimeMs,
                    aiAnalysisTextAr = generateAIAnalysisText(metrics, skinProfile),
                    expertTipsAr = expertTips, productRecommendations = products,
                    skinProfile = skinProfile, confidence = mockResult.confidence
                )
                Result.success(report)
            } else {
                val metricsList = SkinMetric.ALL_TYPES.map { type ->
                    pixelMetrics[type] ?: SkinMetric(type = type, score = 60f, severity = MetricSeverity.FAIR, details = "No spectral data")
                }
                val metricsMap = metricsList.associateBy { it.type }
                val expertTips = mockEngine.generateExpertTips(metricsMap)
                val products = mockEngine.generateProductRecommendations(metricsMap)
                val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                val report = SkinAnalysisReport(
                    providerName = "Pixel_Analysis_Engine",
                    overallScore = metricsList.map { it.score }.average().toFloat(),
                    metrics = metricsList, executionTimeMs = 800,
                    aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                    expertTipsAr = expertTips, productRecommendations = products,
                    skinProfile = skinProfile, confidence = 0.82f
                )
                Result.success(report)
            }
        } catch (e: Exception) {
            _analysisState.value = AnalysisState.Error(e.message ?: "Analysis failed")
            Result.failure(e)
        }
    }

    private fun generateAIAnalysisText(metrics: List<SkinMetric>, profile: SkinProfile): String {
        val score = metrics.map { it.score }.average().toFloat()
        val excellent = metrics.count { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }
        val needsAttention = metrics.count { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }
        val topConcern = metrics.minByOrNull { it.score }

        val sb = StringBuilder()
        sb.append("تحليل شامل للبشرة — ")
        sb.append("نوع البشرة: ${profile.skinTypeAr}")
        if (profile.ageEstimate > 0) sb.append("، العمر التقديري: ${profile.ageEstimate} سنة")
        sb.append("。\n\n")

        sb.append("النتيجة الإجمالية: ${"%.1f".format(score)}/100 — ")
        sb.append(when {
            score >= 85f -> "حالة البشرة ممتازة بشكل عام"
            score >= 70f -> "حالة البشرة جيدة مع بعض المؤشرات التي تحتاج متابعة"
            score >= 55f -> "حالة البشرة متوسطة — هناك مجالات للتحسين"
            else -> "البشرة تحتاج عناية مركزة في عدة مؤشرات"
        })
        sb.append("。\n\n")

        sb.append("المؤشرات الإيجابية: $excellent من ${metrics.size} مؤشر في الحالة الجيدة أو الممتازة")
        if (needsAttention > 0) {
            sb.append("。\n")
            sb.append("المؤشرات التي تحتاج اهتمام: $needsAttention مؤشر")
            topConcern?.let {
                sb.append("، أكثرها إلحاحاً: ${getArabicName(it.type)}")
            }
        }

        if (profile.primaryConcernsAr.isNotEmpty()) {
            sb.append("。\n\n")
            sb.append("أبرز المخاوف: ${profile.primaryConcernsAr.joinToString("، ")}")
        }

        return sb.toString()
    }

    private fun performLocalTFLiteAnalysis(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        for ((spectrum, file) in frames) {
            if (!file.exists()) continue
            val bitmap = BitmapFactory.decodeFile(file.absolutePath) ?: continue
            try {
                val features = featureExtractor.extractFeatures(bitmap, spectrum.name)
                for ((type, score) in features) {
                    val severity = when {
                        score >= 85f -> MetricSeverity.EXCELLENT
                        score >= 70f -> MetricSeverity.GOOD
                        score >= 55f -> MetricSeverity.FAIR
                        score >= 35f -> MetricSeverity.POOR
                        else -> MetricSeverity.CRITICAL
                    }
                    metrics[type] = SkinMetric(
                        type = type, score = score, severity = severity,
                        details = "Analyzed via ${spectrum.displayName}"
                    )
                }
            } catch (e: Exception) {
                Timber.w(e, "TFLite analysis failed for ${spectrum.name}")
            } finally {
                bitmap.recycle()
            }
        }
        return metrics
    }

    private fun performPixelAnalysis(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        val startTime = System.currentTimeMillis()

        for ((spectrum, file) in frames) {
            if (!file.exists()) continue

            val bitmap = BitmapFactory.decodeFile(file.absolutePath) ?: continue
            val stats = computePixelStats(bitmap)
            bitmap.recycle()

            val scoreMap = mapSpectrumToMetrics(spectrum, stats)
            for ((type, score) in scoreMap) {
                val severity = when {
                    score >= 85f -> MetricSeverity.EXCELLENT
                    score >= 70f -> MetricSeverity.GOOD
                    score >= 55f -> MetricSeverity.FAIR
                    score >= 35f -> MetricSeverity.POOR
                    else -> MetricSeverity.CRITICAL
                }
                metrics[type] = SkinMetric(
                    type = type,
                    score = score,
                    severity = severity,
                    details = "Analyzed via ${spectrum.displayName}"
                )
            }
        }

        Timber.i("Pixel analysis complete: ${metrics.size} metrics in ${System.currentTimeMillis() - startTime}ms")
        return metrics
    }

    private data class PixelStats(
        val meanR: Float, val meanG: Float, val meanB: Float,
        val brightness: Float, val variance: Float, val contrast: Float
    )

    private fun computePixelStats(bitmap: Bitmap): PixelStats {
        val sampleSize = 8
        val w = bitmap.width / sampleSize
        val h = bitmap.height / sampleSize
        var sumR = 0L
        var sumG = 0L
        var sumB = 0L
        var sumBright = 0L
        var count = 0
        val brightnessValues = mutableListOf<Float>()

        for (y in 0 until h) {
            for (x in 0 until w) {
                val pixel = bitmap.getPixel(x * sampleSize, y * sampleSize)
                val r = pixel.red
                val g = pixel.green
                val b = pixel.blue
                val bright = (r * 0.299f + g * 0.587f + b * 0.114f)
                sumR += r
                sumG += g
                sumB += b
                sumBright += bright.toLong()
                brightnessValues.add(bright)
                count++
            }
        }

        if (count == 0) return PixelStats(0f, 0f, 0f, 0f, 0f, 0f)

        val meanR = (sumR.toFloat() / count)
        val meanG = (sumG.toFloat() / count)
        val meanB = (sumB.toFloat() / count)
        val meanBright = (sumBright.toFloat() / count)

        val variance = brightnessValues.map { (it - meanBright) * (it - meanBright) }.average().toFloat()
        val contrast = sqrt(variance) / 255f * 100f

        return PixelStats(
            meanR = meanR, meanG = meanG, meanB = meanB,
            brightness = meanBright / 255f * 100f,
            variance = variance / 100f,
            contrast = contrast
        )
    }

    private fun mapSpectrumToMetrics(spectrum: LightSpectrum, stats: PixelStats): Map<SkinMetric.Type, Float> {
        return when (spectrum) {
            LightSpectrum.WHITE -> mapOf(
                SkinMetric.Type.TEXTURE to (stats.contrast * 1.2f).coerceIn(30f, 95f),
                SkinMetric.Type.SKIN_TONE to ((255f - stats.variance) / 3f).coerceIn(30f, 95f),
                SkinMetric.Type.PORES to (stats.brightness * 0.8f + 20f).coerceIn(30f, 90f),
                SkinMetric.Type.WRINKLES to (stats.contrast * 0.9f + 25f).coerceIn(30f, 90f)
            )
            LightSpectrum.UV365 -> mapOf(
                SkinMetric.Type.UV_SPOTS to (stats.variance * 2.5f + 30f).coerceIn(25f, 90f),
                SkinMetric.Type.PIGMENTATION to (stats.contrast * 1.5f).coerceIn(25f, 90f),
                SkinMetric.Type.PORPHYRINS to ((255f - stats.brightness) * 0.6f + 20f).coerceIn(20f, 90f)
            )
            LightSpectrum.POL_P -> mapOf(
                SkinMetric.Type.VASCULAR to (stats.meanR * 0.5f + 30f).coerceIn(30f, 92f),
                SkinMetric.Type.SENSITIVITY to ((255f - stats.meanR) * 0.4f + 20f).coerceIn(25f, 90f),
                SkinMetric.Type.ROSACEA to (stats.meanR * 0.6f + 20f).coerceIn(20f, 90f)
            )
            LightSpectrum.POL_N -> mapOf(
                SkinMetric.Type.SEBUM to (stats.brightness * 0.7f + 25f).coerceIn(25f, 88f),
                SkinMetric.Type.BLACKHEADS to (stats.variance * 1.8f + 30f).coerceIn(20f, 88f),
                SkinMetric.Type.TEXTURE to (stats.contrast * 1.3f + 20f).coerceIn(25f, 92f)
            )
            LightSpectrum.WOODS -> mapOf(
                SkinMetric.Type.ACNE to (stats.contrast * 1.3f + 25f).coerceIn(20f, 90f),
                SkinMetric.Type.MOISTURE to (stats.brightness * 0.9f).coerceIn(30f, 95f),
                SkinMetric.Type.MELASMA to ((255f - stats.brightness) * 0.7f + 15f).coerceIn(20f, 88f)
            )
            LightSpectrum.BLUE -> mapOf(
                SkinMetric.Type.SEBUM to (stats.meanB * 0.5f + 25f).coerceIn(20f, 90f),
                SkinMetric.Type.ACNE to ((255f - stats.brightness) * 0.5f + 25f).coerceIn(20f, 85f)
            )
            LightSpectrum.RED -> mapOf(
                SkinMetric.Type.COLLAGEN to ((255f - stats.meanR) * 0.5f + 30f).coerceIn(30f, 95f),
                SkinMetric.Type.WRINKLES to (stats.contrast * 1.1f + 25f).coerceIn(30f, 90f)
            )
            LightSpectrum.BROWN -> mapOf(
                SkinMetric.Type.PIGMENTATION to (stats.contrast * 1.4f).coerceIn(25f, 88f),
                SkinMetric.Type.DARK_CIRCLES to (stats.brightness * 0.8f + 15f).coerceIn(20f, 85f)
            )
            else -> mapOf(
                SkinMetric.Type.MOISTURE to (stats.brightness * 0.8f + 20f).coerceIn(30f, 92f)
            )
        }
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
        SkinMetric.Type.COLLAGEN -> "الكولاجين"
        SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.PORPHYRINS -> "البورفيرين"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }

    override suspend fun saveReport(report: SkinAnalysisReport): Result<String> {
        return try {
            _analysisState.value = AnalysisState.Saving

            val metricsJson = json.encodeToString(report.metrics)
            val tipsJson = json.encodeToString(report.expertTipsAr)
            val productsJson = json.encodeToString(report.productRecommendations)
            val profileJson = json.encodeToString(report.skinProfile)

            val entity = SkinReportEntity(
                id = report.id,
                timestamp = report.timestamp,
                providerName = report.providerName,
                overallScore = report.overallScore,
                executionTimeMs = report.executionTimeMs,
                metricsJson = metricsJson,
                deviceModel = report.deviceModel,
                notes = report.notes,
                aiAnalysisText = report.aiAnalysisTextAr,
                expertTipsJson = tipsJson,
                productsJson = productsJson,
                skinProfileJson = profileJson,
                confidence = report.confidence,
                scanId = report.scanId
            )

            reportDao.insertReport(entity)
            _analysisState.value = AnalysisState.Complete(report.id)

            Timber.i("Report saved: ${report.id}")
            Result.success(report.id)
        } catch (e: Exception) {
            Timber.e(e, "Failed to save report")
            Result.failure(e)
        }
    }

    override suspend fun getReport(id: String): SkinReportEntity? {
        return reportDao.getReportById(id)
    }

    override fun getAllReports(): Flow<List<SkinReportEntity>> {
        return reportDao.getAllReports()
    }

    override fun getRecentReports(limit: Int): Flow<List<SkinReportEntity>> {
        return reportDao.getRecentReports(limit)
    }

    override suspend fun deleteReport(id: String) {
        reportDao.deleteReport(id)
        val captureDir = File(context.filesDir, "captures/$id")
        if (captureDir.exists()) captureDir.deleteRecursively()
    }

    override suspend fun getReportCount(): Int {
        return reportDao.getReportCount()
    }

    override fun getCapturedImages(reportId: String): Map<LightSpectrum, File> {
        val captureDir = File(context.filesDir, "captures/$reportId")
        if (!captureDir.exists()) return emptyMap()

        val images = mutableMapOf<LightSpectrum, File>()
        for (spectrum in LightSpectrum.entries) {
            if (spectrum == LightSpectrum.OFF || spectrum == LightSpectrum.ALL) continue
            val file = File(captureDir, "frame_${spectrum.name}.jpg")
            if (file.exists()) images[spectrum] = file
        }
        return images
    }
}
