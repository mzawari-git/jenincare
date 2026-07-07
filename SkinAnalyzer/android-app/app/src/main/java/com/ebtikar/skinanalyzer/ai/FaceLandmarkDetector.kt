package com.ebtikar.skinanalyzer.ai

import android.graphics.Bitmap
import android.graphics.RectF
import com.google.mlkit.vision.common.InputImage
import com.google.mlkit.vision.face.Face
import com.google.mlkit.vision.face.FaceDetection
import com.google.mlkit.vision.face.FaceDetectorOptions
import kotlinx.coroutines.tasks.await
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FaceLandmarkDetector @Inject constructor() {

    private val detector by lazy {
        val options = FaceDetectorOptions.Builder()
            .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_ACCURATE)
            .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_ALL)
            .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_ALL)
            .setContourMode(FaceDetectorOptions.CONTOUR_MODE_ALL)
            .setMinFaceSize(0.05f)
            .enableTracking()
            .build()

        FaceDetection.getClient(options)
    }

    data class FaceQuality(
        val pitch: Float,
        val yaw: Float,
        val roll: Float,
        val leftEyeOpen: Float,
        val rightEyeOpen: Float,
        val smiling: Float,
        val overallQuality: Float
    )

    suspend fun detectFaces(bitmap: Bitmap): List<Face> {
        val image = InputImage.fromBitmap(bitmap, 0)
        return try {
            val faces = detector.process(image).await()
            Timber.d("Detected ${faces.size} face(s)")
            faces
        } catch (e: Exception) {
            Timber.e(e, "Face detection failed")
            emptyList()
        }
    }

    suspend fun detectFacesWithQuality(bitmap: Bitmap): Pair<List<Face>, FaceQuality?> {
        val faces = detectFaces(bitmap)
        val quality = if (faces.isNotEmpty()) evaluateFaceQuality(faces[0]) else null
        return faces to quality
    }

    private fun evaluateFaceQuality(face: Face): FaceQuality {
        val pitch = face.headEulerAngleX
        val yaw = face.headEulerAngleY
        val roll = face.headEulerAngleZ
        val leftEyeOpen = face.leftEyeOpenProbability ?: 0.5f
        val rightEyeOpen = face.rightEyeOpenProbability ?: 0.5f
        val smiling = face.smilingProbability ?: 0f

        val pitchScore = (1f - kotlin.math.abs(pitch) / 45f).coerceIn(0f, 1f)
        val yawScore = (1f - kotlin.math.abs(yaw) / 45f).coerceIn(0f, 1f)
        val rollScore = (1f - kotlin.math.abs(roll) / 30f).coerceIn(0f, 1f)
        val eyeOpenScore = (leftEyeOpen + rightEyeOpen) / 2f

        val overallQuality = pitchScore * 0.3f + yawScore * 0.3f + rollScore * 0.2f + eyeOpenScore * 0.2f

        return FaceQuality(
            pitch = pitch,
            yaw = yaw,
            roll = roll,
            leftEyeOpen = leftEyeOpen,
            rightEyeOpen = rightEyeOpen,
            smiling = smiling,
            overallQuality = overallQuality
        )
    }

    fun getPrimaryFaceBounds(faces: List<Face>): RectF? {
        return faces.maxByOrNull { it.boundingBox.width() * it.boundingBox.height() }
            ?.let { face ->
                RectF(
                    face.boundingBox.left.toFloat(),
                    face.boundingBox.top.toFloat(),
                    face.boundingBox.right.toFloat(),
                    face.boundingBox.bottom.toFloat()
                )
            }
    }

    fun alignFace(bitmap: Bitmap, faceBounds: RectF): Bitmap {
        val padding = 0.1f
        val left = (faceBounds.left - faceBounds.width() * padding).coerceAtLeast(0f)
        val top = (faceBounds.top - faceBounds.height() * padding).coerceAtLeast(0f)
        val right = (faceBounds.right + faceBounds.width() * padding).coerceAtMost(bitmap.width.toFloat())
        val bottom = (faceBounds.bottom + faceBounds.height() * padding).coerceAtMost(bitmap.height.toFloat())

        val x = left.toInt()
        val y = top.toInt()
        val width = (right - left).toInt()
        val height = (bottom - top).toInt()

        return Bitmap.createBitmap(bitmap, x, y, width, height)
    }

    fun close() {
        detector.close()
    }
}
