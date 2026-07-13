package com.ebtikar.skinanalyzer.core.provider

import com.ebtikar.skinanalyzer.model.MetricSeverity
import java.io.File

interface AnalysisProvider {
    fun getName(): String
    fun isAvailable(): Boolean
    fun getPriority(): Int
    fun initialize(): Result<Unit>
    suspend fun analyze(images: Map<String, File>): AnalysisResult
    fun shutdown()

    fun classifyScore(score: Float): MetricSeverity {
        return when {
            score >= 90f -> MetricSeverity.EXCELLENT
            score >= 75f -> MetricSeverity.GOOD
            score >= 60f -> MetricSeverity.FAIR
            score >= 40f -> MetricSeverity.POOR
            else -> MetricSeverity.CRITICAL
        }
    }
}
