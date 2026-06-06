package com.jenincare.skinanalyzer.ui.scan

import android.content.Context
import android.graphics.Bitmap
import android.net.Uri
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.domain.usecase.UploadScanUseCase
import com.jenincare.skinanalyzer.data.local.StreakManager
import com.jenincare.skinanalyzer.data.local.AppRatingManager
import com.jenincare.skinanalyzer.util.ScanQualityScorer
import com.jenincare.skinanalyzer.ui.camera.FaceDetectionResult
import com.jenincare.skinanalyzer.ui.camera.HardwareSyncManager
import com.jenincare.skinanalyzer.ui.camera.ImageFilterMode
import com.jenincare.skinanalyzer.ui.camera.LightingQuality
import com.jenincare.skinanalyzer.ui.camera.PoseType
import com.jenincare.skinanalyzer.ui.camera.SpectralMode
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.TimeoutCancellationException
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withTimeout
import javax.inject.Inject

data class SpectralCapture(
    val mode: ImageFilterMode,
    val bitmap: Bitmap? = null,
    val isCaptured: Boolean = false
)

data class CameraUiState(
    val isCameraReady: Boolean = false,
    val isFaceDetected: Boolean = false,
    val faceDetectionResult: FaceDetectionResult = FaceDetectionResult(),
    val lightingQuality: Float = 0f,
    val sharpness: Float = 0f,
    val facePosition: PoseType = PoseType.CENTER,
    val isCapturing: Boolean = false,
    val capturedBitmap: Bitmap? = null,
    val showReviewDialog: Boolean = false,
    val isUploading: Boolean = false,
    val uploadProgress: Float = 0f,
    val scanId: String? = null,
    val error: String? = null,
    val qualityBorderColor: Int = android.graphics.Color.GREEN,
    val autoCaptureProgress: Float = 0f,
    val selectedFilter: ImageFilterMode = ImageFilterMode.RGB,
    val spectralCaptures: List<SpectralCapture> = ImageFilterMode.entries.map {
        SpectralCapture(mode = it)
    },
    val currentSpectralIndex: Int = 0,
    val isMultiSpectralCaptureComplete: Boolean = false,
    val isHardwareConnected: Boolean = false,
    val showBottomSheet: Boolean = false,
    val capturePhase: String = "idle",
    val autoCaptureFailed: Boolean = false
)

