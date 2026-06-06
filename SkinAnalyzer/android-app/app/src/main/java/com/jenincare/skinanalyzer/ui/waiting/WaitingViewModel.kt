package com.jenincare.skinanalyzer.ui.waiting

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.domain.model.ScanStatus
import com.jenincare.skinanalyzer.domain.usecase.UnlockScanUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class WaitingUiState(
    val isLoading: Boolean = true,
    val scanStatus: ScanStatus = ScanStatus.PENDING,
    val statusTextAr: String = "التقرير قيد المراجعة الطبية",
    val isPolling: Boolean = true,
    val pollCount: Int = 0,
    val pin: String = "",
    val showPinEntry: Boolean = false,
    val pinError: String? = null,
    val isUnlocking: Boolean = false,
    val scanId: String = ""
)

@HiltViewModel
class WaitingViewModel @Inject constructor(
    private val scanApiService: ScanApiService,
    private val unlockScanUseCase: UnlockScanUseCase
) : ViewModel() {

    private val _uiState = MutableStateFlow(WaitingUiState())
    val uiState: StateFlow<WaitingUiState> = _uiState.asStateFlow()

    private val _reportReady = MutableSharedFlow<String>()
    val reportReady = _reportReady.asSharedFlow()

    private var pollingJob: Job? = null
    private var backoffMultiplier = 1

    fun startPolling(scanId: String) {
        android.util.Log.d("WaitingVM", "Starting polling for scan: $scanId")
        _uiState.value = _uiState.value.copy(scanId = scanId)
        pollingJob?.cancel()
        pollingJob = viewModelScope.launch {
            while (true) {
                try {
                    android.util.Log.d("WaitingVM", "Polling status for scan: $scanId")
                    val response = scanApiService.getScanStatus(scanId)
                    android.util.Log.d("WaitingVM", "Status response: ${response.code()} ${response.message()}")
                    if (response.isSuccessful) {
                        val body = response.body()
                        val status = body?.status ?: "pending"
                        android.util.Log.d("WaitingVM", "Scan status: $status, scanId=${body?.scanId}")
                        val scanStatus = ScanStatus.fromString(status)

                        _uiState.value = _uiState.value.copy(
                            isLoading = false,
                            scanStatus = scanStatus,
                            pollCount = _uiState.value.pollCount + 1,
                            statusTextAr = getStatusText(scanStatus)
                        )

                        when (scanStatus) {
                            ScanStatus.APPROVED -> {
                                android.util.Log.d("WaitingVM", "Scan APPROVED, navigating to report")
                                _reportReady.emit(scanId)
                                return@launch
                            }
                            ScanStatus.REJECTED -> {
                                android.util.Log.w("WaitingVM", "Scan REJECTED")
                                _uiState.value = _uiState.value.copy(
                                    isPolling = false,
                                    statusTextAr = "عذراً، تم رفض التقرير. يرجى إعادة الفحص"
                                )
                                return@launch
                            }
                            else -> {}
                        }

                        backoffMultiplier = 1
                        delay(getPollInterval())
                    } else {
                        val errorBody = response.errorBody()?.string()
                        android.util.Log.w("WaitingVM", "Status request failed: ${response.code()} $errorBody")
                        backoffMultiplier = minOf(backoffMultiplier * 2, 12)
                        delay(getPollInterval())
                    }
                } catch (e: Exception) {
                    android.util.Log.e("WaitingVM", "Polling exception: ${e.message}", e)
                    backoffMultiplier = minOf(backoffMultiplier * 2, 12)
                    delay(getPollInterval())
                }
            }
        }
    }

    fun showPinEntry() {
        _uiState.value = _uiState.value.copy(
            showPinEntry = true,
            pinError = null,
            pin = ""
        )
    }

    fun hidePinEntry() {
        _uiState.value = _uiState.value.copy(
            showPinEntry = false,
            pin = "",
            pinError = null
        )
    }

    fun updatePin(pin: String) {
        if (pin.length <= 4) {
            _uiState.value = _uiState.value.copy(pin = pin, pinError = null)
        }
    }

    fun submitPin() {
        val currentPin = _uiState.value.pin
        val scanId = _uiState.value.scanId

        if (currentPin.length != 4) {
            _uiState.value = _uiState.value.copy(pinError = "يجب إدخال 4 أرقام")
            return
        }

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isUnlocking = true, pinError = null)

            val result = unlockScanUseCase(scanId, currentPin)
            result.fold(
                onSuccess = {
                    _reportReady.emit(scanId)
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isUnlocking = false,
                        pinError = error.message ?: "رمز PIN غير صحيح",
                        pin = ""
                    )
                }
            )
        }
    }

    fun stopPolling() {
        pollingJob?.cancel()
        pollingJob = null
    }

    private fun getPollInterval(): Long {
        val baseInterval = 5000L
        return baseInterval * backoffMultiplier
    }

    private fun getStatusText(status: ScanStatus): String = when (status) {
        ScanStatus.PENDING -> "التقرير قيد المراجعة الطبية من قبل الفريق المختص في Jenin Care"
        ScanStatus.IN_REVIEW -> "جاري تحليل الصورة بواسطة خبراء البشرة"
        ScanStatus.APPROVED -> "تمت الموافقة على التقرير"
        ScanStatus.REJECTED -> "تم رفض التقرير"
    }

    override fun onCleared() {
        super.onCleared()
        stopPolling()
    }
}
