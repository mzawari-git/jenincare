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

    fun saveBitmap(bitmap: Bitmap, file: File, quality: Int = 95): Boolean {
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
        val w = source.width
        val h = source.height
        val result = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        val pixels = IntArray(w * h)
        source.getPixels(pixels, 0, w, 0, 0, w, h)

        when (spectrumName) {
            "WHITE" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = min(255, (r * 1.05f).toInt())
                    val ng = min(255, (g * 1.05f).toInt())
                    val nb = min(255, (b * 1.08f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "UV365" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = min(255, (r * 0.3f + 30).toInt())
                    val ng = min(255, (g * 0.4f + 20).toInt())
                    val nb = min(255, (b * 1.6f).toInt())
                    val gray = ((nr + ng + nb) / 3)
                    val contrast = ((gray - 128) * 1.8f + 128).toInt().coerceIn(0, 255)
                    val cb = min(255, (contrast * 1.3f).toInt())
                    pixels[i] = (0xFF shl 24) or (contrast shl 16) or (contrast shl 8) or cb
                }
            }
            "POL_P" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val gray = ((r + g + b) / 3)
                    val contrast = ((gray - 128) * 1.5f + 128).toInt().coerceIn(0, 255)
                    val nr = min(255, (contrast * 0.7f + r * 0.3f).toInt())
                    val ng = min(255, (contrast * 0.5f + g * 0.5f).toInt())
                    val nb = min(255, (contrast * 0.9f + b * 0.1f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "POL_N" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val bright = (r * 0.299f + g * 0.587f + b * 0.114f)
                    val sharpen = (bright * 1.4f - 50f).coerceIn(0f, 255f)
                    val nr = min(255, (r * 0.4f + sharpen * 0.6f).toInt())
                    val ng = min(255, (g * 0.3f + sharpen * 0.7f).toInt())
                    val nb = min(255, (b * 0.3f + sharpen * 0.7f).toInt())
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (ng shl 8) or nb
                }
            }
            "WOODS" -> {
                for (i in pixels.indices) {
                    val r = (pixels[i] shr 16) and 0xFF
                    val g = (pixels[i] shr 8) and 0xFF
                    val b = pixels[i] and 0xFF
                    val nr = min(255, (r * 0.5f + 40).toInt())
                    val ng = min(255, (g * 1.4f).toInt())
                    val nb = min(255, (b * 1.5f).toInt())
                    val gray = ((nr + ng + nb) / 3)
                    val contrast = ((gray - 128) * 1.6f + 128).toInt().coerceIn(0, 255)
                    pixels[i] = (0xFF shl 24) or (nr shl 16) or (contrast shl 8) or nb
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
