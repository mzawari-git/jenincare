package com.ebtikar.skinanalyzer.ui.comparison

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.serialization.builtins.ListSerializer
import kotlinx.serialization.json.Json
import timber.log.Timber
import javax.inject.Inject

@HiltViewModel
class ComparisonViewModel @Inject constructor(
    private val repository: SkinAnalysisRepository
) : ViewModel() {

    private val json = Json { ignoreUnknownKeys = true }

    data class ComparisonResult(
        val beforeScore: Float,
        val afterScore: Float,
        val beforeMetrics: List<SkinMetric>,
        val afterMetrics: List<SkinMetric>,
        val deltas: Map<SkinMetric.Type, Float>
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

                val deltas = mutableMapOf<SkinMetric.Type, Float>()
                for (type in SkinMetric.Type.entries) {
                    val before = beforeMetrics.find { it.type == type }?.score ?: 0f
                    val after = afterMetrics.find { it.type == type }?.score ?: 0f
                    deltas[type] = after - before
                }

                _comparisonData.value = ComparisonResult(
                    beforeScore = beforeEntity.overallScore,
                    afterScore = afterEntity.overallScore,
                    beforeMetrics = beforeMetrics,
                    afterMetrics = afterMetrics,
                    deltas = deltas
                )
            } catch (e: Exception) {
                Timber.e(e, "Failed to compare reports")
            }
        }
    }
}
