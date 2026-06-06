package com.ebtikar.skinanalyzer.ui.report

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.serialization.builtins.ListSerializer
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
    private val repository: SkinAnalysisRepository
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

    private var currentReportId: String? = null

    fun loadReport(reportId: String) {
        currentReportId = reportId
        viewModelScope.launch {
            val entity = repository.getReport(reportId)

            if (entity != null) {
                populateFromEntity(entity)
                _capturedImages.value = repository.getCapturedImages(reportId)
            } else {
                val dateFormat = SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault())
                _reportDate.value = dateFormat.format(Date())
                _providerName.value = "Local_TFLite_Engine"
                _analysisTime.value = 1200L

                val sampleMetrics = generateSampleMetrics()
                _metrics.value = sampleMetrics
                _overallScore.value = sampleMetrics.map { it.score }.average().toFloat()
            }
        }
    }

    private fun populateFromEntity(entity: SkinReportEntity) {
        val dateFormat = SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault())
        _reportDate.value = dateFormat.format(Date(entity.timestamp))
        _providerName.value = entity.providerName
        _analysisTime.value = entity.executionTimeMs
        _overallScore.value = entity.overallScore

        try {
            val serializer = ListSerializer(SkinMetric.serializer())
            _metrics.value = json.decodeFromString(serializer, entity.metricsJson)
        } catch (e: Exception) {
            Timber.e(e, "Failed to parse metrics JSON")
            _metrics.value = generateSampleMetrics()
        }
    }

    fun shareReport() {
        Timber.i("Sharing report: $currentReportId")
    }

    fun saveReport() {
        Timber.i("Saving report PDF: $currentReportId")
    }

    private fun generateSampleMetrics(): List<SkinMetric> {
        return SkinMetric.ALL_TYPES.map { type ->
            SkinMetric(
                type = type,
                score = (50..95).random().toFloat(),
                severity = MetricSeverity.GOOD,
                details = "Sample analysis data"
            )
        }
    }
}
