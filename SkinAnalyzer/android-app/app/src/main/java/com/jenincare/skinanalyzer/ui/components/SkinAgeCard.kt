package com.jenincare.skinanalyzer.ui.components

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
import androidx.compose.material.icons.filled.AutoAwesome
import androidx.compose.material.icons.filled.FaceRetouchingNatural
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgElevated
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.HealthExcellent
import com.jenincare.skinanalyzer.ui.theme.HealthFair
import com.jenincare.skinanalyzer.ui.theme.HealthGood
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.TealAccent
import kotlin.math.roundToInt

@Composable
fun SkinAgeCard(
    report: ScanReport,
    modifier: Modifier = Modifier
) {
    val skinAge = estimateSkinAge(report)
    val actualAgeLabel = when {
        skinAge.estimatedAge < 25 -> "شباب ممتاز"
        skinAge.estimatedAge < 35 -> "بشرة شابة"
        skinAge.estimatedAge < 45 -> "عمر بشرة طبيعي"
        else -> "يحتاج عناية"
    }
    val ageColor = when {
        skinAge.difference <= 0 -> HealthExcellent
        skinAge.difference <= 5 -> HealthGood
        else -> HealthFair
    }

    Box(
        modifier = modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(
                Brush.linearGradient(
                    colors = listOf(BgSurface, BgElevated)
                )
            )
            .border(1.dp, GlassBorder, RoundedCornerShape(16.dp))
            .padding(16.dp)
    ) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.FaceRetouchingNatural,
                    contentDescription = null,
                    tint = RoseGold,
                    modifier = Modifier.size(20.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "عمر البشرة",
                    style = ArabicTitleSmall,
                    color = Color.White
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(
                        text = "${skinAge.estimatedAge}",
                        fontSize = 42.sp,
                        fontWeight = FontWeight.Bold,
                        color = ageColor
                    )
                    Text(
                        text = "العمر التقديري",
                        style = ArabicBodySmall,
                        color = Color(0xFFB0B8C1)
                    )
                }

                Box(
                    modifier = Modifier
                        .size(1.dp)
                        .height(60.dp)
                        .background(Color(0xFF2D3748))
                )

                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(
                        text = if (skinAge.difference > 0) "+${skinAge.difference}" else "${skinAge.difference}",
                        fontSize = 28.sp,
                        fontWeight = FontWeight.Bold,
                        color = ageColor
                    )
                    Text(
                        text = actualAgeLabel,
                        style = ArabicBodySmall,
                        color = ageColor
                    )
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            Text(
                text = skinAge.recommendation,
                style = ArabicBodyMedium,
                color = Color(0xFFB0B8C1)
            )
        }
    }
}

data class SkinAgeResult(
    val estimatedAge: Int,
    val difference: Int,
    val recommendation: String
)

private fun estimateSkinAge(report: ScanReport): SkinAgeResult {
    val score = report.scan.overallScore
    val hydration = report.radarMetrics.find { it.nameEn.equals("Hydration", true) || it.nameAr.contains("ترطيب") }?.value ?: 0.5f
    val elasticity = report.radarMetrics.find { it.nameEn.equals("Elasticity", true) || it.nameAr.contains("مرونة") }?.value ?: 0.5f
    val pigmentation = report.radarMetrics.find { it.nameEn.equals("Pigmentation", true) || it.nameAr.contains("تصبغات") }?.value ?: 0.5f

    val baseAge = 30
    val scoreFactor = ((100 - score) * 0.15f).roundToInt()
    val hydrationFactor = ((0.5f - hydration) * 10).roundToInt()
    val elasticityFactor = ((0.5f - elasticity) * 15).roundToInt()
    val pigmentationFactor = (pigmentation * 8).roundToInt()

    val estimatedAge = (baseAge + scoreFactor + hydrationFactor + elasticityFactor + pigmentationFactor).coerceIn(18, 65)
    val referenceAge = 30
    val difference = estimatedAge - referenceAge

    val recommendation = when {
        difference <= -5 -> "بشرتك تبدو أصغر من عمرك! حافظي على روتينك الحالي"
        difference <= 0 -> "بشرتك بمظهر صحي وعمرها مناسب. استمري بالعناية الجيدة"
        difference <= 5 -> "بشرتك بحاجة لعناية إضافية. ركزي على الترطيب والحماية من الشمس"
        else -> "بشرتك تحتاج عناية مركزة. ننصح باستشارة أخصائي جلدية"
    }

    return SkinAgeResult(estimatedAge, difference, recommendation)
}
