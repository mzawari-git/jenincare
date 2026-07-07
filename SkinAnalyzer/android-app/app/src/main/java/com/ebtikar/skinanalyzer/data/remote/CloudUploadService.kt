package com.ebtikar.skinanalyzer.data.remote

import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.HeatmapPoint
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.util.PreferencesManager
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.withContext
import kotlinx.serialization.Serializable
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MultipartBody
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.asRequestBody
import timber.log.Timber
import java.io.File
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class CloudUploadService @Inject constructor(
    private val preferencesManager: PreferencesManager
) {

    private val json = Json { ignoreUnknownKeys = true; encodeDefaults = true }

    private val client = OkHttpClient.Builder()
        .connectTimeout(60, TimeUnit.SECONDS)
        .readTimeout(120, TimeUnit.SECONDS)
        .writeTimeout(120, TimeUnit.SECONDS)
        .build()

    suspend fun uploadAndAnalyze(frames: Map<LightSpectrum, File>): Result<SkinAnalysisReport> {
        return withContext(Dispatchers.IO) {
            try {
                val apiUrl = preferencesManager.apiUrlFlow.first()
                val apiKey = preferencesManager.apiKeyFlow.first()

                if (apiUrl.isBlank()) {
                    return@withContext Result.failure(IllegalStateException("API URL not configured"))
                }

                val url = if (apiUrl.endsWith("/")) "${apiUrl}analyze" else "$apiUrl/analyze"

                val builder = MultipartBody.Builder()
                    .setType(MultipartBody.FORM)

                for ((spectrum, file) in frames) {
                    if (file.exists()) {
                        val mediaType = "image/jpeg".toMediaType()
                        builder.addFormDataPart(
                            "image_${spectrum.name.lowercase()}",
                            "frame_${spectrum.name}.jpg",
                            file.asRequestBody(mediaType)
                        )
                    }
                }

                builder.addFormDataPart("spectra_count", frames.size.toString())

                val requestBody = builder.build()

                val request = Request.Builder()
                    .url(url)
                    .addHeader("Authorization", "Bearer $apiKey")
                    .addHeader("Accept", "application/json")
                    .post(requestBody)
                    .build()

                Timber.i("Uploading ${frames.size} spectral images to API...")
                val response = client.newCall(request).execute()

                response.use { resp ->
                    if (!resp.isSuccessful) {
                        val errorBody = resp.body?.string() ?: "Unknown error"
                        Timber.e("API upload failed: ${resp.code} $errorBody")
                        return@withContext Result.failure(IllegalStateException("API error ${resp.code}: $errorBody"))
                    }

                    val responseBody = resp.body?.string() ?: ""
                    Timber.i("API response received: ${responseBody.take(200)}...")

                    val apiResponse = json.decodeFromString<ApiAnalysisResponse>(responseBody)
                    val report = apiResponse.toSkinAnalysisReport()

                    Timber.i("API analysis complete: ${report.metricCount} metrics, score: ${report.overallScore}")
                    Result.success(report)
                }
            } catch (e: Exception) {
                Timber.e(e, "API upload/analysis failed")
                Result.failure(e)
            }
        }
    }
}

@Serializable
data class ApiAnalysisResponse(
    val success: Boolean = true,
    val overall_score: Float = 0f,
    val metrics: List<ApiMetric> = emptyList(),
    val analysis_text_ar: String = "",
    val tips_ar: List<String> = emptyList(),
    val products: List<ApiProduct> = emptyList(),
    val skin_type_ar: String = "",
    val confidence: Float = 0.85f,
    val scan_id: String = "",
    val execution_time_ms: Long = 0,
    val heatmap_coordinates: List<HeatmapPoint> = emptyList()
) {
    fun toSkinAnalysisReport(): SkinAnalysisReport {
        val skinMetrics = metrics.map { apiMetric ->
            val type = SkinMetric.Type.entries.find {
                it.name.equals(apiMetric.type, ignoreCase = true)
            } ?: SkinMetric.Type.TEXTURE

            SkinMetric(
                type = type,
                score = apiMetric.score,
                severity = when {
                    apiMetric.score >= 72f -> MetricSeverity.EXCELLENT
                    apiMetric.score >= 55f -> MetricSeverity.GOOD
                    apiMetric.score >= 35f -> MetricSeverity.FAIR
                    apiMetric.score >= 20f -> MetricSeverity.POOR
                    else -> MetricSeverity.CRITICAL
                },
                details = apiMetric.details
            )
        }

        val allMetrics = SkinMetric.ALL_TYPES.map { type ->
            skinMetrics.find { it.type == type } ?: SkinMetric(
                type = type, score = 60f, severity = MetricSeverity.FAIR, details = "No data"
            )
        }

        return SkinAnalysisReport(
            providerName = "Cloud_API",
            overallScore = overall_score,
            metrics = allMetrics,
            executionTimeMs = execution_time_ms,
            aiAnalysisTextAr = analysis_text_ar.ifBlank { "تحليل كامل عبر API السحابي" },
            expertTipsAr = tips_ar,
            productRecommendations = products.map { it.toProductRecommendation() },
            confidence = confidence,
            scanId = scan_id,
            heatmapPoints = heatmap_coordinates
        )
    }
}

@Serializable
data class ApiMetric(
    val type: String = "",
    val score: Float = 0f,
    val details: String = ""
)

@Serializable
data class ApiProduct(
    val id: String = "",
    val name: String = "",
    val name_ar: String = "",
    val brand: String = "",
    val category: String = "",
    val price: Float = 0f,
    val currency: String = "ILS",
    val match_score: Float = 0f,
    val reason: String = "",
    val reason_ar: String = ""
) {
    fun toProductRecommendation() = com.ebtikar.skinanalyzer.model.ProductRecommendation(
        id = id,
        name = name,
        nameAr = name_ar,
        brand = brand,
        category = category,
        price = price,
        currency = currency,
        matchScore = match_score,
        reason = reason,
        reasonAr = reason_ar
    )
}
