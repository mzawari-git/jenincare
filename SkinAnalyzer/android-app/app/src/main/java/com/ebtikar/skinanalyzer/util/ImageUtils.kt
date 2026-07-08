package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Canvas
import android.graphics.ColorMatrix
import android.graphics.ColorMatrixColorFilter
import android.graphics.Matrix
import android.graphics.Paint
import timber.log.Timber
import java.io.File
import java.io.FileOutputStream
import kotlin.math.max
import kotlin.math.min

object ImageUtils {

    fun decodeSampledBitmap(file: File, targetWidth: Int, targetHeight: Int): Bitmap? {
        return try {
            val options = BitmapFactory.Options().apply {
                inJustDecodeBounds = true
            }
            BitmapFactory.decodeFile(file.absolutePath, options)

            options.inSampleSize = calculateInSampleSize(options, targetWidth, targetHeight)
            options.inJustDecodeBounds = false

            BitmapFactory.decodeFile(file.absolutePath, options)
        } catch (e: Exception) {
            Timber.e(e, "Failed to decode bitmap from ${file.absolutePath}")
            null
        }
    }

    fun resizeBitmap(bitmap: Bitmap, targetWidth: Int, targetHeight: Int): Bitmap {
        val scaleX = targetWidth.toFloat() / bitmap.width
        val scaleY = targetHeight.toFloat() / bitmap.height
        val scale = minOf(scaleX, scaleY)

        val newWidth = (bitmap.width * scale).toInt()
        val newHeight = (bitmap.height * scale).toInt()

        return Bitmap.createScaledBitmap(bitmap, newWidth, newHeight, true)
    }

    fun rotateBitmap(bitmap: Bitmap, degrees: Float): Bitmap {
        if (degrees == 0f) return bitmap
        val matrix = Matrix().apply { postRotate(degrees) }
        return Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
    }

    fun saveBitmap(bitmap: Bitmap, file: File, quality: Int = 100): Boolean {
        return try {
            FileOutputStream(file).use { fos ->
                bitmap.compress(Bitmap.CompressFormat.JPEG, quality, fos)
            }
            true
        } catch (e: Exception) {
            Timber.e(e, "Failed to save bitmap to ${file.absolutePath}")
            false
        }
    }

    fun createCaptureDirectory(baseDir: File, sessionId: String): File {
        val dir = File(baseDir, "captures/$sessionId")
        if (!dir.exists()) dir.mkdirs()
        return dir
    }

