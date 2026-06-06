package com.jenincare.skinanalyzer.ui.camera

import android.content.Context
import android.graphics.Bitmap
import android.graphics.RectF
import dagger.hilt.android.qualifiers.ApplicationContext
import org.tensorflow.lite.Interpreter
import org.tensorflow.lite.support.common.FileUtil
import org.tensorflow.lite.support.image.ImageProcessor
import org.tensorflow.lite.support.image.TensorImage
import org.tensorflow.lite.support.image.ops.ResizeOp
import org.tensorflow.lite.support.image.ops.Rot90Op
import java.nio.ByteBuffer
import java.nio.ByteOrder
import java.nio.FloatBuffer
import javax.inject.Inject

data class FaceDetectionResult(
    val boundingBox: RectF = RectF(),
    val landmarks: List<Pair<Float, Float>> = emptyList(),
    val confidence: Float = 0f,
    val pitch: Float = 0f,
    val yaw: Float = 0f,
    val roll: Float = 0f
)

data class LightingQuality(
    val overall: Float = 0f,
    val brightness: Float = 0f,
    val contrast: Float = 0f,
    val isAcceptable: Boolean = false
)

class TFLiteFaceDetector @Inject constructor(@ApplicationContext private val context: Context) {
    private var interpreter: Interpreter? = null
    private val inputSize = 320
    private val outputBoxCount = 896
    private val coordinateCount = 16
    private val landmarkCount = 10
    private val scoreCount = 1

    private var isInitialized = false

    init {
        initialize()
    }

    private fun initialize() {
        try {
            val model = FileUtil.loadMappedFile(context, "yyface_detect.tflite")
            val options = Interpreter.Options()
            options.setNumThreads(4)
            interpreter = Interpreter(model, options)
            isInitialized = true
        } catch (e: Exception) {
            isInitialized = false
        }
    }

    fun detectFace(bitmap: Bitmap): FaceDetectionResult {
        if (!isInitialized || interpreter == null) {
            return FaceDetectionResult(confidence = 1f)
        }

        try {
            val imageProcessor = ImageProcessor.Builder()
                .add(ResizeOp(inputSize, inputSize, ResizeOp.ResizeMethod.BILINEAR))
                .add(Rot90Op(0))
                .build()

            val tensorImage = TensorImage.fromBitmap(bitmap)
            val processedImage = imageProcessor.process(tensorImage)
            val inputBuffer = processedImage.tensorBuffer.floatArray

            val regressors = Array(1) {
                Array(outputBoxCount) { FloatArray(coordinateCount) }
            }
            val classificators = Array(1) {
                Array(outputBoxCount) { FloatArray(scoreCount) }
            }

            val outputMap = mapOf(
                0 to regressors,
                1 to classificators
            )

            interpreter?.runForMultipleInputsOutputs(
                arrayOf(
                    ByteBuffer.allocateDirect(inputSize * inputSize * 3 * 4)
                        .order(ByteOrder.nativeOrder())
                        .also { buffer ->
                            buffer.asFloatBuffer().put(inputBuffer)
                        }
                ),
                outputMap
            )

            var bestBox = RectF()
            var bestConfidence = 0f
            var bestLandmarks = listOf<Pair<Float, Float>>()

            for (i in 0 until outputBoxCount) {
                val confidence = sigmoid(classificators[0][i][0])
                if (confidence > bestConfidence) {
                    bestConfidence = confidence
                    val coords = regressors[0][i]

                    val cx = coords[0] / inputSize.toFloat()
                    val cy = coords[1] / inputSize.toFloat()
                    val w = coords[2] / inputSize.toFloat()
                    val h = coords[3] / inputSize.toFloat()

                    bestBox = RectF(
                        (cx - w / 2).coerceIn(0f, 1f),
                        (cy - h / 2).coerceIn(0f, 1f),
                        (cx + w / 2).coerceIn(0f, 1f),
                        (cy + h / 2).coerceIn(0f, 1f)
                    )

                    val landmarks = mutableListOf<Pair<Float, Float>>()
                    for (j in 0 until landmarkCount) {
                        val lx = coords[4 + j * 2] / inputSize.toFloat()
                        val ly = coords[4 + j * 2 + 1] / inputSize.toFloat()
                        landmarks.add(Pair(lx, ly))
                    }
                    bestLandmarks = landmarks
                }
            }

            return FaceDetectionResult(
                boundingBox = bestBox,
                landmarks = bestLandmarks,
                confidence = bestConfidence,
                pitch = 0f,
                yaw = 0f,
                roll = 0f
            )
        } catch (e: Exception) {
            return FaceDetectionResult(confidence = 0f)
        }
    }

    fun isFaceInFrame(bitmap: Bitmap): Boolean {
        val result = detectFace(bitmap)
        return result.confidence > 0.6f &&
                result.boundingBox.width() > 0.3f &&
                result.boundingBox.height() > 0.3f &&
                result.boundingBox.centerX() in 0.3f..0.7f &&
                result.boundingBox.centerY() in 0.3f..0.7f
    }

    fun checkLightingQuality(bitmap: Bitmap): Float {
        val pixels = IntArray(bitmap.width * bitmap.height)
        bitmap.getPixels(pixels, 0, bitmap.width, 0, 0, bitmap.width, bitmap.height)

        var totalLuminance = 0.0
        var minLuminance = Double.MAX_VALUE
        var maxLuminance = Double.MIN_VALUE

        for (pixel in pixels) {
            val r = (pixel shr 16) and 0xFF
            val g = (pixel shr 8) and 0xFF
            val b = pixel and 0xFF
            val luminance = 0.299 * r + 0.587 * g + 0.114 * b
            totalLuminance += luminance
            if (luminance < minLuminance) minLuminance = luminance
            if (luminance > maxLuminance) maxLuminance = luminance
        }

        val avgLuminance = totalLuminance / pixels.size
        val contrast = maxLuminance - minLuminance

        val brightnessScore = when {
            avgLuminance < 40 -> 0.1f
            avgLuminance < 80 -> 0.4f
            avgLuminance < 120 -> 0.6f
            avgLuminance < 180 -> 0.8f
            avgLuminance < 220 -> 1.0f
            else -> 0.7f
        }

        val contrastScore = when {
            contrast < 30 -> 0.2f
            contrast < 60 -> 0.5f
            contrast < 100 -> 0.7f
            contrast < 150 -> 0.9f
            else -> 1.0f
        }

        return (brightnessScore * 0.5f + contrastScore * 0.5f).coerceIn(0f, 1f)
    }

    private fun sigmoid(x: Float): Float {
        return if (x >= 0) {
            1.0f / (1.0f + kotlin.math.exp(-x))
        } else {
            val expX = kotlin.math.exp(x)
            expX / (1.0f + expX)
        }
    }

    fun release() {
        interpreter?.close()
        interpreter = null
        isInitialized = false
    }
}
