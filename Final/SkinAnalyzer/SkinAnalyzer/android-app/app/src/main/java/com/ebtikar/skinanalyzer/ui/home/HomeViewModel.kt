package com.ebtikar.skinanalyzer.ui.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val repository: SkinAnalysisRepository,
    private val networkMonitor: NetworkMonitor,
    private val serialBusManager: SerialBusManager,
    private val preferencesManager: PreferencesManager
) : ViewModel() {

    private val _connectionStatus = MutableStateFlow("Checking...")
    val connectionStatus: StateFlow<String> = _connectionStatus.asStateFlow()

    private val _hardwareStatus = MutableStateFlow("Initializing...")
    val hardwareStatus: StateFlow<String> = _hardwareStatus.asStateFlow()

    private val _analysisMode = MutableStateFlow(Constants.ANALYSIS_AUTO)
    val analysisMode: StateFlow<String> = _analysisMode.asStateFlow()

    private val _diagnosisMode = MutableStateFlow(Constants.DIAGNOSIS_ALL)
    val diagnosisMode: StateFlow<String> = _diagnosisMode.asStateFlow()

    private val _historyCount = MutableStateFlow(0)
    val historyCount: StateFlow<Int> = _historyCount.asStateFlow()

    private val _todayCount = MutableStateFlow(0)
    val todayCount: StateFlow<Int> = _todayCount.asStateFlow()

    val recentReports: StateFlow<List<SkinReportEntity>> = repository.getRecentReports(5)
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), emptyList())

    init {
        checkSystemStatus()
        loadHistory()
        loadDiagnosisMode()
    }

    private fun checkSystemStatus() {
        networkMonitor.isOnlineFlow.onEach { isOnline ->
            _connectionStatus.value = if (isOnline) "Connected" else "Disconnected"
        }.launchIn(viewModelScope)

        if (serialBusManager.isConnected) {
            _hardwareStatus.value = "Ready"
        } else {
            _hardwareStatus.value = "No USB Device"
        }
    }

    fun loadHistory() {
        viewModelScope.launch {
            _historyCount.value = repository.getReportCount()
        }
    }

    fun setAnalysisMode(mode: String) {
        _analysisMode.value = mode
    }

    fun setDiagnosisMode(mode: String) {
        _diagnosisMode.value = mode
        viewModelScope.launch {
            preferencesManager.setDiagnosisMode(mode)
        }
    }

    private fun loadDiagnosisMode() {
        viewModelScope.launch {
            preferencesManager.diagnosisModeFlow.collect { mode ->
                _diagnosisMode.value = mode
            }
        }
    }
}
