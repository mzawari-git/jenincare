package com.ebtikar.skinanalyzer.ai

import android.graphics.Bitmap
import org.opencv.android.Utils
import org.opencv.core.Core
import org.opencv.core.CvType
import org.opencv.core.Mat
import org.opencv.core.MatOfDouble
import org.opencv.core.MatOfPoint
import org.opencv.core.Rect
import org.opencv.core.Scalar
import org.opencv.core.Size
import org.opencv.imgproc.Imgproc
import timber.log.Timber

/**
 * OpenCV-based skin analysis engine.
 * Provides high-accuracy CV algorithms using the native OpenCV library.
 */
object OpenCvSkinEngine {

    init {
        try {
            System.loadLibrary("opencv_java4")
            Timber.i("OpenCV native library loaded successfully")
        } catch (e: UnsatisfiedLinkError) {
            Timber.e(e, "Failed to load OpenCV native library")
        }
    }

    data class TextureAnalysisResult(
        val roughness: Float,
        val smoothness: Float,
        val wrinkleDepth: Float,
        val poreVisibility: Float
    )

    data class ColorAnalysisResult(
        val rednessIndex: Float,
        val pigmentationScore: Float,
        val uniformity: Float,
        val melaninIndex: Float
    )

    data class SpotDetectionResult(
        val spotCount: Int,
        val coveragePercent: Float,
        val avgIntensity: Float,
        val severityScore: Float
    )

    fun laplacianVariance(bitmap: Bitmap): Float {
        val src = bitmapToMat(bitmap) ?: return 0f
        try {
            val gray = Mat()
            Imgproc.cvtColor(src, gray, Imgproc.COLOR_RGBA2GRAY)

            val blurred = Mat()
            Imgproc.GaussianBlur(gray, blurred, Size(3.0, 3.0), 0.0)

            val laplacian = Mat()
            Imgproc.Laplacian(blurred, laplacian, CvType.CV_64F)

            val mean = MatOfDouble()
            val stdDev = MatOfDouble()
            Core.meanStdDev(laplacian, mean, stdDev)

            val stdVal = stdDev.get(0, 0)!![0]
            val variance = stdVal * stdVal
            return variance.toFloat()
        } catch (e: Exception) {
            Timber.w(e, "OpenCV laplacianVariance failed")
            return 0f
        } finally {
            src.release()
        }
    }

    fun cannyEdgeRatio(bitmap: Bitmap, lowThreshold: Double = 50.0, highThreshold: Double = 150.0): Float {
        val src = bitmapToMat(bitmap) ?: return 0f
        try {
            val gray = Mat()
            Imgproc.cvtColor(src, gray, Imgproc.COLOR_RGBA2GRAY)

            val blurred = Mat()
            Imgproc.GaussianBlur(gray, blurred, Size(5.0, 5.0), 1.4)

            val edges = Mat()
            Imgproc.Canny(blurred, edges, lowThreshold, highThreshold)

            val totalPixels = edges.rows() * edges.cols()
            val edgePixels = Core.countNonZero(edges)

            return edgePixels.toFloat() / totalPixels
        } catch (e: Exception) {
            Timber.w(e, "OpenCV cannyEdgeRatio failed")
            return 0f
        } finally {
            src.release()
        }
    }

