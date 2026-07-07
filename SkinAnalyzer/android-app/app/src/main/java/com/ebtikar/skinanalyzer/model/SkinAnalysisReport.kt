package com.ebtikar.skinanalyzer.model

import kotlinx.serialization.Serializable
import java.util.UUID

@Serializable
data class HeatmapPoint(
    val x: Float = 0f,
    val y: Float = 0f,
    val value: Float = 0f,
    val label: String? = null
)

@Serializable
data class SkinAnalysisReport(
    val id: String = UUID.randomUUID().toString(),
    val timestamp: Long = System.currentTimeMillis(),
    val providerName: String,
    val overallScore: Float,
    val metrics: List<SkinMetric>,
    val executionTimeMs: Long,
    val deviceModel: String = "ZMLH02",
    val firmwareVersion: String = "",
    val notes: String = "",
    val aiAnalysisText: String = "",
    val aiAnalysisTextAr: String = "",
    val expertTips: List<String> = emptyList(),
    val expertTipsAr: List<String> = emptyList(),
    val productRecommendations: List<ProductRecommendation> = emptyList(),
    val skinProfile: SkinProfile = SkinProfile(),
    val confidence: Float = 0.85f,
    val scanId: String = "",
    val heatmapPoints: List<HeatmapPoint> = emptyList()
) {
    val metricCount: Int get() = metrics.size

    fun getMetricByType(type: SkinMetric.Type): SkinMetric? =
        metrics.find { it.type == type }

    fun getMetricsByZone(zone: SkinZone): List<SkinMetric> =
        metrics.filter { it.zone == zone }

    fun getAverageScore(): Float =
        if (metrics.isEmpty()) 0f else metrics.map { it.score }.average().toFloat()

    fun getTopConcerns(limit: Int = 3): List<SkinMetric> =
        metrics.sortedBy { it.score }.take(limit)

    fun getExcellentMetrics(): List<SkinMetric> =
        metrics.filter { it.severity == MetricSeverity.EXCELLENT || it.severity == MetricSeverity.GOOD }

    fun getNeedsAttentionMetrics(): List<SkinMetric> =
        metrics.filter { it.severity == MetricSeverity.POOR || it.severity == MetricSeverity.CRITICAL }

    fun getRadarValues(): List<Float> =
        metrics.map { it.score }

    fun getRadarLabels(): List<String> =
        metrics.map { it.type.displayName() }

    private fun SkinMetric.Type.displayName(): String = when (this) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع"
        SkinMetric.Type.VASCULAR -> "الأوعية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس"
        SkinMetric.Type.ACNE -> "الحب"
        SkinMetric.Type.SKIN_TONE -> "اللون"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
