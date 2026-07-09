package com.ebtikar.skinanalyzer.ui.timeline

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.model.SkinMetric
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import timber.log.Timber
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale
import javax.inject.Inject

data class TimelinePoint(
    val reportId: String,
    val timestamp: Long,
    val dateLabel: String,
    val overallScore: Float,
    val metricScores: Map<SkinMetric.Type, Float>
)

@HiltViewModel
class TimelineViewModel @Inject constructor(
    private val repository: SkinAnalysisRepository
) : ViewModel() {

    private val _timelinePoints = MutableStateFlow<List<TimelinePoint>>(emptyList())
    val timelinePoints: StateFlow<List<TimelinePoint>> = _timelinePoints.asStateFlow()

    private val _selectedDays = MutableStateFlow(0)
    val selectedDays: StateFlow<Int> = _selectedDays.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())

    fun loadTimeline(days: Int = 0) {
        _selectedDays.value = days
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val sinceTimestamp = if (days > 0) {
                    Calendar.getInstance().apply { add(Calendar.DAY_OF_YEAR, -days) }.timeInMillis
                } else {
                    0L
                }
                val reports = repository.getReportsSince(sinceTimestamp).first()
                val points = reports.map { entity ->
                    val metrics = parseMetrics(entity.metricsJson)
                    TimelinePoint(
                        reportId = entity.id,
                        timestamp = entity.timestamp,
                        dateLabel = dateFormat.format(entity.timestamp),
                        overallScore = entity.overallScore,
                        metricScores = metrics
                    )
                }
                _timelinePoints.value = points
                Timber.i("Timeline loaded: ${points.size} points, days=$days")
            } catch (e: Exception) {
                Timber.e(e, "Failed to load timeline")
                _timelinePoints.value = emptyList()
            } finally {
                _isLoading.value = false
            }
        }
    }

    private fun parseMetrics(metricsJson: String): Map<SkinMetric.Type, Float> {
        return try {
            val list = kotlinx.serialization.json.Json.decodeFromString<List<SkinMetric>>(metricsJson)
            list.associate { it.type to it.score }
        } catch (e: Exception) {
            Timber.w(e, "Failed to parse metrics JSON for timeline")
            emptyMap()
        }
    }
}
