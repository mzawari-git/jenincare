package com.jenincare.skinanalyzer.ui.timeline

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.horizontalScroll
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
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.OpenInNew
import androidx.compose.material.icons.automirrored.filled.TrendingDown
import androidx.compose.material.icons.automirrored.filled.TrendingUp
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Compare
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator

import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.nativeCanvas
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.jenincare.skinanalyzer.data.model.Scan
import com.jenincare.skinanalyzer.ui.components.ComparisonSlider
import com.jenincare.skinanalyzer.ui.components.EmptyStateView
import com.jenincare.skinanalyzer.ui.components.ErrorView
import com.jenincare.skinanalyzer.ui.components.LoadingOverlay
import com.jenincare.skinanalyzer.ui.report.RadarChart
import com.jenincare.skinanalyzer.ui.report.ScoreGauge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninGreen
import com.jenincare.skinanalyzer.ui.theme.ScoreExcellent
import com.jenincare.skinanalyzer.ui.theme.ScoreGood
import androidx.hilt.navigation.compose.hiltViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TimelineScreen(
    scanId: String,
    onNavigateToReport: (String) -> Unit,
    onNavigateBack: () -> Unit,
    viewModel: TimelineViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()

    LaunchedEffect(scanId) {
        viewModel.loadScans()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("المخطط الزمني", style = ArabicTitleMedium) },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "رجوع")
                    }
                },
                actions = {
                    if (uiState.selectedScanA != null && uiState.selectedScanB != null) {
                        IconButton(onClick = { viewModel.compareSelected() }) {
                            Icon(Icons.Default.Compare, contentDescription = "مقارنة")
                        }
                    }
                    if (uiState.selectedScanA != null || uiState.selectedScanB != null) {
                        IconButton(onClick = { viewModel.clearComparison() }) {
                            Icon(Icons.Default.Close, contentDescription = "مسح الاختيار")
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primaryContainer,
                    titleContentColor = MaterialTheme.colorScheme.onPrimaryContainer
                )
            )
        }
    ) { padding ->
        when {
            uiState.isLoading -> {
                LoadingOverlay(message = "جاري تحميل السجل...")
            }
            uiState.error != null -> {
                ErrorView(
                    message = uiState.error ?: "حدث خطأ",
                    onRetry = { viewModel.loadScans() },
                    modifier = Modifier.padding(padding)
                )
            }
            uiState.scans.isEmpty() -> {
                EmptyStateView(
                    message = "لا توجد فحوصات سابقة",
                    subMessage = "قم بإجراء فحص جديد للبشرة",
                    modifier = Modifier.padding(padding)
                )
            }
            else -> {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(padding)
                        .verticalScroll(rememberScrollState())
                ) {
                    // Horizontal scan timeline
                    Text(
                        text = "سجل الفحوصات",
                        style = ArabicTitleSmall,
                        modifier = Modifier.padding(16.dp, 12.dp, 16.dp, 4.dp)
                    )

                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .horizontalScroll(rememberScrollState())
                            .padding(horizontal = 16.dp),
                        horizontalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        uiState.scans.forEach { scan ->
                            ScanTimelineCard(
                                scan = scan,
                                isSelected = scan == uiState.selectedScanA || scan == uiState.selectedScanB,
                                selectionLabel = when {
                                    scan == uiState.selectedScanA -> "أ"
                                    scan == uiState.selectedScanB -> "ب"
                                    else -> null
                                },
                                onClick = {
                                    if (uiState.selectedScanA == null) {
                                        viewModel.selectScanA(scan)
                                    } else if (uiState.selectedScanB == null && uiState.selectedScanA != scan) {
                                        viewModel.selectScanB(scan)
                                    }
                                },
                                onViewReport = { onNavigateToReport(scan.id) }
                            )
                        }
                    }

                    Spacer(modifier = Modifier.height(16.dp))

                    // Progress graph
                    if (uiState.progressData.size >= 2) {
                        ProgressLineChart(
                            data = uiState.progressData,
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(horizontal = 16.dp)
                        )
                        Spacer(modifier = Modifier.height(16.dp))
                    }

                    // Comparison view
                    if (uiState.isComparing && uiState.reportA != null && uiState.reportB != null) {
                        ComparisonView(
                            reportA = uiState.reportA!!,
                            reportB = uiState.reportB!!,
                            modifier = Modifier.padding(16.dp)
                        )
                    }

                    Spacer(modifier = Modifier.height(32.dp))
                }
            }
        }
    }
}

