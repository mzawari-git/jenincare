package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.launch
import timber.log.Timber
import java.io.File
import java.util.UUID
import javax.inject.Inject

@HiltViewModel
class AnalysisViewModel @Inject constructor(
    @ApplicationContext private val context: Context,
    private val repository: SkinAnalysisRepository
) : ViewModel() {

    private val _currentPhase = MutableStateFlow<CapturePhase?>(null)
    val currentPhase: StateFlow<CapturePhase?> = _currentPhase.asStateFlow()

    private val _progress = MutableStateFlow(0)
    val progress: StateFlow<Int> = _progress.asStateFlow()

    private val _statusMessage = MutableStateFlow("")
    val statusMessage: StateFlow<String> = _statusMessage.asStateFlow()

    private val _isComplete = MutableStateFlow(false)
    val isComplete: StateFlow<Boolean> = _isComplete.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private val _currentSpectrumName = MutableStateFlow("")
    val currentSpectrumName: StateFlow<String> = _currentSpectrumName.asStateFlow()

    private val _capturedImages = MutableStateFlow<Map<LightSpectrum, File>>(emptyMap())
    val capturedImages: StateFlow<Map<LightSpectrum, File>> = _capturedImages.asStateFlow()

    private var reportId = UUID.randomUUID().toString()
    private var isAborted = false
    private var capturedFrames: Map<LightSpectrum, File> = emptyMap()

    fun getReportId(): String = reportId

    init {
        repository.getAnalysisState().onEach { state ->
            when (state) {
                is AnalysisState.Capturing -> {
                    _currentSpectrumName.value = state.phase.displayName
                    _progress.value = state.progress
                    _statusMessage.value = "Capturing ${state.phase.displayName}..."
                }
                is AnalysisState.Analyzing -> {
                    _statusMessage.value = "Analyzing with ${state.provider}..."
                    _progress.value = 80
                }
                is AnalysisState.Saving -> {
                    _statusMessage.value = "Saving report..."
                    _progress.value = 95
                }
                is AnalysisState.Complete -> {
                    _progress.value = 100
                    _isComplete.value = true
                    _statusMessage.value = "Analysis complete"
                }
                is AnalysisState.Error -> {
                    _error.value = state.message
                }
                else -> {}
            }
        }.launchIn(viewModelScope)
    }

    fun initializeAnalysis() {
        isAborted = false
        _error.value = null
        _isComplete.value = false
        _progress.value = 0

        viewModelScope.launch {
            _statusMessage.value = "Initializing analysis..."

            val outputDir = File(context.filesDir, "captures/$reportId")

            val captureResult = repository.startAnalysis(outputDir)

            if (captureResult.isFailure || isAborted) {
                _error.value = captureResult.exceptionOrNull()?.message ?: "Analysis aborted"
                return@launch
            }

            capturedFrames = captureResult.getOrDefault(emptyMap())
            _capturedImages.value = capturedFrames

            _statusMessage.value = "Processing captured images..."
            runAnalysis(capturedFrames)
        }
    }

    private fun runAnalysis(frames: Map<LightSpectrum, File>) {
        viewModelScope.launch {
            val analysisResult = repository.analyzeImages(frames)

            if (analysisResult.isSuccess) {
                val report = analysisResult.getOrThrow()
                val saveResult = repository.saveReport(report)

                if (saveResult.isSuccess) {
                    _progress.value = 100
                    _isComplete.value = true
                    Timber.i("Analysis and save complete: ${report.metricCount} metrics")
                } else {
                    _error.value = "Failed to save report"
                }
            } else {
                _error.value = "Analysis failed: ${analysisResult.exceptionOrNull()?.message}"
            }
        }
    }

    fun abortAnalysis() {
        isAborted = true
    }
}
