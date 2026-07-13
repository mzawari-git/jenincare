package com.ebtikar.skinanalyzer.model

import kotlinx.serialization.Serializable

enum class SkinZone {
    T_ZONE,
    U_ZONE,
    O_ZONE,
    EYE_AREA,
    FULL_FACE
}

enum class MetricSeverity {
    EXCELLENT,
    GOOD,
    FAIR,
    POOR,
    CRITICAL
}

enum class MetricTrend {
    IMPROVING,
    STABLE,
    DECLINING
}

@Serializable
data class SkinMetric(
    val type: Type,
    val score: Float,
    val severity: MetricSeverity,
    val zone: SkinZone = SkinZone.FULL_FACE,
    val details: String = "",
    val recommendations: List<String> = emptyList(),
    val trend: MetricTrend = MetricTrend.STABLE,
    val previousScore: Float? = null,
    val confidence: Float = 0.85f
) {
    val trendDelta: Float?
        get() = previousScore?.let { score - it }

    val isImproved: Boolean
        get() = (trendDelta ?: 0f) > 0f

    @Serializable
    enum class Type {
        MOISTURE,
        PORES,
        SEBUM,
        WRINKLES,
        TEXTURE,
        UV_SPOTS,
        VASCULAR,
        PIGMENTATION,
        DARK_CIRCLES,
        BLACKHEADS,
        ACNE,
        COLLAGEN,
        SKIN_TONE,
        SENSITIVITY,
        PORPHYRINS,
        ROSACEA,
        MELASMA
    }

    companion object {
        val ALL_TYPES = Type.entries.toList()
        const val TOTAL_METRICS = 17
    }
}

@Serializable
data class ProductRecommendation(
    val id: String = "",
    val name: String,
    val nameAr: String = "",
    val brand: String = "",
    val category: String = "",
    val price: Float = 0f,
    val currency: String = "SAR",
    val imageUrl: String = "",
    val matchScore: Float = 0f,
    val reason: String = "",
    val reasonAr: String = ""
)

@Serializable
data class SkinProfile(
    val skinType: String = "mixed",
    val skinTypeAr: String = "مختلطة",
    val fitzpatrickLevel: Int = 3,
    val hydrationLevel: String = "moderate",
    val sensitivityLevel: String = "low",
    val ageEstimate: Int = 0,
    val primaryConcerns: List<String> = emptyList(),
    val primaryConcernsAr: List<String> = emptyList()
)