    fun createSkinMask(bitmap: Bitmap): Array<BooleanArray> {
        val src = bitmapToMat(bitmap) ?: return Array(bitmap.height) { BooleanArray(bitmap.width) }
        try {
            val hsv = Mat()
            Imgproc.cvtColor(src, hsv, Imgproc.COLOR_RGBA2RGB)
            Imgproc.cvtColor(hsv, hsv, Imgproc.COLOR_RGB2HSV)

            val lowerBound = Scalar(0.0, 20.0, 70.0)
            val upperBound = Scalar(25.0, 150.0, 255.0)
            val mask = Mat()
            Core.inRange(hsv, lowerBound, upperBound, mask)

            val kernel = Imgproc.getStructuringElement(Imgproc.MORPH_ELLIPSE, Size(5.0, 5.0))
            val cleaned = Mat()
            Imgproc.morphologyEx(mask, cleaned, Imgproc.MORPH_CLOSE, kernel)
            Imgproc.morphologyEx(cleaned, cleaned, Imgproc.MORPH_OPEN, kernel)

            val rows = src.rows()
            val cols = src.cols()
            val result = Array(rows) { BooleanArray(cols) }
            for (y in 0 until rows) {
                for (x in 0 until cols) {
                    result[y][x] = cleaned.get(y, x)[0] > 0
                }
            }
            return result
        } catch (e: Exception) {
            Timber.w(e, "OpenCV createSkinMask failed")
            return Array(bitmap.height) { BooleanArray(bitmap.width) }
        } finally {
            src.release()
        }
    }

    fun segmentFaceRegions(bitmap: Bitmap): Map<String, Rect> {
        val src = bitmapToMat(bitmap) ?: return emptyMap()
        try {
            val gray = Mat()
            Imgproc.cvtColor(src, gray, Imgproc.COLOR_RGBA2GRAY)

            val blurred = Mat()
            Imgproc.GaussianBlur(gray, blurred, Size(7.0, 7.0), 0.0)

            val thresh = Mat()
            Imgproc.adaptiveThreshold(blurred, thresh, 255.0,
                Imgproc.ADAPTIVE_THRESH_GAUSSIAN_C, Imgproc.THRESH_BINARY_INV, 11, 2.0)

            val kernel = Imgproc.getStructuringElement(Imgproc.MORPH_RECT, Size(3.0, 3.0))
            val opened = Mat()
            Imgproc.morphologyEx(thresh, opened, Imgproc.MORPH_OPEN, kernel)

            val contours = mutableListOf<MatOfPoint>()
            val hierarchy = Mat()
            Imgproc.findContours(opened, contours, hierarchy, Imgproc.RETR_EXTERNAL, Imgproc.CHAIN_APPROX_SIMPLE)

            val largestContour = contours.maxByOrNull { Imgproc.contourArea(it) } ?: return emptyMap()
            val boundingRect = Imgproc.boundingRect(largestContour)

            val w = boundingRect.width
            val h = boundingRect.height
            val x = boundingRect.x
            val y = boundingRect.y

            return mapOf(
                "T_ZONE" to Rect(x + w / 3, y, w / 3, h / 2),
                "LEFT_CHEEK" to Rect(x, y + h / 3, w / 3, h / 3),
                "RIGHT_CHEEK" to Rect(x + 2 * w / 3, y + h / 3, w / 3, h / 3),
                "EYE_AREA" to Rect(x + w / 4, y + h / 4, w / 2, h / 4),
                "CHIN" to Rect(x + w / 3, y + 2 * h / 3, w / 3, h / 3)
            )
        } catch (e: Exception) {
            Timber.w(e, "OpenCV segmentFaceRegions failed")
            return emptyMap()
        } finally {
            src.release()
        }
    }

