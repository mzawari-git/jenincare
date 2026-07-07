package com.ebtikar.skinanalyzer.ui.report

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.HeatmapPoint
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.ProductRecommendation
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinProfile
import com.ebtikar.skinanalyzer.util.PdfReportGenerator
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import kotlinx.serialization.builtins.ListSerializer
import kotlinx.serialization.builtins.serializer
import kotlinx.serialization.json.Json
import timber.log.Timber
import java.io.File
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import javax.inject.Inject

@HiltViewModel
class ReportViewModel @Inject constructor(
    @ApplicationContext private val context: Context,
    private val repository: SkinAnalysisRepository,
    private val pdfReportGenerator: PdfReportGenerator
) : ViewModel() {

    private val json = Json { ignoreUnknownKeys = true }

    private val _overallScore = MutableStateFlow(0f)
    val overallScore: StateFlow<Float> = _overallScore.asStateFlow()

    private val _metrics = MutableStateFlow<List<SkinMetric>>(emptyList())
    val metrics: StateFlow<List<SkinMetric>> = _metrics.asStateFlow()

    private val _providerName = MutableStateFlow("")
    val providerName: StateFlow<String> = _providerName.asStateFlow()

    private val _analysisTime = MutableStateFlow(0L)
    val analysisTime: StateFlow<Long> = _analysisTime.asStateFlow()

    private val _reportDate = MutableStateFlow("")
    val reportDate: StateFlow<String> = _reportDate.asStateFlow()

    private val _capturedImages = MutableStateFlow<Map<LightSpectrum, File>>(emptyMap())
    val capturedImages: StateFlow<Map<LightSpectrum, File>> = _capturedImages.asStateFlow()

    private val _aiAnalysisText = MutableStateFlow("")
    val aiAnalysisText: StateFlow<String> = _aiAnalysisText.asStateFlow()

    private val _expertTips = MutableStateFlow<List<String>>(emptyList())
    val expertTips: StateFlow<List<String>> = _expertTips.asStateFlow()

    private val _productRecommendations = MutableStateFlow<List<ProductRecommendation>>(emptyList())
    val productRecommendations: StateFlow<List<ProductRecommendation>> = _productRecommendations.asStateFlow()

    private val _skinProfile = MutableStateFlow(SkinProfile())
    val skinProfile: StateFlow<SkinProfile> = _skinProfile.asStateFlow()

    private val _confidence = MutableStateFlow(0.85f)
    val confidence: StateFlow<Float> = _confidence.asStateFlow()

    private val _radarValues = MutableStateFlow<List<Float>>(emptyList())
    val radarValues: StateFlow<List<Float>> = _radarValues.asStateFlow()

    private val _radarLabels = MutableStateFlow<List<String>>(emptyList())
    val radarLabels: StateFlow<List<String>> = _radarLabels.asStateFlow()

    private val _topConcerns = MutableStateFlow<List<SkinMetric>>(emptyList())
    val topConcerns: StateFlow<List<SkinMetric>> = _topConcerns.asStateFlow()

    private val _heatmapPoints = MutableStateFlow<List<HeatmapPoint>>(emptyList())
    val heatmapPoints: StateFlow<List<HeatmapPoint>> = _heatmapPoints.asStateFlow()

    private val _excellentMetrics = MutableStateFlow<List<SkinMetric>>(emptyList())
    val excellentMetrics: StateFlow<List<SkinMetric>> = _excellentMetrics.asStateFlow()

    private val _needsAttentionMetrics = MutableStateFlow<List<SkinMetric>>(emptyList())
    val needsAttentionMetrics: StateFlow<List<SkinMetric>> = _needsAttentionMetrics.asStateFlow()

    private var currentReportId: String? = null

    private val _isReportMissing = MutableStateFlow(false)
    val isReportMissing: StateFlow<Boolean> = _isReportMissing.asStateFlow()

    fun loadReport(reportId: String) {
        currentReportId = reportId
        viewModelScope.launch {
            val entity = repository.getReport(reportId)

            if (entity != null) {
                _isReportMissing.value = false
                populateFromEntity(entity)
                _capturedImages.value = repository.getCapturedImages(reportId)
            } else {
                _isReportMissing.value = true
                val dateFormat = SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault())
                _reportDate.value = dateFormat.format(Date())
                _providerName.value = ""
                _analysisTime.value = 0L

                val zeroMetrics = SkinMetric.ALL_TYPES.map { type ->
                    SkinMetric(type = type, score = 0f, severity = MetricSeverity.CRITICAL, details = "")
                }
                _metrics.value = zeroMetrics
                _overallScore.value = 0f
                _aiAnalysisText.value = "التقرير غير موجود — يرجى إجراء تحليل جديد"
                _expertTips.value = emptyList()
            }
        }
    }

    private fun populateFromEntity(entity: SkinReportEntity) {
        val dateFormat = SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault())
        _reportDate.value = dateFormat.format(Date(entity.timestamp))
        _providerName.value = entity.providerName
        _analysisTime.value = entity.executionTimeMs
        _overallScore.value = entity.overallScore
        _aiAnalysisText.value = entity.aiAnalysisText
        _confidence.value = entity.confidence

        try {
            val metricSerializer = ListSerializer(SkinMetric.serializer())
            val metricsList = json.decodeFromString(metricSerializer, entity.metricsJson)
            _metrics.value = metricsList

            val metricsMap = metricsList.associateBy { it.type }
            _radarValues.value = metricsList.map { it.score }
            _radarLabels.value = metricsList.map { getArabicName(it.type) }
            _topConcerns.value = metricsList.sortedBy { it.score }.take(3)
            _excellentMetrics.value = metricsList.filter { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }
            _needsAttentionMetrics.value = metricsList.filter { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse metrics JSON")
            val sampleMetrics = generateSampleMetrics()
            _metrics.value = sampleMetrics
        }

        try {
            val tipsSerializer = ListSerializer(String.serializer())
            _expertTips.value = json.decodeFromString(tipsSerializer, entity.expertTipsJson)
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse tips JSON")
        }

        try {
            val productsSerializer = ListSerializer(ProductRecommendation.serializer())
            _productRecommendations.value = json.decodeFromString(productsSerializer, entity.productsJson)
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse products JSON")
        }

        try {
            _skinProfile.value = json.decodeFromString(SkinProfile.serializer(), entity.skinProfileJson)
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse skin profile JSON")
        }

        try {
            val heatmapSerializer = ListSerializer(HeatmapPoint.serializer())
            _heatmapPoints.value = json.decodeFromString(heatmapSerializer, entity.heatmapPointsJson)
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse heatmap JSON")
        }
    }

    fun shareReport() {
        val reportId = currentReportId ?: return
        viewModelScope.launch {
            val entity = repository.getReport(reportId) ?: return@launch
            val report = entity.toReport()
            val outputDir = File(context.cacheDir, "pdf_reports")
            val pdfFile = pdfReportGenerator.generate(context, report, outputDir, _capturedImages.value)
            if (pdfFile != null) {
                val uri = androidx.core.content.FileProvider.getUriForFile(
                    context, "${context.packageName}.fileprovider", pdfFile
                )
                val shareIntent = android.content.Intent(android.content.Intent.ACTION_SEND).apply {
                    type = "application/pdf"
                    putExtra(android.content.Intent.EXTRA_STREAM, uri)
                    addFlags(android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION or android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                context.startActivity(android.content.Intent.createChooser(shareIntent, "مشاركة التقرير"))
                Timber.i("PDF shared: ${pdfFile.absolutePath}")
            }
        }
    }

    fun saveReport() {
        val reportId = currentReportId ?: return
        viewModelScope.launch {
            val entity = repository.getReport(reportId) ?: return@launch
            val report = entity.toReport()
            val outputDir = File(context.filesDir, "pdf_reports")
            val pdfFile = pdfReportGenerator.generate(context, report, outputDir, _capturedImages.value)
            if (pdfFile != null) {
                Timber.i("PDF saved: ${pdfFile.absolutePath}")
            }
        }
    }

    suspend fun exportCsv(): File? = withContext(Dispatchers.IO) {
        val reportId = currentReportId ?: return@withContext null
        try {
            val entity = repository.getReport(reportId) ?: return@withContext null
            val serializer = ListSerializer(SkinMetric.serializer())
            val metrics = json.decodeFromString(serializer, entity.metricsJson)
            val outputDir = File(context.filesDir, "exports")
            outputDir.mkdirs()
            val csvFile = File(outputDir, "report_${reportId.take(8)}.csv")
            csvFile.bufferedWriter().use { writer ->
                writer.write("Metric,Score,Severity,Details\n")
                for (m in metrics) {
                    writer.write("\"${getArabicName(m.type)}\",${m.score},${m.severity},\"${m.details}\"\n")
                }
                writer.write("\nOverall Score,${entity.overallScore}\n")
                writer.write("Provider,${entity.providerName}\n")
                writer.write("Execution Time (ms),${entity.executionTimeMs}\n")
            }
            Timber.i("CSV exported: ${csvFile.absolutePath}")
            csvFile
        } catch (e: Exception) {
            Timber.e(e, "CSV export failed")
            null
        }
    }

    suspend fun exportJson(): File? = withContext(Dispatchers.IO) {
        val reportId = currentReportId ?: return@withContext null
        try {
            val entity = repository.getReport(reportId) ?: return@withContext null
            val outputDir = File(context.filesDir, "exports")
            outputDir.mkdirs()
            val jsonFile = File(outputDir, "report_${reportId.take(8)}.json")
            val report = entity.toReport()
            val exportJson = Json { prettyPrint = true; ignoreUnknownKeys = true }
            jsonFile.writeText(exportJson.encodeToString(SkinAnalysisReport.serializer(), report))
            Timber.i("JSON exported: ${jsonFile.absolutePath}")
            jsonFile
        } catch (e: Exception) {
            Timber.e(e, "JSON export failed")
            null
        }
    }

    private fun generateSampleMetrics(): List<SkinMetric> {
        return SkinMetric.ALL_TYPES.map { type ->
            SkinMetric(
                type = type,
                score = (50..95).random().toFloat(),
                severity = MetricSeverity.GOOD,
                details = "Sample analysis data",
                recommendations = listOf("حافظي على روتينك الحالي")
            )
        }
    }

    private fun SkinReportEntity.toReport(): SkinAnalysisReport {
        val metricSerializer = ListSerializer(SkinMetric.serializer())
        val metricsList = try {
            json.decodeFromString(metricSerializer, metricsJson)
        } catch (e: Exception) {
            generateSampleMetrics()
        }
        val tipsSerializer = ListSerializer(String.serializer())
        val tipsList = try {
            json.decodeFromString(tipsSerializer, expertTipsJson)
        } catch (e: Exception) {
            emptyList()
        }
        val productsSerializer = ListSerializer(ProductRecommendation.serializer())
        val productsList = try {
            json.decodeFromString(productsSerializer, productsJson)
        } catch (e: Exception) {
            emptyList()
        }
        val profile = try {
            json.decodeFromString(SkinProfile.serializer(), skinProfileJson)
        } catch (e: Exception) {
            SkinProfile()
        }
        return SkinAnalysisReport(
            id = id,
            timestamp = timestamp,
            providerName = providerName,
            overallScore = overallScore,
            metrics = metricsList,
            executionTimeMs = executionTimeMs,
            deviceModel = deviceModel,
            notes = notes,
            aiAnalysisText = aiAnalysisText,
            aiAnalysisTextAr = aiAnalysisText,
            expertTips = tipsList,
            expertTipsAr = tipsList,
            productRecommendations = productsList,
            skinProfile = profile,
            confidence = confidence,
            scanId = scanId,
            heatmapPoints = try {
                val heatmapSerializer = ListSerializer(HeatmapPoint.serializer())
                json.decodeFromString(heatmapSerializer, heatmapPointsJson)
            } catch (e: Exception) {
                emptyList()
            }
        )
    }

    private fun getArabicName(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع"
        SkinMetric.Type.VASCULAR -> "الأوعية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس"
        SkinMetric.Type.ACNE -> "الحب"
        SkinMetric.Type.SKIN_TONE -> "اللون"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
