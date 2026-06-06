package com.jenincare.skinanalyzer.ui.onboarding

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.background
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
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CameraAlt
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Face
import androidx.compose.material.icons.filled.LightMode
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.PanTool
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.jenincare.skinanalyzer.data.local.OnboardingManager
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgDeep
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninGreen
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.TealAccent
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

data class OnboardingPage(
    val icon: ImageVector,
    val iconColor: Color,
    val title: String,
    val description: String
)

private val pages = listOf(
    OnboardingPage(
        icon = Icons.Default.MedicalServices,
        iconColor = RoseGold,
        title = "مرحباً بك في Jenin Care",
        description = "تطبيق احترافي لتحليل البشرة باستخدام تقنيات الذكاء الاصطناعي والتصوير متعدد الأطياف"
    ),
    OnboardingPage(
        icon = Icons.Default.LightMode,
        iconColor = TealAccent,
        title = "الإضاءة المثالية",
        description = "تأكد من وجود إضاءة جيدة على وجهك. تجنب الإضاءة المباشرة القوية والظل"
    ),
    OnboardingPage(
        icon = Icons.Default.Face,
        iconColor = JeninBlue,
        title = "وضع الوجه",
        description = "ضع وجهك داخل الإطار البيضاوي. حافظ على ثبات رأسك وانظر مباشرة للكاميرا"
    ),
    OnboardingPage(
        icon = Icons.Default.PanTool,
        iconColor = Color(0xFFFF6F00),
        title = "المسح متعدد الأطياف",
        description = "سيتم التقاط 3 صور: RGB للسطح، UV للأضرار العميقة، Cross-Polarized للحساسية"
    ),
    OnboardingPage(
        icon = Icons.Default.CheckCircle,
        iconColor = Color(0xFF10B981),
        title = "نتائج احترافية",
        description = "احصل على تقرير مفصل مع توصيات منتجات مخصصة ومتابعة التقدم عبر الزمن"
    )
)

@Composable
fun OnboardingScreen(
    onComplete: () -> Unit,
    onboardingManager: OnboardingManager
) {
    var currentPage by remember { mutableIntStateOf(0) }
    val scope = rememberCoroutineScope()

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(BgDeep)
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.SpaceBetween
    ) {
        Spacer(modifier = Modifier.height(40.dp))

        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.Center
        ) {
            pages.forEachIndexed { index, _ ->
                Box(
                    modifier = Modifier
                        .padding(horizontal = 3.dp)
                        .size(if (index == currentPage) 10.dp else 6.dp)
                        .clip(CircleShape)
                        .background(
                            if (index == currentPage) RoseGold
                            else if (index < currentPage) RoseGold.copy(alpha = 0.5f)
                            else Color(0xFF2D3748)
                        )
                )
            }
        }

        AnimatedContent(
            targetState = currentPage,
            transitionSpec = { fadeIn(tween(300)) togetherWith fadeOut(tween(300)) },
            label = "page_transition"
        ) { page ->
            val p = pages[page]
            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                modifier = Modifier.padding(horizontal = 16.dp)
            ) {
                Box(
                    modifier = Modifier
                        .size(140.dp)
                        .clip(CircleShape)
                        .background(
                            Brush.radialGradient(
                                colors = listOf(p.iconColor.copy(alpha = 0.2f), Color.Transparent)
                            )
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Box(
                        modifier = Modifier
                            .size(100.dp)
                            .clip(CircleShape)
                            .background(p.iconColor.copy(alpha = 0.15f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = p.icon,
                            contentDescription = null,
                            tint = p.iconColor,
                            modifier = Modifier.size(50.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(40.dp))

                Text(
                    text = p.title,
                    style = ArabicTitleLarge,
                    color = Color.White,
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(16.dp))

                Text(
                    text = p.description,
                    style = ArabicBodyLarge,
                    color = Color(0xFFB0B8C1),
                    textAlign = TextAlign.Center,
                    lineHeight = 28.sp
                )
            }
        }

        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            modifier = Modifier.fillMaxWidth()
        ) {
            if (currentPage < pages.size - 1) {
                Button(
                    onClick = { currentPage++ },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(56.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = RoseGold
                    ),
                    shape = RoundedCornerShape(16.dp)
                ) {
                    Text("التالي", style = ArabicTitleSmall, color = Color.White)
                }

                Spacer(modifier = Modifier.height(12.dp))

                Text(
                    text = "تخطي",
                    style = ArabicBodyMedium,
                    color = Color(0xFF4A5568),
                    modifier = Modifier.clickable {
                        scope.launch {
                            onboardingManager.completeOnboarding()
                            onComplete()
                        }
                    }
                )
            } else {
                Button(
                    onClick = {
                        scope.launch {
                            onboardingManager.completeOnboarding()
                            onComplete()
                        }
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(56.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = Color(0xFF10B981)
                    ),
                    shape = RoundedCornerShape(16.dp)
                ) {
                    Icon(Icons.Default.CheckCircle, contentDescription = null, modifier = Modifier.size(20.dp))
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("ابدأ الآن", style = ArabicTitleSmall, color = Color.White)
                }
            }

            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}
