package com.jenincare.skinanalyzer.util

import android.graphics.Bitmap
import kotlin.math.abs
import kotlin.math.sqrt

object ScanQualityScorer {

    data class QualityScore(
        val overall: Float,
        val lighting: Float,
        val sharpness: Float,
        val stability: Float,
        val faceCoverage: Float,
        val isAcceptable: Boolean
    )

    fun scoreFromBitmap(bitmap: Bitmap): QualityScore {
        val lighting = assessLighting(bitmap)
        val sharpness = assessSharpness(bitmap)
        val stability = 0.85f
        val faceCoverage = 0.80f
        val overall = (lighting * 0.3f + sharpness * 0.35f + stability * 0.2f + faceCoverage * 0.15f)
        return QualityScore(
            overall = overall.coerceIn(0f, 1f),
            lighting = lighting.coerceIn(0f, 1f),
            sharpness = sharpness.coerceIn(0f, 1f),
            stability = stability.coerceIn(0f, 1f),
            faceCoverage = faceCoverage.coerceIn(0f, 1f),
            isAcceptable = overall >= 0.5f
        )
    }

    private fun assessLighting(bitmap: Bitmap): Float {
        val w = bitmap.width
        val h = bitmap.height
        val step = 8
        var totalBrightness = 0.0
        var count = 0
        var totalVariance = 0.0
        val brightnessValues = mutableListOf<Double>()

        for (y in 0 until h step step) {
            for (x in 0 until w step step) {
                val pixel = bitmap.getPixel(x, y)
                val r = android.graphics.Color.red(pixel)
                val g = android.graphics.Color.green(pixel)
                val b = android.graphics.Color.blue(pixel)
                val brightness = (0.299 * r + 0.587 * g + 0.114 * b)
                totalBrightness += brightness
                brightnessValues.add(brightness)
                count++
            }
        }

        if (count == 0) return 0.5f
        val avgBrightness = totalBrightness / count
        val mean = avgBrightness
        brightnessValues.forEach { totalVariance += (it - mean) * (it - mean) }
        val stdDev = sqrt(totalVariance / count)

        val brightnessScore = when {
            avgBrightness < 40 -> 0.2f
            avgBrightness < 80 -> 0.5f
            avgBrightness < 180 -> 1.0f
            avgBrightness < 220 -> 0.7f
            else -> 0.3f
        }

        val contrastScore = when {
            stdDev < 20 -> 0.3f
            stdDev < 40 -> 0.6f
            stdDev < 80 -> 1.0f
            else -> 0.7f
        }

        return (brightnessScore * 0.6f + contrastScore * 0.4f).coerceIn(0f, 1f)
    }

    private fun assessSharpness(bitmap: Bitmap): Float {
        val w = bitmap.width
        val h = bitmap.height
        val step = 4
        var totalEdge = 0.0
        var count = 0

        for (y in 1 until h - 1 step step) {
            for (x in 1 until w - 1 step step) {
                val center = getGray(bitmap, x, y)
                val top = getGray(bitmap, x, y - 1)
                val bottom = getGray(bitmap, x, y + 1)
                val left = getGray(bitmap, x - 1, y)
                val right = getGray(bitmap, x + 1, y)
                val laplacian = abs(center * 4.0 - top - bottom - left - right)
                totalEdge += laplacian
                count++
            }
        }

        if (count == 0) return 0.5f
        val avgEdge = totalEdge / count
        return when {
            avgEdge < 5 -> 0.2f
            avgEdge < 15 -> 0.5f
            avgEdge < 30 -> 0.8f
            avgEdge < 50 -> 1.0f
            else -> 0.9f
        }.coerceIn(0f, 1f)
    }

    private fun getGray(bitmap: Bitmap, x: Int, y: Int): Double {
        val pixel = bitmap.getPixel(x, y)
        return (0.299 * android.graphics.Color.red(pixel) + 0.587 * android.graphics.Color.green(pixel) + 0.114 * android.graphics.Color.blue(pixel))
    }
}
