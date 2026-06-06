package com.jenincare.skinanalyzer.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = false)
data class ScanDto(
    @Json(name = "id") val id: String,
    @Json(name = "status") val status: String,
    @Json(name = "image_url") val imageUrl: String,
    @Json(name = "spectral_image_urls") val spectralImageUrls: List<String>?,
    @Json(name = "overall_health_score") val overallHealthScore: Float,
    @Json(name = "radar_metrics") val radarMetrics: RadarMetricsDto,
    @Json(name = "heatmap_coordinates") val heatmapCoordinates: List<HeatmapPointDto>,
    @Json(name = "custom_arabic_analysis") val customArabicAnalysis: String?,
    @Json(name = "expert_free_tips") val expertFreeTips: List<String>,
    @Json(name = "recommended_products") val recommendedProducts: List<ProductDto>,
    @Json(name = "created_at") val createdAt: String,
    @Json(name = "approved_at") val approvedAt: String?,
    @Json(name = "is_locked") val isLocked: Boolean,
    @Json(name = "pin_required") val pinRequired: Boolean
)

@JsonClass(generateAdapter = false)
data class RadarMetricsDto(
    @Json(name = "hydration") val hydration: Float,
    @Json(name = "sebum") val sebum: Float,
    @Json(name = "pigmentation") val pigmentation: Float,
    @Json(name = "pores") val pores: Float,
    @Json(name = "elasticity") val elasticity: Float
)

@JsonClass(generateAdapter = false)
data class HeatmapPointDto(
    @Json(name = "x") val x: Float,
    @Json(name = "y") val y: Float,
    @Json(name = "label") val label: String,
    @Json(name = "severity") val severity: String
)

@JsonClass(generateAdapter = false)
data class UploadResponse(
    @Json(name = "scan") val scan: ScanDto,
    @Json(name = "message") val message: String
)

@JsonClass(generateAdapter = false)
data class ScanResponse(
    @Json(name = "scan") val scan: ScanDto
)

@JsonClass(generateAdapter = false)
data class MultipleScansResponse(
    @Json(name = "scans") val scans: List<ScanDto>,
    @Json(name = "total") val total: Int,
    @Json(name = "page") val page: Int
)

@JsonClass(generateAdapter = false)
data class UnlockResponse(
    @Json(name = "scan") val scan: ScanDto,
    @Json(name = "message") val message: String
)

@JsonClass(generateAdapter = false)
data class ScanTimelineResponse(
    @Json(name = "events") val events: List<TimelineEventDto>
)

@JsonClass(generateAdapter = false)
data class TimelineEventDto(
    @Json(name = "id") val id: String,
    @Json(name = "status") val status: String,
    @Json(name = "timestamp") val timestamp: String,
    @Json(name = "description") val description: String,
    @Json(name = "description_ar") val descriptionAr: String?
)

@JsonClass(generateAdapter = false)
data class ProductDto(
    @Json(name = "id") val id: String,
    @Json(name = "name") val name: String,
    @Json(name = "name_ar") val nameAr: String?,
    @Json(name = "price") val price: Double,
    @Json(name = "image_url") val imageUrl: String?,
    @Json(name = "description") val description: String?,
    @Json(name = "matching_reason") val matchingReason: String?
)

@JsonClass(generateAdapter = false)
data class ProductsResponse(
    @Json(name = "products") val products: List<ProductDto>
)

@JsonClass(generateAdapter = false)
data class ProfileResponse(
    @Json(name = "data") val data: UserDto
)

@JsonClass(generateAdapter = false)
data class AppConfigResponse(
    @Json(name = "data") val data: AppConfigData
)

@JsonClass(generateAdapter = false)
data class AppConfigData(
    @Json(name = "login_enabled") val loginEnabled: Boolean = true,
    @Json(name = "registration_enabled") val registrationEnabled: Boolean = true,
    @Json(name = "maintenance_mode") val maintenanceMode: Boolean = false,
    @Json(name = "maintenance_message_ar") val maintenanceMessageAr: String?,
    @Json(name = "app_name") val appName: String = "SkinAnalyzer",
    @Json(name = "app_name_en") val appNameEn: String = "SkinAnalyzer",
    @Json(name = "primary_color") val primaryColor: String = "#4CAF50",
    @Json(name = "accent_color") val accentColor: String = "#81C784",
    @Json(name = "logo_url") val logoUrl: String?,
    @Json(name = "server_url") val serverUrl: String?,
    @Json(name = "min_app_version") val minAppVersion: String?,
    @Json(name = "latest_app_version") val latestAppVersion: String?
)
