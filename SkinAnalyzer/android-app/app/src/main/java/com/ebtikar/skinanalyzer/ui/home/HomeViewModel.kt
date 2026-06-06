package com.ebtikar.skinanalyzer.ui.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val repository: SkinAnalysisRepository,
    private val networkMonitor: NetworkMonitor,
    private val serialBusManager: SerialBusManager
) : ViewModel() {

    private val _connectionStatus = MutableStateFlow("Checking...")
    val connectionStatus: StateFlow<String> = _connectionStatus.asStateFlow()

    private val _hardwareStatus = MutableStateFlow("Initializing...")
    val hardwareStatus: StateFlow<String> = _hardwareStatus.asStateFlow()

    private val _analysisMode = MutableStateFlow(Constants.ANALYSIS_AUTO)
    val analysisMode: StateFlow<String> = _analysisMode.asStateFlow()

    private val _historyCount = MutableStateFlow(0)
    val historyCount: StateFlow<Int> = _historyCount.asStateFlow()

    init {
        checkSystemStatus()
        loadHistory()
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
}
