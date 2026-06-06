package com.jenincare.skinanalyzer.ui.report

import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.size
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ScoreExcellent
import com.jenincare.skinanalyzer.ui.theme.ScoreFair
import com.jenincare.skinanalyzer.ui.theme.ScoreGood
import com.jenincare.skinanalyzer.ui.theme.ScorePoor

@Composable
fun ScoreGauge(
    score: Int,
    modifier: Modifier = Modifier,
    gaugeSize: Dp = 180.dp,
    strokeWidth: Dp = 16.dp
) {
    val targetScore by animateFloatAsState(
        targetValue = score.toFloat(),
        animationSpec = tween(durationMillis = 1500),
        label = "scoreAnim"
    )

    val scoreColor = when {
        score < 25 -> ScorePoor
        score < 50 -> ScoreFair
        score < 75 -> ScoreGood
        else -> ScoreExcellent
    }

    val gradientBrush = Brush.sweepGradient(
        0f to ScorePoor,
        0.25f to ScoreFair,
        0.5f to ScoreGood,
        0.75f to ScoreExcellent,
        1f to ScorePoor
    )

    val textScore = targetScore.toInt()

    Box(
        modifier = modifier.size(gaugeSize),
        contentAlignment = Alignment.Center
    ) {
        Canvas(modifier = Modifier.fillMaxWidth().height(gaugeSize)) {
            val canvasSize = minOf(size.width, size.height)
            val stroke = strokeWidth.toPx()
            val arcSize = Size(canvasSize - stroke, canvasSize - stroke)
            val topLeft = Offset(stroke / 2, stroke / 2)

            val sweep = (targetScore / 100f) * 270f
            val endAngle = 135f + sweep

            // Outer glow/shadow
            val glowStroke = (stroke + 6.dp.toPx())
            val glowArcSize = Size(canvasSize - glowStroke, canvasSize - glowStroke)
            val glowTopLeft = Offset(glowStroke / 2, glowStroke / 2)
            drawArc(
                color = scoreColor.copy(alpha = 0.15f),
                startAngle = 135f,
                sweepAngle = sweep,
                useCenter = false,
                topLeft = glowTopLeft,
                size = glowArcSize,
                style = Stroke(width = glowStroke, cap = StrokeCap.Round)
            )

            // Background track
            drawArc(
                color = Color.LightGray.copy(alpha = 0.15f),
                startAngle = 135f,
                sweepAngle = 270f,
                useCenter = false,
                topLeft = topLeft,
                size = arcSize,
                style = Stroke(width = stroke, cap = StrokeCap.Round)
            )

            // Full gradient background arc
            drawArc(
                brush = gradientBrush,
                startAngle = 135f,
                sweepAngle = 270f,
                useCenter = false,
                topLeft = topLeft,
                size = arcSize,
                alpha = 0.2f,
                style = Stroke(width = stroke, cap = StrokeCap.Round)
            )

            // Active score arc with gradient
            val activeGradient = Brush.sweepGradient(
                endAngle - Math.toRadians(90.0).toFloat() to scoreColor,
                endAngle - Math.toRadians(180.0).toFloat() to scoreColor.copy(alpha = 0.6f)
            )
            drawArc(
                brush = activeGradient,
                startAngle = 135f,
                sweepAngle = sweep,
                useCenter = false,
                topLeft = topLeft,
                size = arcSize,
                style = Stroke(width = stroke - 2.dp.toPx(), cap = StrokeCap.Round)
            )

            // End cap dot
            if (sweep > 1f) {
                val dotRadius = (stroke / 2) + 2.dp.toPx()
                val capAngleRad = Math.toRadians((endAngle - 90).toDouble()).toFloat()
                val cx = size.width / 2 + (arcSize.width / 2) * kotlin.math.cos(capAngleRad)
                val cy = size.height / 2 + (arcSize.height / 2) * kotlin.math.sin(capAngleRad)

                drawCircle(
                    color = Color.White,
                    radius = dotRadius,
                    center = Offset(cx, cy)
                )
                drawCircle(
                    color = scoreColor,
                    radius = dotRadius * 0.6f,
                    center = Offset(cx, cy)
                )
            }
        }

        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text(
                text = "$textScore",
                fontSize = 48.sp,
                fontWeight = FontWeight.ExtraBold,
                color = scoreColor,
                letterSpacing = (-2).sp
            )
            Text(
                text = "من 100",
                style = ArabicBodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}
