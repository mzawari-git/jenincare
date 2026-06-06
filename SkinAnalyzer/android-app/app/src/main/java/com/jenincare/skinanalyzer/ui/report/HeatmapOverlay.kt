package com.jenincare.skinanalyzer.ui.report

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.nativeCanvas
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.jenincare.skinanalyzer.domain.model.HeatmapPoint
import com.jenincare.skinanalyzer.ui.theme.SeverityMild
import com.jenincare.skinanalyzer.ui.theme.SeverityModerate
import com.jenincare.skinanalyzer.ui.theme.SeveritySevere

@Composable
fun HeatmapOverlay(
    imageUrl: String?,
    heatmapPoints: List<HeatmapPoint>,
    modifier: Modifier = Modifier
) {
    Box(modifier = modifier) {
        if (imageUrl != null) {
            AsyncImage(
                model = imageUrl,
                contentDescription = "صورة الوجه",
                contentScale = ContentScale.Crop,
                modifier = Modifier.fillMaxSize()
            )
        }

        Canvas(modifier = Modifier.fillMaxSize()) {
            heatmapPoints.forEach { point ->
                val x = point.x * size.width
                val y = point.y * size.height

                val severityColor = when {
                    point.severity >= 0.7f -> SeveritySevere
                    point.severity >= 0.4f -> SeverityModerate
                    else -> SeverityMild
                }

                val spotRadius = 24.dp.toPx() * point.severity.coerceIn(0.3f, 1.5f)

                // Outer glow
                drawCircle(
                    brush = Brush.radialGradient(
                        colors = listOf(
                            severityColor.copy(alpha = 0.6f),
                            severityColor.copy(alpha = 0.2f),
                            Color.Transparent
                        )
                    ),
                    radius = spotRadius * 1.8f,
                    center = Offset(x, y)
                )

                // Core spot
                drawCircle(
                    color = severityColor.copy(alpha = 0.5f),
                    radius = spotRadius,
                    center = Offset(x, y)
                )

                // Border
                drawCircle(
                    color = severityColor,
                    radius = spotRadius,
                    center = Offset(x, y),
                    style = androidx.compose.ui.graphics.drawscope.Stroke(2.dp.toPx())
                )

                // Label
                if (point.label.isNotBlank()) {
                    drawContext.canvas.nativeCanvas.drawText(
                        point.label,
                        x,
                        y - spotRadius - 6.dp.toPx(),
                        android.graphics.Paint().apply {
                            color = android.graphics.Color.WHITE
                            textAlign = android.graphics.Paint.Align.CENTER
                            textSize = 11.dp.toPx()
                            isAntiAlias = true
                            typeface = android.graphics.Typeface.DEFAULT_BOLD
                            setShadowLayer(3f, 0f, 1f, android.graphics.Color.parseColor("#80000000"))
                        }
                    )
                }
            }
        }
    }
}
