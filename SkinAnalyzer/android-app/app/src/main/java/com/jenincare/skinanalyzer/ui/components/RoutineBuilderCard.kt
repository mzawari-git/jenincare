package com.jenincare.skinanalyzer.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
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
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material.icons.filled.Spa
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
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgElevated
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.TealAccent

data class RoutineStep(
    val order: Int,
    val name: String,
    val description: String,
    val timeOfDay: String,
    val priority: String
)

@Composable
fun RoutineBuilderCard(
    report: ScanReport,
    modifier: Modifier = Modifier
) {
    val routine = buildRoutine(report)

    Box(
        modifier = modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(
                Brush.linearGradient(colors = listOf(BgSurface, BgElevated))
            )
            .border(1.dp, GlassBorder, RoundedCornerShape(16.dp))
            .padding(16.dp)
    ) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.Spa,
                    contentDescription = null,
                    tint = TealAccent,
                    modifier = Modifier.size(20.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "الروتين المقترح",
                    style = ArabicTitleSmall,
                    color = Color.White
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            routine.forEachIndexed { index, step ->
                RoutineStepItem(step = step)
                if (index < routine.lastIndex) {
                    Spacer(modifier = Modifier.height(8.dp))
                }
            }
        }
    }
}

@Composable
private fun RoutineStepItem(step: RoutineStep) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(12.dp))
            .background(Color(0x1AFFFFFF))
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(36.dp)
                .clip(CircleShape)
                .background(RoseGold.copy(alpha = 0.2f)),
            contentAlignment = Alignment.Center
        ) {
            Text(
                text = "${step.order}",
                color = RoseGold,
                fontWeight = FontWeight.Bold,
                style = MaterialTheme.typography.titleSmall
            )
        }

        Spacer(modifier = Modifier.width(12.dp))

        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = step.name,
                style = ArabicBodyMedium,
                color = Color.White,
                fontWeight = FontWeight.SemiBold
            )
            Text(
                text = step.description,
                style = ArabicBodySmall,
                color = Color(0xFFB0B8C1)
            )
        }

        Spacer(modifier = Modifier.width(8.dp))

        Box(
            modifier = Modifier
                .clip(RoundedCornerShape(8.dp))
                .background(
                    if (step.timeOfDay == "صباحاً") TealAccent.copy(alpha = 0.15f)
                    else RoseGold.copy(alpha = 0.15f)
                )
                .padding(horizontal = 8.dp, vertical = 4.dp)
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.Schedule,
                    contentDescription = null,
                    tint = if (step.timeOfDay == "صباحاً") TealAccent else RoseGold,
                    modifier = Modifier.size(12.dp)
                )
                Spacer(modifier = Modifier.width(4.dp))
                Text(
                    text = step.timeOfDay,
                    style = ArabicBodySmall,
                    color = if (step.timeOfDay == "صباحاً") TealAccent else RoseGold,
                    fontSize = MaterialTheme.typography.labelSmall.fontSize
                )
            }
        }
    }
}

private fun buildRoutine(report: ScanReport): List<RoutineStep> {
    val steps = mutableListOf<RoutineStep>()
    var order = 1

    val hydration = report.radarMetrics.find { it.nameAr.contains("ترطيب") }?.value ?: 0.5f
    val sebum = report.radarMetrics.find { it.nameAr.contains("دهون") }?.value ?: 0.5f
    val pigmentation = report.radarMetrics.find { it.nameAr.contains("تصبغات") }?.value ?: 0.5f
    val pores = report.radarMetrics.find { it.nameAr.contains("مسام") }?.value ?: 0.5f

    steps.add(RoutineStep(order++, "غسول لطيف", "نظفي بشرتك بغسول مناسب لنوع بشرتك", "صباحاً", "عالي"))
    
    if (hydration < 0.6f) {
        steps.add(RoutineStep(order++, "سيروم الهيالورونيك", "رطبي بشرتك بسيروم حمض الهيالورونيك", "صباحاً", "عالي"))
    }

    steps.add(RoutineStep(order++, "واقي شمس SPF50", "ضعي واقي شمس قبل الخروج بـ 20 دقيقة", "صباحاً", "ضروري"))

    if (pigmentation > 0.5f) {
        steps.add(RoutineStep(order++, "سيروم فيتامين سي", "ضعي سيروم فيتامين سي لتفتيح التصبغات", "صباحاً", "متوسط"))
    }

    steps.add(RoutineStep(order++, "إزالة المكياج", "أزيلي المكياج والشوائب بماء ميسيلار", "مساءً", "عالي"))
    steps.add(RoutineStep(order++, "غسول الليل", "نظفي بشرتك مرة ثانية للغسيل المزدوج", "مساءً", "عالي"))

    if (pores > 0.5f) {
        steps.add(RoutineStep(order++, "تونر حمض السالسيليك", "ضعي تونر لتنظيف المسام وتقليصها", "مساءً", "متوسط"))
    }

    if (sebum > 0.6f) {
        steps.add(RoutineStep(order++, "مرطب خالي من الزيوت", "استخدمي مرطب خفيف غير كوميدوجينيك", "مساءً", "عالي"))
    } else {
        steps.add(RoutineStep(order++, "كريم ليلي مرطب", "ضعي كريم ليلي غني بالترطيب", "مساءً", "عالي"))
    }

    return steps
}
