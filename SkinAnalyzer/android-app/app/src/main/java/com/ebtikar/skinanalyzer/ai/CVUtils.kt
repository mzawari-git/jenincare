package com.ebtikar.skinanalyzer.ai

import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Matrix
import android.graphics.Rect
import android.media.ExifInterface
import kotlin.math.PI
import kotlin.math.cos
import kotlin.math.exp
import kotlin.math.pow
import kotlin.math.sin
import kotlin.math.sqrt
import kotlin.math.roundToInt
import kotlin.math.absoluteValue
import kotlin.math.max
import kotlin.math.min
import timber.log.Timber
import java.io.File
import kotlin.math.atan2

object CVUtils {

    data class PixelStats(
        val meanR: Float, val meanG: Float, val meanB: Float,
        val brightness: Float, val variance: Float, val contrast: Float
    )

    fun computePixelStats(bitmap: Bitmap, sampleSize: Int = 8): PixelStats {
        if (bitmap.isRecycled) {
            Timber.w("computePixelStats: bitmap is recycled")
            return PixelStats(0f, 0f, 0f, 0f, 0f, 0f)
        }
        val w = bitmap.width / sampleSize
        val h = bitmap.height / sampleSize
        var sumR = 0L; var sumG = 0L; var sumB = 0L; var sumBright = 0L
        var count = 0
        val brightnessValues = mutableListOf<Float>()

        for (y in 0 until h) {
            for (x in 0 until w) {
                val pixel = bitmap.getPixel(x * sampleSize, y * sampleSize)
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF
                val bright = r * 0.299f + g * 0.587f + b * 0.114f
                sumR += r; sumG += g; sumB += b
                sumBright += bright.toLong()
                brightnessValues.add(bright)
                count++
            }
        }
        if (count == 0) return PixelStats(0f, 0f, 0f, 0f, 0f, 0f)

        val meanR = sumR.toFloat() / count
        val meanG = sumG.toFloat() / count
        val meanB = sumB.toFloat() / count
        val meanBright = sumBright.toFloat() / count
        val variance = brightnessValues.map { (it - meanBright) * (it - meanBright) }.average().toFloat()
        val contrast = sqrt(variance) / 255f * 100f

        return PixelStats(meanR, meanG, meanB, meanBright / 255f * 100f, variance / 100f, contrast)
    }

    fun extractRegion(
        bitmap: Bitmap,
        leftFrac: Float, topFrac: Float,
        rightFrac: Float, bottomFrac: Float
    ): Bitmap? {
        val w = bitmap.width; val h = bitmap.height
        val x = (w * leftFrac).toInt().coerceIn(0, w - 1)
        val y = (h * topFrac).toInt().coerceIn(0, h - 1)
        val rw = (w * (rightFrac - leftFrac)).toInt().coerceIn(1, w - x)
        val rh = (h * (bottomFrac - topFrac)).toInt().coerceIn(1, h - y)
        return Bitmap.createBitmap(bitmap, x, y, rw, rh)
    }

    fun extractFaceRegion(bitmap: Bitmap, faceRect: Rect): Bitmap? {
        val x = (faceRect.left - faceRect.width() * 0.2f).toInt().coerceIn(0, bitmap.width - 1)
        val y = (faceRect.top - faceRect.height() * 0.2f).toInt().coerceIn(0, bitmap.height - 1)
        val w = (faceRect.width() * 1.4f).toInt().coerceAtMost(bitmap.width - x)
        val h = (faceRect.height() * 1.4f).toInt().coerceAtMost(bitmap.height - y)
        return Bitmap.createBitmap(bitmap, x, y, w, h)
    }

    fun laplacianVariance(bitmap: Bitmap): Float {
        val gray = toGray(bitmap)
        val w = gray.first; val h = gray.second
        if (w < 3 || h < 3) return 0f
        val pixels = gray.third

        var sumSq = 0f
        var count = 0

        val kernel = arrayOf(
            intArrayOf(0, -1, 0),
            intArrayOf(-1, 4, -1),
            intArrayOf(0, -1, 0)
        )

        for (y in 1 until h - 1) {
            for (x in 1 until w - 1) {
                var lap = 0f
                for (ky in -1..1) {
                    for (kx in -1..1) {
                        lap += pixels[(y + ky) * w + (x + kx)] * kernel[ky + 1][kx + 1]
                    }
                }
                sumSq += lap * lap
                count++
            }
        }
        return if (count > 0) sqrt(sumSq / count) else 0f
    }

