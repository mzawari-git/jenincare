package com.jenincare.skinanalyzer.ui.components

import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.jenincare.skinanalyzer.ui.theme.RoseGold

@Composable
fun CountdownOverlay(
    count: Int,
    modifier: Modifier = Modifier
) {
    val scale = remember { Animatable(1.5f) }
    val alpha = remember { Animatable(0f) }

    LaunchedEffect(count) {
        scale.snapTo(1.5f)
        alpha.snapTo(0f)
        scale.animateTo(1f, tween(400))
        alpha.animateTo(1f, tween(200))
    }

    Box(
        modifier = modifier.fillMaxSize(),
        contentAlignment = Alignment.Center
    ) {
        Box(
            modifier = Modifier
                .size(120.dp)
                .clip(CircleShape)
                .background(Color.Black.copy(alpha = 0.6f)),
            contentAlignment = Alignment.Center
        ) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Text(
                    text = "$count",
                    fontSize = 56.sp,
                    fontWeight = FontWeight.Bold,
                    color = RoseGold,
                    modifier = Modifier.scale(scale.value)
                )
            }
        }
    }
}
