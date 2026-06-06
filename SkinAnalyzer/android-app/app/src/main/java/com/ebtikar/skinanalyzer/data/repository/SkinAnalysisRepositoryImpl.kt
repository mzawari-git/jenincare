package com.ebtikar.skinanalyzer.data.repository

import android.content.Context
import com.ebtikar.skinanalyzer.BuildConfig
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.data.local.SkinReportDao
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.remote.MockAnalysisEngine
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
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

@Singleton
class SkinAnalysisRepositoryImpl @Inject constructor(
    @ApplicationContext private val context: Context,
    private val capturePipeline: FrameCapturePipeline,
    private val providerManager: AnalysisProviderManager,
    private val reportDao: SkinReportDao,
    private val mockEngine: MockAnalysisEngine
) : SkinAnalysisRepository {

    private val json = Json { ignoreUnknownKeys = true; encodeDefaults = true }

    private val _analysisState = MutableStateFlow<AnalysisState>(AnalysisState.Idle)
    override fun getAnalysisState(): StateFlow<AnalysisState> = _analysisState.asStateFlow()

    override suspend fun startAnalysis(outputDir: File): Result<Map<LightSpectrum, File>> {
        return try {
            _analysisState.value = AnalysisState.Initializing

            val result = capturePipeline.startCaptureSequence(outputDir)

            if (result.isSuccess) {
                val frames = result.getOrThrow()
                _analysisState.value = AnalysisState.Capturing(LightSpectrum.OFF, 100)
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

    override suspend fun analyzeImages(frames: Map<LightSpectrum, File>): Result<SkinAnalysisReport> {
        return try {
            val provider = providerManager.resolveActiveProvider()
            val providerName = provider?.getName() ?: "Mock_Engine"

            _analysisState.value = AnalysisState.Analyzing(providerName)

            val isMock = BuildConfig.API_KEY == "mock_key_for_dev"

            val result = if (provider != null && !isMock) {
                val analysisResult = provider.analyze(frames.mapKeys { it.key.name })
                if (analysisResult.isSuccess) {
                    val metrics = SkinMetric.ALL_TYPES.map { type ->
                        analysisResult.metrics[type] ?: SkinMetric(
                            type = type,
                            score = 0f,
                            severity = MetricSeverity.CRITICAL,
                            details = "No data"
                        )
                    }
                    val metricsMap = metrics.associateBy { it.type }
                    val expertTips = mockEngine.generateExpertTips(metricsMap)
                    val products = mockEngine.generateProductRecommendations(metricsMap)
                    val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                    SkinAnalysisReport(
                        providerName = analysisResult.providerName,
                        overallScore = metrics.map { it.score }.average().toFloat(),
                        metrics = metrics,
                        executionTimeMs = analysisResult.executionTimeMs,
                        aiAnalysisTextAr = generateAIAnalysisText(metrics, skinProfile),
                        expertTipsAr = expertTips,
                        productRecommendations = products,
                        skinProfile = skinProfile,
                        confidence = analysisResult.confidence,
                        scanId = analysisResult.scanId ?: ""
                    )
                } else {
                    return Result.failure(RuntimeException(analysisResult.warnings.joinToString()))
                }
            } else {
                val mockResult = mockEngine.generateMockResult(providerName)
                val metrics = SkinMetric.ALL_TYPES.map { type ->
                    mockResult.metrics[type] ?: SkinMetric(
                        type = type,
                        score = 0f,
                        severity = MetricSeverity.CRITICAL,
                        details = "No data"
                    )
                }
                val metricsMap = metrics.associateBy { it.type }
                val expertTips = mockEngine.generateExpertTips(metricsMap)
                val products = mockEngine.generateProductRecommendations(metricsMap)
                val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                SkinAnalysisReport(
                    providerName = mockResult.providerName,
                    overallScore = metrics.map { it.score }.average().toFloat(),
                    metrics = metrics,
                    executionTimeMs = mockResult.executionTimeMs,
                    aiAnalysisTextAr = generateAIAnalysisText(metrics, skinProfile),
                    expertTipsAr = expertTips,
                    productRecommendations = products,
                    skinProfile = skinProfile,
                    confidence = mockResult.confidence
                )
            }

            Result.success(result)
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
            if (spectrum == LightSpectrum.OFF) continue
            val file = File(captureDir, "frame_${spectrum.name}.jpg")
            if (file.exists()) images[spectrum] = file
        }
        return images
    }
}
