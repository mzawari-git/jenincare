package com.jenincare.skinanalyzer.ui.report

import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.nativeCanvas
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.domain.model.RadarMetric
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninBlueLight
import com.jenincare.skinanalyzer.ui.theme.JeninGreen

@Composable
fun RadarChart(
    metrics: List<RadarMetric>,
    modifier: Modifier = Modifier,
    levels: Int = 5
) {
    val animatedProgress by animateFloatAsState(
        targetValue = if (metrics.isNotEmpty()) 1f else 0f,
        animationSpec = tween(1200),
        label = "radarAnim"
    )

    if (metrics.isEmpty()) return

    Box(
        modifier = modifier
            .fillMaxWidth()
            .height(300.dp)
            .padding(16.dp)
    ) {
        Canvas(modifier = Modifier.fillMaxSize()) {
            val centerX = size.width / 2
            val centerY = size.height / 2
            val radius = minOf(centerX, centerY) * 0.7f
            val count = metrics.size
            val angleStep = (2 * Math.PI / count).toFloat()

            val gridColor = Color.LightGray.copy(alpha = 0.2f)
            val axisColor = Color.LightGray.copy(alpha = 0.35f)
            val fillGradient = Brush.radialGradient(
                colors = listOf(
                    JeninBlueLight.copy(alpha = 0.4f),
                    JeninBlue.copy(alpha = 0.15f)
                )
            )
            val strokeColor = JeninBlue

            // Draw grid
            for (level in 1..levels) {
                val levelRadius = radius * level / levels
                val gridPath = Path()
                for (i in 0 until count) {
                    val angle = i * angleStep - Math.PI.toFloat() / 2
                    val x = centerX + levelRadius * kotlin.math.cos(angle)
                    val y = centerY + levelRadius * kotlin.math.sin(angle)
                    if (i == 0) gridPath.moveTo(x, y) else gridPath.lineTo(x, y)
                }
                gridPath.close()
                drawPath(gridPath, gridColor, style = Stroke(1.dp.toPx()))
            }

            // Draw axes
            for (i in 0 until count) {
                val angle = i * angleStep - Math.PI.toFloat() / 2
                val x = centerX + radius * kotlin.math.cos(angle)
                val y = centerY + radius * kotlin.math.sin(angle)
                drawLine(axisColor, Offset(centerX, centerY), Offset(x, y), strokeWidth = 1.dp.toPx())
            }

            // Draw data polygon
            if (animatedProgress > 0f) {
                val dataPath = Path()
                for (i in 0 until count) {
                    val angle = i * angleStep - Math.PI.toFloat() / 2
                    val value = metrics[i].value * animatedProgress
                    val dataRadius = radius * value
                    val x = centerX + dataRadius * kotlin.math.cos(angle)
                    val y = centerY + dataRadius * kotlin.math.sin(angle)
                    if (i == 0) dataPath.moveTo(x, y) else dataPath.lineTo(x, y)
                }
                dataPath.close()

                drawPath(dataPath, fillGradient)
                drawPath(dataPath, strokeColor, style = Stroke(2.5.dp.toPx()))

                // Draw data points with glow
                for (i in 0 until count) {
                    val angle = i * angleStep - Math.PI.toFloat() / 2
                    val value = metrics[i].value * animatedProgress
                    val dataRadius = radius * value
                    val x = centerX + dataRadius * kotlin.math.cos(angle)
                    val y = centerY + dataRadius * kotlin.math.sin(angle)

                    val pointRadius = 5.dp.toPx()
                    drawCircle(strokeColor.copy(alpha = 0.3f), pointRadius * 2.5f, Offset(x, y))
                    drawCircle(strokeColor, pointRadius, Offset(x, y))
                    drawCircle(Color.White, pointRadius * 0.5f, Offset(x, y))
                }
            }

            // Draw labels (always on top)
            for (i in 0 until count) {
                val angle = i * angleStep - Math.PI.toFloat() / 2
                val labelRadius = radius + 28.dp.toPx()
                val labelX = centerX + labelRadius * kotlin.math.cos(angle)
                val labelY = centerY + labelRadius * kotlin.math.sin(angle)

                drawContext.canvas.nativeCanvas.drawText(
                    metrics[i].nameAr,
                    labelX,
                    labelY,
                    android.graphics.Paint().apply {
                        color = android.graphics.Color.parseColor("#666666")
                        textAlign = android.graphics.Paint.Align.CENTER
                        textSize = 12.dp.toPx()
                        isAntiAlias = true
                        isFakeBoldText = true
                        typeface = android.graphics.Typeface.DEFAULT_BOLD
                    }
                )
            }
        }
    }
}
