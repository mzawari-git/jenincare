package com.ebtikar.skinanalyzer.ai

import com.ebtikar.skinanalyzer.core.provider.AnalysisProvider
import com.ebtikar.skinanalyzer.core.provider.AnalysisResult
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class LocalTFLiteProvider @Inject constructor(
    private val tfliteEngine: TFLiteEngine,
    private val featureExtractor: FeatureExtractor
) : AnalysisProvider {

    override fun getName() = "Local_TFLite_Engine"
    override fun getPriority() = 10

    override fun isAvailable(): Boolean = tfliteEngine.isInitialized()

    override fun initialize(): Result<Unit> {
        val result = tfliteEngine.initialize()
        if (result.isFailure) {
            Timber.w("TFLite engine initialization failed: ${result.exceptionOrNull()?.message}")
        }
        return result
    }

    override suspend fun analyze(images: Map<String, File>): AnalysisResult {
        return withContext(Dispatchers.Default) {
            val startTime = System.currentTimeMillis()
            val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()

            try {
                for ((spectrumName, file) in images) {
                    val bitmap = CVUtils.decodeSampled(file, 512) ?: continue
                    try {
                        val features = featureExtractor.extractFeatures(bitmap, spectrumName)

                        for ((type, score) in features) {
                            val severity = classifyScore(score)
                            metrics[type] = SkinMetric(
                                type = type,
                                score = score,
                                severity = severity,
                                details = "Analyzed via $spectrumName spectrum"
                            )
                        }
                    } finally {
                        bitmap.recycle()
                    }
                }

                val executionTime = System.currentTimeMillis() - startTime
                Timber.i("Local analysis complete: ${metrics.size} metrics in ${executionTime}ms")

                AnalysisResult(
                    providerName = getName(),
                    executionTimeMs = executionTime,
                    metrics = metrics,
                    confidence = 0.85f
                )
            } catch (e: Exception) {
                Timber.e(e, "Local analysis failed")
                AnalysisResult(
                    providerName = getName(),
                    executionTimeMs = System.currentTimeMillis() - startTime,
                    metrics = emptyMap(),
                    warnings = listOf("Analysis error: ${e.message}")
                )
            }
        }
    }

    override fun shutdown() {
        tfliteEngine.shutdown()
    }
}
