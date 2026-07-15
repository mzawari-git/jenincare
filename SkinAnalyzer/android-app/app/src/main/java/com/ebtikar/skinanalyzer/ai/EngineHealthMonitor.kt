package com.ebtikar.skinanalyzer.ai

import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton
import java.util.concurrent.ConcurrentHashMap

/**
 * Tracks the health and performance of each analysis engine.
 * Maintains rolling statistics for success rate, execution time, and confidence.
 * Used to dynamically adjust ensemble weights based on recent engine reliability.
 */
@Singleton
class EngineHealthMonitor @Inject constructor() {

    data class EngineStats(
        val totalAttempts: Int = 0,
        val successfulAttempts: Int = 0,
        val failedAttempts: Int = 0,
        val avgExecutionTimeMs: Float = 0f,
        val avgConfidence: Float = 0f,
        val lastError: String? = null,
        val lastSuccessTimestamp: Long = 0L,
        val lastFailureTimestamp: Long = 0L
    ) {
        val successRate: Float
            get() = if (totalAttempts > 0) successfulAttempts.toFloat() / totalAttempts else 0f

        val isHealthy: Boolean
            get() = totalAttempts == 0 || (successRate > 0.5f && failedAttempts < 3)

        /** Health-adjusted weight multiplier (0.5 to 1.0) */
        val healthWeight: Float
            get() = when {
                totalAttempts == 0 -> 0.8f  // Unknown engine, moderate weight
                successRate >= 0.9f -> 1.0f  // Excellent health
                successRate >= 0.7f -> 0.85f // Good health
                successRate >= 0.5f -> 0.7f  // Fair health
                else -> 0.5f                 // Poor health, minimal weight
            }
    }

    private val stats = ConcurrentHashMap<String, EngineStats>()

    /** Rolling window size for average calculations */
    private val rollingWindowSize = 10

    fun recordSuccess(engineName: String, executionTimeMs: Long, confidence: Float, metricsCount: Int) {
        val current = stats[engineName] ?: EngineStats()
        val newTotal = current.totalAttempts + 1
        val newSuccessful = current.successfulAttempts + 1

        // Update rolling average execution time
        val oldTimeTotal = current.avgExecutionTimeMs * current.successfulAttempts
        val newAvgTime = (oldTimeTotal + executionTimeMs) / newSuccessful

        // Update rolling average confidence
        val oldConfTotal = current.avgConfidence * current.successfulAttempts
        val newAvgConf = (oldConfTotal + confidence) / newSuccessful

        stats[engineName] = current.copy(
            totalAttempts = newTotal,
            successfulAttempts = newSuccessful,
            failedAttempts = current.failedAttempts,
            avgExecutionTimeMs = newAvgTime,
            avgConfidence = newAvgConf,
            lastError = null,
            lastSuccessTimestamp = System.currentTimeMillis()
        )

        Timber.d("EngineHealth: $engineName success (#$newTotal, ${executionTimeMs}ms, conf=${"%.2f".format(confidence.toDouble())}, metrics=$metricsCount)")
    }

    fun recordFailure(engineName: String, error: String, executionTimeMs: Long = 0) {
        val current = stats[engineName] ?: EngineStats()
        stats[engineName] = current.copy(
            totalAttempts = current.totalAttempts + 1,
            failedAttempts = current.failedAttempts + 1,
            lastError = error,
            lastFailureTimestamp = System.currentTimeMillis()
        )

        Timber.w("EngineHealth: $engineName FAILED (#${current.failedAttempts + 1}): $error")
    }

    fun getStats(engineName: String): EngineStats {
        return stats[engineName] ?: EngineStats()
    }

    fun getAllStats(): Map<String, EngineStats> {
        return stats.toMap()
    }

    /**
     * Get health-adjusted weights for ensemble combining.
     * Returns engine name -> adjusted weight.
     */
    fun getAdjustedWeights(): Map<String, Float> {
        val baseWeights = EnsembleAnalysisEngine.BASE_WEIGHTS.toMutableMap()
        val adjusted = mutableMapOf<String, Float>()

        for ((engine, baseWeight) in baseWeights) {
            val health = stats[engine]
            val healthMultiplier = health?.healthWeight ?: 0.8f
            adjusted[engine] = baseWeight * healthMultiplier
        }

        // Normalize to sum to 1.0
        val total = adjusted.values.sum()
        if (total > 0) {
            for (key in adjusted.keys) {
                adjusted[key] = adjusted[key]!! / total
            }
        }

        return adjusted
    }

    /**
     * Get a summary report of all engine health.
     */
    fun getHealthReport(): String = buildString {
        appendLine("=== Engine Health Report ===")
        for ((name, stat) in stats.entries.sortedByDescending { it.value.successRate }) {
            appendLine("$name:")
            appendLine("  Attempts: ${stat.totalAttempts} (${stat.successfulAttempts} OK, ${stat.failedAttempts} FAIL)")
            appendLine("  Success Rate: ${"%.0f".format(stat.successRate * 100)}%")
            appendLine("  Health Weight: ${"%.2f".format(stat.healthWeight)}")
            appendLine("  Avg Time: ${"%.0f".format(stat.avgExecutionTimeMs)}ms")
            appendLine("  Avg Confidence: ${"%.2f".format(stat.avgConfidence)}")
            stat.lastError?.let { appendLine("  Last Error: $it") }
        }
        appendLine()
        appendLine("Adjusted Weights:")
        val weights = getAdjustedWeights()
        for ((name, weight) in weights.entries.sortedByDescending { it.value }) {
            appendLine("  $name: ${"%.3f".format(weight)}")
        }
    }

    fun reset() {
        stats.clear()
    }
}
