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
            .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_FAST)
            .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_ALL)
            .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_ALL)
            .setMinFaceSize(0.05f)
            .enableTracking()
            .build()

        FaceDetection.getClient(options)
    }

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
