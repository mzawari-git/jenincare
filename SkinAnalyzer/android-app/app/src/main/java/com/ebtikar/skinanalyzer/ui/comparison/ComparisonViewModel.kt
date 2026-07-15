package com.ebtikar.skinanalyzer.ui.comparison

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.arabicName
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.serialization.builtins.ListSerializer
import kotlinx.serialization.json.Json
import timber.log.Timber
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import javax.inject.Inject

@HiltViewModel
class ComparisonViewModel @Inject constructor(
    private val repository: SkinAnalysisRepository
) : ViewModel() {

    private val json = Json { ignoreUnknownKeys = true }

    data class MetricDelta(
        val type: SkinMetric.Type,
        val beforeScore: Float,
        val afterScore: Float,
        val delta: Float,
        val beforeSeverity: com.ebtikar.skinanalyzer.model.MetricSeverity,
        val afterSeverity: com.ebtikar.skinanalyzer.model.MetricSeverity
    )

    data class ComparisonResult(
        val beforeScore: Float,
        val afterScore: Float,
        val beforeMetrics: List<SkinMetric>,
        val afterMetrics: List<SkinMetric>,
        val deltas: Map<SkinMetric.Type, Float>,
        val beforeDate: String,
        val afterDate: String,
        val radarLabels: List<String>,
        val beforeRadarValues: List<Float>,
        val afterRadarValues: List<Float>,
        val metricDeltas: List<MetricDelta>
    )

    private val _comparisonData = MutableStateFlow<ComparisonResult?>(null)
    val comparisonData: StateFlow<ComparisonResult?> = _comparisonData.asStateFlow()

    fun compareReports(beforeId: String, afterId: String) {
        viewModelScope.launch {
            val beforeEntity = repository.getReport(beforeId) ?: return@launch
            val afterEntity = repository.getReport(afterId) ?: return@launch

            try {
                val serializer = ListSerializer(SkinMetric.serializer())
                val beforeMetrics = json.decodeFromString(serializer, beforeEntity.metricsJson)
                val afterMetrics = json.decodeFromString(serializer, afterEntity.metricsJson)

                val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                val beforeDate = dateFormat.format(Date(beforeEntity.timestamp))
                val afterDate = dateFormat.format(Date(afterEntity.timestamp))

                val deltas = mutableMapOf<SkinMetric.Type, Float>()
                val metricDeltas = mutableListOf<MetricDelta>()
                for (type in SkinMetric.Type.entries) {
                    val before = beforeMetrics.find { it.type == type }?.score ?: 0f
                    val after = afterMetrics.find { it.type == type }?.score ?: 0f
                    deltas[type] = after - before
                    metricDeltas.add(
                        MetricDelta(
                            type = type,
                            beforeScore = before,
                            afterScore = after,
                            delta = after - before,
                            beforeSeverity = beforeMetrics.find { it.type == type }?.severity
                                ?: com.ebtikar.skinanalyzer.model.MetricSeverity.FAIR,
                            afterSeverity = afterMetrics.find { it.type == type }?.severity
                                ?: com.ebtikar.skinanalyzer.model.MetricSeverity.FAIR
                        )
                    )
                }

                val radarLabels = SkinMetric.ALL_TYPES.map { it.arabicName() }
                val beforeRadar = SkinMetric.ALL_TYPES.map { type ->
                    beforeMetrics.find { it.type == type }?.score ?: 0f
                }
                val afterRadar = SkinMetric.ALL_TYPES.map { type ->
                    afterMetrics.find { it.type == type }?.score ?: 0f
                }

                _comparisonData.value = ComparisonResult(
                    beforeScore = beforeEntity.overallScore,
                    afterScore = afterEntity.overallScore,
                    beforeMetrics = beforeMetrics,
                    afterMetrics = afterMetrics,
                    deltas = deltas,
                    beforeDate = beforeDate,
                    afterDate = afterDate,
                    radarLabels = radarLabels,
                    beforeRadarValues = beforeRadar,
                    afterRadarValues = afterRadar,
                    metricDeltas = metricDeltas.sortedBy { it.delta }
                )
            } catch (e: Exception) {
                Timber.e(e, "Failed to compare reports")
            }
        }
    }

}