    fun multiScaleTextureAnalysis(bitmap: Bitmap): TextureAnalysisResult {
        val src = bitmapToMat(bitmap) ?: return TextureAnalysisResult(0f, 1f, 0f, 0f)
        try {
            val gray = Mat()
            Imgproc.cvtColor(src, gray, Imgproc.COLOR_RGBA2GRAY)

            val totalPixels = (gray.rows() * gray.cols()).toFloat()

            val fineEdges = Mat()
            Imgproc.Canny(gray, fineEdges, 30.0, 90.0)
            val fineRatio = Core.countNonZero(fineEdges) / totalPixels

            val mediumBlur = Mat()
            Imgproc.GaussianBlur(gray, mediumBlur, Size(3.0, 3.0), 0.0)
            val mediumEdges = Mat()
            Imgproc.Canny(mediumBlur, mediumEdges, 50.0, 150.0)
            val mediumRatio = Core.countNonZero(mediumEdges) / totalPixels

            val coarseBlur = Mat()
            Imgproc.GaussianBlur(gray, coarseBlur, Size(7.0, 7.0), 0.0)
            val coarseEdges = Mat()
            Imgproc.Canny(coarseBlur, coarseEdges, 80.0, 200.0)
            val coarseRatio = Core.countNonZero(coarseEdges) / totalPixels

            val gaborKernel = Imgproc.getGaborKernel(
                Size(21.0, 21.0), 4.0, Math.PI / 4, Math.PI / 2, 0.5, 0.0, CvType.CV_32F
            )
            val gaborResult = Mat()
            Imgproc.filter2D(gray, gaborResult, CvType.CV_32F, gaborKernel)
            val gaborStdDev = MatOfDouble()
            Core.meanStdDev(gaborResult, MatOfDouble(), gaborStdDev)
            val gaborEnergy = gaborStdDev.get(0, 0)!![0].toFloat()

            val roughness = (fineRatio * 0.4f + mediumRatio * 0.35f + coarseRatio * 0.25f).coerceIn(0f, 1f)
            val smoothness = (1f - roughness).coerceIn(0f, 1f)
            val wrinkleDepth = (mediumRatio * 0.5f + coarseRatio * 0.5f).coerceIn(0f, 1f)
            val poreVisibility = (fineRatio * 0.7f + gaborEnergy / 50f * 0.3f).coerceIn(0f, 1f)

            return TextureAnalysisResult(roughness, smoothness, wrinkleDepth, poreVisibility)
        } catch (e: Exception) {
            Timber.w(e, "OpenCV multiScaleTextureAnalysis failed")
            return TextureAnalysisResult(0f, 1f, 0f, 0f)
        } finally {
            src.release()
        }
    }

    fun detectSpots(bitmap: Bitmap): SpotDetectionResult {
        val src = bitmapToMat(bitmap) ?: return SpotDetectionResult(0, 0f, 0f, 0f)
        try {
            val gray = Mat()
            Imgproc.cvtColor(src, gray, Imgproc.COLOR_RGBA2GRAY)

            val blurred = Mat()
            Imgproc.GaussianBlur(gray, blurred, Size(5.0, 5.0), 0.0)

            val thresh = Mat()
            Imgproc.adaptiveThreshold(blurred, thresh, 255.0,
                Imgproc.ADAPTIVE_THRESH_GAUSSIAN_C, Imgproc.THRESH_BINARY_INV, 15, 4.0)

            val kernel = Imgproc.getStructuringElement(Imgproc.MORPH_ELLIPSE, Size(3.0, 3.0))
            val cleaned = Mat()
            Imgproc.morphologyEx(thresh, cleaned, Imgproc.MORPH_OPEN, kernel)
            Imgproc.morphologyEx(cleaned, cleaned, Imgproc.MORPH_CLOSE, kernel)

            val contours = mutableListOf<MatOfPoint>()
            val hierarchy = Mat()
            Imgproc.findContours(cleaned, contours, hierarchy, Imgproc.RETR_EXTERNAL, Imgproc.CHAIN_APPROX_SIMPLE)

            val totalPixels = gray.rows() * gray.cols()
            var totalSpotArea = 0.0
            var totalIntensity = 0.0
            var spotCount = 0

            for (contour in contours) {
                val area = Imgproc.contourArea(contour)
                if (area > 15.0 && area < totalPixels * 0.05) {
                    spotCount++
                    totalSpotArea += area

                    val boundingRect = Imgproc.boundingRect(contour)
                    val roi = gray.submat(boundingRect)
                    totalIntensity += Core.mean(roi).`val`[0]
                }
            }

            val coverage = (totalSpotArea / totalPixels * 100).toFloat()
            val avgIntensity = if (spotCount > 0) (totalIntensity / spotCount / 255 * 100).toFloat() else 0f
            val severity = (coverage * 0.6f + avgIntensity * 0.4f).coerceIn(0f, 100f)

            return SpotDetectionResult(spotCount, coverage, avgIntensity, severity)
        } catch (e: Exception) {
            Timber.w(e, "OpenCV detectSpots failed")
            return SpotDetectionResult(0, 0f, 0f, 0f)
        } finally {
            src.release()
        }
    }

