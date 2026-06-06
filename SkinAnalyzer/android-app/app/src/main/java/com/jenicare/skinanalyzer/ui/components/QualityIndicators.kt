package com.jenincare.skinanalyzer.ui.components

import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.BrightnessHigh
import androidx.compose.material.icons.filled.CenterFocusStrong
import androidx.compose.material.icons.filled.Stability
import androidx.compose.material.icons.filled.TouchApp
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.HealthExcellent
import com.jenincare.skinanalyzer.ui.theme.HealthFair
import com.jenincare.skinanalyzer.ui.theme.HealthGood
import com.jenincare.skinanalyzer.ui.theme.SeverityMild
import com.jenincare.skinanalyzer.ui.theme.SeverityModerate
import com.jenincare.skinanalyzer.ui.theme.SeveritySevere

@Composable
fun QualityIndicatorsOverlay(
    lightingQuality: Float,
    sharpness: Float,
    stability: Float,
    faceDistance: Float,
    modifier: Modifier = Modifier
) {
    val animatedLighting by animateFloatAsState(lightingQuality, tween(300), label = "lighting")
    val animatedSharpness by animateFloatAsState(sharpness, tween(300), label = "sharpness")
    val animatedStability by animateFloatAsState(stability, tween(300), label = "stability")
    val animatedDistance by animateFloatAsState(faceDistance, tween(300), label = "distance")

    Column(
        modifier = modifier
            .clip(RoundedCornerShape(16.dp))
            .background(BgSurface.copy(alpha = 0.85f))
            .border(
                width = 1.dp,
                color = getOverallColor(
                    (animatedLighting + animatedSharpness + animatedStability + animatedDistance) / 4f
                ).copy(alpha = 0.4f),
                shape = RoundedCornerShape(16.dp)
            )
            .padding(horizontal = 12.dp, vertical = 8.dp),
        verticalArrangement = Arrangement.spacedBy(6.dp)
    ) {
        QualityMeter(
            icon = Icons.Default.BrightnessHigh,
            label = "الإضاءة",
            value = animatedLighting,
            color = getQualityColor(animatedLighting)
        )
        QualityMeter(
            icon = Icons.Default.CenterFocusStrong,
            label = "الوضوح",
            value = animatedSharpness,
            color = getQualityColor(animatedSharpness)
        )
        QualityMeter(
            icon = Icons.Default.TouchApp,
            label = "الثبات",
            value = animatedStability,
            color = getQualityColor(animatedStability)
        )
        QualityMeter(
            icon = Icons.Default.Stability,
            label = "المسافة",
            value = animatedDistance,
            color = getQualityColor(animatedDistance)
        )
    }
}

@Composable
private fun QualityMeter(
    icon: ImageVector,
    label: String,
    value: Float,
    color: Color
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(6.dp)
    ) {
        Icon(
            imageVector = icon,
            contentDescription = label,
            tint = color,
            modifier = Modifier.size(14.dp)
        )
        Text(
            text = label,
            style = ArabicBodySmall,
            color = Color.White.copy(alpha = 0.7f),
            modifier = Modifier.width(42.dp)
        )
        Box(
            modifier = Modifier
                .weight(1f)
                .height(4.dp)
                .clip(RoundedCornerShape(2.dp))
                .background(Color(0xFF2D3748))
        ) {
            Box(
                modifier = Modifier
                    .fillMaxWidth(fraction = value.coerceIn(0f, 1f))
                    .height(4.dp)
                    .clip(RoundedCornerShape(2.dp))
                    .background(color)
            )
        }
        Text(
            text = "${(value * 100).toInt()}%",
            style = MaterialTheme.typography.labelSmall,
            color = color,
            fontWeight = FontWeight.Bold
        )
    }
}

private fun getQualityColor(value: Float): Color = when {
    value >= 0.7f -> HealthExcellent
    value >= 0.4f -> HealthGood
    else -> HealthFair
}

private fun getOverallColor(avg: Float): Color = when {
    avg >= 0.7f -> SeverityMild
    avg >= 0.4f -> SeverityModerate
    else -> SeveritySevere
}
