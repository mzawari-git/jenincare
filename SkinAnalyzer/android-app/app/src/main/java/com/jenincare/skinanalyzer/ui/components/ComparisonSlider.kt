package com.jenincare.skinanalyzer.ui.components

import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.foundation.background
import androidx.compose.foundation.gestures.detectHorizontalDragGestures
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Compare
import androidx.compose.material.icons.filled.DragHandle
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableFloatStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.layout.onSizeChanged
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.IntOffset
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.TealAccent
import kotlin.math.roundToInt

@Composable
fun ComparisonSlider(
    beforeImageUrl: String?,
    afterImageUrl: String?,
    beforeLabel: String = "قبل",
    afterLabel: String = "بعد",
    modifier: Modifier = Modifier
) {
    var sliderPosition by remember { mutableFloatStateOf(0.5f) }
    var containerWidth by remember { mutableFloatStateOf(0f) }
    val density = LocalDensity.current

    Column(
        modifier = modifier.fillMaxWidth(),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(
            text = "مقارنة قبل وبعد",
            style = ArabicTitleSmall,
            color = Color.White
        )
        Spacer(modifier = Modifier.height(12.dp))

        BoxWithConstraints(
            modifier = Modifier
                .fillMaxWidth()
                .height(300.dp)
                .clip(RoundedCornerShape(16.dp))
                .onSizeChanged { containerWidth = it.width.toFloat() }
                .pointerInput(Unit) {
                    detectHorizontalDragGestures { change, _ ->
                        change.consume()
                        sliderPosition = (change.position.x / containerWidth).coerceIn(0.05f, 0.95f)
                    }
                }
        ) {
            val maxWidth = this.maxWidth

            Box(modifier = Modifier.fillMaxSize()) {
                if (beforeImageUrl != null) {
                    AsyncImage(
                        model = beforeImageUrl,
                        contentDescription = beforeLabel,
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Crop
                    )
                } else {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(Color(0xFF1C2333)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text("لا توجد صورة", color = Color.Gray, style = ArabicBodySmall)
                    }
                }

                Box(
                    modifier = Modifier
                        .fillMaxHeight()
                        .width(with(density) { (containerWidth * sliderPosition).toDp() })
                        .clip(RoundedCornerShape(topStart = 16.dp, bottomStart = 16.dp))
                ) {
                    if (afterImageUrl != null) {
                        AsyncImage(
                            model = afterImageUrl,
                            contentDescription = afterLabel,
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Crop
                        )
                    } else {
                        Box(
                            modifier = Modifier
                                .fillMaxSize()
                                .background(Color(0xFF1C2333)),
                            contentAlignment = Alignment.Center
                        ) {
                            Text("لا توجد صورة", color = Color.Gray, style = ArabicBodySmall)
                        }
                    }
                }

                val handleX = (containerWidth * sliderPosition)
                Box(
                    modifier = Modifier
                        .offset { IntOffset((handleX - 2).dp.toPx().roundToInt(), 0) }
                        .fillMaxHeight()
                        .width(4.dp)
                        .background(RoseGold)
                )

                Box(
                    modifier = Modifier
                        .offset {
                            IntOffset(
                                (handleX - 20).dp.toPx().roundToInt(),
                                (maxHeight.toPx() / 2 - 20).roundToInt()
                            )
                        }
                        .size(40.dp)
                        .clip(CircleShape)
                        .background(RoseGold),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        Icons.Default.DragHandle,
                        contentDescription = "اسحب للمقارنة",
                        tint = Color.White,
                        modifier = Modifier.size(24.dp)
                    )
                }

                Box(
                    modifier = Modifier
                        .align(Alignment.TopStart)
                        .padding(8.dp)
                        .clip(RoundedCornerShape(8.dp))
                        .background(TealAccent.copy(alpha = 0.85f))
                        .padding(horizontal = 8.dp, vertical = 4.dp)
                ) {
                    Text(afterLabel, color = Color.White, style = ArabicBodySmall, fontWeight = FontWeight.Bold)
                }

                Box(
                    modifier = Modifier
                        .align(Alignment.TopEnd)
                        .padding(8.dp)
                        .clip(RoundedCornerShape(8.dp))
                        .background(RoseGold.copy(alpha = 0.85f))
                        .padding(horizontal = 8.dp, vertical = 4.dp)
                ) {
                    Text(beforeLabel, color = Color.White, style = ArabicBodySmall, fontWeight = FontWeight.Bold)
                }
            }
        }
    }
}
