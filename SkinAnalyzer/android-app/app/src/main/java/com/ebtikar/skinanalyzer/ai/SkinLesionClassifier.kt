package com.ebtikar.skinanalyzer.ai

import android.content.Context
import android.graphics.Bitmap
import org.tensorflow.lite.Interpreter
import org.tensorflow.lite.support.common.FileUtil
import org.tensorflow.lite.support.image.TensorImage
import org.tensorflow.lite.support.tensorbuffer.TensorBuffer
import org.tensorflow.lite.DataType
import timber.log.Timber
import java.io.Closeable
import java.nio.ByteBuffer
import java.nio.ByteOrder

/**
 * Skin-specific TFLite model for dermatological analysis.
 * Uses a model trained on skin lesion datasets (HAM10000/ISIC) for accurate
 * classification of skin conditions.
 *
 * Model input: 224x224 RGB bitmap
 * Model output: 7-class probability distribution
 *
 * Classes:
 * 0: melanoma
 * 1: nevus (mole)
 * 2: seborrheic keratosis
 * 3: basal cell carcinoma
 * 4: dermatofibroma
 * 5: vascular lesion
 * 6: benign keratosis
 */
class SkinLesionClassifier(context: Context, modelPath: String = "models/skin_lesion_classifier.tflite") : Closeable {

    data class ClassificationResult(
        val className: String,
        val classNameAr: String,
        val confidence: Float,
        val isMalignant: Boolean,
        val riskLevel: RiskLevel
    )

    enum class RiskLevel(val displayNameAr: String) {
        LOW("منخفض"),
        MEDIUM("متوسط"),
        HIGH("مرتفع"),
        URGENT("عاجل")
    }

    private var interpreter: Interpreter? = null
    private val inputImageSize = 224
    private val labels = listOf(
        "melanoma" to "النmelanoma",
        "nevus" to "الشامة",
        "seborrheic_keratosis" to "الثعلبة الدهنية",
        "basal_cell_carcinoma" to "سرطان الخلايا القاعدية",
        "dermatofibroma" to "الورم الليفي الجلدي",
        "vascular_lesion" to "ال lesions الوعائية",
        "benign_keratosis" to "الثعلبة الحميدة"
    )

    init {
        try {
            val modelBuffer = FileUtil.loadMappedFile(context, modelPath)
            val options = Interpreter.Options().apply {
                setNumThreads(4)
                setUseNNAPI(true)
            }
            interpreter = Interpreter(modelBuffer, options)
            Timber.i("Skin lesion classifier loaded: $modelPath")
        } catch (e: Exception) {
            Timber.w(e, "Failed to load skin lesion classifier, using fallback")
            interpreter = null
        }
    }

    /**
     * Classify a skin image.
     * Returns the most likely skin condition with confidence.
     */
    fun classify(bitmap: Bitmap): ClassificationResult? {
        val interp = interpreter ?: return null

        return try {
            val resized = Bitmap.createScaledBitmap(bitmap, inputImageSize, inputImageSize, true)
            val input = TensorImage(DataType.FLOAT32)
            input.load(resized)
            val inputBuffer = input.buffer

            val outputBuffer = TensorBuffer.createFixedSize(intArrayOf(1, 7), DataType.FLOAT32)
            interp.run(inputBuffer, outputBuffer.buffer)

            val probabilities = outputBuffer.floatArray
            val maxIndex = probabilities.indices.maxByOrNull { probabilities[it] } ?: 0
            val maxConfidence = probabilities[maxIndex]

            val (className, classNameAr) = labels[maxIndex]
            val isMalignant = maxIndex == 0 || maxIndex == 3 // melanoma or BCC
            val riskLevel = when {
                maxConfidence < 0.5f -> RiskLevel.LOW
                isMalignant && maxConfidence > 0.7f -> RiskLevel.URGENT
                isMalignant -> RiskLevel.HIGH
                maxConfidence > 0.8f -> RiskLevel.MEDIUM
                else -> RiskLevel.LOW
            }

            Timber.d("Skin lesion classification: $className ($classNameAr) confidence=${"%.2f".format(maxConfidence)}")

            ClassificationResult(className, classNameAr, maxConfidence, isMalignant, riskLevel)
        } catch (e: Exception) {
            Timber.e(e, "Classification failed")
            null
        }
    }

    override fun close() {
        interpreter?.close()
    }

    companion object {
        /**
         * Check if the model file exists in assets.
         */
        fun isModelAvailable(context: Context): Boolean {
            return try {
                context.assets.open("models/skin_lesion_classifier.tflite").use { true }
            } catch (_: Exception) {
                false
            }
        }
    }
}
