package com.ebtikar.skinanalyzer.ai

import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinZone
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton
import kotlin.math.abs

/**
 * Ensemble Analysis Engine — combines results from multiple analysis engines
 * using weighted median with outlier rejection for robust, clinically-accurate metrics.
 *
 * Uses median-based scoring: the middle value is taken as baseline, and engines
 * that deviate by >20 points get their weight halved (outlier penalty).
 * Zone-aware merging preserves per-region accuracy.
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

    data class ScoredEngine(
        val score: Float,
        val weight: Float,
        val zone: SkinZone,
        val details: String,
        val severity: MetricSeverity
    )

    companion object {
        val BASE_WEIGHTS = mapOf(
            "Local_TFLite" to 0.35f,
            "Advanced_MediaPipe" to 0.30f,
            "CV_OpenCV" to 0.20f,
            "Basic_Pixel" to 0.15f
        )

        const val DISAGREEMENT_THRESHOLD = 25f
        const val OUTLIER_THRESHOLD = 20f
        const val OUTLIER_PENALTY = 0.5f
        const val MAX_CONFIDENCE = 0.98f
        const val MIN_CONFIDENCE = 0.50f
    }

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

        val allTypes = results.flatMap { it.metrics.keys }.distinct()
        val mergedMetrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        val engineContributions = mutableMapOf<String, Float>()

        for (type in allTypes) {
            val contributions = results.filter { it.metrics.containsKey(type) }
            if (contributions.isEmpty()) continue

            val scored = contributions.map { result ->
                val metric = result.metrics[type]!!
                val baseWeight = BASE_WEIGHTS[result.engineName] ?: 0.1f
                val effectiveWeight = baseWeight * result.confidence
                ScoredEngine(
                    score = metric.score,
                    weight = effectiveWeight,
                    zone = metric.zone,
                    details = metric.details,
                    severity = metric.severity
                )
            }

            val finalScore: Float
            val agreement: Float
            val dominantZone: SkinZone

            if (scored.size == 1) {
                finalScore = scored[0].score
                agreement = 1.0f
                dominantZone = scored[0].zone
            } else {
                val sorted = scored.sortedBy { it.score }
                val median = sorted[sorted.size / 2].score

                var weightedSum = 0f
                var weightSum = 0f
                val individualScores = mutableListOf<Float>()

                for (s in scored) {
                    val deviation = abs(s.score - median)
                    val finalWeight = if (deviation > OUTLIER_THRESHOLD) {
                        Timber.d("Ensemble outlier: ${type.name} score=${s.score} deviates ${"%.1f".format(deviation)} from median $median — weight halved")
                        s.weight * OUTLIER_PENALTY
                    } else {
                        s.weight
                    }
                    weightedSum += s.score * finalWeight
                    weightSum += finalWeight
                    individualScores.add(s.score)

                    for (r in contributions) {
                        if (r.metrics[type]?.score == s.score) {
                            engineContributions[r.engineName] =
                                (engineContributions[r.engineName] ?: 0f) + finalWeight
                        }
                    }
                }

                finalScore = if (weightSum > 0) weightedSum / weightSum else median

                val mean = individualScores.average().toFloat()
                val variance = individualScores.map { (it - mean) * (it - mean) }.average().toFloat()
                val stdDev = kotlin.math.sqrt(variance)
                agreement = (1f - (stdDev / DISAGREEMENT_THRESHOLD)).coerceIn(0f, 1f)

                dominantZone = scored.maxByOrNull { it.weight }?.zone ?: SkinZone.FULL_FACE
            }

            val severity = classifyScore(finalScore)

            val bestDetails = scored.maxByOrNull { it.details.length }?.details ?: ""
            val engineNames = contributions.joinToString("+") { it.engineName }
            val enhancedDetails = "$bestDetails | Ensemble($engineNames) — اتفاق: ${"%.0f".format(agreement * 100)}%"

            val engineBonus = (contributions.size.coerceAtMost(4) / 4f) * 0.15f
            val agreementBonus = agreement * 0.10f
            val baseConfidence = results.map { it.confidence }.average().toFloat()
            val finalConfidence = (baseConfidence + engineBonus + agreementBonus)
                .coerceIn(MIN_CONFIDENCE, MAX_CONFIDENCE)

            mergedMetrics[type] = SkinMetric(
                type = type,
                score = finalScore,
                severity = severity,
                zone = dominantZone,
                details = enhancedDetails,
                confidence = finalConfidence
            )
        }

        val totalContribution = engineContributions.values.sum()
        if (totalContribution > 0) {
            for (key in engineContributions.keys) {
                engineContributions[key] = engineContributions[key]!! / totalContribution
            }
        }

        val overallAgreement = if (mergedMetrics.size > 1) {
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
            1f
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
