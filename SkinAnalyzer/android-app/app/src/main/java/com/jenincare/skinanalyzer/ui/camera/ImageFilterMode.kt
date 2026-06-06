package com.jenincare.skinanalyzer.ui.camera

import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.ColorMatrix
import android.graphics.ColorMatrixColorFilter
import android.graphics.Paint

enum class ImageFilterMode(
    val displayName: String,
    val displayNameAr: String,
    val descriptionAr: String,
    val spectralIndex: Int
) {
    RGB("RGB", "RGB", "ألوان حقيقية - تحليل التجاعيد والمسام", 0),
    UV("UV", "أشعة فوق بنفسجية", "تحليل عميق - أضرار الشمس والتصبغات", 1),
    CROSS_POLARIZED("Cross", "مستقطب", "خريطة حساسية البشرة والأوعية الدموية", 2);

    fun colorMatrix(): ColorMatrix = when (this) {
        RGB -> ColorMatrix().apply {
            set(floatArrayOf(
                1.2f, 0f, 0f, 0f, 0f,
                0f, 1.1f, 0f, 0f, 0f,
                0f, 0f, 1.0f, 0f, 0f,
                0f, 0f, 0f, 1f, 0f
            ))
        }
        UV -> ColorMatrix().apply {
            set(floatArrayOf(
                0.5f, 0f, 0f, 0f, 0f,
                0f, 0.2f, 0f, 0f, 0f,
                0f, 0f, 0.9f, 0f, 0f,
                0.3f, 0.1f, 0.6f, 1f, 0f
            ))
        }
        CROSS_POLARIZED -> ColorMatrix().apply {
            set(floatArrayOf(
                1.3f, -0.2f, 0f, 0f, 0f,
                -0.1f, 1.2f, 0f, 0f, 0f,
                0f, 0f, 1.0f, 0f, 0f,
                -0.1f, 0f, 0.2f, 1f, 0f
            ))
        }
    }

    fun applyToBitmap(bitmap: Bitmap): Bitmap {
        if (this == RGB) return bitmap
        val result = bitmap.copy(Bitmap.Config.ARGB_8888, true)
        val canvas = Canvas(result)
        val paint = Paint().apply {
            colorFilter = ColorMatrixColorFilter(colorMatrix())
        }
        canvas.drawBitmap(bitmap, 0f, 0f, paint)
        return result
    }

    fun applyHighContrastMap(bitmap: Bitmap): Bitmap {
        val result = bitmap.copy(Bitmap.Config.ARGB_8888, true)
        val canvas = Canvas(result)
        val matrix = ColorMatrix().apply {
            set(colorMatrix())
            val contrastMatrix = ColorMatrix(
                floatArrayOf(
                    1.5f, 0f, 0f, 0f, 0f,
                    0f, 1.5f, 0f, 0f, 0f,
                    0f, 0f, 1.5f, 0f, 0f,
                    0f, 0f, 0f, 1f, 0f
                )
            )
            postConcat(contrastMatrix)
        }
        val paint = Paint().apply {
            colorFilter = ColorMatrixColorFilter(matrix)
        }
        canvas.drawBitmap(bitmap, 0f, 0f, paint)
        return result
    }

    fun applyThermalMap(bitmap: Bitmap): Bitmap {
        val result = bitmap.copy(Bitmap.Config.ARGB_8888, true)
        val pixels = IntArray(result.width * result.height)
        result.getPixels(pixels, 0, result.width, 0, 0, result.width, result.height)

        for (i in pixels.indices) {
            val r = (pixels[i] shr 16) and 0xFF
            val g = (pixels[i] shr 8) and 0xFF
            val b = pixels[i] and 0xFF
            val intensity = (0.299 * r + 0.587 * g + 0.114 * b).toInt().coerceIn(0, 255)

            val thermalColor = when {
                intensity > 200 -> 0xFFFF4444.toInt()
                intensity > 150 -> 0xFFFF8800.toInt()
                intensity > 100 -> 0xFFFFDD00.toInt()
                intensity > 50 -> 0xFF00CCFF.toInt()
                else -> 0xFF0044FF.toInt()
            }
            pixels[i] = thermalColor
        }
        result.setPixels(pixels, 0, result.width, 0, 0, result.width, result.height)
        return result
    }
}
