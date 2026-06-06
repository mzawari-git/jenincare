package com.jenincare.skinanalyzer.ui.timeline

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.model.Scan
import com.jenincare.skinanalyzer.data.model.RadarMetrics
import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.data.remote.dto.ScanSummaryDto
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.domain.model.ScanStatus
import com.jenincare.skinanalyzer.domain.usecase.GetScanReportUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class TimelineUiState(
    val isLoading: Boolean = true,
    val scans: List<Scan> = emptyList(),
    val selectedScanA: Scan? = null,
    val selectedScanB: Scan? = null,
    val reportA: ScanReport? = null,
    val reportB: ScanReport? = null,
    val isComparing: Boolean = false,
    val error: String? = null,
    val progressData: List<Pair<String, Int>> = emptyList()
)

@HiltViewModel
class TimelineViewModel @Inject constructor(
    private val scanApiService: ScanApiService,
    private val getScanReportUseCase: GetScanReportUseCase
) : ViewModel() {

    private val _uiState = MutableStateFlow(TimelineUiState())
    val uiState: StateFlow<TimelineUiState> = _uiState.asStateFlow()

    fun loadScans() {
        viewModelScope.launch {
            _uiState.value = TimelineUiState(isLoading = true)

            try {
                val response = scanApiService.getScanHistory()
                if (response.isSuccessful) {
                    val scans = response.body()?.scans?.filter {
                        it.status == "approved"
                    }?.sortedByDescending { it.createdAt }?.map { dto ->
                        Scan(
                            id = dto.id,
                            status = ScanStatus.fromString(dto.status),
                            imageUri = dto.imageUrl ?: "",
                            overallHealthScore = dto.overallScore.toFloat(),
                            radarMetrics = RadarMetrics(0f, 0f, 0f, 0f, 0f),
                            heatmapCoordinates = emptyList(),
                            customArabicAnalysis = null,
                            expertFreeTips = emptyList(),
                            recommendedProducts = emptyList(),
                            createdAt = dto.createdAt,
                            approvedAt = dto.reviewedAt
                        )
                    } ?: emptyList()

                    val progressData = scans.map {
                        it.createdAt.take(10) to it.overallHealthScore.toInt()
                    }.reversed()

                    _uiState.value = TimelineUiState(
                        isLoading = false,
                        scans = scans,
                        progressData = progressData
                    )
                } else {
                    _uiState.value = TimelineUiState(
                        isLoading = false,
                        error = "فشل تحميل السجل"
                    )
                }
            } catch (e: Exception) {
                _uiState.value = TimelineUiState(
                    isLoading = false,
                    error = e.message ?: "حدث خطأ"
                )
            }
        }
    }

    fun selectScanA(scan: Scan) {
        _uiState.value = _uiState.value.copy(selectedScanA = scan, isComparing = false, reportA = null)
        if (_uiState.value.selectedScanB != null) {
            loadComparisonReports()
        }
    }

    fun selectScanB(scan: Scan) {
        _uiState.value = _uiState.value.copy(selectedScanB = scan, isComparing = false, reportB = null)
        if (_uiState.value.selectedScanA != null) {
            loadComparisonReports()
        }
    }

    fun compareSelected() {
        if (_uiState.value.selectedScanA != null && _uiState.value.selectedScanB != null) {
            loadComparisonReports()
        }
    }

    private fun loadComparisonReports() {
        val scanA = _uiState.value.selectedScanA ?: return
        val scanB = _uiState.value.selectedScanB ?: return

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isComparing = true)

            val resultA = getScanReportUseCase(scanA.id)
            val resultB = getScanReportUseCase(scanB.id)

            resultA.fold(
                onSuccess = { report -> _uiState.value = _uiState.value.copy(reportA = report) },
                onFailure = {}
            )
            resultB.fold(
                onSuccess = { report -> _uiState.value = _uiState.value.copy(reportB = report) },
                onFailure = {}
            )
        }
    }

    fun clearComparison() {
        _uiState.value = _uiState.value.copy(
            selectedScanA = null,
            selectedScanB = null,
            reportA = null,
            reportB = null,
            isComparing = false
        )
    }
}
