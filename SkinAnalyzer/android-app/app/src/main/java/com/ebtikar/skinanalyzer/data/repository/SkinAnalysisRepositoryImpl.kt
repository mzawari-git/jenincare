package com.ebtikar.skinanalyzer.data.repository

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import com.ebtikar.skinanalyzer.ai.AdvancedSkinAnalyzer
import com.ebtikar.skinanalyzer.ai.CVUtils
import com.ebtikar.skinanalyzer.ai.FaceLandmarkDetector
import com.ebtikar.skinanalyzer.ai.FeatureExtractor
import com.ebtikar.skinanalyzer.ai.LocalTFLiteProvider
import com.ebtikar.skinanalyzer.ai.OpenCVSkinAnalyzer
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.data.knowledge.MetricKnowledge
import com.ebtikar.skinanalyzer.data.knowledge.SkinKnowledgeRepository
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
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
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
    private val mockEngine: MockAnalysisEngine,
    private val cloudUploadService: CloudUploadService,
    private val featureExtractor: FeatureExtractor,
    private val openCVSkinAnalyzer: OpenCVSkinAnalyzer,
    private val advancedSkinAnalyzer: AdvancedSkinAnalyzer,
    private val localTFLiteProvider: LocalTFLiteProvider,
    private val knowledgeRepository: SkinKnowledgeRepository,
    private val faceLandmarkDetector: FaceLandmarkDetector
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

            try {
                withContext(Dispatchers.IO) {
                    knowledgeRepository.refreshFromRemote()
                }
            } catch (_: Exception) { }

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
        } catch (e: Throwable) {
            Timber.e(e, "startAnalysis failed")
            _analysisState.value = AnalysisState.Error(e.message ?: "Unknown error")
            Result.failure(e)
        }
    }

    override suspend fun analyzeImages(frames: Map<LightSpectrum, File>, mode: String): Result<SkinAnalysisReport> {
        return try {
            val analysisFrames = frames.mapValues { (_, file) ->
                val rawFile = File(file.parentFile, file.nameWithoutExtension + "_raw.jpg")
                if (rawFile.exists()) rawFile else file
            }

            val faceCheckOrder = listOf(LightSpectrum.WHITE, LightSpectrum.POL_N, LightSpectrum.POL_P)
            var faceDetected = false
            for (spectrum in faceCheckOrder) {
                val checkFile = analysisFrames[spectrum] ?: continue
                if (!checkFile.exists()) continue
                val checkBitmap = CVUtils.decodeSampled(checkFile, 640) ?: continue
                try {
                    val faces = faceLandmarkDetector.detectFaces(checkBitmap)
                    if (faces.isNotEmpty()) {
                        Timber.i("Face confirmed in $spectrum frame: ${faces.size} face(s)")
                        faceDetected = true
                        break
                    }
                    Timber.d("No face in $spectrum frame, trying next spectrum")
                } finally {
                    if (!checkBitmap.isRecycled) checkBitmap.recycle()
                }
            }
            if (!faceDetected) {
                Timber.w("Post-capture face check: no face in RGB/polarized frames — proceeding anyway (face was validated during capture)")
            }

            val useCloud = mode == Constants.ANALYSIS_CLOUD || mode == Constants.ANALYSIS_AUTO
            val useLocal = mode == Constants.ANALYSIS_LOCAL || mode == Constants.ANALYSIS_AUTO

            if (useCloud) {
                val apiResult = cloudUploadService.uploadAndAnalyze(analysisFrames)
                if (apiResult.isSuccess) {
                    _analysisState.value = AnalysisState.Analyzing("Cloud_API")
                    val cloudReport = apiResult.getOrNull()
                    Timber.i("Engine used: Cloud_API — providerName=${cloudReport?.providerName}, metrics=${cloudReport?.metrics?.size}, confidence=${cloudReport?.confidence}")
                    return apiResult
                }
                if (mode == Constants.ANALYSIS_CLOUD) {
                    return Result.failure(apiResult.exceptionOrNull() ?: Exception("Cloud analysis failed"))
                }
                Timber.w("Cloud API failed, falling back to local analysis: ${apiResult.exceptionOrNull()?.message}")
            }

            if (useLocal) {
                _analysisState.value = AnalysisState.Analyzing("Local_TFLite")
                val localMetrics = performLocalTFLiteAnalysis(analysisFrames)
                if (localMetrics.isNotEmpty()) {
                    val crossValidated = localMetrics.toMutableMap()
                    validateCrossSpectrum(crossValidated)
                    val metricsList = SkinMetric.ALL_TYPES.mapNotNull { type ->
                        crossValidated[type]
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
                    Timber.i("Engine used: Local_TFLite_Engine — metrics=${metricsList.size}, overallScore=${report.overallScore}")
                    return Result.success(report)
                }
                Timber.w("TFLite analysis returned no metrics, falling back to advanced analysis")
            }

            _analysisState.value = AnalysisState.Analyzing("Advanced_Analysis_Engine")

            Timber.i("analysis: frames.size=${analysisFrames.size}, existingFiles=${analysisFrames.count { it.value.exists() }}")
            analysisFrames.forEach { (s, f) -> Timber.d("  frame ${s.name}: exists=${f.exists()}, size=${f.length()}") }

            val advancedMetrics = try {
                performAdvancedAnalysis(analysisFrames)
            } catch (e: Throwable) {
                Timber.e(e, "Advanced analysis engine failed completely")
                emptyMap()
            }
            Timber.i("Advanced analysis returned ${advancedMetrics.size} metric types: ${advancedMetrics.keys.joinToString { it.name }}")
            if (advancedMetrics.size >= 5) {
                val crossValidated = advancedMetrics.toMutableMap()
                validateCrossSpectrum(crossValidated)
                val metricsList = SkinMetric.ALL_TYPES.mapNotNull { type ->
                    crossValidated[type]
                }
                val metricsMap = metricsList.associateBy { it.type }
                val expertTips = mockEngine.generateExpertTips(metricsMap)
                val products = mockEngine.generateProductRecommendations(metricsMap)
                val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                val report = SkinAnalysisReport(
                    providerName = "Advanced_MediaPipe_Engine",
                    overallScore = metricsList.map { it.score }.average().toFloat(),
                    metrics = metricsList, executionTimeMs = 1200,
                    aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                    expertTipsAr = expertTips, productRecommendations = products,
                    skinProfile = skinProfile, confidence = 0.92f
                )
                Timber.i("Engine used: Advanced_MediaPipe_Engine — metrics=${metricsList.size}, overallScore=${report.overallScore}")
                return Result.success(report)
            }

            _analysisState.value = AnalysisState.Analyzing("CV_Analysis_Engine")

            val whiteFile = analysisFrames.entries.find { it.key == LightSpectrum.WHITE }?.value
            Timber.i("OpenCV analysis: whiteFile=${whiteFile?.name}, exists=${whiteFile?.exists()}")
            val pixelMetrics = try {
                withContext(Dispatchers.Default) {
                    openCVSkinAnalyzer.analyze(analysisFrames, whiteFile)
                }
            } catch (e: Throwable) {
                Timber.e(e, "OpenCV analysis engine failed completely")
                emptyMap()
            }
            Timber.i("OpenCV analysis returned ${pixelMetrics.size} metric types: ${pixelMetrics.keys.joinToString { it.name }}")

            if (pixelMetrics.size >= 5) {
                val crossValidated = pixelMetrics.toMutableMap()
                validateCrossSpectrum(crossValidated)
                val metricsList = SkinMetric.ALL_TYPES.mapNotNull { type ->
                    crossValidated[type]
                }
                val metricsMap = metricsList.associateBy { it.type }
                val expertTips = mockEngine.generateExpertTips(metricsMap)
                val products = mockEngine.generateProductRecommendations(metricsMap)
                val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                val report = SkinAnalysisReport(
                    providerName = "CV_Analysis_Engine",
                    overallScore = metricsList.map { it.score }.average().toFloat(),
                    metrics = metricsList, executionTimeMs = 1200,
                    aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                    expertTipsAr = expertTips, productRecommendations = products,
                    skinProfile = skinProfile, confidence = 0.82f
                )
                Timber.i("Engine used: CV_Analysis_Engine — metrics=${metricsList.size}, overallScore=${report.overallScore}")
                Result.success(report)
            } else {
                Timber.w("OpenCV returned only ${pixelMetrics.size} metrics — falling back to Basic Pixel analysis")
                _analysisState.value = AnalysisState.Analyzing("Basic_Pixel_Engine")
                val basicMetrics = try {
                    performBasicPixelAnalysis(analysisFrames)
                } catch (e: Throwable) {
                    Timber.e(e, "Basic Pixel analysis failed")
                    emptyMap()
                }
                Timber.i("Basic Pixel analysis returned ${basicMetrics.size} metric types")
                if (basicMetrics.isNotEmpty()) {
                    val merged = pixelMetrics.toMutableMap()
                    basicMetrics.forEach { (type, metric) ->
                        if (merged[type] == null || merged[type]!!.score == 0f) {
                            merged[type] = metric
                        }
                    }
                    validateCrossSpectrum(merged)
                    val metricsList = SkinMetric.ALL_TYPES.mapNotNull { type -> merged[type] }
                    val metricsMap = metricsList.associateBy { it.type }
                    val expertTips = mockEngine.generateExpertTips(metricsMap)
                    val products = mockEngine.generateProductRecommendations(metricsMap)
                    val skinProfile = mockEngine.generateSkinProfile(metricsMap)
                    val report = SkinAnalysisReport(
                        providerName = "Basic_Pixel_Engine",
                        overallScore = metricsList.map { it.score }.average().toFloat(),
                        metrics = metricsList, executionTimeMs = 800,
                        aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                        expertTipsAr = expertTips, productRecommendations = products,
                        skinProfile = skinProfile, confidence = 0.75f
                    )
                    Timber.i("Engine used: Basic_Pixel_Engine — metrics=${metricsList.size}, overallScore=${report.overallScore}")
                    Result.success(report)
                } else {
                    Timber.w("All analysis engines failed to produce sufficient metrics")
                    _analysisState.value = AnalysisState.Error("لم يتمكن التحليل من استخراج بيانات كافية. تأكد من وجود الوجه بشكل واضح أمام الكاميرا وأعد المحاولة.")
                    return Result.failure(Exception("Insufficient metrics: OpenCV=${pixelMetrics.size}, BasicPixel=${basicMetrics.size}"))
                }
            }
        } catch (e: Throwable) {
            Timber.e(e, "analyzeImages failed")
            _analysisState.value = AnalysisState.Error(e.message ?: "Analysis failed")
            Result.failure(e)
        }
    }

    private fun validateCrossSpectrum(metrics: MutableMap<SkinMetric.Type, SkinMetric>) {
        val uvSpots = metrics[SkinMetric.Type.UV_SPOTS]?.score
        val pigmentation = metrics[SkinMetric.Type.PIGMENTATION]?.score
        val vascular = metrics[SkinMetric.Type.VASCULAR]?.score
        val sensitivity = metrics[SkinMetric.Type.SENSITIVITY]?.score
        val rosacea = metrics[SkinMetric.Type.ROSACEA]?.score
        val acne = metrics[SkinMetric.Type.ACNE]?.score
        val blackheads = metrics[SkinMetric.Type.BLACKHEADS]?.score
        val sebum = metrics[SkinMetric.Type.SEBUM]?.score
        val moisture = metrics[SkinMetric.Type.MOISTURE]?.score
        val wrinkles = metrics[SkinMetric.Type.WRINKLES]?.score

        if (pigmentation != null && uvSpots != null && pigmentation > uvSpots + 15f && uvSpots < 85f) {
            val adjusted = (uvSpots + pigmentation) / 2f
            metrics[SkinMetric.Type.PIGMENTATION]?.let { m ->
                metrics[SkinMetric.Type.PIGMENTATION] = m.copy(score = adjusted, details = m.details + " | تم تعديله بناءً على تحليل UV")
            }
            metrics[SkinMetric.Type.UV_SPOTS]?.let { m ->
                metrics[SkinMetric.Type.UV_SPOTS] = m.copy(score = adjusted, details = m.details + " | تم تعديله بناءً على تحليل التصبغ")
            }
        }

        if (vascular != null && sensitivity != null && rosacea != null) {
            val polAvg = (vascular + sensitivity + rosacea) / 3f
            if (maxOf(vascular, sensitivity, rosacea) - minOf(vascular, sensitivity, rosacea) > 20f) {
                val adjusted = polAvg
                metrics[SkinMetric.Type.VASCULAR]?.let { m ->
                    metrics[SkinMetric.Type.VASCULAR] = m.copy(score = adjusted, details = m.details + " | معدّل عبر المؤشرات")
                }
                metrics[SkinMetric.Type.SENSITIVITY]?.let { m ->
                    metrics[SkinMetric.Type.SENSITIVITY] = m.copy(score = adjusted, details = m.details + " | معدّل عبر المؤشرات")
                }
                metrics[SkinMetric.Type.ROSACEA]?.let { m ->
                    metrics[SkinMetric.Type.ROSACEA] = m.copy(score = adjusted, details = m.details + " | معدّل عبر المؤشرات")
                }
            }
        }

        if (sebum != null && acne != null && blackheads != null && sebum < 60f && (acne < 80f || blackheads < 80f)) {
            if (acne > 70f && sebum > 30f) {
                metrics[SkinMetric.Type.ACNE]?.let { m ->
                    metrics[SkinMetric.Type.ACNE] = m.copy(score = (acne + (100f - sebum)) / 2f, details = m.details + " | تم تعديله بناءً على الدهون")
                }
                metrics[SkinMetric.Type.SEBUM]?.let { m ->
                    metrics[SkinMetric.Type.SEBUM] = m.copy(score = (sebum + (100f - acne)) / 2f, details = m.details + " | تم تعديله بناءً على حب الشباب")
                }
            }
        }

        if (moisture != null && wrinkles != null && moisture < 60f && wrinkles > 60f) {
            val adjustedWrinkles = (wrinkles + (100f - moisture)) / 2f
            metrics[SkinMetric.Type.WRINKLES]?.let { m ->
                metrics[SkinMetric.Type.WRINKLES] = m.copy(score = adjustedWrinkles, details = m.details + " | تم تعديله بناءً على الرطوبة")
            }
        }
    }

    private fun generateAIAnalysisText(metrics: List<SkinMetric>, profile: SkinProfile): String {
        val score = metrics.map { it.score }.average().toFloat()
        val excellent = metrics.count { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }
        val fair = metrics.count { it.severity == MetricSeverity.FAIR }
        val needsAttention = metrics.count { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }
        val topConcern = metrics.minByOrNull { it.score }
        val knowledge = knowledgeRepository.getCachedKnowledge()

        fun getMetricKnowledge(type: SkinMetric.Type): MetricKnowledge? =
            knowledge.metrics[type.name]

        fun getDescription(type: SkinMetric.Type, severity: MetricSeverity): String {
            val desc = getMetricKnowledge(type)?.descriptions?.get(severity.name)
            if (!desc.isNullOrBlank()) return desc
            return when (type) {
                SkinMetric.Type.MOISTURE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مستوى الرطوبة مثالي"; MetricSeverity.FAIR -> "رطوبة متوسطة"; else -> "جفاف واضح" }
                SkinMetric.Type.PORES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "المسام ضيقة ومنتظمة"; MetricSeverity.FAIR -> "بعض المسام الواسعة"; else -> "مسام واسعة" }
                SkinMetric.Type.SEBUM -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "إفراز دهني متوازن"; MetricSeverity.FAIR -> "زيادة طفيفة"; else -> "إفراز دهني زائد" }
                SkinMetric.Type.WRINKLES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "خطوط دقيقة قليلة"; MetricSeverity.FAIR -> "خطوط تعبير واضحة"; else -> "تجاعيد عميقة" }
                SkinMetric.Type.TEXTURE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "ملمس ناعم ومتجانس"; MetricSeverity.FAIR -> "خشونة خفيفة"; else -> "ملمس خشن" }
                SkinMetric.Type.UV_SPOTS -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد أضرار شمس"; MetricSeverity.FAIR -> "بقع شمس خفيفة"; else -> "أضرار شمس متقدمة" }
                SkinMetric.Type.VASCULAR -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "دورة دموية صحية"; MetricSeverity.FAIR -> "احمرار خفيف"; else -> "احمرار واضح" }
                SkinMetric.Type.PIGMENTATION -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة موحد"; MetricSeverity.FAIR -> "تصبغات خفيفة"; else -> "تصبغات غامقة" }
                SkinMetric.Type.DARK_CIRCLES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "منطقة العين مشرقة"; MetricSeverity.FAIR -> "هالات خفيفة"; else -> "هالات داكنة" }
                SkinMetric.Type.BLACKHEADS -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مسام نظيفة"; MetricSeverity.FAIR -> "رؤوس سوداء خفيفة"; else -> "انتشار واسع" }
                SkinMetric.Type.ACNE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد بثور نشطة"; MetricSeverity.FAIR -> "بثور خفيفة"; else -> "حب شباب نشط" }
                SkinMetric.Type.SKIN_TONE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة متجانس"; MetricSeverity.FAIR -> "اختلافات طفيفة"; else -> "عدم تجانس واضح" }
                SkinMetric.Type.SENSITIVITY -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "بشرة غير حساسة"; MetricSeverity.FAIR -> "حساسية خفيفة"; else -> "بشرة شديدة الحساسية" }
                SkinMetric.Type.ROSACEA -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات وردية"; MetricSeverity.FAIR -> "احمرار خفيف"; else -> "علامات وردية واضحة" }
                SkinMetric.Type.MELASMA -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات كلف"; MetricSeverity.FAIR -> "تصبغ خفيف"; else -> "كلف عميق" }
            }
        }

        fun getCauses(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.causesAr?.filter { it.isNotBlank() } ?: emptyList()

        fun getTips(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.tipsAr?.filter { it.isNotBlank() } ?: emptyList()

        fun getIngredients(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.ingredientsAr?.filter { it.isNotBlank() } ?: emptyList()

        val sb = StringBuilder()
        sb.append("تحليل شامل للبشرة — ")
        sb.append("نوع البشرة: ${profile.skinTypeAr}")
        sb.append("\n\n")

        sb.append("النتيجة الإجمالية: ${"%.1f".format(score)}/100 — ")
        sb.append(when {
            score >= 72f -> "حالة البشرة ممتازة بشكل عام"
            score >= 55f -> "حالة البشرة جيدة مع بعض المؤشرات التي تحتاج متابعة"
            score >= 35f -> "حالة البشرة متوسطة — هناك مجالات للتحسين"
            else -> "البشرة تحتاج عناية مركزة في عدة مؤشرات"
        })
        sb.append("\n\n")

        sb.append("المؤشرات الإيجابية: $excellent من ${metrics.size} مؤشر في الحالة الجيدة أو الممتازة")
        sb.append("، والمتوسطة: $fair")
        if (needsAttention > 0) {
            sb.append("، والتي تحتاج اهتماماً: $needsAttention مؤشر")
            topConcern?.let {
                sb.append(" (أكثرها إلحاحاً: ${getArabicName(it.type)})")
            }
        }
        sb.append("\n\n")

        for (metric in metrics) {
            sb.append("【${getArabicName(metric.type)}】— ${"%.0f".format(metric.score)}/100 (${metric.severity.displayAr})\n")
            sb.append(getDescription(metric.type, metric.severity))
            sb.append("\n")

            if (metric.severity == MetricSeverity.POOR || metric.severity == MetricSeverity.CRITICAL) {
                val causes = getCauses(metric.type)
                if (causes.isNotEmpty()) {
                    sb.append("الأسباب: ${causes.take(2).joinToString("، ")}")
                    sb.append("\n")
                }
                val tips = getTips(metric.type)
                if (tips.isNotEmpty()) {
                    sb.append("نصائح: ${tips.take(2).joinToString("، ")}")
                    sb.append("\n")
                }
                val ing = getIngredients(metric.type)
                if (ing.isNotEmpty()) {
                    sb.append("مكونات مفيدة: ${ing.take(3).joinToString("، ")}")
                    sb.append("\n")
                }
            } else if (metric.severity == MetricSeverity.FAIR) {
                val tips = getTips(metric.type)
                if (tips.isNotEmpty()) {
                    sb.append("نصيحة: ${tips.first()}")
                    sb.append("\n")
                }
            }
            sb.append("\n")
        }

        if (profile.primaryConcernsAr.isNotEmpty()) {
            sb.append("أبرز المخاوف المذكورة: ${profile.primaryConcernsAr.joinToString("، ")}")
            sb.append("\n\n")
        }

        val goodIngredients = mutableSetOf<String>()
        for (metric in metrics) {
            if (metric.severity == MetricSeverity.POOR || metric.severity == MetricSeverity.CRITICAL) {
                goodIngredients.addAll(getIngredients(metric.type))
            }
        }
        if (goodIngredients.isNotEmpty()) {
            sb.append("مكونات مقترحة للعناية: ${goodIngredients.take(5).joinToString("، ")}")
            sb.append("\n")
        }

        return sb.toString()
    }

    private suspend fun performLocalTFLiteAnalysis(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> {
        return try {
            if (!localTFLiteProvider.isAvailable()) {
                val initResult = localTFLiteProvider.initialize()
                if (initResult.isFailure) {
                    Timber.w("TFLite init failed: ${initResult.exceptionOrNull()?.message}")
                    return emptyMap()
                }
            }

            val imageMap = mutableMapOf<String, File>()
            for ((spectrum, file) in frames) {
                if (file.exists()) imageMap[spectrum.name] = file
            }

            if (imageMap.isEmpty()) return emptyMap()

            val result = localTFLiteProvider.analyze(imageMap)

            if (result.warnings.isNotEmpty()) {
                Timber.w("TFLite analysis warnings: ${result.warnings}")
            }

            Timber.i("TFLite analysis: ${result.metrics.size} metrics, confidence=${result.confidence}")
            result.metrics
        } catch (e: Exception) {
            Timber.e(e, "TFLite analysis failed")
            emptyMap()
        }
    }

    private suspend fun performBasicPixelAnalysis(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> = withContext(Dispatchers.Default) {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        for ((spectrum, file) in frames) {
            if (!file.exists()) continue
            val bitmap = try { CVUtils.decodeSampled(file) } catch (e: Exception) { Timber.e(e, "decode failed for ${file.name}"); null } ?: continue
            try {
                val stats = CVUtils.computePixelStats(bitmap)
                val texture = CVUtils.laplacianVariance(bitmap)
                val spots = CVUtils.adaptiveThresholdSpots(bitmap)
                val redness = CVUtils.hsvRednessIndex(bitmap)
                val specular = CVUtils.specularHighlightRatio(bitmap)
                val uniformity = CVUtils.labColorUniformity(bitmap)
                val edgeRatio = CVUtils.cannyEdgeRatio(bitmap)
                val gaborTexture = CVUtils.gaborTextureEnergy(bitmap)
                val lbp = CVUtils.localBinaryPattern(bitmap, 2)
                val morphGrad = CVUtils.morphologicalGradient(bitmap, 3)
                val wrinkleDepth = CVUtils.wrinkleDepthEstimate(bitmap)
                val edgeHist = CVUtils.edgeDirectionHistogram(bitmap)
                val vascularComplexity = CVUtils.vascularPatternComplexity(bitmap)
                val inflammatory = CVUtils.inflammatoryMarkerDetection(bitmap)
                val poreDensity = CVUtils.poreDensityEstimate(bitmap)
                val (sebumDist, sebumUniformity) = CVUtils.sebumDistributionAnalysis(bitmap)
                val skinBarrier = CVUtils.skinBarrierEstimate(bitmap)
                val pigHetero = CVUtils.pigmentationHeterogeneity(bitmap)

                when (spectrum) {
                    LightSpectrum.WHITE -> {
                        val texScore = CVUtils.calibratedScore(texture * 0.3f + gaborTexture * 0.4f + lbp * 0.3f, 50f, 5f)
                        val poreScore = CVUtils.calibratedScore(poreDensity * 0.5f + morphGrad / 100f * 0.3f + (1f - specular) * 0.2f, 55f, 5f)
                        val toneScore = CVUtils.calibratedScore(uniformity + CVUtils.colorHistogramAnalysis(bitmap) / 10f, 25f, 2f)
                        metrics[SkinMetric.Type.TEXTURE] = SkinMetric(SkinMetric.Type.TEXTURE, texScore, classify(cvScore = texScore), details = "تحليل متعدد المقياس - الضوء الأبيض")
                        metrics[SkinMetric.Type.SKIN_TONE] = SkinMetric(SkinMetric.Type.SKIN_TONE, toneScore, classify(cvScore = toneScore), details = "تحليل لون البشرة - الضوء الأبيض")
                        metrics[SkinMetric.Type.PORES] = SkinMetric(SkinMetric.Type.PORES, poreScore, classify(cvScore = poreScore), details = "تحليل كثافة المسام - الضوء الأبيض")
                    }
                    LightSpectrum.UV365 -> {
                        val uvSpots = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.15f, 0.005f)
                        val pigmentation = CVUtils.calibratedScore(stats.contrast * 0.5f + pigHetero * 0.5f, 35f, 3f)
                        metrics[SkinMetric.Type.UV_SPOTS] = SkinMetric(SkinMetric.Type.UV_SPOTS, uvSpots, classify(cvScore = uvSpots), details = "تحليل البقع UV - Morphological + Adaptive")
                        metrics[SkinMetric.Type.PIGMENTATION] = SkinMetric(SkinMetric.Type.PIGMENTATION, pigmentation, classify(cvScore = pigmentation), details = "تحليل التصبغ - LAB Variance + Histogram")
                    }
                    LightSpectrum.POL_P -> {
                        val v = CVUtils.calibratedScore(redness * 0.5f + vascularComplexity * 0.3f + inflammatory * 0.2f, 0.25f, 0.02f)
                        val s = CVUtils.calibratedScore(redness * 0.6f + inflammatory * 0.4f, 0.20f, 0.015f)
                        val r = CVUtils.calibratedScore((redness + vascularComplexity) / 2f, 0.18f, 0.01f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, v, classify(cvScore = v), details = "تحليل الأوعية - Vascular Pattern + Inflammatory")
                        metrics[SkinMetric.Type.SENSITIVITY] = SkinMetric(SkinMetric.Type.SENSITIVITY, s, classify(cvScore = s), details = "تحليل الحساسية - Redness + Inflammatory")
                        metrics[SkinMetric.Type.ROSACEA] = SkinMetric(SkinMetric.Type.ROSACEA, r, classify(cvScore = r), details = "تحليل الوردية - Vascular Complexity")
                    }
                    LightSpectrum.POL_N -> {
                        val wrinkleScore = CVUtils.calibratedScore(edgeRatio * 0.3f + wrinkleDepth * 0.3f + edgeHist * 0.2f + lbp * 0.2f, 0.15f, 0.003f)
                        metrics[SkinMetric.Type.WRINKLES] = SkinMetric(SkinMetric.Type.WRINKLES, wrinkleScore, classify(cvScore = wrinkleScore), details = "تحليل التجاعيد - Edge + Gabor + LBP")
                    }
                    LightSpectrum.WOODS -> {
                        val moistureScore = CVUtils.calibratedScoreInverted(stats.brightness / 100f * 0.6f + skinBarrier * 0.4f, 0.05f, 0.85f)
                        val melasmaSpots = CVUtils.adaptiveThresholdSpots(bitmap)
                        val melasmaScore = CVUtils.calibratedScore(melasmaSpots * 0.6f + pigHetero * 0.4f, 0.15f, 0.005f)
                        metrics[SkinMetric.Type.MOISTURE] = SkinMetric(SkinMetric.Type.MOISTURE, moistureScore, classify(cvScore = moistureScore), details = "تحليل الرطوبة - Brightness + Barrier")
                        metrics[SkinMetric.Type.MELASMA] = SkinMetric(SkinMetric.Type.MELASMA, melasmaScore, classify(cvScore = melasmaScore), details = "تحليل الكلف - Spots + Heterogeneity")
                    }
                    LightSpectrum.BLUE -> {
                        val sebumScore = CVUtils.calibratedScoreInverted(stats.meanB / 255f * 0.5f + sebumDist * 0.3f + morphGrad / 100f * 0.2f, 0.15f, 0.55f)
                        val acneScore = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.15f, 0.003f)
                        val blackheadScore = CVUtils.calibratedScore(spots * 0.5f + (1f - sebumUniformity / 50f) * 0.3f + morphGrad / 100f * 0.2f, 0.12f, 0.005f)
                        metrics[SkinMetric.Type.SEBUM] = SkinMetric(SkinMetric.Type.SEBUM, sebumScore, classify(cvScore = sebumScore), details = "تحليل الدهون - Blue + Distribution + Morphology")
                        metrics[SkinMetric.Type.ACNE] = SkinMetric(SkinMetric.Type.ACNE, acneScore, classify(cvScore = acneScore), details = "تحليل حب الشباب - Adaptive + Morphological")
                        metrics[SkinMetric.Type.BLACKHEADS] = SkinMetric(SkinMetric.Type.BLACKHEADS, blackheadScore, classify(cvScore = blackheadScore), details = "تحليل الرؤوس السوداء - Spots + Texture + Morphology")
                    }
                    LightSpectrum.RED -> {
                        val vascularScore = CVUtils.calibratedScore(redness * 0.6f + vascularComplexity * 0.4f, 0.25f, 0.02f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, vascularScore, classify(cvScore = vascularScore), details = "تحليل الأوعية - Redness + Complexity")
                    }
                    LightSpectrum.BROWN -> {
                        val texture = CVUtils.localBinaryPattern(bitmap, 3)
                        val darkCircleScore = CVUtils.calibratedScore(spots * 0.5f + texture * 0.3f + morphGrad / 100f * 0.2f, 0.14f, 0.005f)
                        metrics[SkinMetric.Type.DARK_CIRCLES] = SkinMetric(SkinMetric.Type.DARK_CIRCLES, darkCircleScore, classify(cvScore = darkCircleScore), details = "تحليل الهالات - Spots + LBP + Morphology")
                    }
                    else -> {}
                }
            } catch (e: Exception) {
                Timber.e(e, "performBasicPixelAnalysis: spectrum ${spectrum.name} failed")
            }
            bitmap.recycle()
        }
        metrics
    }

    private fun classify(cvScore: Float): MetricSeverity = when {
        cvScore >= 72f -> MetricSeverity.EXCELLENT
        cvScore >= 55f -> MetricSeverity.GOOD
        cvScore >= 35f -> MetricSeverity.FAIR
        cvScore >= 20f -> MetricSeverity.POOR
        else -> MetricSeverity.CRITICAL
    }

    private suspend fun performAdvancedAnalysis(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> {
        val whiteFile = frames.entries.find { it.key == LightSpectrum.WHITE }?.value
        return advancedSkinAnalyzer.analyze(frames, whiteFile)
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

    override suspend fun saveReport(report: SkinAnalysisReport): Result<String> {
        return try {
            _analysisState.value = AnalysisState.Saving

            val metricsJson = json.encodeToString(report.metrics)
            val tipsJson = json.encodeToString(report.expertTipsAr)
            val productsJson = json.encodeToString(report.productRecommendations)
            val profileJson = json.encodeToString(report.skinProfile)
            val heatmapJson = json.encodeToString(report.heatmapPoints)

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
                scanId = report.scanId,
                heatmapPointsJson = heatmapJson
            )

            reportDao.insertReport(entity)
            _analysisState.value = AnalysisState.Complete(report.id)

            Timber.i("Report saved: ${report.id}")
            Result.success(report.id)
        } catch (e: Throwable) {
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
        Timber.d("getCapturedImages: dir=$captureDir, exists=${captureDir.exists()}")
        if (!captureDir.exists()) return emptyMap()

        val files = captureDir.list()?.joinToString() ?: "empty"
        Timber.d("getCapturedImages: files=[$files]")

        val images = mutableMapOf<LightSpectrum, File>()
        for (spectrum in LightSpectrum.entries) {
            if (spectrum == LightSpectrum.OFF || spectrum == LightSpectrum.ALL) continue
            val file = File(captureDir, "frame_${spectrum.name}.jpg")
            if (file.exists()) images[spectrum] = file
        }
        Timber.d("getCapturedImages: found ${images.size} images")
        return images
    }
}