    fun cannyEdgeRatio(bitmap: Bitmap, lowThreshold: Float = 50f, highThreshold: Float = 150f): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 3 || h < 3) return 0f
        val (mag, dir) = sobelGradients(gray, w, h)
        val nms = nonMaxSuppression(mag, dir, w, h)
        val edges = doubleThreshold(nms, w, h, lowThreshold, highThreshold)
        val edgeCount = edges.count { it > 0 }
        val totalPixels = (w - 2) * (h - 2)
        return if (totalPixels > 0) edgeCount.toFloat() / totalPixels else 0f
    }

    fun hsvRednessIndex(bitmap: Bitmap, mask: Bitmap? = null): Float {
        if (bitmap.isRecycled) { Timber.w("hsvRednessIndex: bitmap recycled"); return 0f }
        val w = bitmap.width; val h = bitmap.height
        var redPixels = 0
        var totalPixels = 0
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        val maskPixels = if (mask != null) {
            val mp = IntArray(mask.width * mask.height)
            mask.getPixels(mp, 0, mask.width, 0, 0, mask.width, mask.height)
            mp
        } else null

        val maskW = mask?.width ?: w
        for (y in 0 until h) {
            for (x in 0 until w) {
                if (maskPixels != null) {
                    val mi = y * maskW + x
                    if (mi >= maskPixels.size) continue
                    val mp = maskPixels[mi] and 0xFF
                    if (mp < 128) continue
                }
                val pixel = pixels[y * w + x]
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF

                val hsv = rgbToHsv(r, g, b)
                val hue = hsv.first
                val sat = hsv.second
                val value = hsv.third

                if ((hue <= 10f || hue >= 160f) && sat > 50f && value > 50f) {
                    redPixels++
                }
                totalPixels++
            }
        }
        return if (totalPixels > 0) redPixels.toFloat() / totalPixels else 0f
    }

    fun adaptiveThresholdSpots(bitmap: Bitmap, blockSize: Int = 11, c: Int = 5): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < blockSize || h < blockSize) return 0f
        var spotPixels = 0
        var totalPixels = 0
        val radius = blockSize / 2

        for (y in radius until h - radius) {
            for (x in radius until w - radius) {
                var sum = 0f
                for (ky in -radius..radius) {
                    for (kx in -radius..radius) {
                        sum += gray[(y + ky) * w + (x + kx)]
                    }
                }
                val mean = sum / (blockSize * blockSize).toFloat()
                val center = gray[y * w + x]
                if (center < mean - c) spotPixels++
                totalPixels++
            }
        }
        return if (totalPixels > 0) spotPixels.toFloat() / totalPixels else 0f
    }

    fun brightnessUniformity(bitmap: Bitmap): Pair<Float, Float> {
        val (w, h, gray) = toGray(bitmap)
        var sum = 0L; var sumSq = 0L
        for (i in gray.indices) {
            val v = gray[i].toLong()
            sum += v; sumSq += v * v
        }
        val count = gray.size.toFloat()
        val mean = sum / count
        val variance = (sumSq / count) - (mean * mean)
        return Pair(mean.toFloat() / 255f * 100f, sqrt(variance.toFloat()) / 128f * 100f)
    }

    fun hsvSebumIndex(bitmap: Bitmap): Float {
        if (bitmap.isRecycled) { Timber.w("hsvSebumIndex: bitmap recycled"); return 0f }
        val w = bitmap.width; val h = bitmap.height
        var brightPixels = 0
        var totalPixels = 0
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        for (y in 0 until h) {
            for (x in 0 until w) {
                val pixel = pixels[y * w + x]
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF
                val hsv = rgbToHsv(r, g, b)
                val sat = hsv.second; val value = hsv.third
                if (value > 200f && sat < 50f) brightPixels++
                totalPixels++
            }
        }
        return if (totalPixels > 0) brightPixels.toFloat() / totalPixels else 0f
    }

    fun labColorUniformity(bitmap: Bitmap): Float {
        if (bitmap.isRecycled) { Timber.w("labColorUniformity: bitmap recycled"); return 0f }
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        val aVals = FloatArray(w * h)
        val bVals = FloatArray(w * h)
        var count = 0

        for (y in 0 until h step 4) {
            for (x in 0 until w step 4) {
                val pixel = pixels[y * w + x]
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF
                val labLinear = rgbToLabApprox(r, g, b)
                aVals[count] = labLinear.second
                bVals[count] = labLinear.third
                count++
            }
        }

        var meanA = 0f; var meanB = 0f
        for (i in 0 until count) { meanA += aVals[i]; meanB += bVals[i] }
        meanA /= count; meanB /= count

        var varA = 0f; var varB = 0f
        for (i in 0 until count) {
            varA += (aVals[i] - meanA) * (aVals[i] - meanA)
            varB += (bVals[i] - meanB) * (bVals[i] - meanB)
        }
        varA = sqrt(varA / count); varB = sqrt(varB / count)
        return (varA + varB) / 2f
    }

    fun specularHighlightRatio(bitmap: Bitmap): Float {
        val (w, h, gray) = toGray(bitmap)
        val threshold = gray.average().toFloat() * 1.3f
        var specular = 0f
        var count = 0f
        for (v in gray) {
            if (v > threshold) specular++
            count++
        }
        return if (count > 0) specular / count else 0f
    }

    fun createSkinMask(bitmap: Bitmap): Bitmap {
        if (bitmap.isRecycled) { Timber.w("createSkinMask: bitmap recycled"); return Bitmap.createBitmap(1, 1, Bitmap.Config.ARGB_8888) }
        val w = bitmap.width; val h = bitmap.height
        val mask = IntArray(w * h)
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)
        for (i in pixels.indices) {
            val p = pixels[i]
            val r = p shr 16 and 0xFF
            val g = p shr 8 and 0xFF
            val b = p and 0xFF
            val (h, s, v) = rgbToHsv(r, g, b)
            mask[i] = if (h in 0f..85f && s in 2f..95f && v in 4f..100f) 0xFFFFFFFF.toInt() else 0
        }
        val out = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        out.setPixels(mask, 0, w, 0, 0, w, h)
        return out
    }

    fun skinPixelFraction(bitmap: Bitmap): Float {
        if (bitmap.isRecycled) { Timber.w("skinPixelFraction: bitmap recycled"); return 0f }
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)
        var skinCount = 0
        for (p in pixels) {
            val r = p shr 16 and 0xFF
            val g = p shr 8 and 0xFF
            val b = p and 0xFF
            val (_, s, v) = rgbToHsv(r, g, b)
            if (s in 2f..95f && v in 4f..100f) skinCount++
        }
        return skinCount.toFloat() / pixels.size
    }

    fun findLargestSkinRegion(bitmap: Bitmap, padding: Float = 0.1f): android.graphics.Rect? {
        if (bitmap.isRecycled) { Timber.w("findLargestSkinRegion: bitmap recycled"); return null }
        val w = bitmap.width
        val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        val skinMask = IntArray(w * h) { i ->
            val p = pixels[i]
            val r = p shr 16 and 0xFF
            val g = p shr 8 and 0xFF
            val b = p and 0xFF
            val (h, s, v) = rgbToHsv(r, g, b)
            if (h in 0f..85f && s in 2f..95f && v in 4f..100f) 1 else 0
        }

        val totalSkinPixels = skinMask.sum()

        val visited = BooleanArray(w * h)
        var largestArea = 0
        var largestRect: android.graphics.Rect? = null
        val minArea = (w * h * 0.01f).toInt() // Lowered to 1% for distant/small faces

        val dx = intArrayOf(-1, 1, 0, 0)
        val dy = intArrayOf(0, 0, -1, 1)

        for (y in 0 until h) {
            for (x in 0 until w) {
                val idx = y * w + x
                if (skinMask[idx] != 1 || visited[idx]) continue

                val stack = mutableListOf(idx)
                visited[idx] = true
                var minX = x; var maxX = x
                var minY = y; var maxY = y
                var area = 0

                while (stack.isNotEmpty()) {
                    val ci = stack.removeAt(stack.lastIndex)
                    val cx = ci % w
                    val cy = ci / w
                    area++
                    if (cx < minX) minX = cx
                    if (cx > maxX) maxX = cx
                    if (cy < minY) minY = cy
                    if (cy > maxY) maxY = cy

                    for (d in 0..3) {
                        val nx = cx + dx[d]
                        val ny = cy + dy[d]
                        if (nx in 0 until w && ny in 0 until h) {
                            val ni = ny * w + nx
                            if (skinMask[ni] == 1 && !visited[ni]) {
                                visited[ni] = true
                                stack.add(ni)
                            }
                        }
                    }
                }

                if (area > largestArea && area >= minArea) {
                    largestArea = area
                    largestRect = android.graphics.Rect(minX, minY, maxX + 1, maxY + 1)
                }
            }
        }

        if (largestRect == null) {
            Timber.d("findLargestSkinRegion: NO region found. totalSkinPixels=$totalSkinPixels (${100f * totalSkinPixels / (w * h)}%%), minArea=$minArea, image=${w}x$h")
        } else {
            Timber.d("findLargestSkinRegion: region=${largestRect.width()}x${largestRect.height()} area=$largestArea/${w * h} (${100f * largestArea / (w * h)}%%)")
        }
        return largestRect?.let {
            val padX = (it.width() * padding).toInt().coerceAtLeast(10)
            val padY = (it.height() * padding).toInt().coerceAtLeast(10)
            android.graphics.Rect(
                (it.left - padX).coerceAtLeast(0),
                (it.top - padY).coerceAtLeast(0),
                (it.right + padX).coerceAtMost(w),
                (it.bottom + padY).coerceAtMost(h)
            )
        }
    }

    fun decodeSampled(file: File, maxSize: Int = 1024): Bitmap? {
        return try {
            val opts = BitmapFactory.Options().apply { inJustDecodeBounds = true }
            BitmapFactory.decodeFile(file.absolutePath, opts)
            val (w, h) = opts.outWidth to opts.outHeight
            var sample = 1
            while (w / sample > maxSize && h / sample > maxSize) sample *= 2
            val bitmap = BitmapFactory.Options().apply { inSampleSize = sample }.let {
                BitmapFactory.decodeFile(file.absolutePath, it)
            } ?: return null
            val ei = ExifInterface(file.absolutePath)
            val orientation = ei.getAttributeInt(ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL)
            val matrix = Matrix()
            when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> matrix.postRotate(90f)
                ExifInterface.ORIENTATION_ROTATE_180 -> matrix.postRotate(180f)
                ExifInterface.ORIENTATION_ROTATE_270 -> matrix.postRotate(270f)
                ExifInterface.ORIENTATION_FLIP_HORIZONTAL -> matrix.preScale(-1f, 1f)
                ExifInterface.ORIENTATION_FLIP_VERTICAL -> matrix.preScale(1f, -1f)
                ExifInterface.ORIENTATION_TRANSPOSE -> { matrix.preScale(-1f, 1f); matrix.postRotate(90f) }
                ExifInterface.ORIENTATION_TRANSVERSE -> { matrix.preScale(1f, -1f); matrix.postRotate(90f) }
            }
            if (!matrix.isIdentity) {
                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                if (rotated !== bitmap) bitmap.recycle()
                rotated
            } else bitmap
        } catch (e: Exception) { null }
    }

    fun calibratedScore(value: Float, worst: Float, best: Float, maxScore: Float = 85f, minScore: Float = 15f): Float {
        if (value >= worst) return minScore
        if (value <= best) return maxScore
        return ((worst - value) / (worst - best) * (maxScore - minScore) + minScore).coerceIn(minScore, maxScore)
    }

    fun calibratedScoreInverted(value: Float, best: Float, worst: Float, maxScore: Float = 85f, minScore: Float = 15f): Float {
        if (value <= best) return maxScore
        if (value >= worst) return minScore
        return (maxScore + minScore - ((value - best) / (worst - best) * (maxScore - minScore) + minScore)).coerceIn(minScore, maxScore)
    }

    private fun toGray(bitmap: Bitmap): Triple<Int, Int, FloatArray> {
        if (bitmap.isRecycled) {
            Timber.w("toGray: bitmap is recycled — returning empty gray. Stack:\n${Throwable().stackTraceToString()}")
            return Triple(1, 1, floatArrayOf(0f))
        }
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)
        val gray = FloatArray(w * h) { i ->
            val p = pixels[i]
            (p shr 16 and 0xFF) * 0.299f + (p shr 8 and 0xFF) * 0.587f + (p and 0xFF) * 0.114f
        }
        return Triple(w, h, gray)
    }

    private fun sobelGradients(gray: FloatArray, w: Int, h: Int): Pair<FloatArray, FloatArray> {
        val mag = FloatArray(w * h)
        val dir = FloatArray(w * h)
        for (y in 1 until h - 1) {
            for (x in 1 until w - 1) {
                val gx = -gray[(y - 1) * w + (x - 1)] + gray[(y - 1) * w + (x + 1)]
                    - 2f * gray[y * w + (x - 1)] + 2f * gray[y * w + (x + 1)]
                    - gray[(y + 1) * w + (x - 1)] + gray[(y + 1) * w + (x + 1)]

                val gy = -gray[(y - 1) * w + (x - 1)] - 2f * gray[(y - 1) * w + x] - gray[(y - 1) * w + (x + 1)]
                    + gray[(y + 1) * w + (x - 1)] + 2f * gray[(y + 1) * w + x] + gray[(y + 1) * w + (x + 1)]

                mag[y * w + x] = sqrt((gx * gx + gy * gy).toDouble()).toFloat()
                dir[y * w + x] = (kotlin.math.atan2(gy.toDouble(), gx.toDouble()) * 180.0 / kotlin.math.PI).toFloat()
            }
        }
        return Pair(mag, dir)
    }

    private fun nonMaxSuppression(mag: FloatArray, dir: FloatArray, w: Int, h: Int): FloatArray {
        val result = mag.copyOf()
        for (y in 1 until h - 1) {
            for (x in 1 until w - 1) {
                val angle = ((dir[y * w + x] % 180f) + 180f) % 180f
                val idx1: Int; val idx2: Int
                when {
                    angle < 22.5f || angle >= 157.5f -> { idx1 = y * w + x + 1; idx2 = y * w + x - 1 }
                    angle < 67.5f -> { idx1 = (y + 1) * w + x + 1; idx2 = (y - 1) * w + x - 1 }
                    angle < 112.5f -> { idx1 = (y + 1) * w + x; idx2 = (y - 1) * w + x }
                    else -> { idx1 = (y + 1) * w + x - 1; idx2 = (y - 1) * w + x + 1 }
                }
                if (mag[y * w + x] < mag[idx1] || mag[y * w + x] < mag[idx2]) {
                    result[y * w + x] = 0f
                }
            }
        }
        return result
    }

    private fun doubleThreshold(nms: FloatArray, w: Int, h: Int, low: Float, high: Float): ByteArray {
        val edges = ByteArray(w * h)
        for (y in 1 until h - 1) {
            for (x in 1 until w - 1) {
                val v = nms[y * w + x]
                edges[y * w + x] = when {
                    v >= high -> 2
                    v >= low -> 1
                    else -> 0
                }
            }
        }
        return edges
    }

    private fun rgbToHsv(r: Int, g: Int, b: Int): Triple<Float, Float, Float> {
        val rf = r / 255f; val gf = g / 255f; val bf = b / 255f
        val max = maxOf(rf, gf, bf); val min = minOf(rf, gf, bf); val delta = max - min
        var hue = 0f
        if (delta > 0f) {
            hue = when (max) {
                rf -> ((gf - bf) / delta) % 6f
                gf -> ((bf - rf) / delta) + 2f
                else -> ((rf - gf) / delta) + 4f
            } * 60f
            if (hue < 0f) hue += 360f
        }
        val sat = if (max > 0f) delta / max * 100f else 0f
        val value = max * 100f
        return Triple(hue, sat, value)
    }

    private fun rgbToLabApprox(r: Int, g: Int, b: Int): Triple<Float, Float, Float> {
        val rf = r / 255f; val gf = g / 255f; val bf = b / 255f

        fun linearize(c: Float): Float = if (c > 0.04045f) ((c + 0.055f) / 1.055f).toDouble().pow(2.4).toFloat() else c / 12.92f

        val rL = linearize(rf); val gL = linearize(gf); val bL = linearize(bf)

        val xn = 0.95047f; val yn = 1.0f; val zn = 1.08883f
        val x = (rL * 0.4124564f + gL * 0.3575761f + bL * 0.1804375f) / xn
        val y = (rL * 0.2126729f + gL * 0.7151522f + bL * 0.0721750f) / yn
        val z = (rL * 0.0193339f + gL * 0.1191920f + bL * 0.9503041f) / zn

        fun f(t: Float): Float = if (t > 0.008856f) t.toDouble().pow(1.0 / 3.0).toFloat() else (7.787f * t) + (16f / 116f)

        val l = (116f * f(y) - 16f)
        val a = 500f * (f(x) - f(y))
        val b2 = 200f * (f(y) - f(z))
        return Triple(l, a, b2)
    }

    data class FacePositionResult(
        val score: Int,          // 0-100 alignment score
        val skinRegionCenterX: Float,  // normalized 0..1
        val skinRegionCenterY: Float,  // normalized 0..1
        val coverage: Float,     // skin / image area ratio
        val topRatio: Float,     // skin rect top / image height
        val messageKey: String,  // lookup key for Arabic text
        val isValid: Boolean     // score >= 70
    )

    fun evaluateFacePosition(bitmap: Bitmap, threshold: Int = 70, minCoverage: Float = 0.05f): FacePositionResult {
        val safeThreshold = threshold.coerceAtMost(85)
        if (bitmap.isRecycled) {
            Timber.w("evaluateFacePosition: bitmap recycled")
            return FacePositionResult(0, 0.5f, 0.5f, 0f, 0f, "face_not_visible", false)
        }
        val imageArea = bitmap.width * bitmap.height
        val skinRect = findLargestSkinRegion(bitmap, 0f)

        if (skinRect == null || skinRect.isEmpty()) {
            return FacePositionResult(0, 0.5f, 0.5f, 0f, 0f, "face_not_visible", false)
        }

        val skinArea = skinRect.width().toFloat() * skinRect.height().toFloat()
        val coverage = skinArea / imageArea
        val topRatio = skinRect.top.toFloat() / bitmap.height.toFloat()
        val centerX = skinRect.centerX().toFloat() / bitmap.width.toFloat()
        val centerY = skinRect.centerY().toFloat() / bitmap.height.toFloat()
        val horizontalOffset = kotlin.math.abs(centerX - 0.5f)

        // coverage (0-35 pts): ideal = 10-35% of frame
        val coverageScore = when {
            coverage >= 0.10f && coverage <= 0.35f -> 35
            coverage > 0.35f -> 32  // Close face — still good, face is clearly visible
            coverage >= 0.06f -> 25
            coverage >= 0.03f -> 18  // Acceptable for distant face
            coverage >= minCoverage -> 12
            else -> (coverage / minCoverage * 12f).toInt().coerceIn(0, 12)
        }

        // top margin (0-40 pts): forehead should start in top 10% of image
        val topScore = when {
            topRatio <= 0.08f -> 40   // ideal — forehead at very top
            topRatio <= 0.15f -> 35
            topRatio <= 0.25f -> 28
            topRatio <= 0.35f -> 20   // forehead just visible
            topRatio <= 0.45f -> 14   // mostly chin, forehead cropped
            else -> if (coverage > 0.30f) 10 else 5  // close face can have higher topRatio
        }

        // horizontal centering (0-25 pts): face should be centered ±15%
        val centerScore = ((1f - horizontalOffset * 3.33f) * 25f).toInt().coerceIn(0, 25)

        val totalScore = coverageScore + topScore + centerScore

        val messageKey = when {
            totalScore < 20 -> "face_not_visible"
            topRatio > 0.45f && coverage < 0.10f -> "face_too_low"
            coverage < 0.02f -> "face_too_far"
            coverage > 0.50f && totalScore < 50 -> "face_too_close"
            horizontalOffset > 0.30f -> "face_off_center"
            totalScore < safeThreshold -> "adjust_position"
            else -> "face_position_good"
        }

        return FacePositionResult(
            score = totalScore,
            skinRegionCenterX = centerX,
            skinRegionCenterY = centerY,
            coverage = coverage,
            topRatio = topRatio,
            messageKey = messageKey,
            isValid = totalScore >= safeThreshold
        )
    }

    fun normalizeBrightness(bitmap: Bitmap, targetMean: Float = 128f): Bitmap {
        if (bitmap.isRecycled) { Timber.w("normalizeBrightness: bitmap recycled"); return bitmap }
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        var mean = 0.0
        for (p in pixels) {
            mean += (p shr 16 and 0xFF) + (p shr 8 and 0xFF) + (p and 0xFF)
        }
        mean /= (w * h * 3.0)
        if (mean < 1.0) return bitmap

        val scale = targetMean / mean
        val out = IntArray(w * h) { i ->
            val p = pixels[i]
            val r = ((p shr 16 and 0xFF) * scale).toInt().coerceIn(0, 255)
            val g = ((p shr 8 and 0xFF) * scale).toInt().coerceIn(0, 255)
            val b = ((p and 0xFF) * scale).toInt().coerceIn(0, 255)
            0xFF shl 24 or (r shl 16) or (g shl 8) or b
        }
        val result = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        result.setPixels(out, 0, w, 0, 0, w, h)
        return result
    }

    // ═══════════════════════════════════════════════════════════════
    // ADVANCED ANALYSIS — Gabor, LBP, Morphology, Multi-Scale
    // ═══════════════════════════════════════════════════════════════

    fun gaborFilterResponse(bitmap: Bitmap, frequency: Float = 0.25f, theta: Float = 0f, sigmaX: Float = 4f, sigmaY: Float = 4f): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 7 || h < 7) return 0f
        val kernelSize = 7
        val half = kernelSize / 2
        val kernel = Array(kernelSize) { FloatArray(kernelSize) }
        var sumKernel = 0f
        for (ky in -half..half) {
            for (kx in -half..half) {
                val xRot = kx * cos(theta.toDouble()).toFloat() + ky * sin(theta.toDouble()).toFloat()
                val yRot = -kx * sin(theta.toDouble()).toFloat() + ky * cos(theta.toDouble()).toFloat()
                val envelope = exp(-(xRot * xRot / (2f * sigmaX * sigmaX) + yRot * yRot / (2f * sigmaY * sigmaY)).toDouble()).toFloat()
                val carrier = cos(2f * PI.toFloat() * frequency * xRot).toFloat()
                kernel[ky + half][kx + half] = envelope * carrier
                sumKernel += envelope * carrier
            }
        }
        if (sumKernel.absoluteValue > 0.001f) {
            for (ky in 0 until kernelSize) for (kx in 0 until kernelSize) kernel[ky][kx] /= sumKernel
        }
        var responseSum = 0f
        var count = 0
        for (y in half until h - half) {
            for (x in half until w - half) {
                var conv = 0f
                for (ky in -half..half) {
                    for (kx in -half..half) {
                        conv += gray[(y + ky) * w + (x + kx)] * kernel[ky + half][kx + half]
                    }
                }
                responseSum += conv * conv
                count++
            }
        }
        return if (count > 0) sqrt(responseSum / count) else 0f
    }

    fun gaborTextureEnergy(bitmap: Bitmap): Float {
        val orientations = floatArrayOf(0f, PI.toFloat() / 4f, PI.toFloat() / 2f, 3f * PI.toFloat() / 4f)
        var totalEnergy = 0f
        for (theta in orientations) {
            totalEnergy += gaborFilterResponse(bitmap, 0.25f, theta, 4f, 4f)
        }
        return totalEnergy / orientations.size
    }

    fun localBinaryPattern(bitmap: Bitmap, sampleStep: Int = 2): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 3 || h < 3) return 0f
        val uniformityCounts = IntArray(60)
        var totalPixels = 0
        for (y in 1 until h - 1 step sampleStep) {
            for (x in 1 until w - 1 step sampleStep) {
                val center = gray[y * w + x]
                var code = 0
                val neighbors = floatArrayOf(
                    gray[(y - 1) * w + (x - 1)], gray[(y - 1) * w + x], gray[(y - 1) * w + (x + 1)],
                    gray[y * w + (x + 1)],
                    gray[(y + 1) * w + (x + 1)], gray[(y + 1) * w + x], gray[(y + 1) * w + (x - 1)],
                    gray[y * w + (x - 1)]
                )
                for (i in 0..7) {
                    if (neighbors[i] >= center) code = code or (1 shl i)
                }
                val bin = code % 60
                uniformityCounts[bin]++
                totalPixels++
            }
        }
        if (totalPixels == 0) return 0f
        var entropy = 0.0
        for (count in uniformityCounts) {
            if (count > 0) {
                val p = count.toDouble() / totalPixels
                entropy -= p * Math.log(p)
            }
        }
        return (entropy / Math.log(2.0)).toFloat()
    }

    fun morphologicalGradient(bitmap: Bitmap, kernelSize: Int = 3): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < kernelSize || h < kernelSize) return 0f
        val half = kernelSize / 2
        var dilatedSum = 0f
        var erodedSum = 0f
        var count = 0
        for (y in half until h - half) {
            for (x in half until w - half) {
                var maxVal = 0f
                var minVal = 255f
                for (ky in -half..half) {
                    for (kx in -half..half) {
                        val v = gray[(y + ky) * w + (x + kx)]
                        if (v > maxVal) maxVal = v
                        if (v < minVal) minVal = v
                    }
                }
                dilatedSum += maxVal
                erodedSum += minVal
                count++
            }
        }
        return if (count > 0) (dilatedSum - erodedSum) / count else 0f
    }

    fun multiScaleTextureAnalysis(bitmap: Bitmap): Triple<Float, Float, Float> {
        val fineTexture = gaborFilterResponse(bitmap, 0.5f, 0f, 2f, 2f)
        val mediumTexture = gaborFilterResponse(bitmap, 0.25f, 0f, 4f, 4f)
        val coarseTexture = gaborFilterResponse(bitmap, 0.125f, 0f, 8f, 8f)
        return Triple(fineTexture, mediumTexture, coarseTexture)
    }

    fun colorHistogramAnalysis(bitmap: Bitmap): Float {
        if (bitmap.isRecycled) return 0f
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)
        val bins = 32
        val histogram = IntArray(bins * 3)
        for (p in pixels) {
            val r = p shr 16 and 0xFF
            val g = p shr 8 and 0xFF
            val b = p and 0xFF
            histogram[(r * bins / 256) * bins + g * bins / 256]++
            histogram[(g * bins / 256) * bins + b * bins / 256 + bins * bins]++
            histogram[(b * bins / 256) * bins + r * bins / 256 + 2 * bins * bins]++
        }
        val totalPixels = w * h.toFloat()
        var entropy = 0f
        for (count in histogram) {
            if (count > 0) {
                val p = count / totalPixels
                entropy -= p * Math.log(p.toDouble()).toFloat()
            }
        }
        return entropy
    }

    fun skinTextureUniformity(bitmap: Bitmap): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 5 || h < 5) return 0f
        val blockSize = 8
        val blockMeans = mutableListOf<Float>()
        for (by in 0 until h - blockSize step blockSize) {
            for (bx in 0 until w - blockSize step blockSize) {
                var sum = 0f
                for (y in by until by + blockSize) {
                    for (x in bx until bx + blockSize) {
                        sum += gray[y * w + x]
                    }
                }
                blockMeans.add(sum / (blockSize * blockSize))
            }
        }
        if (blockMeans.isEmpty()) return 0f
        val mean = blockMeans.average().toFloat()
        val variance = blockMeans.map { (it - mean).pow(2) }.average().toFloat()
        return sqrt(variance)
    }

    fun colorVarianceAnalysis(bitmap: Bitmap): Pair<Float, Float> {
        if (bitmap.isRecycled) return Pair(0f, 0f)
        val w = bitmap.width; val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)
        var sumA = 0f; var sumB = 0f
        var count = 0
        for (y in 0 until h step 4) {
            for (x in 0 until w step 4) {
                val pixel = pixels[y * w + x]
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF
                val lab = rgbToLabApprox(r, g, b)
                sumA += lab.second
                sumB += lab.third
                count++
            }
        }
        if (count == 0) return Pair(0f, 0f)
        val meanA = sumA / count; val meanB = sumB / count
        var varA = 0f; var varB = 0f
        for (y in 0 until h step 4) {
            for (x in 0 until w step 4) {
                val pixel = pixels[y * w + x]
                val r = pixel shr 16 and 0xFF
                val g = pixel shr 8 and 0xFF
                val b = pixel and 0xFF
                val lab = rgbToLabApprox(r, g, b)
                varA += (lab.second - meanA).pow(2)
                varB += (lab.third - meanB).pow(2)
            }
        }
        return Pair(sqrt(varA / count), sqrt(varB / count))
    }

    fun frequencyDomainTexture(bitmap: Bitmap): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 8 || h < 8) return 0f
        val size = minOf(w, h).coerceAtMost(64)
        var highFreqEnergy = 0f
        var totalEnergy = 0f
        for (y in 0 until size step 2) {
            for (x in 0 until size step 2) {
                val idx = y * w + x
                if (idx >= gray.size) continue
                val v = gray[idx]
                totalEnergy += v * v
                if (x > size / 4 || y > size / 4) {
                    highFreqEnergy += v * v
                }
            }
        }
        return if (totalEnergy > 0) highFreqEnergy / totalEnergy else 0f
    }

    fun edgeDirectionHistogram(bitmap: Bitmap): Float {
        val (w, h, gray) = toGray(bitmap)
        if (w < 3 || h < 3) return 0f
        val (mag, dir) = sobelGradients(gray, w, h)
        val bins = 8
        val histogram = FloatArray(bins)
        for (y in 1 until h - 1) {
            for (x in 1 until w - 1) {
                val idx = y * w + x
                if (mag[idx] > 20f) {
                    val angle = ((dir[idx] % 180f) + 180f) % 180f
                    val bin = ((angle / 180f) * bins).toInt().coerceIn(0, bins - 1)
                    histogram[bin] += mag[idx]
                }
            }
        }
        val maxBin = histogram.max()
        val minBin = histogram.filter { it > 0 }.minOrNull() ?: 0f
        return if (maxBin > 0) (maxBin - minBin) / maxBin else 0f
    }

    fun skinElasticityEstimate(bitmap: Bitmap): Float {
        val lbp = localBinaryPattern(bitmap, 4)
        val texture = gaborTextureEnergy(bitmap)
        val uniformity = skinTextureUniformity(bitmap)
        return (lbp * 0.3f + texture * 0.4f + (1f - uniformity / 50f) * 0.3f).coerceIn(0f, 1f)
    }

    fun wrinkleDepthEstimate(bitmap: Bitmap): Float {
        val (fine, medium, coarse) = multiScaleTextureAnalysis(bitmap)
        val edgeHist = edgeDirectionHistogram(bitmap)
        return (fine * 0.2f + medium * 0.4f + coarse * 0.2f + edgeHist * 0.2f)
    }

    fun poreDensityEstimate(bitmap: Bitmap): Float {
        val spots = adaptiveThresholdSpots(bitmap, 7, 3)
        val morphGrad = morphologicalGradient(bitmap, 3)
        val lbp = localBinaryPattern(bitmap, 3)
        return (spots * 0.5f + morphGrad / 100f * 0.3f + lbp * 0.2f).coerceIn(0f, 1f)
    }

    fun pigmentationHeterogeneity(bitmap: Bitmap): Float {
        val (varA, varB) = colorVarianceAnalysis(bitmap)
        val histEntropy = colorHistogramAnalysis(bitmap)
        return ((varA + varB) / 2f + histEntropy / 10f) / 2f
    }

    fun vascularPatternComplexity(bitmap: Bitmap): Float {
        val redness = hsvRednessIndex(bitmap)
        val edgeDir = edgeDirectionHistogram(bitmap)
        val texture = gaborTextureEnergy(bitmap)
        return (redness * 0.4f + edgeDir * 0.3f + texture * 0.3f)
    }

    fun sebumDistributionAnalysis(bitmap: Bitmap): Pair<Float, Float> {
        val sebum = hsvSebumIndex(bitmap)
        val uniformity = skinTextureUniformity(bitmap)
        return Pair(sebum, uniformity)
    }

    fun skinBarrierEstimate(bitmap: Bitmap): Float {
        val moisture = brightnessUniformity(bitmap)
        val texture = gaborTextureEnergy(bitmap)
        val elasticity = skinElasticityEstimate(bitmap)
        return (moisture.first * 0.4f + texture * 0.3f + elasticity * 0.3f) / 100f
    }

    fun inflammatoryMarkerDetection(bitmap: Bitmap): Float {
        val redness = hsvRednessIndex(bitmap)
        val spots = adaptiveThresholdSpots(bitmap, 9, 4)
        val labVar = labColorUniformity(bitmap)
        return (redness * 0.4f + spots * 0.3f + labVar / 20f * 0.3f).coerceIn(0f, 1f)
    }
}
