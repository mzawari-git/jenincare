package com.ebtikar.skinanalyzer.ui.analysis

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ebtikar.skinanalyzer.camera.CapturePhase
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.launch
import timber.log.Timber
import java.io.File
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.UUID
import javax.inject.Inject

@HiltViewModel
class AnalysisViewModel @Inject constructor(
    @ApplicationContext private val context: Context,
    private val repository: SkinAnalysisRepository,
    private val preferencesManager: PreferencesManager
) : ViewModel() {

    private val _currentPhase = MutableStateFlow<CapturePhase?>(null)
    val currentPhase: StateFlow<CapturePhase?> = _currentPhase.asStateFlow()

    private val _progress = MutableStateFlow(0)
    val progress: StateFlow<Int> = _progress.asStateFlow()

    private val _statusMessage = MutableStateFlow("")
    val statusMessage: StateFlow<String> = _statusMessage.asStateFlow()

    private val _currentStep = MutableStateFlow(0)
    val currentStep: StateFlow<Int> = _currentStep.asStateFlow()

    private val _totalSteps = MutableStateFlow(0)
    val totalSteps: StateFlow<Int> = _totalSteps.asStateFlow()

    private val _isComplete = MutableStateFlow(false)
    val isComplete: StateFlow<Boolean> = _isComplete.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private val _currentSpectrumName = MutableStateFlow("")
    val currentSpectrumName: StateFlow<String> = _currentSpectrumName.asStateFlow()

    private val _capturedImages = MutableStateFlow<Map<LightSpectrum, File>>(emptyMap())
    val capturedImages: StateFlow<Map<LightSpectrum, File>> = _capturedImages.asStateFlow()

    private val _hydration = MutableStateFlow(0f)
    val hydration: StateFlow<Float> = _hydration.asStateFlow()
    private val _pores = MutableStateFlow(0f)
    val pores: StateFlow<Float> = _pores.asStateFlow()
    private val _redness = MutableStateFlow(0f)
    val redness: StateFlow<Float> = _redness.asStateFlow()
    private val _texture = MutableStateFlow(0f)
    val texture: StateFlow<Float> = _texture.asStateFlow()
    private val _acne = MutableStateFlow(0f)
    val acne: StateFlow<Float> = _acne.asStateFlow()
    private val _sensitivity = MutableStateFlow(0f)
    val sensitivity: StateFlow<Float> = _sensitivity.asStateFlow()
    private val _pigmentation = MutableStateFlow(0f)
    val pigmentation: StateFlow<Float> = _pigmentation.asStateFlow()

    private val _trackingAccuracy = MutableStateFlow(0)
    val trackingAccuracy: StateFlow<Int> = _trackingAccuracy.asStateFlow()
    private val _scanArea = MutableStateFlow(0)
    val scanArea: StateFlow<Int> = _scanArea.asStateFlow()
    private val _faceDetected = MutableStateFlow(false)
    val faceDetected: StateFlow<Boolean> = _faceDetected.asStateFlow()

    private val _radarValues = MutableStateFlow<List<Float>>(emptyList())
    val radarValues: StateFlow<List<Float>> = _radarValues.asStateFlow()
    private val _radarLabels = MutableStateFlow<List<String>>(emptyList())
    val radarLabels: StateFlow<List<String>> = _radarLabels.asStateFlow()
    private val _overallScore = MutableStateFlow(0f)
    val overallScore: StateFlow<Float> = _overallScore.asStateFlow()

    private val _recentScans = MutableStateFlow<List<Pair<String, Int>>>(emptyList())
    val recentScans: StateFlow<List<Pair<String, Int>>> = _recentScans.asStateFlow()
    private val _recommendations = MutableStateFlow<List<String>>(emptyList())
    val recommendations: StateFlow<List<String>> = _recommendations.asStateFlow()

    private var reportId = UUID.randomUUID().toString()
    @Volatile private var isAborted = false
    private var analysisJob: kotlinx.coroutines.Job? = null
    private var capturedFrames: Map<LightSpectrum, File> = emptyMap()
    private var diagnosisMode = Constants.DIAGNOSIS_ALL

    fun getReportId(): String = reportId

    init {
        repository.getAnalysisState().onEach { state ->
            when (state) {
                is AnalysisState.WaitingForFace -> {
                    _statusMessage.value = "ضع وجهك أمام الكاميرا — جاري البحث عن الوجه..."
                    _progress.value = 5
                }
                is AnalysisState.Capturing -> {
                    _currentSpectrumName.value = state.phase.displayName
                    _progress.value = state.progress
                    _currentStep.value = state.step
                    _totalSteps.value = state.totalSteps
                    val arName = state.spectrumDisplayAr.ifBlank { state.phase.displayNameAr }
                    if (state.step > 0 && state.totalSteps > 0) {
                        _statusMessage.value = "${state.step} من ${state.totalSteps} — $arName"
                    } else {
                        _statusMessage.value = "جاري المسح..."
                    }
                }
                is AnalysisState.Analyzing -> {
                    _statusMessage.value = "جاري التحليل عبر ${state.provider}..."
                    _progress.value = 85
                }
                is AnalysisState.Saving -> {
                    _statusMessage.value = "جاري حفظ التقرير..."
                    _progress.value = 95
                }
                is AnalysisState.Complete -> {
                    _progress.value = 100
                    _isComplete.value = true
                    _statusMessage.value = "اكتمل التحليل"
                }
                is AnalysisState.Error -> {
                    _error.value = state.message
                }
                else -> {}
            }
        }.launchIn(viewModelScope)
    }

    fun updateTrackingData(positionScore: Int, faceDetected: Boolean) {
        _trackingAccuracy.value = positionScore.coerceIn(0, 100)
        _scanArea.value = (positionScore * 0.85f).toInt().coerceIn(0, 100)
        _faceDetected.value = faceDetected
    }

    private fun applyReportToUI(report: SkinAnalysisReport) {
        fun metricScore(type: SkinMetric.Type): Float {
            return report.getMetricByType(type)?.score ?: 0f
        }

        _hydration.value = metricScore(SkinMetric.Type.MOISTURE)
        _pores.value = metricScore(SkinMetric.Type.PORES)
        _redness.value = metricScore(SkinMetric.Type.VASCULAR)
        _texture.value = metricScore(SkinMetric.Type.TEXTURE)
        _acne.value = metricScore(SkinMetric.Type.ACNE)
        _sensitivity.value = metricScore(SkinMetric.Type.SENSITIVITY)
        _pigmentation.value = metricScore(SkinMetric.Type.PIGMENTATION)

        _radarValues.value = report.getRadarValues()
        _radarLabels.value = report.getRadarLabels()
        _overallScore.value = report.overallScore

        _recommendations.value = report.expertTipsAr

        Timber.i("UI updated from real report: score=${report.overallScore}, metrics=${report.metricCount}")
    }

    private suspend fun loadHistoryFromDb() {
        try {
            val reports = repository.getRecentReports(3).first()
            val dateFormat = SimpleDateFormat("dd/MM HH:mm", Locale.getDefault())
            _recentScans.value = reports.map { entity ->
                val dateStr = dateFormat.format(Date(entity.timestamp))
                dateStr to entity.overallScore.toInt()
            }
            Timber.i("Loaded ${reports.size} reports from DB")
        } catch (e: Exception) {
            Timber.w(e, "Failed to load history from DB")
            _recentScans.value = emptyList()
        }
    }

    fun setDiagnosisMode(mode: String) {
        diagnosisMode = mode
    }

    fun initializeAnalysis(previewSurface: android.view.Surface? = null) {
        analysisJob?.cancel()
        reportId = UUID.randomUUID().toString()
        isAborted = false
        _error.value = null
        _isComplete.value = false
        _progress.value = 0
        _currentPhase.value = null
        _currentStep.value = 0
        _totalSteps.value = 0
        _currentSpectrumName.value = ""
        _statusMessage.value = ""
        _trackingAccuracy.value = 0
        _scanArea.value = 0
        _faceDetected.value = false

        _hydration.value = 0f
        _pores.value = 0f
        _redness.value = 0f
        _texture.value = 0f
        _acne.value = 0f
        _sensitivity.value = 0f
        _pigmentation.value = 0f
        _radarValues.value = emptyList()
        _radarLabels.value = emptyList()
        _overallScore.value = 0f
        _recentScans.value = emptyList()
        _recommendations.value = emptyList()

        analysisJob = viewModelScope.launch {
            _statusMessage.value = "Initializing analysis..."

            val outputDir = File(context.filesDir, "captures/$reportId")

            val captureResult = repository.startAnalysis(outputDir, diagnosisMode, previewSurface)

            if (captureResult.isFailure || isAborted) {
                _error.value = captureResult.exceptionOrNull()?.message ?: "Analysis aborted"
                return@launch
            }

            capturedFrames = captureResult.getOrDefault(emptyMap())
            _capturedImages.value = capturedFrames

            _statusMessage.value = "Processing captured images..."

            val mode = preferencesManager.analysisModeFlow.first()
            runAnalysis(capturedFrames, mode)
        }
    }

    private fun runAnalysis(frames: Map<LightSpectrum, File>, mode: String = Constants.ANALYSIS_AUTO) {
        viewModelScope.launch {
            val analysisResult = repository.analyzeImages(frames, mode)

            if (analysisResult.isSuccess) {
                val report = analysisResult.getOrThrow()
                val fixedReport = report.copy(id = reportId)
                val saveResult = repository.saveReport(fixedReport)

                if (saveResult.isSuccess) {
                    applyReportToUI(fixedReport)
                    loadHistoryFromDb()

                    _progress.value = 100
                    _isComplete.value = true
                    Timber.i("Analysis and save complete: ${report.metricCount} metrics, score=${report.overallScore}")
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
        analysisJob?.cancel()
        analysisJob = null
    }
}
