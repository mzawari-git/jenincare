package com.jenincare.skinanalyzer.ui.report

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.remote.dto.AddToCartRequest
import com.jenincare.skinanalyzer.domain.model.Defect
import com.jenincare.skinanalyzer.domain.model.HeatmapPoint
import com.jenincare.skinanalyzer.domain.model.RadarMetric
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.domain.usecase.GetScanReportUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class ReportUiState(
    val isLoading: Boolean = true,
    val report: ScanReport? = null,
    val error: String? = null,
    val selectedDefectId: String? = null,
    val isSharing: Boolean = false,
    val cartMessage: String? = null
)

@HiltViewModel
class ReportViewModel @Inject constructor(
    private val getScanReportUseCase: GetScanReportUseCase,
    private val apiService: ApiService
) : ViewModel() {

    private val _uiState = MutableStateFlow(ReportUiState())
    val uiState: StateFlow<ReportUiState> = _uiState.asStateFlow()

    fun loadReport(scanId: String) {
        android.util.Log.d("ReportVM", "Loading report for scan: $scanId")
        viewModelScope.launch {
            _uiState.value = ReportUiState(isLoading = true)

            val result = getScanReportUseCase(scanId)
            android.util.Log.d("ReportVM", "Report result: ${result.isSuccess}")
            result.fold(
                onSuccess = { report ->
                    android.util.Log.d("ReportVM", "Report loaded: defects=${report.defects.size}, score=${report.scan.overallScore}")
                    _uiState.value = ReportUiState(
                        isLoading = false,
                        report = report
                    )
                },
                onFailure = { error ->
                    android.util.Log.e("ReportVM", "Report load failed: ${error.message}", error)
                    _uiState.value = ReportUiState(
                        isLoading = false,
                        error = error.message ?: "فشل تحميل التقرير"
                    )
                }
            )
        }
    }

    fun addToCart(scanId: String, productId: String) {
        viewModelScope.launch {
            try {
                val response = apiService.addToCart(
                    scanId = scanId,
                    request = AddToCartRequest(productId = productId)
                )
                if (response.isSuccessful) {
                    _uiState.value = _uiState.value.copy(cartMessage = "تمت إضافة المنتج إلى السلة")
                } else {
                    _uiState.value = _uiState.value.copy(cartMessage = "فشل الإضافة إلى السلة")
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(cartMessage = "خطأ في الاتصال")
            }
        }
    }

    fun clearCartMessage() {
        _uiState.value = _uiState.value.copy(cartMessage = null)
    }

    fun selectDefect(defectId: String?) {
        _uiState.value = _uiState.value.copy(selectedDefectId = defectId)
    }

    fun retry(scanId: String) {
        loadReport(scanId)
    }
}
