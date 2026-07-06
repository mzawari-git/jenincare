package com.ebtikar.skinanalyzer.data.remote

import android.content.Context
import com.ebtikar.skinanalyzer.data.local.TokenManager
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.ProductRecommendation
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinProfile
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CloudAnalysisService @Inject constructor(
    @ApplicationContext private val context: Context,
    private val apiService: CloudApiService,
    private val tokenManager: TokenManager
) {

    private val json = Json { ignoreUnknownKeys = true }

    private val _uploadProgress = MutableStateFlow(UploadProgress())
    val uploadProgress: StateFlow<UploadProgress> = _uploadProgress.asStateFlow()

    private val _analysisStatus = MutableStateFlow(CloudAnalysisStatus.IDLE)
    val analysisStatus: StateFlow<CloudAnalysisStatus> = _analysisStatus.asStateFlow()

    data class UploadProgress(
        val totalImages: Int = 0,
        val uploadedImages: Int = 0,
        val currentSpectrum: LightSpectrum? = null,
        val isUploading: Boolean = false,
        val scanId: String? = null
    ) {
        val percentComplete: Int
            get() = if (totalImages > 0) (uploadedImages * 100 / totalImages) else 0
    }

    enum class CloudAnalysisStatus {
        IDLE, UPLOADING, PROCESSING, COMPLETE, FAILED
    }

    suspend fun uploadAndAnalyze(
        capturedFrames: Map<LightSpectrum, File>
    ): Result<SkinAnalysisReport> = withContext(Dispatchers.IO) {
        return@withContext try {
            _analysisStatus.value = CloudAnalysisStatus.UPLOADING
            _uploadProgress.value = UploadProgress(
                totalImages = capturedFrames.size,
                isUploading = true
            )

            var scanId: String? = null

            for ((spectrum, file) in capturedFrames) {
                _uploadProgress.value = _uploadProgress.value.copy(
                    currentSpectrum = spectrum
                )

                val requestFile = file.asRequestBody("image/jpeg".toMediaType())
                val body = MultipartBody.Part.createFormData("image", file.name, requestFile)

                try {
                    val response = apiService.uploadScan(body)
                    if (response.data != null) {
                        scanId = response.data.id.toString()
                        Timber.d("Uploaded ${spectrum.name}: scan_id=$scanId")
                    }
                } catch (e: Exception) {
                    Timber.e(e, "Failed to upload ${spectrum.name}")
                }

                _uploadProgress.value = _uploadProgress.value.copy(
                    uploadedImages = _uploadProgress.value.uploadedImages + 1
                )
            }

            if (scanId == null) {
                _analysisStatus.value = CloudAnalysisStatus.FAILED
                return@withContext Result.failure(IllegalStateException("No scan ID returned from server"))
            }

            _uploadProgress.value = _uploadProgress.value.copy(
                scanId = scanId,
                isUploading = false
            )

            _analysisStatus.value = CloudAnalysisStatus.PROCESSING
            val report = pollForResult(scanId)

            _analysisStatus.value = CloudAnalysisStatus.COMPLETE
            Result.success(report)
        } catch (e: Exception) {
            _analysisStatus.value = CloudAnalysisStatus.FAILED
            Timber.e(e, "Cloud analysis failed")
            Result.failure(e)
        }
    }

    private suspend fun pollForResult(scanId: String): SkinAnalysisReport {
        val maxAttempts = 60
        val pollIntervalMs = 2000L

        for (attempt in 1..maxAttempts) {
            try {
                val statusResponse = apiService.getScanStatus(scanId.toInt())
                Timber.d("Poll attempt $attempt: status=${statusResponse.status}")

                when (statusResponse.status.lowercase()) {
                    "completed", "analyzed", "approved" -> {
                        return fetchFullReport(scanId.toInt())
                    }
                    "failed", "error", "rejected" -> {
                        throw IllegalStateException("Analysis failed on server: ${statusResponse.message}")
                    }
                    else -> {
                        kotlinx.coroutines.delay(pollIntervalMs)
                    }
                }
            } catch (e: Exception) {
                if (attempt == maxAttempts) throw e
                kotlinx.coroutines.delay(pollIntervalMs)
            }
        }

        throw IllegalStateException("Analysis timeout after ${maxAttempts * pollIntervalMs / 1000}s")
    }

    private suspend fun fetchFullReport(scanId: Int): SkinAnalysisReport {
        val reportResponse = apiService.getScanReport(scanId)
        val detailResponse = apiService.getScanDetail(scanId)

        val metrics = mutableListOf<SkinMetric>()
        val radarMetrics = detailResponse.data?.radarMetrics ?: emptyMap()

        for (type in SkinMetric.ALL_TYPES) {
            val score = radarMetrics[type.name.lowercase()]
                ?: radarMetrics[type.name]
                ?: getScoreFromReportMetrics(reportResponse, type)

            val severity = classifyScore(score)
            metrics.add(
                SkinMetric(
                    type = type,
                    score = score,
                    severity = severity,
                    details = reportResponse.customArabicAnalysis ?: "",
                    recommendations = reportResponse.generalTips ?: emptyList()
                )
            )
        }

        val products = detailResponse.data?.products?.map { p ->
            ProductRecommendation(
                id = p.id.toString(),
                name = p.name ?: "",
                nameAr = p.nameAr ?: "",
                brand = p.brand ?: "",
                price = p.price ?: 0f,
                currency = p.currency ?: "ILS",
                imageUrl = p.imageUrl ?: "",
                matchScore = 0.85f,
                reason = p.matchingReason ?: "",
                reasonAr = p.matchingReason ?: ""
            )
        } ?: emptyList()

        val overallScore = detailResponse.data?.overallHealthScore
            ?: reportResponse.scan?.overallScore
            ?: metrics.map { it.score }.average().toFloat()

        return SkinAnalysisReport(
            providerName = "Cloud_AI_Engine",
            overallScore = overallScore,
            metrics = metrics,
            executionTimeMs = 0,
            aiAnalysisTextAr = detailResponse.data?.customArabicAnalysis ?: reportResponse.customArabicAnalysis ?: "",
            expertTipsAr = detailResponse.data?.expertFreeTips ?: reportResponse.generalTips ?: emptyList(),
            productRecommendations = products,
            skinProfile = SkinProfile(),
            confidence = detailResponse.data?.radarMetrics?.size?.toFloat()?.div(SkinMetric.TOTAL_METRICS.toFloat()) ?: 0.85f,
            scanId = scanId.toString()
        )
    }

    private fun getScoreFromReportMetrics(report: ScanReportResponse, type: SkinMetric.Type): Float {
        val advanced = report.advancedMetrics ?: emptyMap()
        return advanced[type.name.lowercase()]
            ?: advanced[type.name]
            ?: when (type) {
                SkinMetric.Type.MOISTURE -> report.metrics?.hydration ?: 0f
                SkinMetric.Type.SEBUM -> report.metrics?.sebum ?: 0f
                SkinMetric.Type.PIGMENTATION -> report.metrics?.pigmentation ?: 0f
                SkinMetric.Type.PORES -> report.metrics?.pores ?: 0f
                else -> 50f
            }
    }

    private fun classifyScore(score: Float): MetricSeverity = when {
        score >= 72f -> MetricSeverity.EXCELLENT
        score >= 55f -> MetricSeverity.GOOD
        score >= 35f -> MetricSeverity.FAIR
        score >= 20f -> MetricSeverity.POOR
        else -> MetricSeverity.CRITICAL
    }

    suspend fun getScanHistory(): Result<List<ScanItem>> = withContext(Dispatchers.IO) {
        return@withContext try {
            val response = apiService.getScans()
            Result.success(response.data ?: emptyList())
        } catch (e: Exception) {
            Timber.e(e, "Failed to fetch scan history")
            Result.failure(e)
        }
    }

    fun reset() {
        _uploadProgress.value = UploadProgress()
        _analysisStatus.value = CloudAnalysisStatus.IDLE
    }
}
