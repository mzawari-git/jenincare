package com.ebtikar.skinanalyzer.ui.result

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.Json
import timber.log.Timber
import javax.inject.Inject

@HiltViewModel
class ResultViewModel @Inject constructor(
    @ApplicationContext private val context: Context,
    private val repository: SkinAnalysisRepository
) : ViewModel() {

    private val json = Json { ignoreUnknownKeys = true; encodeDefaults = true }

    private val _overallScore = MutableStateFlow(0f)
    val overallScore: StateFlow<Float> = _overallScore.asStateFlow()

    private val _metrics = MutableStateFlow<List<SkinMetric>>(emptyList())
    val metrics: StateFlow<List<SkinMetric>> = _metrics.asStateFlow()

    fun loadReport(reportId: String) {
        viewModelScope.launch {
            try {
                val entity = withContext(Dispatchers.IO) {
                    repository.getReport(reportId)
                }
                if (entity == null) {
                    Timber.w("Report not found: $reportId")
                    return@launch
                }

                _overallScore.value = entity.overallScore

                val metricsList = try {
                    json.decodeFromString<List<SkinMetric>>(entity.metricsJson)
                } catch (e: Exception) {
                    Timber.w(e, "Failed to parse metrics JSON")
                    emptyList()
                }

                _metrics.value = metricsList
                Timber.i("Result loaded: score=${entity.overallScore}, metrics=${metricsList.size}")
            } catch (e: Exception) {
                Timber.e(e, "Failed to load report for result screen")
            }
        }
    }
}
