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

@Serializable
data class SkinMetric(
    val type: Type,
    val score: Float,
    val severity: MetricSeverity,
    val zone: SkinZone = SkinZone.FULL_FACE,
    val details: String = "",
    val recommendations: List<String> = emptyList()
) {
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
        SENSITIVITY
    }

    companion object {
        val ALL_TYPES = Type.entries.toList()
        const val TOTAL_METRICS = 14
    }
}
