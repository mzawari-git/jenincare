package com.ebtikar.skinanalyzer.ai

import com.ebtikar.skinanalyzer.core.provider.AnalysisProvider
import com.ebtikar.skinanalyzer.core.provider.AnalysisResult
import com.ebtikar.skinanalyzer.data.remote.CloudApiService
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CloudAnalysisProvider @Inject constructor(
    private val networkMonitor: NetworkMonitor,
    private val apiService: CloudApiService
) : AnalysisProvider {

    override fun getName() = "Cloud_Analysis_Engine"
    override fun getPriority() = 50

    override fun isAvailable(): Boolean = networkMonitor.isOnline()

    override fun initialize(): Result<Unit> {
        return if (networkMonitor.isOnline()) {
            Timber.i("Cloud provider initialized")
            Result.success(Unit)
        } else {
            Result.failure(IllegalStateException("Network unavailable"))
        }
    }

    override suspend fun analyze(images: Map<String, File>): AnalysisResult {
        return withContext(Dispatchers.IO) {
            val startTime = System.currentTimeMillis()

            try {
                val primaryImage = images.values.firstOrNull()
                    ?: throw IllegalStateException("No images provided")

                val requestBody = primaryImage.asRequestBody("image/jpeg".toMediaType())
                val imagePart = MultipartBody.Part.createFormData("image", primaryImage.name, requestBody)

                val response = apiService.uploadScan(imagePart)

                val scanId = response.data?.id
                if (scanId == null) {
                    throw IllegalStateException("No scan ID returned from server")
                }

                Timber.i("Scan uploaded successfully, ID: $scanId, status: ${response.data?.status}")

                AnalysisResult(
                    providerName = getName(),
                    executionTimeMs = System.currentTimeMillis() - startTime,
                    metrics = emptyMap(),
                    confidence = 0.95f,
                    rawResult = mapOf("scan_id" to scanId.toString(), "status" to response.data.status)
                )
            } catch (e: Exception) {
                Timber.e(e, "Cloud analysis failed")
                AnalysisResult(
                    providerName = getName(),
                    executionTimeMs = System.currentTimeMillis() - startTime,
                    metrics = emptyMap(),
                    warnings = listOf("Cloud analysis failed: ${e.message}")
                )
            }
        }
    }

    override fun shutdown() {}
}
