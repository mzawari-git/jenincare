package com.jenincare.skinanalyzer.util

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Matrix
import androidx.exifinterface.media.ExifInterface
import dagger.hilt.android.qualifiers.ApplicationContext
import java.io.File
import java.io.FileOutputStream
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ImageCompressor @Inject constructor(
    @ApplicationContext private val context: Context
) {
    companion object {
        private const val MAX_WIDTH = 1920
        private const val MAX_HEIGHT = 1920
        private const val JPEG_QUALITY = 85
        private const val MAX_FILE_SIZE_BYTES = 2 * 1024 * 1024L
    }

    fun compress(inputFile: File): File {
        val originalBitmap = BitmapFactory.decodeFile(inputFile.absolutePath)
            ?: throw IllegalArgumentException("Unable to decode image file: ${inputFile.absolutePath}")

        val rotatedBitmap = correctOrientation(originalBitmap, inputFile.absolutePath)
        val scaledBitmap = scaleDown(rotatedBitmap, MAX_WIDTH, MAX_HEIGHT)

        if (scaledBitmap !== rotatedBitmap) {
            rotatedBitmap.recycle()
        }
        if (originalBitmap !== rotatedBitmap && originalBitmap !== scaledBitmap) {
            originalBitmap.recycle()
        }

        val compressedFile = File.createTempFile("compressed_", ".jpg", context.cacheDir)
        var quality = JPEG_QUALITY

        do {
            FileOutputStream(compressedFile).use { outputStream ->
                scaledBitmap.compress(Bitmap.CompressFormat.JPEG, quality, outputStream)
            }
            quality -= 5
        } while (compressedFile.length() > MAX_FILE_SIZE_BYTES && quality > 50)

        scaledBitmap.recycle()
        return compressedFile
    }

    private fun scaleDown(bitmap: Bitmap, maxWidth: Int, maxHeight: Int): Bitmap {
        val width = bitmap.width
        val height = bitmap.height

        if (width <= maxWidth && height <= maxHeight) {
            return bitmap
        }

        val scaleFactor = minOf(
            maxWidth.toFloat() / width.toFloat(),
            maxHeight.toFloat() / height.toFloat()
        )

        val newWidth = (width * scaleFactor).toInt()
        val newHeight = (height * scaleFactor).toInt()

        return Bitmap.createScaledBitmap(bitmap, newWidth, newHeight, true)
    }

    private fun correctOrientation(bitmap: Bitmap, imagePath: String): Bitmap {
        return try {
            val exif = ExifInterface(imagePath)
            val orientation = exif.getAttributeInt(
                ExifInterface.TAG_ORIENTATION,
                ExifInterface.ORIENTATION_NORMAL
            )

            val rotationDegrees = when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> 90f
                ExifInterface.ORIENTATION_ROTATE_180 -> 180f
                ExifInterface.ORIENTATION_ROTATE_270 -> 270f
                else -> 0f
            }

            if (rotationDegrees == 0f) {
                return bitmap
            }

            val matrix = Matrix()
            matrix.postRotate(rotationDegrees)

            Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
        } catch (e: Exception) {
            bitmap
        }
    }
}
