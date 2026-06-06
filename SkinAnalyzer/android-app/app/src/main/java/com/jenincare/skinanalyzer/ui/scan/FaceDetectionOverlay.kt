package com.jenincare.skinanalyzer.ui.scan

import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.os.VibratorManager
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.nativeCanvas
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.ui.camera.FaceDetectionResult
import com.jenincare.skinanalyzer.ui.theme.SeverityMild
import com.jenincare.skinanalyzer.ui.theme.SeverityModerate
import com.jenincare.skinanalyzer.ui.theme.SeveritySevere
import kotlin.math.abs
import kotlin.math.cos
import kotlin.math.sin
import kotlinx.coroutines.delay

private const val IDEAL_FACE_RATIO = 0.45f
private const val FAR_THRESHOLD = 0.25f
private const val PERFECT_THRESHOLD = 0.08f

@Composable
fun FaceDetectionOverlay(
    faceResult: FaceDetectionResult,
    @Suppress("UNUSED_PARAMETER") lightingQuality: Float,
    isFaceDetected: Boolean,
    modifier: Modifier = Modifier
) {
    val density = LocalDensity.current
    val context = LocalContext.current
    val faceRatio = faceResult.boundingBox.width() * faceResult.boundingBox.height()
    val distanceScore = if (isFaceDetected) {
        1f - abs(faceRatio - IDEAL_FACE_RATIO).coerceAtMost(1f)
    } else 0f

    var pulsePhase by remember { mutableStateOf(0f) }
    var isPerfect by remember { mutableStateOf(false) }
    var hapticTriggered by remember { mutableStateOf(false) }

    val reticleAlpha by animateFloatAsState(
        targetValue = if (isFaceDetected) 1f else 0.4f,
        animationSpec = tween(600),
        label = "reticleAlpha"
    )

    val reticleColor = when {
        !isFaceDetected -> Color.Red.copy(alpha = 0.5f)
        distanceScore > 0.85f -> Color(0xFF00E5FF)
        distanceScore > 0.6f -> Color(0xFF00FF88)
        distanceScore > 0.3f -> SeverityModerate
        else -> Color.Red
    }

    LaunchedEffect(Unit) {
        while (true) {
            pulsePhase = (pulsePhase + 0.05f) % 1f
            delay(50)
        }
    }

    LaunchedEffect(distanceScore, isFaceDetected) {
        if (isFaceDetected && distanceScore > 0.85f && !hapticTriggered) {
            isPerfect = true
            hapticTriggered = true
            triggerHaptic(context)
            delay(300)
            isPerfect = false
        } else if (distanceScore <= 0.85f) {
            hapticTriggered = false
            isPerfect = false
        }
    }

    val pulseIntensity = if (isPerfect) 0f else abs(pulsePhase - 0.5f) * 2f

    Box(modifier = modifier.fillMaxSize()) {
        Canvas(modifier = Modifier.fillMaxSize()) {
            val canvasWidth = size.width
            val canvasHeight = size.height
            val centerX = canvasWidth / 2f
            val centerY = canvasHeight / 2f

            val reticleRadius = canvasWidth * 0.28f
            val pulseRadius = reticleRadius + (12.dp.toPx() * pulseIntensity)

            val glowRadius = reticleRadius + 20.dp.toPx()

            val glowColor = reticleColor.copy(
                alpha = reticleAlpha * 0.15f * (1f - pulseIntensity * 0.5f)
            )

            drawCircle(
                color = glowColor,
                radius = glowRadius,
                center = Offset(centerX, centerY)
            )

            drawCircle(
                color = reticleColor.copy(alpha = reticleAlpha * 0.08f),
                radius = reticleRadius * 0.3f,
                center = Offset(centerX, centerY),
                style = Stroke(width = 1.dp.toPx())
            )

            val strokeWidth = if (isPerfect) 4.dp.toPx() else (2.5f + pulseIntensity * 1.5f).dp.toPx()

            drawCircle(
                color = reticleColor.copy(alpha = reticleAlpha * 0.6f),
                radius = pulseRadius,
                center = Offset(centerX, centerY),
                style = Stroke(
                    width = strokeWidth,
                    cap = StrokeCap.Round
                )
            )

            val dashLength = 12.dp.toPx()
            val dashCount = 60
            for (i in 0 until dashCount) {
                val angle = (i.toFloat() / dashCount) * 360f

                val alpha = if (isPerfect) 0.9f else 0.5f + pulseIntensity * 0.4f
                val dashColor = if (isPerfect) Color(0xFF00E5FF).copy(alpha = alpha)
                else reticleColor.copy(alpha = alpha * reticleAlpha)

                drawArc(
                    color = dashColor,
                    startAngle = angle,
                    sweepAngle = (dashLength / (pulseRadius * 2f * Math.PI) * 360f).toFloat().coerceAtMost(10f),
                    useCenter = false,
                    topLeft = Offset(centerX - pulseRadius, centerY - pulseRadius),
                    size = Size(pulseRadius * 2, pulseRadius * 2),
                    style = Stroke(
                        width = 3.dp.toPx(),
                        cap = StrokeCap.Round
                    )
                )
            }

            val cornerLength = 30.dp.toPx()
            val cornerOffset = reticleRadius - 2.dp.toPx()
            val cornerColor = if (isPerfect) Color(0xFF00E5FF).copy(alpha = 0.8f)
            else reticleColor.copy(alpha = 0.7f * reticleAlpha)

            val corners = listOf(
                Pair(-1f, -1f),
                Pair(1f, -1f),
                Pair(-1f, 1f),
                Pair(1f, 1f)
            )

            corners.forEach { (dx, dy) ->
                val cx = centerX + dx * cornerOffset
                val cy = centerY + dy * cornerOffset

                drawLine(
                    color = cornerColor,
                    start = Offset(cx, cy),
                    end = Offset(cx + dx * cornerLength, cy),
                    strokeWidth = 3.dp.toPx(),
                    cap = StrokeCap.Round
                )
                drawLine(
                    color = cornerColor,
                    start = Offset(cx, cy),
                    end = Offset(cx, cy + dy * cornerLength),
                    strokeWidth = 3.dp.toPx(),
                    cap = StrokeCap.Round
                )
            }

            if (isFaceDetected && faceResult.confidence > 0.3f) {
                val box = faceResult.boundingBox
                val left = box.left * canvasWidth
                val top = box.top * canvasHeight
                val right = box.right * canvasWidth
                val bottom = box.bottom * canvasHeight

                val faceW = right - left
                val faceH = bottom - top
                val actualRatio = faceW * faceH / (canvasWidth * canvasHeight)

                val landmarkColor = if (actualRatio > IDEAL_FACE_RATIO - PERFECT_THRESHOLD) {
                    Color(0xFF00FF88)
                } else Color.Green.copy(alpha = 0.6f)

                faceResult.landmarks.forEach { (lx, ly) ->
                    drawCircle(
                        color = landmarkColor,
                        radius = 4.dp.toPx(),
                        center = Offset(lx * canvasWidth, ly * canvasHeight)
                    )
                }

                val proximityLabel = when {
                    actualRatio > IDEAL_FACE_RATIO + 0.1f -> "قريب جداً"
                    actualRatio < IDEAL_FACE_RATIO - 0.15f -> "ابتعد قليلاً"
                    distanceScore > 0.85f -> "ممتاز!"
                    else -> "اضبط المسافة"
                }

                val labelColor = when {
                    distanceScore > 0.85f -> Color(0xFF00E5FF)
                    distanceScore > 0.6f -> Color(0xFF00FF88)
                    else -> Color.Red
                }

                drawContext.canvas.nativeCanvas.drawText(
                    proximityLabel,
                    canvasWidth / 2f,
                    centerY + reticleRadius + 40.dp.toPx(),
                    android.graphics.Paint().apply {
                        color = labelColor.toArgb()
                        textAlign = android.graphics.Paint.Align.CENTER
                        textSize = 16.dp.toPx() * density.density
                        isAntiAlias = true
                        isFakeBoldText = true
                        setShadowLayer(6f, 0f, 2f, android.graphics.Color.parseColor("#80000000"))
                    }
                )
            }

            if (!isFaceDetected || faceResult.confidence <= 0.3f) {
                drawContext.canvas.nativeCanvas.drawText(
                    "ضع وجهك داخل الإطار",
                    canvasWidth / 2f,
                    centerY + reticleRadius + 40.dp.toPx(),
                    android.graphics.Paint().apply {
                        color = android.graphics.Color.WHITE
                        textAlign = android.graphics.Paint.Align.CENTER
                        textSize = 16.dp.toPx() * density.density
                        isAntiAlias = true
                        isFakeBoldText = true
                        typeface = android.graphics.Typeface.DEFAULT_BOLD
                        setShadowLayer(6f, 0f, 2f, android.graphics.Color.parseColor("#80000000"))
                    }
                )
            }

            val scanLineAlpha = if (isPerfect) 0.3f else 0.1f + pulseIntensity * 0.15f
            val scanOffset = (pulsePhase * 2f - 1f) * reticleRadius

            drawLine(
                color = reticleColor.copy(alpha = scanLineAlpha * reticleAlpha),
                start = Offset(centerX - reticleRadius, centerY + scanOffset),
                end = Offset(centerX + reticleRadius, centerY + scanOffset),
                strokeWidth = 1.dp.toPx()
            )

            drawLine(
                color = reticleColor.copy(alpha = scanLineAlpha * reticleAlpha * 0.5f),
                start = Offset(centerX + scanOffset, centerY - reticleRadius),
                end = Offset(centerX + scanOffset, centerY + reticleRadius),
                strokeWidth = 1.dp.toPx()
            )
        }
    }
}

private fun triggerHaptic(context: android.content.Context) {
    try {
        val vibrator = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            val manager = context.getSystemService(android.content.Context.VIBRATOR_MANAGER_SERVICE) as VibratorManager
            manager.defaultVibrator
        } else {
            @Suppress("DEPRECATION")
            context.getSystemService(android.content.Context.VIBRATOR_SERVICE) as Vibrator
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            vibrator.vibrate(
                VibrationEffect.createOneShot(100, VibrationEffect.DEFAULT_AMPLITUDE)
            )
        } else {
            @Suppress("DEPRECATION")
            vibrator.vibrate(100)
        }
    } catch (_: Exception) {
    }
}
