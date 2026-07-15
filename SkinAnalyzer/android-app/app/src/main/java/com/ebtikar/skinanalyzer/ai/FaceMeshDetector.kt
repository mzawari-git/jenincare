package com.ebtikar.skinanalyzer.ai

import android.content.Context
import android.graphics.Bitmap
import android.graphics.PointF
import android.graphics.RectF
import com.google.mediapipe.framework.image.BitmapImageBuilder
import com.google.mediapipe.tasks.core.BaseOptions
import com.google.mediapipe.tasks.vision.facelandmarker.FaceLandmarker
import com.google.mediapipe.tasks.vision.facelandmarker.FaceLandmarkerResult
import com.google.mlkit.vision.common.InputImage
import com.google.mlkit.vision.face.FaceDetection
import com.google.mlkit.vision.face.FaceDetectorOptions
import com.google.mlkit.vision.face.FaceLandmark
import dagger.hilt.android.qualifiers.ApplicationContext
import timber.log.Timber
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FaceMeshDetector @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private var landmarker: FaceLandmarker? = null
    private var useMediaPipe: Boolean = false

    private val mlKitDetector by lazy {
        val options = FaceDetectorOptions.Builder()
            .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_FAST)
            .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_ALL)
            .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_NONE)
            .setContourMode(FaceDetectorOptions.CONTOUR_MODE_NONE)
            .setMinFaceSize(0.15f)
            .build()
        FaceDetection.getClient(options)
    }

    data class FaceMeshResult(
        val landmarks: List<PointF>,
        val landmarks3D: List<FloatArray>,
        val faceRect: RectF,
        val confidence: Float
    )

    fun initialize(): Boolean {
        return try {
            val baseOptions = BaseOptions.builder()
                .setModelAssetPath("models/face_landmarker.task")
                .build()

            val options = FaceLandmarker.FaceLandmarkerOptions.builder()
                .setBaseOptions(baseOptions)
                .setOutputFaceBlendshapes(false)
                .setOutputFacialTransformationMatrixes(true)
                .setNumFaces(1)
                .setMinFaceDetectionConfidence(0.5f)
                .setMinFacePresenceConfidence(0.5f)
                .setMinTrackingConfidence(0.5f)
                .build()

            landmarker = FaceLandmarker.createFromOptions(context, options)
            useMediaPipe = true
            Timber.i("FaceMeshDetector initialized with MediaPipe")
            true
        } catch (e: Throwable) {
            Timber.w(e, "MediaPipe model not available, will use ML Kit fallback")
            useMediaPipe = false
            landmarker = null
            false
        }
    }

    fun detect(bitmap: Bitmap): FaceMeshResult? {
        if (useMediaPipe && landmarker != null) {
            val result = detectWithMediaPipe(bitmap)
            if (result != null) return result
            Timber.w("MediaPipe detection failed, falling back to ML Kit")
        }
        return detectWithMLKit(bitmap)
    }

    private fun detectWithMediaPipe(bitmap: Bitmap): FaceMeshResult? {
        val detector = landmarker ?: return null
        return try {
            val mpImage = BitmapImageBuilder(bitmap).build()
            val result: FaceLandmarkerResult = detector.detect(mpImage)

            if (result.faceLandmarks().isEmpty()) {
                Timber.w("MediaPipe: No face landmarks detected")
                return null
            }

            val faceLandmarks = result.faceLandmarks()[0]
            val w = bitmap.width.toFloat()
            val h = bitmap.height.toFloat()

            val landmarks = faceLandmarks.map { lm ->
                PointF(lm.x() * w, lm.y() * h)
            }

            val landmarks3D = faceLandmarks.map { lm ->
                floatArrayOf(lm.x() * w, lm.y() * h, lm.z() * w)
            }

            val minX = landmarks.minOf { it.x }
            val maxX = landmarks.maxOf { it.x }
            val minY = landmarks.minOf { it.y }
            val maxY = landmarks.maxOf { it.y }
            val faceRect = RectF(minX, minY, maxX, maxY)

            val confidence = 0.88f

            Timber.i("MediaPipe face mesh: ${landmarks.size} landmarks, rect=[${faceRect.left.toInt()},${faceRect.top.toInt()},${faceRect.right.toInt()},${faceRect.bottom.toInt()}]")

            FaceMeshResult(landmarks, landmarks3D, faceRect, confidence)
        } catch (e: Exception) {
            Timber.e(e, "MediaPipe face mesh detection failed")
            null
        }
    }

    private fun detectWithMLKit(bitmap: Bitmap): FaceMeshResult? {
        return try {
            val image = InputImage.fromBitmap(bitmap, 0)
            val faces = com.google.android.gms.tasks.Tasks.await(
                mlKitDetector.process(image), 5000, TimeUnit.MILLISECONDS
            )

            if (faces.isEmpty()) {
                Timber.w("ML Kit: No face detected")
                return null
            }

            val face = faces[0]
            val w = bitmap.width.toFloat()
            val h = bitmap.height.toFloat()
            val box = face.boundingBox

            val faceRect = RectF(box.left.toFloat(), box.top.toFloat(), box.right.toFloat(), box.bottom.toFloat())

            val landmarks = mutableListOf<PointF>()
            val landmarks3D = mutableListOf<FloatArray>()

            for (lm in face.allLandmarks) {
                val pt = PointF(lm.position.x.toFloat(), lm.position.y.toFloat())
                landmarks.add(pt)
                landmarks3D.add(floatArrayOf(pt.x, pt.y, 0f))
            }

            val centerX = faceRect.centerX()
            val centerY = faceRect.centerY()

            val facial128 = generateApproximateLandmarks(landmarks, box, w, h, centerX, centerY)

            Timber.i("ML Kit face: ${landmarks.size} landmarks, ${facial128.size} approximate points, rect=[${faceRect.left.toInt()},${faceRect.top.toInt()},${faceRect.right.toInt()},${faceRect.bottom.toInt()}]")

            FaceMeshResult(facial128, facial128.map { floatArrayOf(it.x, it.y, 0f) }, faceRect, 0.85f)
        } catch (e: Exception) {
            Timber.w(e, "ML Kit face detection failed")
            null
        }
    }

    private fun generateApproximateLandmarks(
        detected: List<PointF>,
        box: android.graphics.Rect,
        imgW: Float,
        imgH: Float,
        cx: Float,
        cy: Float
    ): List<PointF> {
        val result = mutableListOf<PointF>()
        val bW = box.width().toFloat()
        val bH = box.height().toFloat()
        val left = box.left.toFloat()
        val top = box.top.toFloat()

        val leftEye = detected.firstOrNull { isEyeLandmark(it, box) }
        val rightEye = detected.firstOrNull { isEyeLandmarkOpposite(it, box) }
        val nose = detected.firstOrNull { isNoseLandmark(it, box) }
        val mouthL = detected.firstOrNull { isMouthLandmark(it, box, left = true) }
        val mouthR = detected.firstOrNull { isMouthLandmark(it, box, left = false) }

        val eyeY = (leftEye?.y ?: rightEye?.y ?: (top + bH * 0.35f))
        val mouthY = (mouthL?.y ?: mouthR?.y ?: (top + bH * 0.75f))
        val noseX = nose?.x ?: cx
        val noseY = nose?.y ?: (top + bH * 0.55f)

        result.add(PointF(cx, top - bH * 0.05f))
        result.add(PointF(cx, top - bH * 0.05f))
        result.add(PointF(noseX, noseY))
        result.add(PointF(left, top))
        result.add(PointF(left + bW, top))
        result.add(PointF(left, top + bH * 0.5f))
        result.add(PointF(left + bW, top + bH * 0.5f))
        result.add(PointF(left, top + bH))
        result.add(PointF(left + bW, top + bH))
        result.add(PointF(cx, top + bH))
        result.add(PointF(cx, eyeY - bH * 0.05f))
        result.add(PointF(left + bW * 0.25f, eyeY))
        result.add(PointF(left + bW * 0.75f, eyeY))
        result.add(PointF(left + bW * 0.3f, mouthY))
        result.add(PointF(left + bW * 0.7f, mouthY))
        result.add(PointF(cx, mouthY))
        result.add(PointF(left + bW * 0.4f, eyeY - bH * 0.1f))
        result.add(PointF(left + bW * 0.6f, eyeY - bH * 0.1f))
        result.add(PointF(left + bW * 0.2f, top + bH * 0.2f))
        result.add(PointF(left + bW * 0.8f, top + bH * 0.2f))
        result.add(PointF(left + bW * 0.25f, top + bH * 0.15f))
        result.add(PointF(left + bW * 0.75f, top + bH * 0.15f))
        result.add(PointF(cx, top + bH * 0.15f))
        result.add(PointF(left + bW * 0.1f, cy))
        result.add(PointF(left + bW * 0.9f, cy))
        result.add(PointF(left + bW * 0.15f, top + bH * 0.8f))
        result.add(PointF(left + bW * 0.85f, top + bH * 0.8f))
        result.add(PointF(left + bW * 0.4f, top + bH * 0.9f))
        result.add(PointF(left + bW * 0.6f, top + bH * 0.9f))
        result.add(PointF(left + bW * 0.1f, top + bH * 0.35f))
        result.add(PointF(left + bW * 0.9f, top + bH * 0.35f))
        result.add(PointF(left + bW * 0.4f, eyeY))
        result.add(PointF(left + bW * 0.5f, eyeY))
        result.add(PointF(left + bW * 0.6f, eyeY))
        result.add(PointF(cx, top + bH * 0.85f))
        result.add(PointF(left + bW * 0.35f, top + bH * 0.65f))
        result.add(PointF(left + bW * 0.65f, top + bH * 0.65f))
        result.add(PointF(left + bW * 0.35f, top + bH * 0.35f))
        result.add(PointF(left + bW * 0.65f, top + bH * 0.35f))
        result.add(PointF(left + bW * 0.5f, top + bH * 0.65f))

        return result
    }

    private fun isEyeLandmark(pt: PointF, box: android.graphics.Rect): Boolean {
        return pt.x < box.centerX() && pt.y < box.centerY() + box.height() * 0.1f
    }

    private fun isEyeLandmarkOpposite(pt: PointF, box: android.graphics.Rect): Boolean {
        return pt.x > box.centerX() && pt.y < box.centerY() + box.height() * 0.1f
    }

    private fun isNoseLandmark(pt: PointF, box: android.graphics.Rect): Boolean {
        val cx = box.centerX()
        return pt.x in (cx - box.width() * 0.15f)..(cx + box.width() * 0.15f) &&
               pt.y > box.centerY() - box.height() * 0.1f &&
               pt.y < box.centerY() + box.height() * 0.3f
    }

    private fun isMouthLandmark(pt: PointF, box: android.graphics.Rect, left: Boolean): Boolean {
        val cx = box.centerX()
        return if (left) pt.x < cx && pt.y > box.centerY() + box.height() * 0.1f
               else pt.x > cx && pt.y > box.centerY() + box.height() * 0.1f
    }

    fun close() {
        try {
            landmarker?.close()
            landmarker = null
            mlKitDetector.close()
        } catch (e: Exception) {
            Timber.w(e, "Error closing FaceMeshDetector")
        }
    }
}
