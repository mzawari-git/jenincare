package com.ebtikar.skinanalyzer.ui.calibration

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@HiltViewModel
class CalibrationViewModel @Inject constructor(
    @ApplicationContext private val context: Context,
    private val serialBusManager: SerialBusManager,
    private val spectrumController: SpectrumController,
    private val cameraManager: USBCameraManager,
    private val networkMonitor: NetworkMonitor
) : ViewModel() {

    data class CalibrationResult(
        val status: TestStatus,
        val message: String
    )

    enum class TestStatus { PENDING, RUNNING, PASS, FAIL }

    private val _calibrationResults = MutableStateFlow<Map<String, CalibrationResult>>(emptyMap())
    val calibrationResults: StateFlow<Map<String, CalibrationResult>> = _calibrationResults.asStateFlow()

    private val _isRunning = MutableStateFlow(false)
    val isRunning: StateFlow<Boolean> = _isRunning.asStateFlow()

    fun startCalibration() {
        viewModelScope.launch {
            _isRunning.value = true
            _calibrationResults.value = emptyMap()

            testUSB()
            testNetwork()
            testCamera()
            testSpectrumLights()

            _isRunning.value = false
        }
    }

    private suspend fun testUSB() {
        updateResult("USB Connection", TestStatus.RUNNING, "Testing...")
        delay(500)
        if (serialBusManager.isConnected) {
            updateResult("USB Connection", TestStatus.PASS, "Connected")
        } else {
            updateResult("USB Connection", TestStatus.FAIL, "Not connected")
        }
    }

    private suspend fun testNetwork() {
        updateResult("Network", TestStatus.RUNNING, "Testing...")
        delay(500)
        if (networkMonitor.isOnline()) {
            updateResult("Network", TestStatus.PASS, "Online")
        } else {
            updateResult("Network", TestStatus.FAIL, "Offline")
        }
    }

    private suspend fun testCamera() {
        updateResult("Camera", TestStatus.RUNNING, "Testing...")
        delay(500)
        val cameraId = cameraManager.findBestCamera()
        if (cameraId != null) {
            updateResult("Camera", TestStatus.PASS, "Found: $cameraId")
        } else {
            updateResult("Camera", TestStatus.FAIL, "No camera found")
        }
    }

    private suspend fun testSpectrumLights() {
        val spectra = LightSpectrum.entries.filter { it != LightSpectrum.OFF }
        for (spectrum in spectra) {
            updateResult("Light: ${spectrum.displayName}", TestStatus.RUNNING, "Activating...")
            val result = spectrumController.activate(spectrum)
            delay(300)
            if (result.isSuccess) {
                updateResult("Light: ${spectrum.displayName}", TestStatus.PASS, "OK")
            } else {
                updateResult("Light: ${spectrum.displayName}", TestStatus.FAIL, "Failed")
            }
        }
        spectrumController.activate(LightSpectrum.OFF)
    }

    private fun updateResult(name: String, status: TestStatus, message: String) {
        _calibrationResults.value = _calibrationResults.value.toMutableMap().apply {
            put(name, CalibrationResult(status, message))
        }
    }
}
