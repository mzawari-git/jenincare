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

    private fun parseOutputs(outputBuffer: ByteBuffer, spectrumType: String): Map<SkinMetric.Type, Float> {
        outputBuffer.rewind()
        val results = mutableMapOf<SkinMetric.Type, Float>()

        when (spectrumType) {
            "WHITE" -> {
                results[SkinMetric.Type.TEXTURE] = outputBuffer.float
                results[SkinMetric.Type.SKIN_TONE] = outputBuffer.float
                results[SkinMetric.Type.PORES] = outputBuffer.float
                results[SkinMetric.Type.WRINKLES] = outputBuffer.float
            }
            "UV365" -> {
                results[SkinMetric.Type.UV_SPOTS] = outputBuffer.float
                results[SkinMetric.Type.PIGMENTATION] = outputBuffer.float
                results[SkinMetric.Type.PORPHYRINS] = outputBuffer.float
            }
            "POL_P" -> {
                results[SkinMetric.Type.VASCULAR] = outputBuffer.float
                results[SkinMetric.Type.SENSITIVITY] = outputBuffer.float
                results[SkinMetric.Type.ROSACEA] = outputBuffer.float
            }
            "POL_N" -> {
                results[SkinMetric.Type.SEBUM] = outputBuffer.float
                results[SkinMetric.Type.BLACKHEADS] = outputBuffer.float
                results[SkinMetric.Type.TEXTURE] = outputBuffer.float
            }
            "WOODS" -> {
                results[SkinMetric.Type.ACNE] = outputBuffer.float
                results[SkinMetric.Type.MOISTURE] = outputBuffer.float
                results[SkinMetric.Type.MELASMA] = outputBuffer.float
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
