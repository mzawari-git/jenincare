package com.ebtikar.skinanalyzer.ai

import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton
import kotlin.math.abs

/**
 * Ensemble Analysis Engine — combines results from multiple analysis engines
 * using weighted voting to produce more robust and accurate metrics.
 *
 * Each engine contributes its metrics with a confidence weight.
 * The ensemble computes a weighted average across all engines that produced
 * a value for each metric type.
 */
@Singleton
class EnsembleAnalysisEngine @Inject constructor() {

    data class EngineResult(
        val engineName: String,
        val metrics: Map<SkinMetric.Type, SkinMetric>,
        val confidence: Float,
        val executionTimeMs: Long
    )

    data class EnsembleReport(
        val metrics: Map<SkinMetric.Type, SkinMetric>,
        val overallConfidence: Float,
        val engineContributions: Map<String, Float>,
        val engineCount: Int,
        val agreementScore: Float
    )

    companion object {
        /** Base weights for each engine (before health adjustment) */
        val BASE_WEIGHTS = mapOf(
            "Local_TFLite" to 0.35f,
            "Advanced_MediaPipe" to 0.30f,
            "CV_OpenCV" to 0.20f,
            "Basic_Pixel" to 0.15f
        )

        /** Minimum agreement threshold: if engines disagree by more than this, flag it */
        const val DISAGREEMENT_THRESHOLD = 25f

        /** Minimum number of engines for ensemble voting */
        const val MIN_ENGINES_FOR_ENSEMBLE = 2

        /** Maximum confidence cap */
        const val MAX_CONFIDENCE = 0.98f

        /** Minimum confidence floor */
        const val MIN_CONFIDENCE = 0.50f
    }

    /**
     * Combine results from multiple engines using weighted voting.
     * Returns an EnsembleReport with merged metrics and confidence scores.
     */
    fun combineResults(results: List<EngineResult>): EnsembleReport {
        if (results.isEmpty()) {
            return EnsembleReport(emptyMap(), 0f, emptyMap(), 0, 0f)
        }

        if (results.size == 1) {
            val single = results.first()
            return EnsembleReport(
                metrics = single.metrics,
                overallConfidence = single.confidence.coerceIn(MIN_CONFIDENCE, MAX_CONFIDENCE),
                engineContributions = mapOf(single.engineName to 1.0f),
                engineCount = 1,
                agreementScore = 1.0f
            )
        }

        // Collect all metric types across all engines
        val allTypes = results.flatMap { it.metrics.keys }.distinct()

        val mergedMetrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        val engineContributions = mutableMapOf<String, Float>()
        var totalWeight = 0f

        for (type in allTypes) {
            val contributions = results.filter { it.metrics.containsKey(type) }
            if (contributions.isEmpty()) continue

            // Calculate weighted average score
            var weightedScore = 0f
            var weightSum = 0f
            var bestDetails = ""
            var bestSeverity = MetricSeverity.GOOD
            val individualScores = mutableListOf<Float>()

            for (result in contributions) {
                val metric = result.metrics[type] ?: continue
                val baseWeight = BASE_WEIGHTS[result.engineName] ?: 0.1f
                val healthWeight = result.confidence  // confidence reflects engine health
                val effectiveWeight = baseWeight * healthWeight

                weightedScore += metric.score * effectiveWeight
                weightSum += effectiveWeight
                individualScores.add(metric.score)

                // Track the most detailed description
                if (metric.details.length > bestDetails.length) {
                    bestDetails = metric.details
                    bestSeverity = metric.severity
                }

                // Accumulate engine contributions
                engineContributions[result.engineName] =
                    (engineContributions[result.engineName] ?: 0f) + effectiveWeight
            }

            if (weightSum > 0) {
                val finalScore = weightedScore / weightSum

                // Calculate agreement: how much do engines agree?
                val agreement = if (individualScores.size > 1) {
                    val mean = individualScores.average().toFloat()
                    val variance = individualScores.map { (it - mean) * (it - mean) }.average().toFloat()
                    val stdDev = kotlin.math.sqrt(variance)
                    // Low std dev = high agreement (1.0 = perfect, 0.0 = no agreement)
                    (1f - (stdDev / DISAGREEMENT_THRESHOLD)).coerceIn(0f, 1f)
                } else {
                    1.0f  // Single engine = full agreement with itself
                }

                // Adjust confidence based on agreement and engine count
                val engineBonus = (contributions.size.coerceAtMost(4) / 4f) * 0.15f
                val agreementBonus = agreement * 0.10f
                val baseConfidence = results.map { it.confidence }.average().toFloat()
                val finalConfidence = (baseConfidence + engineBonus + agreementBonus)
                    .coerceIn(MIN_CONFIDENCE, MAX_CONFIDENCE)

                // Classify final score
                val severity = classifyScore(finalScore)

                // Build enhanced details string
                val engineNames = contributions.joinToString("+") { it.engineName }
                val enhancedDetails = "$bestDetails | Ensemble($engineNames) — اتفاق: ${"%.0f".format(agreement * 100)}%"

                mergedMetrics[type] = SkinMetric(
                    type = type,
                    score = finalScore,
                    severity = severity,
                    details = enhancedDetails
                )

                totalWeight += weightSum
            }
        }

        // Normalize engine contributions to sum to 1.0
        val totalContribution = engineContributions.values.sum()
        if (totalContribution > 0) {
            for (key in engineContributions.keys) {
                engineContributions[key] = engineContributions[key]!! / totalContribution
            }
        }

        // Overall agreement across all metrics
        val overallAgreement = if (mergedMetrics.isNotEmpty()) {
            // Re-calculate per-type agreement for overall
            var totalAgreement = 0f
            var typeCount = 0
            for (type in allTypes) {
                val scores = results.filter { it.metrics.containsKey(type) }
                    .mapNotNull { it.metrics[type]?.score }
                if (scores.size > 1) {
                    val mean = scores.average().toFloat()
                    val stdDev = kotlin.math.sqrt(
                        scores.map { (it - mean) * (it - mean) }.average().toFloat()
                    )
                    totalAgreement += (1f - (stdDev / DISAGREEMENT_THRESHOLD)).coerceIn(0f, 1f)
                    typeCount++
                }
            }
            if (typeCount > 0) totalAgreement / typeCount else 1f
        } else {
            0f
        }

        val overallConfidence = (results.map { it.confidence }.average().toFloat() +
            (results.size.coerceAtMost(4) / 4f) * 0.1f)
            .coerceIn(MIN_CONFIDENCE, MAX_CONFIDENCE)

        Timber.i("Ensemble: ${results.size} engines, ${mergedMetrics.size} metrics, " +
            "confidence=${"%.2f".format(overallConfidence)}, agreement=${"%.0f".format(overallAgreement * 100)}%")

        return EnsembleReport(
            metrics = mergedMetrics,
            overallConfidence = overallConfidence,
            engineContributions = engineContributions,
            engineCount = results.size,
            agreementScore = overallAgreement
        )
    }

    private fun classifyScore(score: Float): MetricSeverity = when {
        score >= 72f -> MetricSeverity.EXCELLENT
        score >= 55f -> MetricSeverity.GOOD
        score >= 35f -> MetricSeverity.FAIR
        score >= 20f -> MetricSeverity.POOR
        else -> MetricSeverity.CRITICAL
    }
}