@HiltViewModel
class CameraViewModel @Inject constructor(
    private val uploadScanUseCase: UploadScanUseCase,
    private val streakManager: StreakManager,
    private val appRatingManager: AppRatingManager,
    @ApplicationContext private val appContext: Context
) : ViewModel() {

    private val _uiState = MutableStateFlow(CameraUiState())
    val uiState: StateFlow<CameraUiState> = _uiState.asStateFlow()

    private val _voiceInstruction = MutableSharedFlow<String>()
    val voiceInstruction = _voiceInstruction.asSharedFlow()

    private val _uploadComplete = MutableSharedFlow<String>()
    val uploadComplete = _uploadComplete.asSharedFlow()

    private val hardwareSyncManager = HardwareSyncManager(appContext)

    init {
        viewModelScope.launch {
            val connection = hardwareSyncManager.connectToDevice()
            _uiState.value = _uiState.value.copy(
                isHardwareConnected = connection is com.jenincare.skinanalyzer.ui.camera.HardwareConnection.Connected
            )
        }
    }

    fun performMultiSpectralCapture(captureFunction: suspend (ImageFilterMode) -> Bitmap?) {
        if (_uiState.value.isCapturing || _uiState.value.isUploading) return
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(autoCaptureFailed = false, isCapturing = false)
            val modes = ImageFilterMode.entries
            for (i in _uiState.value.currentSpectralIndex until modes.size) {
                if (_uiState.value.isUploading) break
                val success = captureSingleMode(captureFunction)
                if (!success) break
                if (i + 1 < modes.size) delay(500)
            }
            _uiState.value = _uiState.value.copy(
                isCapturing = false,
                autoCaptureProgress = 0f
            )
        }
    }

    fun performFullAutoCapture(captureFunction: suspend (ImageFilterMode) -> Bitmap?) {
        if (_uiState.value.isCapturing || _uiState.value.isUploading) return
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(autoCaptureFailed = false, isCapturing = false)
            val modes = ImageFilterMode.entries
            for (i in _uiState.value.currentSpectralIndex until modes.size) {
                if (_uiState.value.isUploading) break
                val success = captureSingleMode(captureFunction)
                if (!success) break
                if (i + 1 < modes.size) delay(500)
            }
            _uiState.value = _uiState.value.copy(
                isCapturing = false,
                autoCaptureProgress = 0f
            )
        }
    }

    private suspend fun captureSingleMode(captureFunction: suspend (ImageFilterMode) -> Bitmap?): Boolean {
        val currentIndex = _uiState.value.currentSpectralIndex
        val modes = ImageFilterMode.entries
        if (currentIndex >= modes.size) return false

        val mode = modes[currentIndex]
        var lastError: String? = null

        for (attempt in 0 until 3) {
            _uiState.value = _uiState.value.copy(
                isCapturing = true,
                capturePhase = "capturing_${mode.name.lowercase()}",
                selectedFilter = mode,
                error = null
            )

            hardwareSyncManager.switchMode(
                when (mode) {
                    ImageFilterMode.RGB -> SpectralMode.RGB
                    ImageFilterMode.UV -> SpectralMode.UV
                    ImageFilterMode.CROSS_POLARIZED -> SpectralMode.CROSS_POLARIZED
                }
            )

            // Bug fix: Wait for the hardware to stabilize and clear the old frame buffer
            // to ensure we don't capture the previous spectral mode.
            delay(600)

            val bitmap = try {
                withTimeout(8000L) {
                    captureFunction(mode)
                }
            } catch (e: kotlinx.coroutines.TimeoutCancellationException) {
                lastError = "انتهت مهلة التقاط الصورة في وضع ${mode.displayNameAr}"
                null
            } catch (e: Exception) {
                lastError = "خطأ في التقاط الصورة: ${e.localizedMessage}"
                null
            }

            if (bitmap != null) {
                val captures = _uiState.value.spectralCaptures.toMutableList()
                captures[currentIndex] = captures[currentIndex].copy(
                    bitmap = bitmap,
                    isCaptured = true
                )

                val nextIndex = currentIndex + 1
                val isComplete = nextIndex >= modes.size

                _uiState.value = _uiState.value.copy(
                    spectralCaptures = captures,
                    currentSpectralIndex = nextIndex,
                    isMultiSpectralCaptureComplete = isComplete,
                    isCapturing = !isComplete,
                    capturePhase = if (isComplete) "complete" else "ready_next",
                    showBottomSheet = isComplete
                )

                return true
            }

            if (attempt < 2) {
                delay(500)
            }
        }

        _uiState.value = _uiState.value.copy(
            isCapturing = false,
            error = lastError ?: "فشل التقاط الصورة في وضع ${mode.displayNameAr} بعد المحاولة",
            autoCaptureFailed = true
        )
        return false
    }

    fun resetSpectralIndex() {
        _uiState.value = _uiState.value.copy(
            currentSpectralIndex = 0,
            autoCaptureFailed = false,
            isCapturing = false
        )
    }

    fun resetMultiSpectralCapture() {
        _uiState.value = _uiState.value.copy(
            spectralCaptures = ImageFilterMode.entries.map { SpectralCapture(mode = it) },
            currentSpectralIndex = 0,
            isMultiSpectralCaptureComplete = false,
            showBottomSheet = false,
            capturePhase = "idle",
            isCapturing = false,
            autoCaptureFailed = false
        )
    }

    fun dismissBottomSheet() {
        _uiState.value = _uiState.value.copy(showBottomSheet = false)
    }

    fun updateCameraReady(ready: Boolean) {
        _uiState.value = _uiState.value.copy(isCameraReady = ready)
    }

    fun updateFaceDetection(result: FaceDetectionResult) {
        val isDetected = result.confidence > 0.5f
        val qualityColor = when {
            result.confidence < 0.4f -> android.graphics.Color.RED
            result.confidence < 0.7f -> android.graphics.Color.parseColor("#FF9800")
            else -> android.graphics.Color.GREEN
        }

        _uiState.value = _uiState.value.copy(
            isFaceDetected = isDetected,
            faceDetectionResult = result,
            qualityBorderColor = qualityColor
        )
    }

    fun updateLightingQuality(quality: Float) {
        _uiState.value = _uiState.value.copy(lightingQuality = quality)
    }

    fun updateAutoCaptureProgress(progress: Float) {
        _uiState.value = _uiState.value.copy(autoCaptureProgress = progress)
    }

    fun updateSharpness(sharpness: Float) {
        _uiState.value = _uiState.value.copy(sharpness = sharpness)
    }

    fun updateQualityFromBitmap(bitmap: Bitmap) {
        viewModelScope.launch {
            val score = ScanQualityScorer.scoreFromBitmap(bitmap)
            _uiState.value = _uiState.value.copy(
                sharpness = score.sharpness,
                lightingQuality = score.lighting
            )
        }
    }

    fun updateFacePosition(poseType: PoseType) {
        _uiState.value = _uiState.value.copy(facePosition = poseType)
    }

    fun selectFilter(filter: ImageFilterMode) {
        _uiState.value = _uiState.value.copy(selectedFilter = filter)
    }

    fun applyFilterToBitmap(bitmap: Bitmap): Bitmap {
        return _uiState.value.selectedFilter.applyToBitmap(bitmap)
    }

    fun startCapture() {
        _uiState.value = _uiState.value.copy(isCapturing = true)
    }

    fun onImageCaptured(bitmap: Bitmap) {
        _uiState.value = _uiState.value.copy(
            isCapturing = false,
            capturedBitmap = bitmap,
            showReviewDialog = true
        )
    }

    fun retakePhoto() {
        _uiState.value = _uiState.value.copy(
            capturedBitmap = null,
            showReviewDialog = false
        )
    }

    fun uploadScan(imageUri: Uri, spectralUris: List<Uri> = emptyList()) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(
                isUploading = true,
                uploadProgress = 0f,
                showReviewDialog = false,
                capturedBitmap = null,
                error = null
            )

            val result = uploadScanUseCase(
                imageUri = imageUri,
                spectralUris = spectralUris,
                onProgress = { progress ->
                    _uiState.value = _uiState.value.copy(uploadProgress = progress)
                }
            )

            result.fold(
                onSuccess = { response ->
                    _uiState.value = _uiState.value.copy(
                        isUploading = false,
                        uploadProgress = 1f,
                        scanId = response.scanId
                    )
                    viewModelScope.launch {
                        streakManager.recordScan()
                        appRatingManager.incrementScanCount()
                    }
                    response.scanId?.let { _uploadComplete.emit(it) }
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isUploading = false,
                        uploadProgress = 0f,
                        error = error.message ?: "فشل الرفع"
                    )
                }
            )
        }
    }

    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null, autoCaptureFailed = false)
    }

    fun dismissReviewDialog() {
        _uiState.value = _uiState.value.copy(showReviewDialog = false)
    }

    override fun onCleared() {
        super.onCleared()
        hardwareSyncManager.release()
    }
}
