package com.jenincare.skinanalyzer.ui.waiting

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInVertically
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
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.Shield
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.rotate
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleMedium
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninGreen

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun WaitingScreen(
    scanId: String,
    onReportReady: (String) -> Unit,
    onNavigateBack: () -> Unit,
    viewModel: WaitingViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()

    LaunchedEffect(scanId) {
        viewModel.startPolling(scanId)
    }

    LaunchedEffect(Unit) {
        viewModel.reportReady.collect { id ->
            onReportReady(id)
        }
    }

    val infiniteTransition = rememberInfiniteTransition(label = "waiting")
    val pulseScale by infiniteTransition.animateFloat(
        initialValue = 0.9f,
        targetValue = 1.1f,
        animationSpec = infiniteRepeatable(
            animation = tween(1500, easing = LinearEasing),
            repeatMode = RepeatMode.Reverse
        ),
        label = "pulse"
    )
    val rotation by infiniteTransition.animateFloat(
        initialValue = 0f,
        targetValue = 360f,
        animationSpec = infiniteRepeatable(
            animation = tween(8000, easing = LinearEasing),
            repeatMode = RepeatMode.Restart
        ),
        label = "rotation"
    )
    val dotAlpha1 by infiniteTransition.animateFloat(
        initialValue = 0.3f,
        targetValue = 1f,
        animationSpec = infiniteRepeatable(
            animation = tween(800, easing = LinearEasing),
            repeatMode = RepeatMode.Reverse
        ),
        label = "dot1"
    )
    val dotAlpha2 by infiniteTransition.animateFloat(
        initialValue = 0.3f,
        targetValue = 1f,
        animationSpec = infiniteRepeatable(
            animation = tween(800, easing = LinearEasing),
            repeatMode = RepeatMode.Reverse
        ),
        label = "dot2"
    )
    val dotAlpha3 by infiniteTransition.animateFloat(
        initialValue = 0.3f,
        targetValue = 1f,
        animationSpec = infiniteRepeatable(
            animation = tween(800, easing = LinearEasing),
            repeatMode = RepeatMode.Reverse
        ),
        label = "dot3"
    )

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("حالة التقرير", style = ArabicTitleMedium) },
                navigationIcon = {
                    IconButton(onClick = {
                        viewModel.stopPolling()
                        onNavigateBack()
                    }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "رجوع")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primaryContainer,
                    titleContentColor = MaterialTheme.colorScheme.onPrimaryContainer
                )
            )
        }
    ) { padding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .background(
                    Brush.verticalGradient(
                        colors = listOf(
                            MaterialTheme.colorScheme.background,
                            MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.3f)
                        )
                    )
                ),
            contentAlignment = Alignment.Center
        ) {
            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center,
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(32.dp)
            ) {
                // Pulsing medical cross icon
                Box(
                    modifier = Modifier
                        .size(120.dp)
                        .scale(pulseScale),
                    contentAlignment = Alignment.Center
                ) {
                    Box(
                        modifier = Modifier
                            .size(100.dp)
                            .clip(CircleShape)
                            .background(
                                Brush.radialGradient(
                                    colors = listOf(
                                        JeninGreen.copy(alpha = 0.2f),
                                        JeninBlue.copy(alpha = 0.1f)
                                    )
                                )
                            )
                    )
                    Icon(
                        Icons.Default.MedicalServices,
                        contentDescription = null,
                        modifier = Modifier
                            .size(64.dp)
                            .rotate(rotation),
                        tint = JeninBlue
                    )
                }

                Spacer(modifier = Modifier.height(32.dp))

                Text(
                    text = uiState.statusTextAr,
                    style = ArabicBodyLarge.copy(
                        fontWeight = FontWeight.SemiBold,
                        textAlign = TextAlign.Center
                    ),
                    color = MaterialTheme.colorScheme.onBackground,
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(modifier = Modifier.height(24.dp))

                Text(
                    text = "فريق Jenin Care الطبي يقوم بمراجعة وتحليل النتائج",
                    style = ArabicBodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(32.dp))

                // Progress and polling dots
                if (uiState.isPolling) {
                    Row(
                        modifier = Modifier
                            .clip(RoundedCornerShape(20.dp))
                            .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f))
                            .padding(horizontal = 24.dp, vertical = 12.dp),
                        horizontalArrangement = Arrangement.Center,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            Icons.Default.Shield,
                            contentDescription = null,
                            modifier = Modifier.size(16.dp),
                            tint = JeninGreen
                        )
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(
                            text = "جاري المتابعة",
                            style = MaterialTheme.typography.labelSmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(".", color = MaterialTheme.colorScheme.primary, fontSize = 20.sp, modifier = Modifier.alpha(dotAlpha1))
                        Text(".", color = MaterialTheme.colorScheme.primary, fontSize = 20.sp, modifier = Modifier.alpha(dotAlpha2))
                        Text(".", color = MaterialTheme.colorScheme.primary, fontSize = 20.sp, modifier = Modifier.alpha(dotAlpha3))
                    }

                    Spacer(modifier = Modifier.height(16.dp))

                    LinearProgressIndicator(
                        modifier = Modifier
                            .fillMaxWidth(0.6f)
                            .height(4.dp)
                            .clip(RoundedCornerShape(2.dp)),
                        color = JeninBlue,
                        trackColor = MaterialTheme.colorScheme.surfaceVariant
                    )
                }

                Spacer(modifier = Modifier.height(40.dp))

                // PIN Entry section
                AnimatedVisibility(
                    visible = !uiState.showPinEntry,
                    enter = fadeIn(),
                    exit = fadeOut()
                ) {
                    Button(
                        onClick = { viewModel.showPinEntry() },
                        modifier = Modifier
                            .fillMaxWidth(0.8f)
                            .height(52.dp)
                            .shadow(8.dp, RoundedCornerShape(16.dp)),
                        shape = RoundedCornerShape(16.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = MaterialTheme.colorScheme.primary
                        )
                    ) {
                        Icon(Icons.Default.Lock, contentDescription = null, modifier = Modifier.size(20.dp))
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(
                            text = "إدخال رمز PIN",
                            style = ArabicLabelLarge
                        )
                    }
                }

                AnimatedVisibility(
                    visible = uiState.showPinEntry,
                    enter = slideInVertically(initialOffsetY = { it / 2 }) + fadeIn(),
                    exit = fadeOut()
                ) {
                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        modifier = Modifier.fillMaxWidth(0.85f)
                    ) {
                        Text(
                            text = "أدخل رمز PIN للوصول إلى التقرير",
                            style = ArabicBodyMedium,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            textAlign = TextAlign.Center
                        )

                        Spacer(modifier = Modifier.height(16.dp))

                        OutlinedTextField(
                            value = uiState.pin,
                            onValueChange = { viewModel.updatePin(it.filter { c -> c.isDigit() }) },
                            modifier = Modifier.fillMaxWidth(),
                            textStyle = MaterialTheme.typography.headlineLarge.copy(
                                textAlign = TextAlign.Center,
                                letterSpacing = 12.sp,
                                fontWeight = FontWeight.Bold
                            ),
                            singleLine = true,
                            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.NumberPassword),
                            isError = uiState.pinError != null,
                            supportingText = uiState.pinError?.let {
                                { Text(it, color = MaterialTheme.colorScheme.error) }
                            },
                            placeholder = {
                                Text(
                                    "----",
                                    modifier = Modifier.fillMaxWidth(),
                                    textAlign = TextAlign.Center,
                                    style = MaterialTheme.typography.headlineLarge.copy(
                                        letterSpacing = 12.sp,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.4f)
                                    )
                                )
                            },
                            colors = OutlinedTextFieldDefaults.colors(
                                focusedBorderColor = MaterialTheme.colorScheme.primary,
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline
                            ),
                            shape = RoundedCornerShape(12.dp),
                            enabled = !uiState.isUnlocking
                        )

                        Spacer(modifier = Modifier.height(20.dp))

                        Button(
                            onClick = { viewModel.submitPin() },
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(52.dp),
                            shape = RoundedCornerShape(16.dp),
                            enabled = uiState.pin.length == 4 && !uiState.isUnlocking
                        ) {
                            if (uiState.isUnlocking) {
                                CircularProgressIndicator(
                                    modifier = Modifier.size(20.dp),
                                    color = MaterialTheme.colorScheme.onPrimary,
                                    strokeWidth = 2.dp
                                )
                                Spacer(modifier = Modifier.width(8.dp))
                            }
                            Text(
                                text = if (uiState.isUnlocking) "جاري التحقق..." else "تأكيد",
                                style = ArabicLabelLarge
                            )
                        }

                        Spacer(modifier = Modifier.height(8.dp))

                        TextButton(onClick = { viewModel.hidePinEntry() }) {
                            Text("إلغاء", style = ArabicBodyMedium)
                        }
                    }
                }
            }
        }
    }
}
