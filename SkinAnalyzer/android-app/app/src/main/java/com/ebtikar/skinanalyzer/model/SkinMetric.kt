package com.ebtikar.skinanalyzer.model

import kotlinx.serialization.Serializable

enum class SkinZone {
    T_ZONE,
    U_ZONE,
    O_ZONE,
    EYE_AREA,
    FULL_FACE
}

enum class MetricSeverity(val displayAr: String) {
    EXCELLENT("ممتاز"),
    GOOD("جيد"),
    FAIR("متوسط"),
    POOR("ضعيف"),
    CRITICAL("شديد")
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
        SKIN_TONE,
        SENSITIVITY,
        ROSACEA,
        MELASMA
    }

    companion object {
        val ALL_TYPES = Type.entries.toList()
        const val TOTAL_METRICS = 15
    }
}

fun SkinMetric.Type.arabicName(): String = when (this) {
    SkinMetric.Type.MOISTURE -> "الرطوبة"
    SkinMetric.Type.PORES -> "المسام"
    SkinMetric.Type.SEBUM -> "الدهنية"
    SkinMetric.Type.WRINKLES -> "التجاعيد"
    SkinMetric.Type.TEXTURE -> "الملمس"
    SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
    SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
    SkinMetric.Type.PIGMENTATION -> "التصبغ"
    SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
    SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
    SkinMetric.Type.ACNE -> "حب الشباب"
    SkinMetric.Type.SKIN_TONE -> "لون البشرة"
    SkinMetric.Type.SENSITIVITY -> "الحساسية"
    SkinMetric.Type.ROSACEA -> "الوردية"
    SkinMetric.Type.MELASMA -> "الكلف"
}

fun SkinMetric.Type.iconRes(): Int = when (this) {
    SkinMetric.Type.MOISTURE -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_moisture
    SkinMetric.Type.PORES -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_pores
    SkinMetric.Type.SEBUM -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_sebum
    SkinMetric.Type.WRINKLES -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_wrinkles
    SkinMetric.Type.TEXTURE -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_texture
    SkinMetric.Type.UV_SPOTS -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_uv
    SkinMetric.Type.VASCULAR -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_vascular
    SkinMetric.Type.PIGMENTATION -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_spots
    SkinMetric.Type.DARK_CIRCLES -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_dark_circles
    SkinMetric.Type.BLACKHEADS -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_pores
    SkinMetric.Type.ACNE -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_sensitivity
    SkinMetric.Type.SKIN_TONE -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_texture
    SkinMetric.Type.SENSITIVITY -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_sensitivity
    SkinMetric.Type.ROSACEA -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_rosacea
    SkinMetric.Type.MELASMA -> com.ebtikar.skinanalyzer.R.drawable.ic_metric_melasma
}

@Serializable
data class ProductRecommendation(
    val id: String = "",
    val name: String,
    val nameAr: String = "",
    val brand: String = "",
    val category: String = "",
    val price: Float = 0f,
    val currency: String = "ILS",
    val imageUrl: String = "",
    val matchScore: Float = 0f,
    val reason: String = "",
    val reasonAr: String = "",
    val shopUrl: String = ""
) {
    val displayUrl: String
        get() = shopUrl.ifEmpty { "https://jenincare.shop/public/product/$id" }
}

@Serializable
data class SkinProfile(
    val skinType: String = "mixed",
    val skinTypeAr: String = "مختلطة",
    val hydrationLevel: String = "moderate",
    val sensitivityLevel: String = "low",
    val primaryConcerns: List<String> = emptyList(),
    val primaryConcernsAr: List<String> = emptyList()
)
