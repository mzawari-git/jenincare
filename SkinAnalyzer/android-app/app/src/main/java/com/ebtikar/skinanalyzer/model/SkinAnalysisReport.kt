package com.ebtikar.skinanalyzer.model

import kotlinx.serialization.Serializable
import java.util.UUID

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
    val notes: String = ""
) {
    val metricCount: Int get() = metrics.size

    fun getMetricByType(type: SkinMetric.Type): SkinMetric? =
        metrics.find { it.type == type }

    fun getMetricsByZone(zone: SkinZone): List<SkinMetric> =
        metrics.filter { it.zone == zone }

    fun getAverageScore(): Float =
        if (metrics.isEmpty()) 0f else metrics.map { it.score }.average().toFloat()
}