    fun applySpectralFilter(source: Bitmap, spectrumName: String): Bitmap {
        if (source.isRecycled) {
            Timber.w("applySpectralFilter: source bitmap recycled for $spectrumName — returning blank")
            return Bitmap.createBitmap(1, 1, Bitmap.Config.ARGB_8888)
        }
        val w = source.width
        val h = source.height
        val pixels = IntArray(w * h)
        source.getPixels(pixels, 0, w, 0, 0, w, h)

        when (spectrumName) {
            "WHITE" -> {
                val exposure = 1.12f
                val contrast = 1.08f
                val mid = 128f
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = ((r - mid) * contrast + mid * exposure).toInt().coerceIn(0, 255)
                    val ng = ((g - mid) * contrast + mid * exposure).toInt().coerceIn(0, 255)
                    val nb = ((b - mid) * contrast + mid * exposure).toInt().coerceIn(0, 255)
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "UV365" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val lum = r * 0.299f + g * 0.587f + b * 0.114f
                    val contrast = ((lum - 128f) * 1.4f + 128f).coerceIn(0f, 255f)
                    val nr = (lum * 0.50f + contrast * 0.35f + 60f).toInt().coerceIn(0, 255)
                    val ng = (lum * 0.45f + contrast * 0.30f + 50f).toInt().coerceIn(0, 255)
                    val nb = (lum * 0.35f + b * 0.50f + 90f).toInt().coerceIn(0, 255)
                    val porphyrinBoost = if (r > g + 15 && r > b + 15) 80 else 0
                    pixels[i] = (0xFF shl 24) or ((nr + porphyrinBoost).coerceIn(0, 255) shl 16) or (ng shl 8) or nb
                }
            }
            "POL_P" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val bright = r * 0.299f + g * 0.587f + b * 0.114f
                    val deepTissue = ((bright - 128f) * 1.3f + 128f).coerceIn(0f, 255f)
                    val nr = (r * 0.80f + deepTissue * 0.45f).toInt().coerceIn(0, 255)
                    val ng = (g * 0.50f + deepTissue * 0.30f).toInt().coerceIn(0, 255)
                    val nb = (b * 0.45f + deepTissue * 0.30f).toInt().coerceIn(0, 255)
                    val redBoost = if (r > g * 1.3f && r > b * 1.3f) 40 else 0
                    pixels[i] = (0xFF shl 24) or ((nr + redBoost).coerceIn(0, 255) shl 16) or (ng shl 8) or nb
                }
            }
            "POL_N" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val bright = r * 0.299f + g * 0.587f + b * 0.114f
                    val textureDetail = (bright * 0.45f + (bright - 128f) * 1.3f + 128f).coerceIn(0f, 255f)
                    val nr = (textureDetail * 0.40f + r * 0.35f).toInt().coerceIn(0, 255)
                    val ng = (textureDetail * 0.40f + g * 0.35f).toInt().coerceIn(0, 255)
                    val nb = (textureDetail * 0.40f + b * 0.35f).toInt().coerceIn(0, 255)
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "WOODS" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val lum = r * 0.299f + g * 0.587f + b * 0.114f
                    val nr = (lum * 0.35f + 40f).toInt().coerceIn(0, 255)
                    val ng = (lum * 0.55f + g * 0.30f + 50f).toInt().coerceIn(0, 255)
                    val nb = (lum * 0.40f + b * 0.70f + 80f).toInt().coerceIn(0, 255)
                    val greenFluorescence = if (g > r * 1.3f && g > b * 1.1f) 50 else 0
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or ((ng + greenFluorescence).coerceIn(0, 255) shl 8) or nb
                }
            }
            "BLUE" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = ((pixels[i] shr 8) and 0xFF)
                    val b = pixels[i] and 0xFF
                    val lum = r * 0.299f + g * 0.587f + b * 0.114f
                    val nr = min(255, (r * 0.15f + lum * 0.10f + 20f).toInt())
                    val ng = min(255, (g * 0.40f + lum * 0.15f + 15f).toInt())
                    val nb = min(255, (b * 1.8f + 40f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "RED" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = ((pixels[i] shr 8) and 0xFF)
                    val b = pixels[i] and 0xFF
                    val lum = r * 0.299f + g * 0.587f + b * 0.114f
                    val nr = min(255, (r * 1.8f + 30f).toInt())
                    val ng = min(255, (g * 0.30f + lum * 0.10f + 15f).toInt())
                    val nb = min(255, (b * 0.10f + lum * 0.05f + 10f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "BROWN" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = min(255, (r * 1.4f + 30f).toInt())
                    val ng = min(255, (g * 0.85f + 10f).toInt())
                    val nb = min(255, (b * 0.70f + 20f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            else -> {}
        }

        val result = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        result.setPixels(pixels, 0, w, 0, 0, w, h)
        return result
    }

    private fun calculateInSampleSize(options: BitmapFactory.Options, reqWidth: Int, reqHeight: Int): Int {
        val height = options.outHeight
        val width = options.outWidth
        var inSampleSize = 1

        if (height > reqHeight || width > reqWidth) {
            val halfHeight = height / 2
            val halfWidth = width / 2

            while (halfHeight / inSampleSize >= reqHeight && halfWidth / inSampleSize >= reqWidth) {
                inSampleSize *= 2
            }
        }
        return inSampleSize
    }

    fun applyClaheEnhancement(source: Bitmap, clipLimit: Float = 3.0f): Bitmap {
        if (source.isRecycled) return source
        val w = source.width
        val h = source.height
        val pixels = IntArray(w * h)
        source.getPixels(pixels, 0, w, 0, 0, w, h)

        val yPlane = IntArray(w * h)
        val uPlane = IntArray(w * h)
        val vPlane = IntArray(w * h)

        for (i in pixels.indices) {
            val r = (pixels[i] shr 16) and 0xFF
            val g = (pixels[i] shr 8) and 0xFF
            val b = pixels[i] and 0xFF
            val y = ((66 * r + 129 * g + 25 * b + 128) shr 8) + 16
            val u = ((-38 * r - 74 * g + 112 * b + 128) shr 8) + 128
            val v = ((112 * r - 94 * g - 18 * b + 128) shr 8) + 128
            yPlane[i] = y.coerceIn(0, 255)
            uPlane[i] = u.coerceIn(0, 255)
            vPlane[i] = v.coerceIn(0, 255)
        }

        val tileSize = 8
        val tilesX = (w + tileSize - 1) / tileSize
        val tilesY = (h + tileSize - 1) / tileSize
        val numTiles = tilesX * tilesY
        val pixelsPerTile = tileSize * tileSize

        val lut = Array(numTiles) { IntArray(256) }

        for (ty in 0 until tilesY) {
            for (tx in 0 until tilesX) {
                val tileIndex = ty * tilesX + tx
                val hist = IntArray(256)
                val startX = tx * tileSize
                val startY = ty * tileSize
                val endX = minOf(startX + tileSize, w)
                val endY = minOf(startY + tileSize, h)
                var count = 0

                for (y in startY until endY) {
                    for (x in startX until endX) {
                        hist[yPlane[y * w + x]]++
                        count++
                    }
                }

                if (count == 0) continue
                val limit = (clipLimit * count / 256).toInt().coerceAtLeast(1)
                var excess = 0
                for (j in 0..255) {
                    if (hist[j] > limit) {
                        excess += hist[j] - limit
                        hist[j] = limit
                    }
                }
                val redistrib = excess / 256
                val residual = excess - redistrib * 256
                for (j in 0..255) {
                    hist[j] += redistrib
                    if (j < residual) hist[j]++
                }

                var cumSum = 0
                for (j in 0..255) {
                    cumSum += hist[j]
                    lut[tileIndex][j] = ((cumSum * 255.0 / count).toInt()).coerceIn(0, 255)
                }
            }
        }

        for (y in 0 until h) {
            for (x in 0 until w) {
                val tx = (x / tileSize).coerceIn(0, tilesX - 1)
                val ty = (y / tileSize).coerceIn(0, tilesY - 1)
                val tileIndex = ty * tilesX + tx
                val srcY = yPlane[y * w + x]
                yPlane[y * w + x] = lut[tileIndex][srcY]
            }
        }

        val result = IntArray(w * h)
        for (i in result.indices) {
            val y = yPlane[i].coerceIn(0, 255)
            val u = uPlane[i] - 128
            val v = vPlane[i] - 128
            val r = ((298 * y + 409 * v + 128) shr 8).coerceIn(0, 255)
            val g = ((298 * y - 100 * u - 208 * v + 128) shr 8).coerceIn(0, 255)
            val b = ((298 * y + 516 * u + 128) shr 8).coerceIn(0, 255)
            result[i] = (0xFF shl 24) or (r shl 16) or (g shl 8) or b
        }

        val output = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        output.setPixels(result, 0, w, 0, 0, w, h)
        return output
    }

    fun ensureMinBrightness(bitmap: Bitmap, minBrightness: Int = 40): Bitmap {
        if (bitmap.isRecycled) return bitmap
        val w = bitmap.width
        val h = bitmap.height
        val pixels = IntArray(w * h)
        bitmap.getPixels(pixels, 0, w, 0, 0, w, h)

        var currentMin = 255
        for (i in pixels.indices) {
            val r = (pixels[i] shr 16) and 0xFF
            val g = (pixels[i] shr 8) and 0xFF
            val b = pixels[i] and 0xFF
            val lum = (r * 0.299f + g * 0.587f + b * 0.114f).toInt()
            if (lum < currentMin) currentMin = lum
        }

        if (currentMin >= minBrightness) return bitmap

        val boost = minBrightness - currentMin
        for (i in pixels.indices) {
            val r = ((pixels[i] shr 16) and 0xFF) + boost
            val g = ((pixels[i] shr 8) and 0xFF) + boost
            val b = (pixels[i] and 0xFF) + boost
            pixels[i] = (0xFF shl 24) or (r.coerceIn(0, 255) shl 16) or (g.coerceIn(0, 255) shl 8) or b.coerceIn(0, 255)
        }

        val result = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        result.setPixels(pixels, 0, w, 0, 0, w, h)
        return result
    }

    fun isDarkSpectrum(spectrumName: String): Boolean =
        spectrumName in listOf("UV365", "WOODS", "BLUE", "RED", "BROWN", "POL_N")
}
