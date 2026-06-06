package com.jenincare.skinanalyzer.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.ui.theme.BgElevated
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder

@Composable
fun GlassCard(
    modifier: Modifier = Modifier,
    borderColor: Color = GlassBorder,
    borderWidth: Dp = 1.dp,
    shape: RoundedCornerShape = RoundedCornerShape(16.dp),
    gradientStart: Color = BgSurface,
    gradientEnd: Color = BgElevated,
    contentPadding: Dp = 16.dp,
    content: @Composable () -> Unit
) {
    Box(
        modifier = modifier
            .clip(shape)
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(gradientStart, gradientEnd)
                ),
                shape = shape
            )
            .border(
                border = BorderStroke(borderWidth, borderColor),
                shape = shape
            )
            .padding(contentPadding)
    ) {
        content()
    }
}

@Composable
fun GlassCardSimple(
    modifier: Modifier = Modifier,
    borderColor: Color = GlassBorder,
    shape: RoundedCornerShape = RoundedCornerShape(16.dp),
    content: @Composable () -> Unit
) {
    Box(
        modifier = modifier
            .clip(shape)
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(BgSurface, BgElevated)
                ),
                shape = shape
            )
            .border(
                border = BorderStroke(1.dp, borderColor),
                shape = shape
            )
            .padding(16.dp)
    ) {
        content()
    }
}