    fun analyzeColor(bitmap: Bitmap): ColorAnalysisResult {
        val src = bitmapToMat(bitmap) ?: return ColorAnalysisResult(0f, 0f, 1f, 0.5f)
        try {
            val lab = Mat()
            Imgproc.cvtColor(src, lab, Imgproc.COLOR_RGBA2RGB)
            Imgproc.cvtColor(lab, lab, Imgproc.COLOR_RGB2Lab)

            val channels = mutableListOf<Mat>()
            Core.split(lab, channels)

            val meanL = Core.mean(channels[0]).`val`[0]
            val meanA = Core.mean(channels[1]).`val`[0]

            val stdL = MatOfDouble()
            val stdA = MatOfDouble()
            val stdB = MatOfDouble()
            Core.meanStdDev(channels[0], MatOfDouble(), stdL)
            Core.meanStdDev(channels[1], MatOfDouble(), stdA)
            Core.meanStdDev(channels[2], MatOfDouble(), stdB)

            val stdLVal = stdL.get(0, 0)!![0].toFloat()
            val stdAVal = stdA.get(0, 0)!![0].toFloat()
            val stdBVal = stdB.get(0, 0)!![0].toFloat()

            val rednessIndex = ((meanA - 128.0) / 127.0 * 100.0).coerceIn(0.0, 100.0).toFloat()
            val pigmentationVariance = (stdLVal + stdAVal + stdBVal) / 3f
            val uniformity = (1f - pigmentationVariance / 50f).coerceIn(0f, 1f)
            val melaninIndex = ((255.0 - meanL) / 255.0).coerceIn(0.0, 1.0).toFloat()

            return ColorAnalysisResult(rednessIndex, pigmentationVariance, uniformity, melaninIndex)
        } catch (e: Exception) {
            Timber.w(e, "OpenCV analyzeColor failed")
            return ColorAnalysisResult(0f, 0f, 1f, 0.5f)
        } finally {
            src.release()
        }
    }

    fun applyClahe(bitmap: Bitmap, clipLimit: Double = 2.0, tileGridSize: Int = 8): Bitmap {
        val src = bitmapToMat(bitmap) ?: return bitmap
        try {
            val lab = Mat()
            Imgproc.cvtColor(src, lab, Imgproc.COLOR_RGBA2RGB)
            Imgproc.cvtColor(lab, lab, Imgproc.COLOR_RGB2Lab)

            val channels = mutableListOf<Mat>()
            Core.split(lab, channels)

            val clahe = Imgproc.createCLAHE(clipLimit, Size(tileGridSize.toDouble(), tileGridSize.toDouble()))
            clahe.apply(channels[0], channels[0])

            Core.merge(channels, lab)
            val result = Mat()
            Imgproc.cvtColor(lab, result, Imgproc.COLOR_Lab2RGB)
            Imgproc.cvtColor(result, result, Imgproc.COLOR_RGB2RGBA)

            val output = Bitmap.createBitmap(result.cols(), result.rows(), Bitmap.Config.ARGB_8888)
            Utils.matToBitmap(result, output)
            return output
        } catch (e: Exception) {
            Timber.w(e, "OpenCV applyClahe failed")
            return bitmap
        } finally {
            src.release()
        }
    }

    private fun bitmapToMat(bitmap: Bitmap): Mat? {
        return try {
            val mat = Mat(bitmap.height, bitmap.width, CvType.CV_8UC4)
            Utils.bitmapToMat(bitmap, mat)
            mat
        } catch (e: Exception) {
            Timber.e(e, "Failed to convert bitmap to Mat")
            null
        }
    }
}
