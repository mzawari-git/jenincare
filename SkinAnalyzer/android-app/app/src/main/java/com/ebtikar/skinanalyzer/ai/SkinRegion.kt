package com.ebtikar.skinanalyzer.ai

import android.graphics.PointF
import android.graphics.RectF

enum class SkinRegion(val displayName: String, val displayNameAr: String) {
    FOREHEAD("Forehead", "الجبهة"),
    NOSE("Nose", "الأنف"),
    LEFT_CHEEK("Left Cheek", "الخد الأيسر"),
    RIGHT_CHEEK("Right Cheek", "الخد الأيمن"),
    CHIN("Chin", "الذقن"),
    PERIORBITAL_LEFT("Left Eye Area", "منطقة العين اليسرى"),
    PERIORBITAL_RIGHT("Right Eye Area", "منطقة العين اليمنى"),
    PERIORAL("Mouth Area", "منطقة الفم"),
    FULL_FACE("Full Face", "الوجه كامل");

    companion object {
        val FOREHEAD_INDICES = intArrayOf(
            107, 66, 105, 63, 70, 336, 296, 334, 293, 300,
            283, 282, 295, 285, 337, 299, 338, 10, 151, 9,
            8, 108, 109, 110, 111, 112, 24, 23, 22, 26, 113,
            243, 190, 56, 28, 27, 29, 30, 247
        )

        val NOSE_INDICES = intArrayOf(
            1, 2, 98, 327, 168, 6, 197, 195, 5, 4,
            19, 94, 2, 164, 0, 11, 12, 248, 420, 437,
            277, 436, 278, 435, 279, 360, 280, 359, 281, 358,
            282, 357, 355, 220, 238, 237, 236, 235, 234, 233,
            232, 231, 230, 229, 228, 227, 226, 225, 224, 223,
            222, 221, 189, 245, 188, 247
        )

        val LEFT_CHEEK_INDICES = intArrayOf(
            36, 100, 101, 102, 48, 116, 117, 118, 119, 120,
            121, 128, 245, 126, 125, 124, 123, 114, 113, 112,
            111, 110, 109, 108, 107, 106, 105, 104, 103, 67,
            109, 10, 338, 297, 332, 284, 251, 389, 356, 454,
            323, 361, 288, 397, 365, 364, 394, 379, 378, 400,
            377, 152, 148, 176, 149, 150, 136, 172, 58, 132,
            93, 234, 127, 162, 21, 54, 103
        )

        val RIGHT_CHEEK_INDICES = intArrayOf(
            266, 330, 331, 332, 280, 347, 348, 349, 350, 351,
            352, 353, 462, 355, 354, 353, 352, 343, 342, 341,
            340, 339, 338, 337, 336, 335, 334, 333, 332, 297,
            338, 10, 151, 9, 8, 108, 109, 110, 111, 112,
            24, 23, 22, 26, 113, 243, 190, 56, 28, 27,
            29, 30, 247, 126, 125, 124, 123, 114, 113, 112,
            36, 100, 101, 102, 48, 116, 117, 118, 119, 120,
            121, 128, 245
        )

        val CHIN_INDICES = intArrayOf(
            152, 148, 176, 149, 150, 136, 172, 58, 132, 93,
            234, 127, 162, 21, 54, 103, 67, 109, 10, 338,
            297, 332, 284, 251, 389, 356, 454, 323, 361, 288,
            397, 365, 379, 378, 400, 377, 17, 84, 181, 91,
            146, 61, 144, 240, 239, 238, 237, 236, 235, 234
        )

        val LEFT_EYE_INDICES = intArrayOf(
            33, 7, 163, 144, 145, 153, 154, 155, 133, 173,
            157, 158, 159, 160, 161, 246, 130, 25, 110, 24,
            23, 22, 26, 112, 243, 190, 56, 28, 27, 29,
            30, 247
        )

        val RIGHT_EYE_INDICES = intArrayOf(
            362, 382, 381, 380, 374, 373, 390, 249, 263, 466,
            388, 387, 386, 385, 384, 398, 359, 255, 339, 254,
            253, 252, 256, 341, 463, 414, 286, 258, 257, 259,
            260, 467
        )

        val PERIORAL_INDICES = intArrayOf(
            61, 146, 91, 181, 84, 17, 314, 405, 321, 375,
            291, 308, 324, 318, 402, 317, 14, 87, 178, 88,
            95, 185, 40, 39, 37, 0, 267, 269, 270, 409,
            415, 310, 311, 312, 13, 82, 81, 42, 183, 78
        )
    }

    private fun resolvePoints(landmarks: List<PointF>): List<PointF> {
        val idxArr = getIndices()
        val result = mutableListOf<PointF>()
        for (idx in idxArr) {
            if (idx < landmarks.size) {
                val lm = landmarks[idx]
                result.add(PointF(lm.x, lm.y))
            }
        }
        return result
    }

    fun extractRegionPixels(
        landmarks: List<PointF>,
        bitmapWidth: Int,
        bitmapHeight: Int
    ): List<PointF> {
        return resolvePoints(landmarks)
    }

    fun getRegionBounds(landmarks: List<PointF>): RectF? {
        val points = resolvePoints(landmarks)
        if (points.isEmpty()) return null

        var minX = Float.MAX_VALUE
        var maxX = Float.MIN_VALUE
        var minY = Float.MAX_VALUE
        var maxY = Float.MIN_VALUE

        for (p in points) {
            if (p.x < minX) minX = p.x
            if (p.x > maxX) maxX = p.x
            if (p.y < minY) minY = p.y
            if (p.y > maxY) maxY = p.y
        }

        return RectF(minX, minY, maxX, maxY)
    }

    fun containsPoint(point: PointF, landmarks: List<PointF>): Boolean {
        val regionPoints = resolvePoints(landmarks)
        if (regionPoints.size < 3) return false

        var cx = 0f; var cy = 0f
        for (p in regionPoints) { cx += p.x; cy += p.y }
        cx /= regionPoints.size; cy /= regionPoints.size

        val sortedPoints = regionPoints.sortedBy { p ->
            kotlin.math.atan2((p.y - cy).toDouble(), (p.x - cx).toDouble())
        }

        return isPointInPolygon(point, sortedPoints)
    }

    private fun isPointInPolygon(point: PointF, polygon: List<PointF>): Boolean {
        var inside = false
        var j = polygon.size - 1
        for (i in polygon.indices) {
            val xi = polygon[i].x; val yi = polygon[i].y
            val xj = polygon[j].x; val yj = polygon[j].y
            if (((yi > point.y) != (yj > point.y)) &&
                (point.x < (xj - xi) * (point.y - yi) / (yj - yi) + xi)
            ) {
                inside = !inside
            }
            j = i
        }
        return inside
    }

    fun getIndices(): IntArray {
        return when (this) {
            FOREHEAD -> FOREHEAD_INDICES
            NOSE -> NOSE_INDICES
            LEFT_CHEEK -> LEFT_CHEEK_INDICES
            RIGHT_CHEEK -> RIGHT_CHEEK_INDICES
            CHIN -> CHIN_INDICES
            PERIORBITAL_LEFT -> LEFT_EYE_INDICES
            PERIORBITAL_RIGHT -> RIGHT_EYE_INDICES
            PERIORAL -> PERIORAL_INDICES
            FULL_FACE -> (FOREHEAD_INDICES + NOSE_INDICES + LEFT_CHEEK_INDICES +
                    RIGHT_CHEEK_INDICES + CHIN_INDICES).distinct().toIntArray()
        }
    }
}

data class LandmarkDepth(
    val x: Float,
    val y: Float,
    val z: Float
)
