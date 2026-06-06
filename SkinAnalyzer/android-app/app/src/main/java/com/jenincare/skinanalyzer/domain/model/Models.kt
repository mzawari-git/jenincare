package com.jenincare.skinanalyzer.domain.model

enum class ScanStatus {
    PENDING,
    IN_REVIEW,
    APPROVED,
    REJECTED;

    companion object {
        fun fromString(value: String): ScanStatus = when (value.lowercase()) {
            "pending" -> PENDING
            "in_review", "in-review" -> IN_REVIEW
            "approved" -> APPROVED
            "rejected" -> REJECTED
            else -> PENDING
        }
    }

    fun toStringAr(): String = when (this) {
        PENDING -> "قيد الانتظار"
        IN_REVIEW -> "قيد المراجعة"
        APPROVED -> "تمت الموافقة"
        REJECTED -> "مرفوض"
    }
}

data class Scan(
    val id: String,
    val userId: String,
    val imageUrl: String?,
    val status: ScanStatus,
    val overallScore: Int,
    val createdAt: String,
    val reviewedAt: String?
)

data class RadarMetric(
    val nameAr: String,
    val nameEn: String,
    val value: Float
)

data class HeatmapPoint(
    val x: Float,
    val y: Float,
    val severity: Float,
    val label: String,
    val description: String
)

data class Defect(
    val id: String,
    val nameAr: String,
    val nameEn: String,
    val severity: Float,
    val tipAr: String,
    val tipEn: String,
    val iconName: String,
    val products: List<ProductRecommendation>
)

data class ProductRecommendation(
    val id: String,
    val nameAr: String,
    val nameEn: String,
    val price: Double,
    val imageUrl: String,
    val shopUrl: String,
    val reason: String
)

data class ScanReport(
    val scan: Scan,
    val radarMetrics: List<RadarMetric>,
    val advancedMetrics: Map<String, Int> = emptyMap(),
    val heatmapPoints: List<HeatmapPoint>,
    val defects: List<Defect>,
    val tips: List<String>,
    val spectralAnalysis: List<SpectralAnalysisEntry> = emptyList(),
    val facialZoneAnalysis: List<FacialZoneEntry> = emptyList(),
    val customArabicAnalysis: String? = null,
    val spectralImageUrls: List<String> = emptyList()
)

data class SpectralAnalysisEntry(
    val mode: String,
    val label: String,
    val labelAr: String,
    val analysisFocus: String,
    val score: Int
)

data class FacialZoneEntry(
    val zone: String,
    val name: String,
    val nameAr: String,
    val severity: Int,
    val note: String?,
    val noteAr: String?
)
