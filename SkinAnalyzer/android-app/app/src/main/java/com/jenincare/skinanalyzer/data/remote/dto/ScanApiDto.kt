package com.jenincare.skinanalyzer.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = false)
data class UploadScanRequest(
    @Json(name = "lighting_quality") val lightingQuality: Float,
    @Json(name = "face_confidence") val faceConfidence: Float,
    @Json(name = "image_width") val imageWidth: Int,
    @Json(name = "image_height") val imageHeight: Int,
    @Json(name = "has_spectral_captures") val hasSpectralCaptures: Boolean = false,
    @Json(name = "face_bounding_box") val faceBoundingBox: FaceBoundingBoxDto? = null,
    @Json(name = "face_landmarks") val faceLandmarks: List<LandmarkDto>? = null
)

@JsonClass(generateAdapter = false)
data class FaceBoundingBoxDto(
    @Json(name = "left") val left: Float,
    @Json(name = "top") val top: Float,
    @Json(name = "right") val right: Float,
    @Json(name = "bottom") val bottom: Float
)

@JsonClass(generateAdapter = false)
data class LandmarkDto(
    @Json(name = "x") val x: Float,
    @Json(name = "y") val y: Float
)

@JsonClass(generateAdapter = false)
data class UploadScanResponse(
    @Json(name = "scan_id") val scanId: String?,
    @Json(name = "status") val status: String?,
    @Json(name = "message") val message: String?
)

@JsonClass(generateAdapter = false)
data class UploadChunkResponse(
    @Json(name = "scan_id") val scanId: String?,
    @Json(name = "chunk_index") val chunkIndex: Int?,
    @Json(name = "status") val status: String?
)

@JsonClass(generateAdapter = false)
data class ScanStatusResponse(
    @Json(name = "scan_id") val scanId: String?,
    @Json(name = "status") val status: String?,
    @Json(name = "message") val message: String?
)

@JsonClass(generateAdapter = false)
data class UnlockScanRequest(
    @Json(name = "pin") val pin: String
)

@JsonClass(generateAdapter = false)
data class UnlockScanResponse(
    @Json(name = "scan_id") val scanId: String?,
    @Json(name = "status") val status: String?,
    @Json(name = "unlocked") val unlocked: Boolean?
)

@JsonClass(generateAdapter = false)
data class ScanReportResponse(
    @Json(name = "scan") val scan: ScanSummaryDto,
    @Json(name = "metrics") val metrics: MetricsDto,
    @Json(name = "advanced_metrics") val advancedMetrics: AdvancedMetricsDto?,
    @Json(name = "heatmap_points") val heatmapPoints: List<ReportHeatmapPointDto>,
    @Json(name = "defects") val defects: List<DefectDto>,
    @Json(name = "general_tips") val generalTips: List<TipDto>,
    @Json(name = "spectral_analysis") val spectralAnalysis: List<SpectralAnalysisDto>?,
    @Json(name = "facial_zone_analysis") val facialZoneAnalysis: List<FacialZoneDto>?,
    @Json(name = "custom_arabic_analysis") val customArabicAnalysis: String?
)

@JsonClass(generateAdapter = false)
data class ScanSummaryDto(
    @Json(name = "id") val id: String,
    @Json(name = "user_id") val userId: String,
    @Json(name = "image_url") val imageUrl: String?,
    @Json(name = "spectral_image_urls") val spectralImageUrls: List<String>?,
    @Json(name = "status") val status: String,
    @Json(name = "analysis_status") val analysisStatus: String?,
    @Json(name = "overall_score") val overallScore: Int,
    @Json(name = "confidence") val confidence: Float?,
    @Json(name = "analyzed_by") val analyzedBy: String?,
    @Json(name = "created_at") val createdAt: String,
    @Json(name = "reviewed_at") val reviewedAt: String?,
    @Json(name = "analyzed_at") val analyzedAt: String?
)

@JsonClass(generateAdapter = false)
data class MetricsDto(
    @Json(name = "hydration") val hydration: Float,
    @Json(name = "sebum") val sebum: Float,
    @Json(name = "pigmentation") val pigmentation: Float,
    @Json(name = "pores") val pores: Float,
    @Json(name = "elasticity") val elasticity: Float
)

@JsonClass(generateAdapter = false)
data class ReportHeatmapPointDto(
    @Json(name = "x") val x: Float,
    @Json(name = "y") val y: Float,
    @Json(name = "severity") val severity: Float,
    @Json(name = "label") val label: String?,
    @Json(name = "label_ar") val labelAr: String?,
    @Json(name = "description") val description: String?,
    @Json(name = "description_ar") val descriptionAr: String?
)

@JsonClass(generateAdapter = false)
data class DefectDto(
    @Json(name = "id") val id: String,
    @Json(name = "name_ar") val nameAr: String,
    @Json(name = "name_en") val nameEn: String,
    @Json(name = "severity") val severity: Float,
    @Json(name = "tip_ar") val tipAr: String,
    @Json(name = "tip_en") val tipEn: String,
    @Json(name = "icon_name") val iconName: String,
    @Json(name = "recommended_products") val recommendedProducts: List<ReportProductDto>
)

@JsonClass(generateAdapter = false)
data class ReportProductDto(
    @Json(name = "id") val id: String,
    @Json(name = "name_ar") val nameAr: String,
    @Json(name = "name_en") val nameEn: String,
    @Json(name = "price") val price: Double,
    @Json(name = "image_url") val imageUrl: String,
    @Json(name = "shop_url") val shopUrl: String,
    @Json(name = "matching_reason") val matchingReason: String?,
    @Json(name = "matching_reason_ar") val matchingReasonAr: String?
)

@JsonClass(generateAdapter = false)
data class AdvancedMetricsDto(
    @Json(name = "brightness") val brightness: Int?,
    @Json(name = "texture") val texture: Int?,
    @Json(name = "redness") val redness: Int?,
    @Json(name = "sensitivity") val sensitivity: Int?,
    @Json(name = "oiliness") val oiliness: Int?
)

@JsonClass(generateAdapter = false)
data class SpectralAnalysisDto(
    @Json(name = "mode") val mode: String,
    @Json(name = "label") val label: String?,
    @Json(name = "label_ar") val labelAr: String?,
    @Json(name = "analysis_focus") val analysisFocus: String?,
    @Json(name = "score") val score: Int?
)

@JsonClass(generateAdapter = false)
data class FacialZoneDto(
    @Json(name = "zone") val zone: String,
    @Json(name = "name") val name: String?,
    @Json(name = "name_ar") val nameAr: String?,
    @Json(name = "severity") val severity: Int?,
    @Json(name = "note") val note: String?,
    @Json(name = "note_ar") val noteAr: String?
)

@JsonClass(generateAdapter = false)
data class TipDto(
    @Json(name = "ar") val ar: String?,
    @Json(name = "en") val en: String?
)

@JsonClass(generateAdapter = false)
data class ScanHistoryResponse(
    @Json(name = "scans") val scans: List<ScanSummaryDto>
)
