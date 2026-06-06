package com.ebtikar.skinanalyzer.data.remote

import com.ebtikar.skinanalyzer.core.provider.AnalysisResult
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import kotlinx.coroutines.delay
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class MockAnalysisEngine @Inject constructor() {

    suspend fun generateMockResult(providerName: String): AnalysisResult {
        delay(1500)

        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

        val spectrumMetrics = mapOf(
            SkinMetric.Type.TEXTURE to (65..90).random().toFloat(),
            SkinMetric.Type.SKIN_TONE to (70..92).random().toFloat(),
            SkinMetric.Type.PORES to (55..85).random().toFloat(),
            SkinMetric.Type.UV_SPOTS to (60..88).random().toFloat(),
            SkinMetric.Type.PIGMENTATION to (58..87).random().toFloat(),
            SkinMetric.Type.VASCULAR to (62..90).random().toFloat(),
            SkinMetric.Type.SENSITIVITY to (55..85).random().toFloat(),
            SkinMetric.Type.SEBUM to (50..80).random().toFloat(),
            SkinMetric.Type.BLACKHEADS to (45..78).random().toFloat(),
            SkinMetric.Type.ACNE to (50..82).random().toFloat(),
            SkinMetric.Type.MOISTURE to (60..90).random().toFloat(),
            SkinMetric.Type.WRINKLES to (55..88).random().toFloat(),
            SkinMetric.Type.DARK_CIRCLES to (48..80).random().toFloat(),
            SkinMetric.Type.COLLAGEN to (58..85).random().toFloat()
        )

        for ((type, score) in spectrumMetrics) {
            val severity = classifyMockScore(score)
            metrics[type] = SkinMetric(
                type = type,
                score = score,
                severity = severity,
                details = "Mock analysis data"
            )
        }

        Timber.i("Mock analysis generated: ${metrics.size} metrics")

        return AnalysisResult(
            providerName = providerName,
            executionTimeMs = 1500,
            metrics = metrics,
            confidence = 0.75f
        )
    }

    private fun classifyMockScore(score: Float): MetricSeverity {
        return when {
            score >= 85f -> MetricSeverity.EXCELLENT
            score >= 70f -> MetricSeverity.GOOD
            score >= 55f -> MetricSeverity.FAIR
            score >= 35f -> MetricSeverity.POOR
            else -> MetricSeverity.CRITICAL
        }
    }
}
