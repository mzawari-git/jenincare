package com.ebtikar.skinanalyzer.data.repository

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import com.ebtikar.skinanalyzer.ai.AdvancedSkinAnalyzer
import com.ebtikar.skinanalyzer.ai.CVUtils
import com.ebtikar.skinanalyzer.ai.EngineHealthMonitor
import com.ebtikar.skinanalyzer.ai.EnsembleAnalysisEngine
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
import com.ebtikar.skinanalyzer.model.arabicName
import com.ebtikar.skinanalyzer.model.SkinProfile
import com.ebtikar.skinanalyzer.model.HeatmapPoint
import com.ebtikar.skinanalyzer.model.SkinZone
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
import java.util.concurrent.ConcurrentHashMap
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
    private val faceLandmarkDetector: FaceLandmarkDetector,
    private val ensembleEngine: EnsembleAnalysisEngine,
    private val healthMonitor: EngineHealthMonitor
) : SkinAnalysisRepository {

    private val json = Json { ignoreUnknownKeys = true; encodeDefaults = true }

    private val _analysisState = MutableStateFlow<AnalysisState>(AnalysisState.Idle)
    override fun getAnalysisState(): StateFlow<AnalysisState> = _analysisState.asStateFlow()

    /** Analysis cache: reportId -> cached report for quick re-analysis */
    private val analysisCache = ConcurrentHashMap<String, SkinAnalysisReport>()

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
            Timber.i("analyzeImages: received ${frames.size} frames, mode=$mode")
            for ((spec, f) in frames) {
                Timber.i("  Frame ${spec.name}: file=${f.name}, exists=${f.exists()}, size=${if(f.exists()) f.length() else -1}L, parent=${f.parentFile?.absolutePath}")
            }

            val analysisFrames = frames.mapValues { (_, file) ->
                val rawFile = File(file.parentFile, file.nameWithoutExtension + "_raw.jpg")
                val useRaw = rawFile.exists()
                Timber.d("  Frame resolution: ${file.name} -> raw=${rawFile.name}(exists=$useRaw) -> using=${if(useRaw) rawFile.name else file.name}")
                if (useRaw) rawFile else file
            }

            Timber.i("analysisFrames resolved: ${analysisFrames.size} files")
            for ((spec, f) in analysisFrames) {
                Timber.i("  Analysis frame ${spec.name}: ${f.name}, exists=${f.exists()}, size=${if(f.exists()) f.length() else -1}L")
            }

            // Check cache for quick re-analysis
            val cacheKey = analysisFrames.values.joinToString("|") { it.nameWithoutExtension }
            analysisCache[cacheKey]?.let { cached ->
                Timber.i("Analysis cache hit for key=$cacheKey, returning cached report")
                return Result.success(cached)
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
                Timber.w("Post-capture face check: no face in RGB/polarized frames — proceeding anyway")
            }

            val useCloud = mode == Constants.ANALYSIS_CLOUD || mode == Constants.ANALYSIS_AUTO
            val useLocal = mode == Constants.ANALYSIS_LOCAL || mode == Constants.ANALYSIS_AUTO

            // Phase 1: Try cloud analysis
            if (useCloud) {
                val startTime = System.currentTimeMillis()
                val apiResult = cloudUploadService.uploadAndAnalyze(analysisFrames)
                val elapsed = System.currentTimeMillis() - startTime
                if (apiResult.isSuccess) {
                    _analysisState.value = AnalysisState.Analyzing("Cloud_API")
                    val cloudReport = apiResult.getOrNull()
                    cloudReport?.let {
                        healthMonitor.recordSuccess("Cloud_API", elapsed, it.confidence, it.metrics.size)
                    }
                    Timber.i("Engine used: Cloud_API — providerName=${cloudReport?.providerName}, metrics=${cloudReport?.metrics?.size}, confidence=${cloudReport?.confidence}")
                    cloudReport?.let { analysisCache[cacheKey] = it }
                    return apiResult
                }
                healthMonitor.recordFailure("Cloud_API", apiResult.exceptionOrNull()?.message ?: "Unknown", elapsed)
                if (mode == Constants.ANALYSIS_CLOUD) {
                    return Result.failure(apiResult.exceptionOrNull() ?: Exception("Cloud analysis failed"))
                }
                Timber.w("Cloud API failed, falling back to local analysis: ${apiResult.exceptionOrNull()?.message}")
            }

            // Phase 2: Run all local engines and collect results
            val ensembleResults = mutableListOf<EnsembleAnalysisEngine.EngineResult>()

            // 2a: TFLite engine
            _analysisState.value = AnalysisState.Analyzing("Local_TFLite")
            val tfliteStartTime = System.currentTimeMillis()
            try {
                val tfliteMetrics = performLocalTFLiteAnalysis(analysisFrames)
                val tfliteElapsed = System.currentTimeMillis() - tfliteStartTime
                if (tfliteMetrics.isNotEmpty()) {
                    healthMonitor.recordSuccess("Local_TFLite", tfliteElapsed, 0.88f, tfliteMetrics.size)
                    ensembleResults.add(EnsembleAnalysisEngine.EngineResult(
                        engineName = "Local_TFLite",
                        metrics = tfliteMetrics,
                        confidence = 0.88f,
                        executionTimeMs = tfliteElapsed
                    ))
                    Timber.i("TFLite: ${tfliteMetrics.size} metrics in ${tfliteElapsed}ms")
                } else {
                    healthMonitor.recordFailure("Local_TFLite", "No metrics returned", tfliteElapsed)
                }
            } catch (e: Exception) {
                healthMonitor.recordFailure("Local_TFLite", e.message ?: "Exception")
                Timber.w(e, "TFLite analysis failed")
            }

            // 2b: Advanced/MediaPipe engine
            _analysisState.value = AnalysisState.Analyzing("Advanced_Analysis_Engine")
            val advancedStartTime = System.currentTimeMillis()
            try {
                val advancedMetrics = performAdvancedAnalysis(analysisFrames)
                val advancedElapsed = System.currentTimeMillis() - advancedStartTime
                if (advancedMetrics.isNotEmpty()) {
                    healthMonitor.recordSuccess("Advanced_MediaPipe", advancedElapsed, 0.92f, advancedMetrics.size)
                    ensembleResults.add(EnsembleAnalysisEngine.EngineResult(
                        engineName = "Advanced_MediaPipe",
                        metrics = advancedMetrics,
                        confidence = 0.92f,
                        executionTimeMs = advancedElapsed
                    ))
                    Timber.i("Advanced: ${advancedMetrics.size} metrics in ${advancedElapsed}ms")
                } else {
                    healthMonitor.recordFailure("Advanced_MediaPipe", "No metrics returned", advancedElapsed)
                }
            } catch (e: Throwable) {
                healthMonitor.recordFailure("Advanced_MediaPipe", e.message ?: "Exception")
                Timber.e(e, "Advanced analysis FAILED: ${e.javaClass.simpleName}: ${e.message}")
                e.stackTrace.take(10).forEach { Timber.e("  at $it") }
            }

            // 2c: OpenCV engine
            _analysisState.value = AnalysisState.Analyzing("CV_Analysis_Engine")
            val opencvStartTime = System.currentTimeMillis()
            try {
                val whiteFile = analysisFrames.entries.find { it.key == LightSpectrum.WHITE }?.value
                val opencvMetrics = withContext(Dispatchers.Default) {
                    openCVSkinAnalyzer.analyze(analysisFrames, whiteFile)
                }
                val opencvElapsed = System.currentTimeMillis() - opencvStartTime
                if (opencvMetrics.isNotEmpty()) {
                    healthMonitor.recordSuccess("CV_OpenCV", opencvElapsed, 0.82f, opencvMetrics.size)
                    ensembleResults.add(EnsembleAnalysisEngine.EngineResult(
                        engineName = "CV_OpenCV",
                        metrics = opencvMetrics,
                        confidence = 0.82f,
                        executionTimeMs = opencvElapsed
                    ))
                    Timber.i("OpenCV: ${opencvMetrics.size} metrics in ${opencvElapsed}ms")
                } else {
                    healthMonitor.recordFailure("CV_OpenCV", "No metrics returned", opencvElapsed)
                }
            } catch (e: Throwable) {
                healthMonitor.recordFailure("CV_OpenCV", e.message ?: "Exception")
                Timber.e(e, "OpenCV analysis FAILED: ${e.javaClass.simpleName}: ${e.message}")
                e.stackTrace.take(10).forEach { Timber.e("  at $it") }
            }

            // 2d: Basic Pixel engine (always run as fallback)
            _analysisState.value = AnalysisState.Analyzing("Basic_Pixel_Engine")
            val basicStartTime = System.currentTimeMillis()
            try {
                val basicMetrics = performBasicPixelAnalysis(analysisFrames)
                val basicElapsed = System.currentTimeMillis() - basicStartTime
                if (basicMetrics.isNotEmpty()) {
                    healthMonitor.recordSuccess("Basic_Pixel", basicElapsed, 0.75f, basicMetrics.size)
                    ensembleResults.add(EnsembleAnalysisEngine.EngineResult(
                        engineName = "Basic_Pixel",
                        metrics = basicMetrics,
                        confidence = 0.75f,
                        executionTimeMs = basicElapsed
                    ))
                    Timber.i("Basic Pixel: ${basicMetrics.size} metrics in ${basicElapsed}ms")
                } else {
                    healthMonitor.recordFailure("Basic_Pixel", "No metrics returned", basicElapsed)
                }
            } catch (e: Throwable) {
                healthMonitor.recordFailure("Basic_Pixel", e.message ?: "Exception")
                Timber.e(e, "Basic Pixel analysis FAILED: ${e.javaClass.simpleName}: ${e.message}")
                e.stackTrace.take(10).forEach { Timber.e("  at $it") }
            }

            // Phase 3: Combine results using ensemble engine
            if (ensembleResults.isEmpty()) {
                Timber.w("All 4 analysis engines returned empty metrics — attempting emergency fallback")
                val emergencyMetrics = performEmergencyFallback(analysisFrames)
                if (emergencyMetrics.isNotEmpty()) {
                    Timber.i("Emergency fallback produced ${emergencyMetrics.size} metrics")
                    ensembleResults.add(EnsembleAnalysisEngine.EngineResult(
                        engineName = "Emergency_Fallback",
                        metrics = emergencyMetrics,
                        confidence = 0.50f,
                        executionTimeMs = 0
                    ))
                } else {
                    Timber.e("Emergency fallback also produced no metrics. Files: ${analysisFrames.map { "${it.key}=${it.value.name}(exists=${it.value.exists()}, size=${if(it.value.exists()) it.value.length() else 0})" }}")
                    _analysisState.value = AnalysisState.Error("لم يتمكن التحليل من استخراج بيانات كافية. تأكد من وجود الوجه بشكل واضح أمام الكاميرا وأعد المحاولة.")
                    return Result.failure(Exception("All analysis engines failed (including emergency fallback)"))
                }
            }

            _analysisState.value = AnalysisState.Analyzing("Ensemble_Combining")
            val ensembleReport = ensembleEngine.combineResults(ensembleResults)

            // Apply cross-spectrum validation
            val crossValidated = ensembleReport.metrics.toMutableMap()
            validateCrossSpectrum(crossValidated)

            // Fill any missing metric types with estimates from correlated metrics
            fillMissingMetrics(crossValidated)

            val metricsList = SkinMetric.ALL_TYPES.mapNotNull { type -> crossValidated[type] }
            val metricsMap = metricsList.associateBy { it.type }
            val expertTips = mockEngine.generateExpertTips(metricsMap)
            val products = mockEngine.generateProductRecommendations(metricsMap)
            val skinProfile = mockEngine.generateSkinProfile(metricsMap)
            val heatmapPoints = generateHeatmapPoints(metricsList)

            val report = SkinAnalysisReport(
                providerName = "Ensemble_Engine(${ensembleResults.joinToString("+") { it.engineName }})",
                overallScore = metricsList.map { it.score }.average().toFloat(),
                metrics = metricsList,
                executionTimeMs = ensembleResults.sumOf { it.executionTimeMs },
                aiAnalysisTextAr = generateAIAnalysisText(metricsList, skinProfile),
                expertTipsAr = expertTips,
                productRecommendations = products,
                skinProfile = skinProfile,
                confidence = ensembleReport.overallConfidence,
                heatmapPoints = heatmapPoints
            )

            // Cache the result
            analysisCache[cacheKey] = report

            Timber.i("Ensemble analysis complete: ${metricsList.size} metrics, overallScore=${report.overallScore}, " +
                "confidence=${report.confidence}, engines=${ensembleReport.engineCount}, " +
                "agreement=${"%.0f".format(ensembleReport.agreementScore * 100)}%")
            Timber.i("Engine contributions: ${ensembleReport.engineContributions}")

            Result.success(report)
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

    private fun fillMissingMetrics(metrics: MutableMap<SkinMetric.Type, SkinMetric>) {
        val present = metrics.keys.toSet()
        val missing = SkinMetric.ALL_TYPES.filter { it !in present }
        if (missing.isEmpty()) return
        Timber.i("fillMissingMetrics: ${missing.size} missing types: ${missing.map { it.name }}")

        fun avgScore(vararg types: SkinMetric.Type): Float {
            val scores = types.mapNotNull { metrics[it]?.score }
            return if (scores.isNotEmpty()) scores.average().toFloat() else 60f
        }

        fun avgScoreNearby(vararg types: SkinMetric.Type): Float {
            val scores = types.mapNotNull { metrics[it]?.score }
            return if (scores.isNotEmpty()) scores.average().toFloat() else 60f
        }

        fun avgSeverity(vararg types: SkinMetric.Type): MetricSeverity {
            val sev = types.mapNotNull { metrics[it]?.severity }
            return if (sev.isNotEmpty()) sev.sortedBy { it.ordinal }[sev.size / 2] else MetricSeverity.FAIR
        }

        for (type in missing) {
            val estimatedScore: Float
            val zone: SkinZone
            val details: String

            when (type) {
                SkinMetric.Type.MOISTURE -> {
                    estimatedScore = avgScore(SkinMetric.Type.TEXTURE, SkinMetric.Type.SKIN_TONE, SkinMetric.Type.SEBUM)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات متعلقة (لم يتم التقاط طيف WOODS)"
                }
                SkinMetric.Type.MELASMA -> {
                    estimatedScore = avgScore(SkinMetric.Type.UV_SPOTS, SkinMetric.Type.PIGMENTATION)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات التصبغ (لم يتم التقاط طيف WOODS)"
                }
                SkinMetric.Type.DARK_CIRCLES -> {
                    estimatedScore = avgScore(SkinMetric.Type.PIGMENTATION, SkinMetric.Type.VASCULAR, SkinMetric.Type.UV_SPOTS)
                    zone = SkinZone.EYE_AREA
                    details = "تقدير من مؤشرات الأوعية والتصبغ (لم يتم التقاط طيف BROWN)"
                }
                SkinMetric.Type.UV_SPOTS -> {
                    estimatedScore = avgScore(SkinMetric.Type.PIGMENTATION, SkinMetric.Type.MELASMA)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات التصبغ (لم يتم التقاط طيف UV)"
                }
                SkinMetric.Type.PIGMENTATION -> {
                    estimatedScore = avgScore(SkinMetric.Type.UV_SPOTS, SkinMetric.Type.SKIN_TONE, SkinMetric.Type.MELASMA)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات لون البشرة والبقع (لم يتم التقاط طيف UV)"
                }
                SkinMetric.Type.VASCULAR -> {
                    estimatedScore = avgScore(SkinMetric.Type.SENSITIVITY, SkinMetric.Type.ROSACEA)
                    zone = SkinZone.U_ZONE
                    details = "تقدير من مؤشرات الحساسية والوردية (لم يتم التقاط طيف POL_P)"
                }
                SkinMetric.Type.SENSITIVITY -> {
                    estimatedScore = avgScore(SkinMetric.Type.VASCULAR, SkinMetric.Type.ROSACEA)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات الأوعية (لم يتم التقاط طيف POL_P)"
                }
                SkinMetric.Type.ROSACEA -> {
                    estimatedScore = avgScore(SkinMetric.Type.VASCULAR, SkinMetric.Type.SENSITIVITY)
                    zone = SkinZone.U_ZONE
                    details = "تقدير من مؤشرات الأوعية الدموية (لم يتم التقاط طيف POL_P)"
                }
                SkinMetric.Type.WRINKLES -> {
                    estimatedScore = avgScore(SkinMetric.Type.TEXTURE, SkinMetric.Type.SKIN_TONE)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات الملمس (لم يتم التقاط طيف POL_N)"
                }
                SkinMetric.Type.SEBUM -> {
                    estimatedScore = avgScore(SkinMetric.Type.ACNE, SkinMetric.Type.BLACKHEADS, SkinMetric.Type.PORES)
                    zone = SkinZone.T_ZONE
                    details = "تقدير من مؤشرات حب الشباب والمسام (لم يتم التقاط طيف BLUE)"
                }
                SkinMetric.Type.ACNE -> {
                    estimatedScore = avgScore(SkinMetric.Type.SEBUM, SkinMetric.Type.BLACKHEADS, SkinMetric.Type.PORES)
                    zone = SkinZone.T_ZONE
                    details = "تقدير من مؤشرات الدهون والرؤوس السوداء (لم يتم التقاط طيف BLUE)"
                }
                SkinMetric.Type.BLACKHEADS -> {
                    estimatedScore = avgScore(SkinMetric.Type.SEBUM, SkinMetric.Type.ACNE, SkinMetric.Type.PORES)
                    zone = SkinZone.T_ZONE
                    details = "تقدير من مؤشرات الدهون وحب الشباب (لم يتم التقاط طيف BLUE)"
                }
                SkinMetric.Type.TEXTURE -> {
                    estimatedScore = avgScore(SkinMetric.Type.WRINKLES, SkinMetric.Type.PORES, SkinMetric.Type.SKIN_TONE)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات التجاعيد والمسام (لم يتم التقاط طيف WHITE)"
                }
                SkinMetric.Type.PORES -> {
                    estimatedScore = avgScore(SkinMetric.Type.TEXTURE, SkinMetric.Type.SEBUM, SkinMetric.Type.BLACKHEADS)
                    zone = SkinZone.T_ZONE
                    details = "تقدير من مؤشرات الملمس والدهون (لم يتم التقاط طيف WHITE)"
                }
                SkinMetric.Type.SKIN_TONE -> {
                    estimatedScore = avgScore(SkinMetric.Type.TEXTURE, SkinMetric.Type.PIGMENTATION, SkinMetric.Type.UV_SPOTS)
                    zone = SkinZone.FULL_FACE
                    details = "تقدير من مؤشرات الملمس والتصبغ (لم يتم التقاط طيف WHITE)"
                }
            }

            val severity = classify(estimatedScore)
            metrics[type] = SkinMetric(
                type = type,
                score = estimatedScore.coerceIn(10f, 90f),
                severity = severity,
                zone = zone,
                details = details,
                confidence = 0.55f
            )
            Timber.i("fillMissingMetrics: estimated ${type.name} = ${"%.1f".format(estimatedScore)} (severity=$severity)")
        }
    }

    private fun generateHeatmapPoints(metrics: List<SkinMetric>): List<HeatmapPoint> {
        val points = mutableListOf<HeatmapPoint>()
        val zoneToCoords = mapOf(
            SkinZone.T_ZONE to listOf(Pair(0.5f, 0.35f)),
            SkinZone.U_ZONE to listOf(Pair(0.3f, 0.55f), Pair(0.7f, 0.55f)),
            SkinZone.O_ZONE to listOf(Pair(0.25f, 0.5f), Pair(0.75f, 0.5f)),
            SkinZone.EYE_AREA to listOf(Pair(0.35f, 0.4f), Pair(0.65f, 0.4f)),
            SkinZone.FULL_FACE to listOf(Pair(0.5f, 0.5f))
        )

        for (metric in metrics) {
            if (metric.severity == MetricSeverity.EXCELLENT || metric.severity == MetricSeverity.GOOD) continue
            val coords = zoneToCoords[metric.zone] ?: continue
            val severityValue = (100f - metric.score) / 100f
            for (coord in coords) {
                val x = coord.first
                val y = coord.second
                val jitterX = x + (Math.random().toFloat() - 0.5f) * 0.06f
                val jitterY = y + (Math.random().toFloat() - 0.5f) * 0.06f
                points.add(HeatmapPoint(
                    x = jitterX.coerceIn(0.1f, 0.9f),
                    y = jitterY.coerceIn(0.1f, 0.9f),
                    value = severityValue.coerceIn(0f, 1f),
                    label = metric.type.name
                ))
            }
        }
        Timber.i("Generated ${points.size} heatmap points from ${metrics.size} metrics")
        return points
    }

    private fun generateAIAnalysisText(metrics: List<SkinMetric>, profile: SkinProfile): String {
        val score = metrics.map { it.score }.average().toFloat()
        val excellent = metrics.count { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }
        val fair = metrics.count { it.severity == MetricSeverity.FAIR }
        val poor = metrics.count { it.severity == MetricSeverity.POOR }
        val critical = metrics.count { it.severity == MetricSeverity.CRITICAL }
        val needsAttention = poor + critical
        val knowledge = knowledgeRepository.getCachedKnowledge()

        fun getMetricKnowledge(type: SkinMetric.Type): MetricKnowledge? =
            knowledge.metrics[type.name]

        fun getDescription(type: SkinMetric.Type, severity: MetricSeverity): String {
            val desc = getMetricKnowledge(type)?.descriptions?.get(severity.name)
            if (!desc.isNullOrBlank()) return desc
            return when (type) {
                SkinMetric.Type.MOISTURE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مستوى الرطوبة مثالي — البشرة مرطبة بشكل كافٍ"; MetricSeverity.FAIR -> "رطوبة متوسطة — تحتاجين لترطيب إضافي"; MetricSeverity.POOR -> "جفاف واضح — البشرة تحتاج لترطيب مكثف"; else -> "جفاف شديد — البشرة في حالة حرجة وتحتاج رطوبة فورية" }
                SkinMetric.Type.PORES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "المسام ضيقة ومنتظمة — مظهر ناعم"; MetricSeverity.FAIR -> "بعض المسام الواسعة في منطقة الأنف والجبين"; MetricSeverity.POOR -> "مسام واسعة وملوحة — تحتاج لعناية مركزة"; else -> "مسام مسدودة وواسعة جداً — احمرار وتهيج مصاحب" }
                SkinMetric.Type.SEBUM -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "إفراز دهني متوازن — بشرة صحية"; MetricSeverity.FAIR -> "زيادة طفيفة في الإفرازات الدهنية"; MetricSeverity.POOR -> "إفراز دهني زائد — بشرة لامعة عرضة لحب الشباب"; else -> "إفراز دهني مفرط — انسداد المسام والالتهابات" }
                SkinMetric.Type.WRINKLES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "خطوط دقيقة قليلة جداً — بشرة شابة"; MetricSeverity.FAIR -> "خطوط تعبير واضحة حول العينين والجبين"; MetricSeverity.POOR -> "تجاعيد واضحة — تحتاج روتين مضاد للشيخوخة"; else -> "تجاعيد عميقة — علاماتشيخوخة متقدمة" }
                SkinMetric.Type.TEXTURE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "ملمس ناعم ومتجانس"; MetricSeverity.FAIR -> "خشونة خفيفة في بعض المناطق"; MetricSeverity.POOR -> "ملمس خشن وغير متساوٍ — يحتاج تقشير"; else -> "ملمس خشن جداً مع بقع جافة ودهنية" }
                SkinMetric.Type.UV_SPOTS -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد أضرار شمس واضحة"; MetricSeverity.FAIR -> "بقع شمس خفيفة — يحتاج واقي شمس"; MetricSeverity.POOR -> "أضرار شمس متقدمة — بقع بنية"; else -> "أضرار شمس شديدة — تصبغات عميقة" }
                SkinMetric.Type.VASCULAR -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "دورة دموية صحية — لا احمرار"; MetricSeverity.FAIR -> "احمرار خفيف في الخدود"; MetricSeverity.POOR -> "احمرار واضح وأوعية دموية بارزة"; else -> "احمرار شديد وتهاب مزمن" }
                SkinMetric.Type.PIGMENTATION -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة موحد ومتجانس"; MetricSeverity.FAIR -> "تصبغات خفيفة متفرقة"; MetricSeverity.POOR -> "تصبغات غامقة واسعة الانتشار"; else -> "تصبغات شديدة — تغير واضح في لون البشرة" }
                SkinMetric.Type.DARK_CIRCLES -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "منطقة العين مشرقة ونضرة"; MetricSeverity.FAIR -> "هالات خفيفة تحت العين"; MetricSeverity.POOR -> "هالات داكنة واضحة تحت العين"; else -> "هالات داكنة جداً مع تورم وتجعيد" }
                SkinMetric.Type.BLACKHEADS -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مسام نظيفة من الرؤوس السوداء"; MetricSeverity.FAIR -> "رؤوس سوداء خفيفة في الأنف والذقن"; MetricSeverity.POOR -> "انتشار واسع للرؤوس السوداء"; else -> "رؤوس سوداء ملتهبة ومنتشرة" }
                SkinMetric.Type.ACNE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد بثور نشطة"; MetricSeverity.FAIR -> "بثور خفيفة متفرقة"; MetricSeverity.POOR -> "حب شباب نشط مع التهابات"; else -> "حب شباب شديد — التهابات وندبات" }
                SkinMetric.Type.SKIN_TONE -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة متجانس ومشرق"; MetricSeverity.FAIR -> "اختلافات طفيفة في اللون"; MetricSeverity.POOR -> "عدم تجانس واضح في لون البشرة"; else -> "تباين كبير في لون البشرة" }
                SkinMetric.Type.SENSITIVITY -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "بشرة غير حساسة — تتحمل المنتجات"; MetricSeverity.FAIR -> "حساسية خفيفة لبعض المكونات"; MetricSeverity.POOR -> "بشرة حساسة — تهيج سهل"; else -> "بشرة شديدة الحساسية — لا تتحمل أي منتج" }
                SkinMetric.Type.ROSACEA -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات وردية — بشرة صافية"; MetricSeverity.FAIR -> "احمرار خفيف قد يكون بداية وردية"; MetricSeverity.POOR -> "علامات وردية واضحة — احمرار مزمن"; else -> "وردية متقدمة — التهاب واحمرار شديد" }
                SkinMetric.Type.MELASMA -> when (severity) { MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات كلف — تصبغ موحد"; MetricSeverity.FAIR -> "تصبغ خفيف في بعض المناطق"; MetricSeverity.POOR -> "كلف واضح — يحتاج علاج مكثف"; else -> "كلف عميق واسع الانتشار — حالة حرجة" }
            }
        }

        fun getCauses(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.causesAr?.filter { it.isNotBlank() } ?: emptyList()

        fun getTips(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.tipsAr?.filter { it.isNotBlank() } ?: emptyList()

        fun getIngredients(type: SkinMetric.Type): List<String> =
            getMetricKnowledge(type)?.ingredientsAr?.filter { it.isNotBlank() } ?: emptyList()

        fun zoneNameAr(zone: SkinZone): String = when (zone) {
            SkinZone.T_ZONE -> "منطقة T — الجبهة والأنف والذقن"
            SkinZone.U_ZONE -> "الخدود والوجنتين"
            SkinZone.O_ZONE -> "المنطقة الخارجية للوجه"
            SkinZone.EYE_AREA -> "منطقة حول العين"
            SkinZone.FULL_FACE -> "الوجه بالكامل"
        }

        fun zoneEmoji(zone: SkinZone): String = when (zone) {
            SkinZone.T_ZONE -> "🔹"
            SkinZone.U_ZONE -> "🔸"
            SkinZone.O_ZONE -> "◾"
            SkinZone.EYE_AREA -> "👁"
            SkinZone.FULL_FACE -> "🔹"
        }

        val sb = StringBuilder()

        sb.appendLine("══════════════════════════════════════")
        sb.appendLine("       تقرير تحليل البشرة")
        sb.appendLine("    DERMA AI — تحليل متكامل")
        sb.appendLine("══════════════════════════════════════")
        sb.appendLine()

        sb.appendLine("▸ الملخص التنفيذي")
        sb.appendLine("─────────────────────")
        sb.appendLine("• النتيجة الإجمالية: ${"%.0f".format(score)}/100 — ")
        sb.appendLine(when {
            score >= 72f -> "  حالة البشرة ممتازة بشكل عام"
            score >= 55f -> "  حالة البشرة جيدة مع بعض المؤشرات التي تحتاج متابعة"
            score >= 35f -> "  حالة البشرة متوسطة — هناك مجالات للتحسين"
            else -> "  البشرة تحتاج عناية مركزة في عدة مؤشرات"
        })
        sb.appendLine()
        sb.appendLine("• المؤشرات الإيجابية: $excellent من ${metrics.size}")
        sb.appendLine("  المتوسطة: $fair | تحتاج عناية: $poor | حرجة: $critical")
        if (needsAttention > 0) {
            val urgent = metrics.filter { it.severity == MetricSeverity.CRITICAL || it.severity == MetricSeverity.POOR }
                .sortedBy { it.score }
                .take(3)
            if (urgent.isNotEmpty()) {
                sb.appendLine("• أعلى إلحاحاً: ${urgent.joinToString("، ") { "${it.type.arabicName()} (${"%.0f".format(it.score)})" }}")
            }
        }
        sb.appendLine("• نوع البشرة: ${profile.skinTypeAr} | الحساسية: ${profile.sensitivityLevel}")
        sb.appendLine()

        val metricsByZone = metrics.groupBy { it.zone }
        val zoneOrder = listOf(SkinZone.T_ZONE, SkinZone.U_ZONE, SkinZone.EYE_AREA, SkinZone.O_ZONE, SkinZone.FULL_FACE)

        sb.appendLine("▸ التحليل حسب المنطقة")
        sb.appendLine("─────────────────────")
        for (zone in zoneOrder) {
            val zoneMetrics = metricsByZone[zone] ?: continue
            if (zoneMetrics.isEmpty()) continue
            sb.appendLine()
            sb.appendLine("${zoneEmoji(zone)} ${zoneNameAr(zone)}:")
            for (m in zoneMetrics.sortedBy { it.score }) {
                val desc = getDescription(m.type, m.severity)
                sb.appendLine("   ${m.type.arabicName()}: ${"%.0f".format(m.score)}/100 (${m.severity.displayAr})")
                sb.appendLine("   $desc")
            }
        }
        sb.appendLine()

        val causalLinks = mutableListOf<String>()
        val sebum = metrics.find { it.type == SkinMetric.Type.SEBUM }
        val acne = metrics.find { it.type == SkinMetric.Type.ACNE }
        val blackheads = metrics.find { it.type == SkinMetric.Type.BLACKHEADS }
        val pores = metrics.find { it.type == SkinMetric.Type.PORES }
        val moisture = metrics.find { it.type == SkinMetric.Type.MOISTURE }
        val wrinkles = metrics.find { it.type == SkinMetric.Type.WRINKLES }
        val uvSpots = metrics.find { it.type == SkinMetric.Type.UV_SPOTS }
        val pigmentation = metrics.find { it.type == SkinMetric.Type.PIGMENTATION }

        if (sebum != null && sebum.severity == MetricSeverity.POOR || sebum?.severity == MetricSeverity.CRITICAL) {
            val targets = listOfNotNull(
                acne?.let { it.type.arabicName() },
                pores?.let { it.type.arabicName() },
                blackheads?.let { it.type.arabicName() }
            ).joinToString("، ")
            if (targets.isNotEmpty()) causalLinks.add("الإفراز الدهني الزائد يسبب: $targets")
        }
        if (moisture != null && (moisture.severity == MetricSeverity.POOR || moisture.severity == MetricSeverity.CRITICAL)) {
            val targets = listOfNotNull(
                wrinkles?.let { it.type.arabicName() },
                (metrics.find { it.type == SkinMetric.Type.TEXTURE })?.let { it.type.arabicName() }
            ).joinToString("، ")
            if (targets.isNotEmpty()) causalLinks.add("نقص الرطوبة يسبب: $targets")
        }
        if (uvSpots != null && (uvSpots.severity == MetricSeverity.POOR || uvSpots.severity == MetricSeverity.CRITICAL)) {
            val targets = listOfNotNull(
                pigmentation?.let { it.type.arabicName() }
            ).joinToString("، ")
            if (targets.isNotEmpty()) causalLinks.add("التعرض للشمس يسبب: $targets")
            else causalLinks.add("التعرض للشمس يسبب: ${uvSpots.type.arabicName()}")
        }

        if (causalLinks.isNotEmpty()) {
            sb.appendLine("▸ العوامل المؤثرة")
            sb.appendLine("─────────────────────")
            for (link in causalLinks) sb.appendLine("• $link")
            sb.appendLine()
        }

        val urgentMetrics = metrics.filter { it.severity == MetricSeverity.CRITICAL || it.severity == MetricSeverity.POOR }
            .sortedBy { it.score }

        if (urgentMetrics.isNotEmpty()) {
            sb.appendLine("▸ خطة العلاج المقترحة")
            sb.appendLine("─────────────────────")
            sb.appendLine()

            sb.appendLine("🔴 فوري (هذا الأسبوع):")
            var stepNum = 1
            for (m in urgentMetrics.take(3)) {
                val tips = getTips(m.type)
                if (tips.isNotEmpty()) {
                    sb.appendLine("   $stepNum. ${tips.first()} — ${m.type.arabicName()}")
                    stepNum++
                }
            }
            val sunProtection = metrics.find { it.type == SkinMetric.Type.UV_SPOTS || it.type == SkinMetric.Type.PIGMENTATION }
            if (sunProtection != null && (sunProtection.severity == MetricSeverity.POOR || sunProtection.severity == MetricSeverity.CRITICAL || sunProtection.severity == MetricSeverity.FAIR)) {
                sb.appendLine("   $stepNum. واقي شمس SPF 50+ — كل يوم بدون استثناء")
                stepNum++
            }
            sb.appendLine()

            val mediumMetrics = metrics.filter { it.severity == MetricSeverity.FAIR }
            if (mediumMetrics.isNotEmpty()) {
                sb.appendLine("🟡 قصير المدى (هذا الشهر):")
                for (m in mediumMetrics.take(3)) {
                    val tips = getTips(m.type)
                    if (tips.isNotEmpty()) {
                        sb.appendLine("   $stepNum. ${tips.first()} — ${m.type.arabicName()}")
                        stepNum++
                    }
                }
                sb.appendLine()
            }

            sb.appendLine("🟢 طويل المدى (٣-٦ شهور):")
            if (wrinkles != null && wrinkles.severity != MetricSeverity.EXCELLENT && wrinkles.severity != MetricSeverity.GOOD) {
                sb.appendLine("   $stepNum. ريتينول تدريجي ليلاً للتجاعيد")
                stepNum++
            }
            if (pigmentation != null && (pigmentation.severity == MetricSeverity.POOR || pigmentation.severity == MetricSeverity.CRITICAL)) {
                sb.appendLine("   $stepNum. علاج تصبغ مع طبيب جلدية")
                stepNum++
            }
            if (stepNum == (urgentMetrics.take(3).size + if (sunProtection != null) 1 else 0) + 1) {
                sb.appendLine("   $stepNum. متابعة دورية مع أخصائي جلدية")
            }
            sb.appendLine()
        }

        val goodIngredients = mutableSetOf<String>()
        for (m in metrics) {
            if (m.severity == MetricSeverity.POOR || m.severity == MetricSeverity.CRITICAL) {
                goodIngredients.addAll(getIngredients(m.type))
            }
        }
        if (goodIngredients.isNotEmpty()) {
            sb.appendLine("▸ المكونات المقترحة")
            sb.appendLine("─────────────────────")
            sb.appendLine(goodIngredients.take(5).joinToString(" • "))
            sb.appendLine()
        }

        if (profile.primaryConcernsAr.isNotEmpty()) {
            sb.appendLine("▸ أبرز المخاوف")
            sb.appendLine("─────────────────────")
            sb.appendLine(profile.primaryConcernsAr.joinToString("، "))
            sb.appendLine()
        }

        sb.appendLine("══════════════════════════════════════")
        sb.appendLine("هذا التقرير تم إنشاؤه بواسطة الذكاء الاصطناعي")
        sb.appendLine("ولا يغني عن استشارة الطبيب المختص")
        sb.appendLine("══════════════════════════════════════")

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
                        val uvSpots = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.40f, 0.005f)
                        val pigmentation = CVUtils.calibratedScore(stats.contrast * 0.5f + pigHetero * 0.5f, 45f, 3f)
                        metrics[SkinMetric.Type.UV_SPOTS] = SkinMetric(SkinMetric.Type.UV_SPOTS, uvSpots, classify(cvScore = uvSpots), details = "تحليل البقع UV - Morphological + Adaptive")
                        metrics[SkinMetric.Type.PIGMENTATION] = SkinMetric(SkinMetric.Type.PIGMENTATION, pigmentation, classify(cvScore = pigmentation), details = "تحليل التصبغ - LAB Variance + Histogram")
                    }
                    LightSpectrum.POL_P -> {
                        val v = CVUtils.calibratedScore(redness * 0.5f + vascularComplexity * 0.3f + inflammatory * 0.2f, 0.60f, 0.02f)
                        val s = CVUtils.calibratedScore(redness * 0.6f + inflammatory * 0.4f, 0.50f, 0.015f)
                        val r = CVUtils.calibratedScore((redness + vascularComplexity) / 2f, 0.45f, 0.01f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, v, classify(cvScore = v), details = "تحليل الأوعية - Vascular Pattern + Inflammatory")
                        metrics[SkinMetric.Type.SENSITIVITY] = SkinMetric(SkinMetric.Type.SENSITIVITY, s, classify(cvScore = s), details = "تحليل الحساسية - Redness + Inflammatory")
                        metrics[SkinMetric.Type.ROSACEA] = SkinMetric(SkinMetric.Type.ROSACEA, r, classify(cvScore = r), details = "تحليل الوردية - Vascular Complexity")
                    }
                    LightSpectrum.POL_N -> {
                        val wrinkleScore = CVUtils.calibratedScore(edgeRatio * 0.3f + wrinkleDepth * 0.3f + edgeHist * 0.2f + lbp * 0.2f, 0.40f, 0.003f)
                        metrics[SkinMetric.Type.WRINKLES] = SkinMetric(SkinMetric.Type.WRINKLES, wrinkleScore, classify(cvScore = wrinkleScore), details = "تحليل التجاعيد - Edge + Gabor + LBP")
                    }
                    LightSpectrum.WOODS -> {
                        val moistureScore = CVUtils.calibratedScoreInverted(stats.brightness / 100f * 0.6f + skinBarrier * 0.4f, 0.05f, 0.85f)
                        val melasmaSpots = CVUtils.adaptiveThresholdSpots(bitmap)
                        val melasmaScore = CVUtils.calibratedScore(melasmaSpots * 0.6f + pigHetero * 0.4f, 0.40f, 0.005f)
                        metrics[SkinMetric.Type.MOISTURE] = SkinMetric(SkinMetric.Type.MOISTURE, moistureScore, classify(cvScore = moistureScore), details = "تحليل الرطوبة - Brightness + Barrier")
                        metrics[SkinMetric.Type.MELASMA] = SkinMetric(SkinMetric.Type.MELASMA, melasmaScore, classify(cvScore = melasmaScore), details = "تحليل الكلف - Spots + Heterogeneity")
                    }
                    LightSpectrum.BLUE -> {
                        val sebumScore = CVUtils.calibratedScoreInverted(stats.meanB / 255f * 0.5f + sebumDist * 0.3f + morphGrad / 100f * 0.2f, 0.15f, 0.55f)
                        val acneScore = CVUtils.calibratedScore(spots * 0.6f + morphGrad / 100f * 0.4f, 0.40f, 0.003f)
                        val blackheadScore = CVUtils.calibratedScore(spots * 0.5f + (1f - sebumUniformity / 50f) * 0.3f + morphGrad / 100f * 0.2f, 0.35f, 0.005f)
                        metrics[SkinMetric.Type.SEBUM] = SkinMetric(SkinMetric.Type.SEBUM, sebumScore, classify(cvScore = sebumScore), details = "تحليل الدهون - Blue + Distribution + Morphology")
                        metrics[SkinMetric.Type.ACNE] = SkinMetric(SkinMetric.Type.ACNE, acneScore, classify(cvScore = acneScore), details = "تحليل حب الشباب - Adaptive + Morphological")
                        metrics[SkinMetric.Type.BLACKHEADS] = SkinMetric(SkinMetric.Type.BLACKHEADS, blackheadScore, classify(cvScore = blackheadScore), details = "تحليل الرؤوس السوداء - Spots + Texture + Morphology")
                    }
                    LightSpectrum.RED -> {
                        val vascularScore = CVUtils.calibratedScore(redness * 0.6f + vascularComplexity * 0.4f, 0.60f, 0.02f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, vascularScore, classify(cvScore = vascularScore), details = "تحليل الأوعية - Redness + Complexity")
                    }
                    LightSpectrum.BROWN -> {
                        val texture = CVUtils.localBinaryPattern(bitmap, 3)
                        val darkCircleScore = CVUtils.calibratedScore(spots * 0.5f + texture * 0.3f + morphGrad / 100f * 0.2f, 0.38f, 0.005f)
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

    /**
     * Emergency fallback when all 4 engines fail.
     * Uses extremely simple pixel-level analysis with no dependencies on
     * FaceMesh, TFLite, or ML Kit. Just raw bitmap statistics.
     */
    private suspend fun performEmergencyFallback(frames: Map<LightSpectrum, File>): Map<SkinMetric.Type, SkinMetric> = withContext(Dispatchers.Default) {
        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        var decodedCount = 0
        for ((spectrum, file) in frames) {
            Timber.d("Emergency fallback: checking ${spectrum.name} -> ${file.name} (exists=${file.exists()}, size=${if(file.exists()) file.length() else 0})")
            if (!file.exists()) continue
            val bitmap = try {
                CVUtils.decodeSampled(file, 512)
            } catch (e: Exception) {
                Timber.e(e, "Emergency fallback: decode failed for ${file.name}")
                null
            } ?: continue
            decodedCount++
            try {
                val width = bitmap.width
                val height = bitmap.height
                val totalPixels = width * height
                var rSum = 0L; var gSum = 0L; var bSum = 0L
                var brightnessSum = 0L
                val sampleStep = maxOf(1, totalPixels / 10000)
                var count = 0
                for (y in 0 until height step sampleStep) {
                    for (x in 0 until width step sampleStep) {
                        val px = bitmap.getPixel(x, y)
                        rSum += android.graphics.Color.red(px)
                        gSum += android.graphics.Color.green(px)
                        bSum += android.graphics.Color.blue(px)
                        brightnessSum += (android.graphics.Color.red(px) + android.graphics.Color.green(px) + android.graphics.Color.blue(px)) / 3
                        count++
                    }
                }
                if (count == 0) { bitmap.recycle(); continue }
                val avgR = rSum.toFloat() / count
                val avgG = gSum.toFloat() / count
                val avgB = bSum.toFloat() / count
                val avgBrightness = brightnessSum.toFloat() / count / 255f

                when (spectrum) {
                    LightSpectrum.WHITE -> {
                        val texScore = CVUtils.calibratedScore(avgBrightness * 80f, 50f, 5f)
                        val toneScore = CVUtils.calibratedScore((avgR + avgG + avgB) / 3f / 255f * 100f, 25f, 2f)
                        metrics[SkinMetric.Type.TEXTURE] = SkinMetric(SkinMetric.Type.TEXTURE, texScore, classify(texScore), details = "تحليل أساسي - الضوء الأبيض")
                        metrics[SkinMetric.Type.SKIN_TONE] = SkinMetric(SkinMetric.Type.SKIN_TONE, toneScore, classify(toneScore), details = "تحليل لون أساسي - الضوء الأبيض")
                    }
                    LightSpectrum.UV365 -> {
                        val uvScore = CVUtils.calibratedScore(avgBrightness * 100f, 0.40f, 0.005f)
                        metrics[SkinMetric.Type.UV_SPOTS] = SkinMetric(SkinMetric.Type.UV_SPOTS, uvScore, classify(uvScore), details = "تحليل أساسي UV")
                    }
                    LightSpectrum.POL_P -> {
                        val redness = avgR / maxOf(avgG, avgB, 1f)
                        val vScore = CVUtils.calibratedScore(redness * 20f, 0.60f, 0.02f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, vScore, classify(vScore), details = "تحليل أساسي - قطبي متقاطع")
                    }
                    LightSpectrum.POL_N -> {
                        val contrast = (maxOf(avgR, avgG, avgB) - minOf(avgR, avgG, avgB)).toFloat() / 255f
                        val wScore = CVUtils.calibratedScore(contrast * 50f, 0.40f, 0.003f)
                        metrics[SkinMetric.Type.WRINKLES] = SkinMetric(SkinMetric.Type.WRINKLES, wScore, classify(wScore), details = "تحليل أساسي - قطبي موازي")
                    }
                    LightSpectrum.WOODS -> {
                        metrics[SkinMetric.Type.MOISTURE] = SkinMetric(SkinMetric.Type.MOISTURE, CVUtils.calibratedScoreInverted(avgBrightness * 85f, 0.05f, 0.85f), classify(avgBrightness * 85f), details = "تحليل أساسي - وودز")
                    }
                    LightSpectrum.BLUE -> {
                        val bBlue = avgB.toFloat() / 255f
                        metrics[SkinMetric.Type.SEBUM] = SkinMetric(SkinMetric.Type.SEBUM, CVUtils.calibratedScoreInverted(bBlue * 80f, 0.15f, 0.55f), classify(bBlue * 80f), details = "تحليل أساسي - أزرق")
                    }
                    LightSpectrum.RED -> {
                        val redness = avgR / maxOf(avgG, avgB, 1f)
                        metrics[SkinMetric.Type.VASCULAR] = SkinMetric(SkinMetric.Type.VASCULAR, CVUtils.calibratedScore(redness * 20f, 0.60f, 0.02f), classify(redness * 20f), details = "تحليل أساسي - أحمر")
                    }
                    LightSpectrum.BROWN -> {
                        metrics[SkinMetric.Type.DARK_CIRCLES] = SkinMetric(SkinMetric.Type.DARK_CIRCLES, CVUtils.calibratedScore(avgBrightness * 70f, 0.38f, 0.005f), classify(avgBrightness * 70f), details = "تحليل أساسي - بني")
                    }
                    else -> {}
                }
            } catch (e: Exception) {
                Timber.e(e, "Emergency fallback: analysis failed for ${spectrum.name}")
            }
            bitmap.recycle()
        }
        Timber.i("Emergency fallback: decoded $decodedCount frames, produced ${metrics.size} metrics")
        metrics
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
        val dcimDir = File(android.os.Environment.getExternalStoragePublicDirectory(android.os.Environment.DIRECTORY_DCIM), "Jenincare/$id")
        val internalDir = File(context.filesDir, "captures/$id")
        if (dcimDir.exists()) dcimDir.deleteRecursively()
        if (internalDir.exists()) internalDir.deleteRecursively()
    }

    override suspend fun getReportCount(): Int {
        return reportDao.getReportCount()
    }

    override fun getCapturedImages(reportId: String): Map<LightSpectrum, File> {
        // Try DCIM first, then internal storage fallback
        val dcimDir = File(android.os.Environment.getExternalStoragePublicDirectory(android.os.Environment.DIRECTORY_DCIM), "Jenincare/$reportId")
        val internalDir = File(context.filesDir, "captures/$reportId")
        val captureDir = if (dcimDir.exists()) dcimDir else internalDir
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

    override fun getReportsSince(sinceTimestamp: Long): Flow<List<SkinReportEntity>> {
        return reportDao.getReportsSince(sinceTimestamp)
    }
}
