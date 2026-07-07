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
                    val contrast = ((lum - 128f) * 1.6f + 128f).coerceIn(0f, 255f)
                    val nr = (lum * 0.35f + contrast * 0.25f + 25f).toInt().coerceIn(0, 255)
                    val ng = (lum * 0.30f + contrast * 0.20f + 20f).toInt().coerceIn(0, 255)
                    val nb = (lum * 0.25f + b * 0.60f + 60f).toInt().coerceIn(0, 255)
                    val porphyrinBoost = if (r > g + 20 && r > b + 20) 60 else 0
                    pixels[i] = (0xFF shl 24) or ((nr + porphyrinBoost).coerceIn(0, 255) shl 16) or (ng shl 8) or nb
                }
            }
            "POL_P" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val bright = r * 0.299f + g * 0.587f + b * 0.114f
                    val surfaceGlare = if (bright > 200f) (bright - 200f) / 55f else 0f
                    val deepTissue = ((bright - 128f) * 1.3f + 128f).coerceIn(0f, 255f)
                    val nr = (r * 0.85f + deepTissue * 0.40f).toInt().coerceIn(0, 255)
                    val ng = (g * 0.35f + deepTissue * 0.25f).toInt().coerceIn(0, 255)
                    val nb = (b * 0.30f + deepTissue * 0.30f).toInt().coerceIn(0, 255)
                    val redBoost = if (r > g * 1.3f && r > b * 1.3f) 30 else 0
                    pixels[i] = (0xFF shl 24) or ((nr + redBoost).coerceIn(0, 255) shl 16) or (ng shl 8) or nb
                }
            }
            "POL_N" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val bright = r * 0.299f + g * 0.587f + b * 0.114f
                    val textureDetail = (bright * 0.3f + (bright - 128f) * 1.4f + 128f).coerceIn(0f, 255f)
                    val nr = (textureDetail * 0.30f + r * 0.25f).toInt().coerceIn(0, 255)
                    val ng = (textureDetail * 0.35f + g * 0.25f).toInt().coerceIn(0, 255)
                    val nb = (textureDetail * 0.35f + b * 0.25f).toInt().coerceIn(0, 255)
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "WOODS" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val lum = r * 0.299f + g * 0.587f + b * 0.114f
                    val nr = (lum * 0.20f + 15f).toInt().coerceIn(0, 255)
                    val ng = (lum * 0.50f + g * 0.30f + 20f).toInt().coerceIn(0, 255)
                    val nb = (lum * 0.30f + b * 0.80f + 50f).toInt().coerceIn(0, 255)
                    val greenFluorescence = if (g > r * 1.5f && g > b * 1.2f) 40 else 0
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or ((ng + greenFluorescence).coerceIn(0, 255) shl 8) or nb
                }
            }
            "BLUE" -> {
                for (i in pixels.indices) {
                    val b = pixels[i] and 0xFF
                    val g = ((pixels[i] shr 8) and 0xFF)
                    val nr = 0
                    val ng = min(255, (g * 0.3f).toInt())
                    val nb = min(255, (b * 1.5f + 30).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "RED" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = ((pixels[i] shr 8) and 0xFF)
                    val nr = min(255, (r * 1.5f + 20).toInt())
                    val ng = min(255, (g * 0.2f).toInt())
                    val nb = 0
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "BROWN" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = min(255, (r * 1.3f + 20).toInt())
                    val ng = min(255, (g * 0.9f).toInt())
                    val nb = min(255, (b * 0.5f).toInt())
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
}
