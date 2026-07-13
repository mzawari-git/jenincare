package com.ebtikar.skinanalyzer.ai

import android.graphics.Bitmap
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinZone
import timber.log.Timber
import java.nio.ByteBuffer
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FeatureExtractor @Inject constructor(
    private val tfliteEngine: TFLiteEngine
) {

    companion object {
        private const val MODEL_INPUT_SIZE = 512
        private const val NUM_SEGMENTATION_CLASSES = 3
    }

    fun extractFeatures(bitmap: Bitmap, spectrumType: String): Map<SkinMetric.Type, Float> {
        val resized = Bitmap.createScaledBitmap(bitmap, MODEL_INPUT_SIZE, MODEL_INPUT_SIZE, true)
        val inputBuffer = prepareInputBuffer(resized)
        val outputBuffer = tfliteEngine.createOutputBuffer(NUM_SEGMENTATION_CLASSES)

        tfliteEngine.runInference(inputBuffer, outputBuffer)

        return parseOutputs(outputBuffer, spectrumType)
    }

    fun segmentFace(bitmap: Bitmap): Map<SkinZone, Bitmap> {
        val resized = Bitmap.createScaledBitmap(bitmap, MODEL_INPUT_SIZE, MODEL_INPUT_SIZE, true)
        val inputBuffer = prepareInputBuffer(resized)
        val outputBuffer = tfliteEngine.createOutputBuffer(NUM_SEGMENTATION_CLASSES)

        tfliteEngine.runInference(inputBuffer, outputBuffer)

        return mapZones(bitmap, outputBuffer)
    }

    private fun prepareInputBuffer(bitmap: Bitmap): ByteBuffer {
        val buffer = tfliteEngine.createInputBuffer()
        val pixels = IntArray(MODEL_INPUT_SIZE * MODEL_INPUT_SIZE)
        bitmap.getPixels(pixels, 0, MODEL_INPUT_SIZE, 0, 0, MODEL_INPUT_SIZE, MODEL_INPUT_SIZE)

        for (pixel in pixels) {
            buffer.putFloat(((pixel shr 16 and 0xFF) / 255.0f).toFloat())
            buffer.putFloat(((pixel shr 8 and 0xFF) / 255.0f).toFloat())
            buffer.putFloat(((pixel and 0xFF) / 255.0f).toFloat())
        }
        buffer.rewind()
        return buffer
    }

    /** Aggregate stats for one output channel across the whole [MODEL_INPUT_SIZE]x[MODEL_INPUT_SIZE] map. */
    private data class ChannelStats(val mean: Float, val coverage: Float, val variance: Float)

    /**
     * The model output is a per-pixel map shaped [1, H, W, NUM_SEGMENTATION_CLASSES]
     * (e.g. one channel highlighting spots/lesions, one highlighting texture/edges,
     * one highlighting tone/color uniformity). A meaningful 0-100 metric score has to
     * be aggregated across the whole map - reading a handful of raw floats from the
     * very start of the buffer only ever reflected pixel (0,0) of channel 0, which is
     * effectively noise. This computes, per channel, the mean activation (overall
     * severity), the fraction of pixels above an "affected" threshold (coverage), and
     * the variance (unevenness) so each spectrum can derive several distinct metrics
     * from the same small set of channels.
     */
    private fun computeChannelStats(outputBuffer: ByteBuffer): List<ChannelStats> {
        outputBuffer.rewind()
        val pixelCount = MODEL_INPUT_SIZE * MODEL_INPUT_SIZE
        val sums = FloatArray(NUM_SEGMENTATION_CLASSES)
        val sumSquares = FloatArray(NUM_SEGMENTATION_CLASSES)
        val affectedCounts = IntArray(NUM_SEGMENTATION_CLASSES)
        val affectedThreshold = 0.5f

        for (i in 0 until pixelCount) {
            for (c in 0 until NUM_SEGMENTATION_CLASSES) {
                val v = outputBuffer.float
                sums[c] += v
                sumSquares[c] += v * v
                if (v >= affectedThreshold) affectedCounts[c]++
            }
        }

        return (0 until NUM_SEGMENTATION_CLASSES).map { c ->
            val mean = sums[c] / pixelCount
            val variance = (sumSquares[c] / pixelCount) - (mean * mean)
            ChannelStats(
                mean = mean.coerceIn(0f, 1f),
                coverage = affectedCounts[c].toFloat() / pixelCount,
                variance = variance.coerceAtLeast(0f)
            )
        }
    }

    /** Maps a 0..1 activation into a 0..100 health score. Higher activation = worse condition. */
    private fun toHealthScore(activation: Float, weight: Float = 1f, bias: Float = 0f): Float {
        val severity = (activation * weight + bias).coerceIn(0f, 1f)
        return ((1f - severity) * 100f).coerceIn(0f, 100f)
    }

    private fun parseOutputs(outputBuffer: ByteBuffer, spectrumType: String): Map<SkinMetric.Type, Float> {
        val stats = computeChannelStats(outputBuffer)
        val spotMask = stats[0]      // lesions / spots / discrete defects
        val textureMask = stats[1]   // edges / texture / fine structure
        val toneMask = stats[2]      // color / tone uniformity

        val results = mutableMapOf<SkinMetric.Type, Float>()

        when (spectrumType) {
            "WHITE" -> {
                results[SkinMetric.Type.TEXTURE] = toHealthScore(textureMask.mean, weight = 1.1f)
                results[SkinMetric.Type.SKIN_TONE] = toHealthScore(toneMask.variance, weight = 4f)
                results[SkinMetric.Type.PORES] = toHealthScore(spotMask.coverage, weight = 1.3f)
                results[SkinMetric.Type.WRINKLES] = toHealthScore(textureMask.variance, weight = 4f)
            }
            "UV365" -> {
                results[SkinMetric.Type.UV_SPOTS] = toHealthScore(spotMask.coverage, weight = 1.4f)
                results[SkinMetric.Type.PIGMENTATION] = toHealthScore(toneMask.mean, weight = 1.1f)
                results[SkinMetric.Type.PORPHYRINS] = toHealthScore(spotMask.mean, weight = 1.2f)
            }
            "POL_P" -> {
                results[SkinMetric.Type.VASCULAR] = toHealthScore(spotMask.coverage, weight = 1.3f)
                results[SkinMetric.Type.SENSITIVITY] = toHealthScore(toneMask.mean, weight = 1.1f)
                results[SkinMetric.Type.ROSACEA] = toHealthScore(spotMask.mean, weight = 1.2f)
            }
            "POL_N" -> {
                results[SkinMetric.Type.SEBUM] = toHealthScore(toneMask.mean, weight = 1.1f)
                results[SkinMetric.Type.BLACKHEADS] = toHealthScore(spotMask.coverage, weight = 1.4f)
                results[SkinMetric.Type.TEXTURE] = toHealthScore(textureMask.mean, weight = 1.1f)
            }
            "WOODS" -> {
                results[SkinMetric.Type.ACNE] = toHealthScore(spotMask.coverage, weight = 1.3f)
                results[SkinMetric.Type.MOISTURE] = toHealthScore(1f - toneMask.mean, weight = 1f)
                results[SkinMetric.Type.MELASMA] = toHealthScore(toneMask.mean, weight = 1.2f)
            }
            "BLUE" -> {
                results[SkinMetric.Type.SEBUM] = toHealthScore(toneMask.mean, weight = 1.1f)
                results[SkinMetric.Type.ACNE] = toHealthScore(spotMask.coverage, weight = 1.3f)
            }
            "RED" -> {
                results[SkinMetric.Type.COLLAGEN] = toHealthScore(textureMask.mean, weight = 1.1f)
                results[SkinMetric.Type.WRINKLES] = toHealthScore(textureMask.variance, weight = 4f)
            }
            "BROWN" -> {
                results[SkinMetric.Type.PIGMENTATION] = toHealthScore(toneMask.mean, weight = 1.1f)
                results[SkinMetric.Type.DARK_CIRCLES] = toHealthScore(spotMask.mean, weight = 1.2f)
            }
            else -> {
                Timber.w("Unrecognized spectrum type for feature extraction: $spectrumType")
            }
        }

        return results
    }

    private fun mapZones(originalBitmap: Bitmap, outputBuffer: ByteBuffer): Map<SkinZone, Bitmap> {
        outputBuffer.rewind()
        val zones = mutableMapOf<SkinZone, Bitmap>()

        val width = originalBitmap.width
        val height = originalBitmap.height
        val faceCenterX = width / 2
        val faceCenterY = height / 2

        zones[SkinZone.T_ZONE] = cropZone(originalBitmap, 
            (faceCenterX - width * 0.15f).toInt(),
            0,
            (width * 0.3f).toInt(),
            (height * 0.6f).toInt()
        )

        zones[SkinZone.U_ZONE] = cropZone(originalBitmap,
            (faceCenterX - width * 0.35f).toInt(),
            (height * 0.4f).toInt(),
            (width * 0.7f).toInt(),
            (height * 0.5f).toInt()
        )

        zones[SkinZone.EYE_AREA] = cropZone(originalBitmap,
            (faceCenterX - width * 0.3f).toInt(),
            (height * 0.2f).toInt(),
            (width * 0.6f).toInt(),
            (height * 0.2f).toInt()
        )

        return zones
    }

    private fun cropZone(bitmap: Bitmap, x: Int, y: Int, width: Int, height: Int): Bitmap {
        val safeX = x.coerceIn(0, bitmap.width - 1)
        val safeY = y.coerceIn(0, bitmap.height - 1)
        val safeW = width.coerceIn(1, bitmap.width - safeX)
        val safeH = height.coerceIn(1, bitmap.height - safeY)
        return Bitmap.createBitmap(bitmap, safeX, safeY, safeW, safeH)
    }
}

fun classifyScore(score: Float): MetricSeverity {
    return when {
        score >= 85f -> MetricSeverity.EXCELLENT
        score >= 70f -> MetricSeverity.GOOD
        score >= 55f -> MetricSeverity.FAIR
        score >= 35f -> MetricSeverity.POOR
        else -> MetricSeverity.CRITICAL
    }
}
