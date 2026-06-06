package com.jenincare.skinanalyzer.ui.report

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.expandVertically
import androidx.compose.animation.shrinkVertically
import androidx.compose.foundation.background
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
import androidx.compose.material.icons.filled.ArrowDropDown
import androidx.compose.material.icons.filled.Brightness1
import androidx.compose.material.icons.filled.ExpandLess
import androidx.compose.material.icons.filled.ExpandMore
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Lightbulb
import androidx.compose.material.icons.filled.LocalFireDepartment
import androidx.compose.material.icons.filled.Warning
import androidx.compose.material.icons.filled.Whatshot
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.domain.model.Defect
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.SeverityMild
import com.jenincare.skinanalyzer.ui.theme.SeverityModerate
import com.jenincare.skinanalyzer.ui.theme.SeveritySevere

@Composable
fun DefectCard(
    defect: Defect,
    onAddToCart: (productId: String) -> Unit,
    modifier: Modifier = Modifier
) {
    var isExpanded by remember { mutableStateOf(false) }

    val severityColor = when {
        defect.severity <= 0.3f -> SeverityMild
        defect.severity <= 0.6f -> SeverityModerate
        else -> SeveritySevere
    }

    val severityLabel = when {
        defect.severity <= 0.3f -> "خفيف"
        defect.severity <= 0.6f -> "متوسط"
        else -> "شديد"
    }

    val severityIcon = when {
        defect.severity <= 0.3f -> Icons.Default.Info
        defect.severity <= 0.6f -> Icons.Default.Warning
        else -> Icons.Default.LocalFireDepartment
    }

    val severityGradient = Brush.horizontalGradient(
        colors = listOf(
            severityColor.copy(alpha = 0.3f),
            severityColor
        )
    )

    Card(
        modifier = modifier
            .fillMaxWidth()
            .padding(vertical = 6.dp),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surface
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .clickable { isExpanded = !isExpanded }
                .padding(16.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    modifier = Modifier.weight(1f)
                ) {
                    Box(
                        modifier = Modifier
                            .size(44.dp)
                            .clip(CircleShape)
                            .background(severityColor.copy(alpha = 0.12f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            severityIcon,
                            contentDescription = null,
                            tint = severityColor,
                            modifier = Modifier.size(24.dp)
                        )
                    }

                    Spacer(modifier = Modifier.width(12.dp))

                    Column {
                        Text(
                            text = defect.nameAr,
                            style = ArabicTitleSmall,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Text(
                            text = defect.nameEn,
                            style = MaterialTheme.typography.labelSmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }

                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(8.dp))
                            .background(severityColor.copy(alpha = 0.15f))
                            .padding(horizontal = 10.dp, vertical = 4.dp)
                    ) {
                        Text(
                            text = severityLabel,
                            style = MaterialTheme.typography.labelSmall,
                            color = severityColor,
                            fontWeight = FontWeight.Bold
                        )
                    }
                    Spacer(modifier = Modifier.width(4.dp))
                    Icon(
                        if (isExpanded) Icons.Default.ExpandLess else Icons.Default.ExpandMore,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.size(20.dp)
                    )
                }
            }

            // Gradient severity bar
            Spacer(modifier = Modifier.height(10.dp))
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(6.dp)
                    .clip(RoundedCornerShape(3.dp))
                    .background(severityGradient)
            ) {
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(6.dp)
                        .clip(RoundedCornerShape(3.dp))
                        .background(Color.White.copy(alpha = 0.3f))
                )
            }

            AnimatedVisibility(
                visible = isExpanded,
                enter = expandVertically(),
                exit = shrinkVertically()
            ) {
                Column(modifier = Modifier.padding(top = 12.dp)) {
                    HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)
                    Spacer(modifier = Modifier.height(12.dp))

                    Row(verticalAlignment = Alignment.Top) {
                        Icon(
                            Icons.Default.Lightbulb,
                            contentDescription = null,
                            tint = severityColor,
                            modifier = Modifier.size(18.dp)
                        )
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(
                            text = "نصيحة طبية",
                            style = ArabicLabelLarge,
                            color = MaterialTheme.colorScheme.primary
                        )
                    }
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = defect.tipAr,
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )

                    if (defect.products.isNotEmpty()) {
                        Spacer(modifier = Modifier.height(16.dp))
                        Text(
                            text = "المنتجات الموصى بها",
                            style = ArabicLabelLarge,
                            color = MaterialTheme.colorScheme.primary
                        )
                        Spacer(modifier = Modifier.height(8.dp))

                        defect.products.forEach { product ->
                            ProductRecommendationCard(
                                product = product,
                                onAddToCart = { onAddToCart(product.id) }
                            )
                        }
                    }
                }
            }
        }
    }
}
