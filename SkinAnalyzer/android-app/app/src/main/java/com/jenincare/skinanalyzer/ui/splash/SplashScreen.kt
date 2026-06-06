package com.jenincare.skinanalyzer.ui.splash

import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.size
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.jenincare.skinanalyzer.R
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.MedicalNavy
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.ScoreExcellent
import kotlinx.coroutines.delay

@Composable
fun SplashScreen(
    onNavigateToLogin: () -> Unit,
    onNavigateToHome: () -> Unit,
    viewModel: SplashViewModel = hiltViewModel()
) {
    var startAnimation by remember { mutableStateOf(false) }
    val alphaAnim by animateFloatAsState(
        targetValue = if (startAnimation) 1f else 0f,
        animationSpec = tween(1000),
        label = "alpha"
    )
    val scaleAnim by animateFloatAsState(
        targetValue = if (startAnimation) 1f else 0.5f,
        animationSpec = tween(1000),
        label = "scale"
    )

    val uiState = viewModel.uiState

    LaunchedEffect(Unit) {
        startAnimation = true
        delay(800)
        viewModel.checkStartDestination()
    }

    LaunchedEffect(uiState.value.destination) {
        when (uiState.value.destination) {
            is SplashDestination.Login -> {
                delay(1200)
                onNavigateToLogin()
            }
            is SplashDestination.Home -> {
                delay(1200)
                onNavigateToHome()
            }
            null -> {}
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(
                        MedicalNavy,                        // #0D1117 أسود بحري داكن
                        MedicalNavy,
                        RoseGold.copy(alpha = 0.15f)       // لمسة ذهبية خفيفة في الأسفل
                    )
                )
            ),
        contentAlignment = Alignment.Center
    ) {
        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center,
            modifier = Modifier
                .scale(scaleAnim)
                .alpha(alphaAnim)
        ) {
            Icon(
                painter = painterResource(id = R.drawable.ic_skin_analyzer),
                contentDescription = "SkinAnalyzer",
                modifier = Modifier.size(100.dp),
                tint = Color.White
            )

            Spacer(modifier = Modifier.height(24.dp))

            Text(
                text = "SkinAnalyzer",
                style = MaterialTheme.typography.headlineLarge.copy(
                    fontWeight = FontWeight.ExtraBold,
                    color = Color.White
                )
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "Jenin Care",
                style = MaterialTheme.typography.titleLarge.copy(
                    fontWeight = FontWeight.SemiBold,
                    color = Color.White.copy(alpha = 0.9f)
                )
            )

            Spacer(modifier = Modifier.height(16.dp))

            Text(
                text = "تحليل بشرة متقدم بالذكاء الاصطناعي",
                style = ArabicBodyLarge.copy(
                    color = Color.White.copy(alpha = 0.85f)
                )
            )
        }
    }
}
