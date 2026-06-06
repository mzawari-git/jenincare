package com.jenincare.skinanalyzer.ui.update

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.SystemUpdateAlt
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.blur
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.jenincare.skinanalyzer.data.remote.dto.AppUpdateResponse
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgDeep
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninGreen
import com.jenincare.skinanalyzer.ui.theme.TealAccent

@Composable
fun UpdateDialog(
    update: AppUpdateResponse,
    progress: UpdateProgress,
    onUpdate: () -> Unit,
    onDismiss: () -> Unit,
    onOpenSettings: () -> Unit
) {
    val animatedProgress = remember(progress.progress) {
        progress.progress / 100f
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(BgDeep.copy(alpha = 0.85f))
            .clickable(enabled = !update.forceUpdate) { onDismiss() },
        contentAlignment = Alignment.Center
    ) {
        Box(
            modifier = Modifier
                .fillMaxWidth(0.88f)
                .clip(RoundedCornerShape(28.dp))
                .background(
                    Brush.linearGradient(
                        colors = listOf(
                            BgSurface.copy(alpha = 0.95f),
                            Color(0xFF1A2030).copy(alpha = 0.95f)
                        )
                    )
                )
                .border(1.dp, GlassBorder, RoundedCornerShape(28.dp))
                .padding(24.dp)
                .clickable(enabled = false) {}
        ) {
            Column(
                modifier = Modifier.fillMaxWidth(),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                if (!update.forceUpdate) {
                    Box(
                        modifier = Modifier
                            .align(Alignment.End)
                            .size(32.dp)
                            .clip(CircleShape)
                            .background(Color(0x1AFFFFFF))
                            .clickable { onDismiss() },
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            Icons.Default.Close,
                            contentDescription = "إغلاق",
                            tint = Color(0xFFB0B8C1),
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(8.dp))

                Box(
                    modifier = Modifier
                        .size(80.dp)
                        .clip(CircleShape)
                        .background(
                            Brush.radialGradient(
                                colors = listOf(
                                    JeninBlue.copy(alpha = 0.3f),
                                    TealAccent.copy(alpha = 0.1f)
                                )
                            )
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Box(
                        modifier = Modifier
                            .size(56.dp)
                            .clip(CircleShape)
                            .background(
                                Brush.horizontalGradient(listOf(JeninBlue, TealAccent))
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            Icons.Default.SystemUpdateAlt,
                            contentDescription = null,
                            tint = Color.White,
                            modifier = Modifier.size(28.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(16.dp))

                Text(
                    text = "تحديث التطبيق",
                    style = ArabicTitleSmall,
                    color = Color.White,
                    fontWeight = FontWeight.Bold
                )

                Spacer(modifier = Modifier.height(4.dp))

                Text(
                    text = "الإصدار ${update.latestVersion ?: "?"} متاح",
                    style = ArabicBodyMedium,
                    color = Color(0xFFB0B8C1),
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(20.dp))

                if (progress.downloading) {
                    Box(
                        modifier = Modifier.size(120.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(
                            progress = { animatedProgress },
                            modifier = Modifier.size(120.dp),
                            color = JeninBlue,
                            trackColor = Color(0xFF2D3748),
                            strokeWidth = 6.dp,
                            strokeCap = StrokeCap.Round
                        )
                        Column(horizontalAlignment = Alignment.CenterHorizontally) {
                            Text(
                                text = "${(animatedProgress * 100).toInt()}",
                                style = MaterialTheme.typography.headlineLarge.copy(
                                    fontWeight = FontWeight.Bold,
                                    color = Color.White,
                                    fontSize = 32.sp
                                )
                            )
                            Text(
                                text = "%",
                                style = ArabicBodySmall,
                                color = Color(0xFFB0B8C1)
                            )
                        }
                    }

                    Spacer(modifier = Modifier.height(8.dp))

                    Text(
                        text = "جاري تحميل التحديث...",
                        style = ArabicBodySmall,
                        color = Color(0xFFB0B8C1)
                    )
                } else {
                    Box(
                        modifier = Modifier
                            .size(100.dp)
                            .clip(CircleShape)
                            .background(
                                Brush.radialGradient(
                                    colors = listOf(
                                        JeninBlue.copy(alpha = 0.15f),
                                        Color.Transparent
                                    )
                                )
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            Icons.Default.MedicalServices,
                            contentDescription = null,
                            tint = JeninBlue.copy(alpha = 0.5f),
                            modifier = Modifier.size(48.dp)
                        )
                    }
                }

                if (!update.releaseNotes.isNullOrBlank() && !progress.downloading) {
                    Spacer(modifier = Modifier.height(16.dp))
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clip(RoundedCornerShape(16.dp))
                            .background(Color(0x1AFFFFFF))
                            .padding(14.dp)
                    ) {
                        Column {
                            Text(
                                text = "ملاحظات الإصدار:",
                                style = ArabicBodySmall,
                                color = Color(0xFFC9956B),
                                fontWeight = FontWeight.SemiBold
                            )
                            Spacer(modifier = Modifier.height(4.dp))
                            Text(
                                text = update.releaseNotes,
                                style = ArabicBodySmall,
                                color = Color(0xFFB0B8C1),
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .height(80.dp)
                                    .verticalScroll(rememberScrollState())
                            )
                        }
                    }
                }

                if (update.forceUpdate && !progress.downloading) {
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = "هذا التحديث إلزامي للمتابعة",
                        style = ArabicBodySmall,
                        color = Color(0xFFE53935),
                        textAlign = TextAlign.Center,
                        modifier = Modifier
                            .fillMaxWidth()
                            .clip(RoundedCornerShape(8.dp))
                            .background(Color(0x1AE53935))
                            .padding(8.dp)
                    )
                }

                Spacer(modifier = Modifier.height(24.dp))

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    if (!update.forceUpdate && !progress.downloading && progress.progress < 100) {
                        Button(
                            onClick = onDismiss,
                            modifier = Modifier.weight(1f),
                            colors = ButtonDefaults.buttonColors(
                                containerColor = Color(0xFF2D3748),
                                contentColor = Color.White
                            ),
                            shape = RoundedCornerShape(14.dp)
                        ) {
                            Text("لاحقاً", style = ArabicLabelLarge)
                        }
                    }

                    Button(
                        onClick = {
                            when {
                                progress.progress == 100 -> onUpdate()
                                progress.downloading -> {}
                                else -> onUpdate()
                            }
                        },
                        enabled = !progress.downloading || progress.progress == 100,
                        modifier = Modifier.weight(1f),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = if (progress.progress == 100) TealAccent else JeninBlue,
                            contentColor = Color.White
                        ),
                        shape = RoundedCornerShape(14.dp)
                    ) {
                        Text(
                            text = when {
                                progress.progress == 100 -> "تثبيت"
                                progress.downloading -> "..."
                                else -> "تحديث الآن"
                            },
                            style = ArabicLabelLarge
                        )
                    }
                }

                Spacer(modifier = Modifier.height(8.dp))
            }
        }
    }
}
