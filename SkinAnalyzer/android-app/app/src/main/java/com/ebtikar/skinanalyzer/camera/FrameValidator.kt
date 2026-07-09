package com.ebtikar.skinanalyzer.camera

import android.graphics.Bitmap
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton
import kotlin.math.abs
import kotlin.math.sqrt

/**
 * Validates captured frames for common quality issues:
 * - Too dark (underexposed)
 * - Too bright (overexposed)
 * - Low contrast (flat histogram)
 * - Corrupted (uniform color, likely decoder failure)
 * - Wrong dimensions
 */
@Singleton
class FrameValidator @Inject constructor() {

    data class ValidationResult(
        val isValid: Boolean,
        val brightness: Float,      // 0-255 average
        val contrast: Float,        // standard deviation of luminance
        val isDark: Boolean,
        val isBright: Boolean,
        val isLowContrast: Boolean,
        val isCorrupted: Boolean,
        val message: String
    )

    companion object {
        /** Minimum average brightness for a valid frame (0-255) */
        const val MIN_BRIGHTNESS = 15f

        /** Maximum average brightness for a valid frame */
        const val MAX_BRIGHTNESS = 240f

        /** Minimum contrast (std dev) for a valid frame */
        const val MIN_CONTRAST = 8f

        /** Maximum number of unique color bins to consider corrupted */
        const val MAX_COLOR_BINS_CORRUPTED = 5

        /** Minimum image dimensions */
        const val MIN_WIDTH = 100
        const val MIN_HEIGHT = 100
    }

    fun validate(bitmap: Bitmap?): ValidationResult {
        if (bitmap == null) {
            return ValidationResult(false, 0f, 0f, true, false, false, true, "Frame is null")
        }

        if (bitmap.isRecycled) {
            return ValidationResult(false, 0f, 0f, true, false, false, true, "Frame is recycled")
        }

        if (bitmap.width < MIN_WIDTH || bitmap.height < MIN_HEIGHT) {
            return ValidationResult(
                false, 0f, 0f, true, false, false, true,
                "Frame too small: ${bitmap.width}x${bitmap.height} (min ${MIN_WIDTH}x${MIN_HEIGHT})"
            )
        }

        // Sample pixels for analysis (full scan is too slow for real-time)
        val sampleSize = calculateSampleSize(bitmap)
        var totalLuminance = 0L
        var pixelCount = 0
        val colorBins = mutableSetOf<Int>()

        for (y in 0 until bitmap.height step sampleSize) {
            for (x in 0 until bitmap.width step sampleSize) {
                val pixel = bitmap.getPixel(x, y)
                val r = (pixel shr 16) and 0xFF
                val g = (pixel shr 8) and 0xFF
                val b = pixel and 0xFF
                val luminance = (0.299f * r + 0.587f * g + 0.114f * b).toInt()
                totalLuminance += luminance
                pixelCount++

                // Bin colors for corruption check (quantize to 32-level)
                val bin = ((r / 32) shl 10) or ((g / 32) shl 5) or (b / 32)
                colorBins.add(bin)
            }
        }

        if (pixelCount == 0) {
            return ValidationResult(false, 0f, 0f, true, false, false, true, "No pixels sampled")
        }

        val avgBrightness = totalLuminance.toFloat() / pixelCount

        // Calculate contrast (std dev)
        var varianceSum = 0.0
        for (y in 0 until bitmap.height step sampleSize) {
            for (x in 0 until bitmap.width step sampleSize) {
                val pixel = bitmap.getPixel(x, y)
                val r = (pixel shr 16) and 0xFF
                val g = (pixel shr 8) and 0xFF
                val b = pixel and 0xFF
                val luminance = 0.299f * r + 0.587f * g + 0.114f * b
                varianceSum += (luminance - avgBrightness).toDouble().let { it * it }
            }
        }
        val contrast = sqrt(varianceSum / pixelCount).toFloat()

        val isDark = avgBrightness < MIN_BRIGHTNESS
        val isBright = avgBrightness > MAX_BRIGHTNESS
        val isLowContrast = contrast < MIN_CONTRAST
        val isCorrupted = colorBins.size <= MAX_COLOR_BINS_CORRUPTED

        val isValid = !isDark && !isBright && !isCorrupted && pixelCount > 0

        val message = buildString {
            if (isDark) append("Frame too dark (brightness=${avgBrightness.toInt()}) ")
            if (isBright) append("Frame too bright (brightness=${avgBrightness.toInt()}) ")
            if (isLowContrast) append("Low contrast (${contrast.toInt()}) ")
            if (isCorrupted) append("Likely corrupted (${colorBins.size} color bins) ")
            if (isValid) append("OK")
        }.trim()

        Timber.d("FrameValidator: brightness=${avgBrightness.toInt()}, contrast=${contrast.toInt()}, " +
            "bins=${colorBins.size}, dark=$isDark, bright=$isBright, lowContrast=$isLowContrast, " +
            "corrupted=$isCorrupted, valid=$isValid")

        return ValidationResult(
            isValid = isValid,
            brightness = avgBrightness,
            contrast = contrast,
            isDark = isDark,
            isBright = isBright,
            isLowContrast = isLowContrast,
            isCorrupted = isCorrupted,
            message = message
        )
    }

    /**
     * Check if the frame should be retried (not permanently invalid).
     */
    fun shouldRetry(result: ValidationResult): Boolean {
        // Dark/bright frames can retry (auto-exposure may adjust)
        // Corrupted frames should not retry (hardware issue)
        return result.isDark || result.isBright || result.isLowContrast
    }

    private fun calculateSampleSize(bitmap: Bitmap): Int {
        val pixels = bitmap.width * bitmap.height
        return when {
            pixels > 4_000_000 -> 8  // Sample 1/64 of pixels for 4MP+
            pixels > 1_000_000 -> 4  // Sample 1/16 for 1MP+
            pixels > 250_000 -> 2    // Sample 1/4 for VGA+
            else -> 1                 // Full scan for small images
        }
    }
}