@Composable
private fun ScanTimelineCard(
    scan: Scan,
    isSelected: Boolean,
    selectionLabel: String?,
    onClick: () -> Unit,
    onViewReport: () -> Unit
) {
    Card(
        modifier = Modifier
            .width(120.dp)
            .clickable { onClick() },
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(
            containerColor = if (isSelected) MaterialTheme.colorScheme.primaryContainer
            else MaterialTheme.colorScheme.surface
        ),
        elevation = CardDefaults.cardElevation(
            defaultElevation = if (isSelected) 6.dp else 2.dp
        )
    ) {
        Column(
            modifier = Modifier.padding(12.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            if (selectionLabel != null) {
                Box(
                    modifier = Modifier
                        .size(28.dp)
                        .clip(CircleShape)
                        .background(MaterialTheme.colorScheme.primary),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = selectionLabel,
                        style = MaterialTheme.typography.labelLarge,
                        color = MaterialTheme.colorScheme.onPrimary,
                        fontWeight = FontWeight.Bold
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            val scoreColor = when {
                scan.overallHealthScore >= 75f -> ScoreExcellent
                scan.overallHealthScore >= 50f -> ScoreGood
                else -> MaterialTheme.colorScheme.error
            }

            Text(
                text = "${scan.overallHealthScore.toInt()}",
                style = MaterialTheme.typography.headlineMedium.copy(
                    fontWeight = FontWeight.Bold,
                    color = scoreColor
                )
            )
            Text(
                text = "/100",
                style = MaterialTheme.typography.labelSmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )

            Spacer(modifier = Modifier.height(4.dp))

            Text(
                text = scan.createdAt.takeLast(5),
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )

            Spacer(modifier = Modifier.height(6.dp))

            TextButton(
                onClick = onViewReport,
                modifier = Modifier
                    .clip(RoundedCornerShape(8.dp))
                    .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f))
            ) {
                Icon(
                    Icons.AutoMirrored.Filled.OpenInNew,
                    contentDescription = null,
                    modifier = Modifier.size(14.dp)
                )
                Spacer(modifier = Modifier.width(2.dp))
                Text(
                    text = "عرض",
                    style = MaterialTheme.typography.labelSmall
                )
            }
        }
    }
}

@Composable
private fun ProgressLineChart(
    data: List<Pair<String, Int>>,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.AutoMirrored.Filled.TrendingUp,
                    contentDescription = null,
                    tint = JeninGreen,
                    modifier = Modifier.size(20.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "تطور النتائج",
                    style = ArabicTitleSmall,
                    color = MaterialTheme.colorScheme.onSurface
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            Canvas(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(160.dp)
            ) {
                if (data.size < 2) return@Canvas

                val minScore = (data.minOf { it.second } - 10).coerceAtLeast(0).toFloat()
                val maxScore = (data.maxOf { it.second } + 10).coerceAtMost(100).toFloat()
                val range = maxScore - minScore
                val stepX = size.width / (data.size - 1).coerceAtLeast(1)

                // Draw grid lines
                for (i in 0..4) {
                    val y = size.height * (1 - i / 4f)
                    drawLine(
                        Color.LightGray.copy(alpha = 0.3f),
                        Offset(0f, y),
                        Offset(size.width, y),
                        strokeWidth = 1.dp.toPx()
                    )
                    drawContext.canvas.nativeCanvas.drawText(
                        "${(minScore + range * i / 4).toInt()}",
                        0f,
                        y,
                        android.graphics.Paint().apply {
                            color = android.graphics.Color.GRAY
                            textSize = 10.dp.toPx()
                            isAntiAlias = true
                        }
                    )
                }

                // Draw line path
                val path = Path()
                data.forEachIndexed { index, pair ->
                    val x = index * stepX
                    val y = size.height * (1 - (pair.second - minScore) / range)
                    if (index == 0) path.moveTo(x, y) else path.lineTo(x, y)
                }

                drawPath(
                    path,
                    brush = Brush.horizontalGradient(listOf(JeninBlue, JeninGreen)),
                    style = Stroke(width = 3.dp.toPx(), cap = StrokeCap.Round)
                )

                // Draw dots
                data.forEachIndexed { index, pair ->
                    val x = index * stepX
                    val y = size.height * (1 - (pair.second - minScore) / range)
                    drawCircle(Color.White, 6.dp.toPx(), Offset(x, y))
                    drawCircle(JeninBlue, 4.dp.toPx(), Offset(x, y))

                    if (index % 2 == 0) {
                        drawContext.canvas.nativeCanvas.drawText(
                            pair.first.takeLast(5),
                            x,
                            size.height - 4.dp.toPx(),
                            android.graphics.Paint().apply {
                                color = android.graphics.Color.GRAY
                                textSize = 9.dp.toPx()
                                textAlign = android.graphics.Paint.Align.CENTER
                                isAntiAlias = true
                            }
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun ComparisonView(
    reportA: com.jenincare.skinanalyzer.domain.model.ScanReport,
    reportB: com.jenincare.skinanalyzer.domain.model.ScanReport,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Column(
            modifier = Modifier.padding(20.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(
                text = "مقارنة الفحوصات",
                style = ArabicTitleMedium,
                color = MaterialTheme.colorScheme.onSurface
            )

            Spacer(modifier = Modifier.height(16.dp))

            ComparisonSlider(
                beforeImageUrl = reportA.scan.imageUrl,
                afterImageUrl = reportB.scan.imageUrl,
                beforeLabel = "السابق",
                afterLabel = "الحالي"
            )

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("السابق", style = ArabicBodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Spacer(modifier = Modifier.height(8.dp))
                    ScoreGauge(score = reportA.scan.overallScore, gaugeSize = 110.dp, strokeWidth = 8.dp)
                }

                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("الحالي", style = ArabicBodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Spacer(modifier = Modifier.height(8.dp))
                    ScoreGauge(score = reportB.scan.overallScore, gaugeSize = 110.dp, strokeWidth = 8.dp)
                }
            }

            // Improvement indicator
            val diff = reportB.scan.overallScore - reportA.scan.overallScore
            if (diff != 0) {
                Spacer(modifier = Modifier.height(12.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(
                        if (diff > 0) Icons.AutoMirrored.Filled.TrendingUp else Icons.AutoMirrored.Filled.TrendingDown,
                        contentDescription = null,
                        tint = if (diff > 0) JeninGreen else MaterialTheme.colorScheme.error,
                        modifier = Modifier.size(18.dp)
                    )
                    Spacer(modifier = Modifier.width(4.dp))
                    Text(
                        text = if (diff > 0) "تحسن بنسبة $diff نقطة" else "تراجع بنسبة ${-diff} نقطة",
                        style = ArabicBodyMedium,
                        color = if (diff > 0) JeninGreen else MaterialTheme.colorScheme.error,
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }
        }
    }
}
