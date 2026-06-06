package com.jenincare.skinanalyzer.ui.home

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.CameraAlt
import androidx.compose.material.icons.filled.History
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material.icons.filled.Lightbulb
import androidx.compose.material.icons.filled.LocalFireDepartment
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.Menu
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.Timeline
import androidx.compose.material.icons.filled.TrendingUp
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.DrawerValue
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalDrawerSheet
import androidx.compose.material3.ModalNavigationDrawer
import androidx.compose.material3.NavigationDrawerItem
import androidx.compose.material3.NavigationDrawerItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.material3.rememberDrawerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.domain.model.ScanStatus
import com.jenincare.skinanalyzer.ui.components.ErrorView
import com.jenincare.skinanalyzer.ui.components.LoadingOverlay
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicBodySmall
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgElevated
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.JeninBlue
import com.jenincare.skinanalyzer.ui.theme.JeninGreen
import com.jenincare.skinanalyzer.ui.theme.JeninGreenLight
import com.jenincare.skinanalyzer.ui.theme.RoseGold
import com.jenincare.skinanalyzer.ui.theme.ScoreExcellent
import com.jenincare.skinanalyzer.ui.theme.ScoreFair
import com.jenincare.skinanalyzer.ui.theme.ScoreGood
import com.jenincare.skinanalyzer.ui.theme.ScorePoor
import com.jenincare.skinanalyzer.ui.theme.TealAccent
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(
    onNavigateToCamera: () -> Unit,
    onNavigateToWaiting: (String) -> Unit,
    onNavigateToReport: (String) -> Unit,
    onNavigateToTimeline: (String) -> Unit,
    onNavigateToSettings: () -> Unit,
    onLogout: () -> Unit,
    viewModel: HomeViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val drawerState = rememberDrawerState(initialValue = DrawerValue.Closed)
    val scope = rememberCoroutineScope()

    ModalNavigationDrawer(
        drawerState = drawerState,
        drawerContent = {
            ModalDrawerSheet {
                Spacer(modifier = Modifier.height(24.dp))

                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Box(
                        modifier = Modifier
                            .size(72.dp)
                            .clip(CircleShape)
                            .background(Brush.horizontalGradient(listOf(JeninBlue, JeninGreen))),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            Icons.Default.MedicalServices,
                            contentDescription = null,
                            tint = Color.White,
                            modifier = Modifier.size(36.dp)
                        )
                    }
                    Spacer(modifier = Modifier.height(12.dp))
                    Text("Jenin Care", style = ArabicTitleMedium, color = JeninBlue)
                    Text("مستخدم", style = ArabicBodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }

                Spacer(modifier = Modifier.height(16.dp))

                DrawerMenuItem(Icons.Default.History, "سجل الفحوصات") {
                    scope.launch { drawerState.close() }
                }
                DrawerMenuItem(Icons.Default.CameraAlt, "فحص جديد") {
                    scope.launch { drawerState.close() }
                    onNavigateToCamera()
                }
                DrawerMenuItem(Icons.Default.Timeline, "تقاريري") {
                    scope.launch { drawerState.close() }
                }
                DrawerMenuItem(Icons.Default.Settings, "الإعدادات") {
                    scope.launch { drawerState.close() }
                    onNavigateToSettings()
                }

                Spacer(modifier = Modifier.weight(1f))

                DrawerMenuItem(Icons.AutoMirrored.Filled.ExitToApp, "تسجيل الخروج") {
                    viewModel.logout()
                    onLogout()
                }
            }
        }
    ) {
        Scaffold(
            topBar = {
                TopAppBar(
                    title = {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(
                                Icons.Default.MedicalServices,
                                contentDescription = null,
                                tint = JeninBlue,
                                modifier = Modifier.size(28.dp)
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Text("Jenin Care", style = ArabicTitleMedium)
                        }
                    },
                    navigationIcon = {
                        IconButton(onClick = {
                            scope.launch { drawerState.open() }
                        }) {
                            Icon(Icons.Default.Menu, contentDescription = "القائمة")
                        }
                    },
                    actions = {
                        IconButton(onClick = { viewModel.loadScans() }) {
                            Icon(Icons.Default.Refresh, contentDescription = "تحديث")
                        }
                    },
                    colors = TopAppBarDefaults.topAppBarColors(
                        containerColor = MaterialTheme.colorScheme.primaryContainer,
                        titleContentColor = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                )
            },
            floatingActionButton = {
                FloatingActionButton(
                    onClick = {
                        onNavigateToCamera()
                    },
                    containerColor = JeninBlue,
                    contentColor = Color.White
                ) {
                    Icon(Icons.Default.Add, contentDescription = "فحص جديد")
                }
            }
        ) { padding ->
            when {
                uiState.isLoading -> {
                    LoadingOverlay(message = "جاري تحميل الفحوصات...")
                }
                uiState.error != null -> {
                    ErrorView(
                        message = uiState.error ?: "حدث خطأ",
                        onRetry = { viewModel.loadScans() },
                        modifier = Modifier.padding(padding)
                    )
                }
                uiState.scans.isEmpty() -> {
                    EmptyScanState(
                        onStartScan = onNavigateToCamera,
                        modifier = Modifier.padding(padding)
                    )
                }
                else -> {
                    val approvedScans = uiState.scans.filter { it.status == ScanStatus.APPROVED }
                    val avgScore = if (approvedScans.isNotEmpty()) {
                        approvedScans.map { it.overallScore }.average().toInt()
                    } else 0
                    val totalScans = uiState.scans.size

                    LazyColumn(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(padding)
                            .background(
                                Brush.verticalGradient(
                                    colors = listOf(
                                        MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.1f),
                                        MaterialTheme.colorScheme.background
                                    )
                                )
                            ),
                        contentPadding = PaddingValues(16.dp),
                        verticalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        item {
                            Text(
                                "ملخص سريع",
                                style = ArabicTitleLarge,
                                modifier = Modifier.padding(bottom = 8.dp)
                            )
                        }

                        item {
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                SummaryCard(
                                    title = "إجمالي الفحوصات",
                                    value = "$totalScans",
                                    icon = Icons.Default.MedicalServices,
                                    color = JeninBlue,
                                    modifier = Modifier.weight(1f)
                                )
                                SummaryCard(
                                    title = "متوسط النتيجة",
                                    value = "$avgScore",
                                    suffix = "%",
                                    icon = Icons.Default.Star,
                                    color = when {
                                        avgScore >= 75 -> ScoreExcellent
                                        avgScore >= 50 -> ScoreGood
                                        avgScore >= 25 -> ScoreFair
                                        else -> ScorePoor
                                    },
                                    modifier = Modifier.weight(1f)
                                )
                            }
                        }

                        item {
                            StreakCard(
                                currentStreak = uiState.currentStreak,
                                longestStreak = uiState.longestStreak,
                                todayScanned = uiState.todayScanned
                            )
                        }

                        item {
                            DailyTipCard(tip = uiState.dailyTip)
                        }

                        if (approvedScans.size >= 2) {
                            item {
                                SkinJourneyChart(scans = approvedScans)
                            }
                        }

                        item {
                            Spacer(modifier = Modifier.height(4.dp))
                            Text(
                                "آخر الفحوصات",
                                style = ArabicTitleLarge,
                                modifier = Modifier.padding(top = 8.dp, bottom = 8.dp)
                            )
                        }

                        items(uiState.scans) { scan ->
                            ScanHistoryCard(
                                scan = scan,
                                onClick = {
                                    when (scan.status) {
                                        ScanStatus.APPROVED -> onNavigateToReport(scan.id)
                                        ScanStatus.PENDING, ScanStatus.IN_REVIEW -> onNavigateToWaiting(scan.id)
                                        else -> {}
                                    }
                                },
                                onTimeline = { onNavigateToTimeline(scan.id) }
                            )
                        }

                        item { Spacer(modifier = Modifier.height(80.dp)) }
                    }
                }
            }
        }

        if (uiState.showRatingPrompt) {
            AlertDialog(
                onDismissRequest = { viewModel.dismissRating() },
                icon = {
                    Icon(
                        Icons.Default.Star,
                        contentDescription = null,
                        tint = RoseGold,
                        modifier = Modifier.size(36.dp)
                    )
                },
                title = {
                    Text(
                        text = "هل تستمتع بالتطبيق؟",
                        style = ArabicTitleMedium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                },
                text = {
                    Text(
                        text = "نحن سعداء باستخدامك Jenin Care. هل يمكنك تقييم التطبيق؟",
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                },
                confirmButton = {
                    Button(
                        onClick = { viewModel.rateApp() },
                        colors = ButtonDefaults.buttonColors(containerColor = RoseGold)
                    ) {
                        Text("تقييم الآن", color = Color.White)
                    }
                },
                dismissButton = {
                    TextButton(onClick = { viewModel.dismissRating() }) {
                        Text("لاحقاً", color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
            )
        }
    }
}

@Composable
private fun SummaryCard(
    title: String,
    value: String,
    suffix: String = "",
    icon: ImageVector,
    color: Color,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier.padding(16.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Box(
                modifier = Modifier
                    .size(40.dp)
                    .clip(CircleShape)
                    .background(color.copy(alpha = 0.12f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    icon,
                    contentDescription = null,
                    tint = color,
                    modifier = Modifier.size(22.dp)
                )
            }
            Spacer(modifier = Modifier.height(8.dp))
            Row(verticalAlignment = Alignment.Bottom) {
                Text(
                    text = value,
                    style = MaterialTheme.typography.headlineMedium.copy(
                        fontWeight = FontWeight.Bold,
                        color = color
                    )
                )
                if (suffix.isNotEmpty()) {
                    Text(
                        text = suffix,
                        style = MaterialTheme.typography.titleMedium.copy(
                            fontWeight = FontWeight.Bold,
                            color = color
                        )
                    )
                }
            }
            Text(
                text = title,
                style = MaterialTheme.typography.labelSmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}

@Composable
private fun EmptyScanState(
    onStartScan: () -> Unit,
    modifier: Modifier = Modifier
) {
    Box(
        modifier = modifier.fillMaxSize(),
        contentAlignment = Alignment.Center
    ) {
        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center,
            modifier = Modifier.padding(32.dp)
        ) {
            Box(
                modifier = Modifier
                    .size(120.dp)
                    .clip(CircleShape)
                    .background(JeninBlue.copy(alpha = 0.1f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    Icons.Default.MedicalServices,
                    contentDescription = null,
                    modifier = Modifier.size(60.dp),
                    tint = JeninBlue.copy(alpha = 0.6f)
                )
            }
            Spacer(modifier = Modifier.height(24.dp))
            Text(
                "مرحباً بك في Jenin Care",
                style = ArabicTitleLarge,
                color = MaterialTheme.colorScheme.onBackground
            )
            Spacer(modifier = Modifier.height(12.dp))
            Text(
                "قم بإجراء أول فحص للبشرة",
                style = ArabicBodyLarge,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                "للحصول على تحليل شامل وتوصيات مخصصة",
                style = ArabicBodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                textAlign = TextAlign.Center
            )
            Spacer(modifier = Modifier.height(32.dp))

            Box(
                modifier = Modifier
                    .clip(RoundedCornerShape(16.dp))
                    .background(Brush.horizontalGradient(listOf(JeninBlue, JeninGreen)))
                    .clickable { onStartScan() }
                    .padding(horizontal = 40.dp, vertical = 16.dp)
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(
                        Icons.Default.CameraAlt,
                        contentDescription = null,
                        tint = Color.White,
                        modifier = Modifier.size(20.dp)
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        "ابدأ الفحص الآن",
                        style = ArabicLabelLarge,
                        color = Color.White,
                        fontWeight = FontWeight.Bold
                    )
                }
            }
        }
    }
}

@Composable
private fun ScanHistoryCard(
    scan: Scan,
    onClick: () -> Unit,
    onTimeline: () -> Unit
) {
    val scoreColor = when {
        scan.overallScore >= 75 -> ScoreExcellent
        scan.overallScore >= 50 -> ScoreGood
        scan.overallScore >= 25 -> ScoreFair
        else -> ScorePoor
    }

    val statusColor = when (scan.status) {
        ScanStatus.APPROVED -> JeninGreen
        ScanStatus.PENDING, ScanStatus.IN_REVIEW -> ScoreFair
        ScanStatus.REJECTED -> ScorePoor
    }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() },
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                modifier = Modifier
                    .size(56.dp)
                    .clip(RoundedCornerShape(14.dp))
                    .background(scoreColor.copy(alpha = 0.15f)),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = "${scan.overallScore}",
                    style = MaterialTheme.typography.headlineMedium.copy(
                        fontWeight = FontWeight.Bold,
                        color = scoreColor
                    )
                )
            }

            Spacer(modifier = Modifier.width(16.dp))

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = "فحص ${scan.createdAt.take(10)}",
                    style = ArabicLabelLarge,
                    color = MaterialTheme.colorScheme.onSurface
                )
                Spacer(modifier = Modifier.height(2.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        modifier = Modifier
                            .size(8.dp)
                            .clip(CircleShape)
                            .background(statusColor)
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = scan.status.toStringAr(),
                        style = MaterialTheme.typography.labelSmall,
                        color = statusColor
                    )
                }
                scan.reviewedAt?.let {
                    Spacer(modifier = Modifier.height(2.dp))
                    Text(
                        text = "تمت المراجعة: ${it.take(10)}",
                        style = MaterialTheme.typography.labelSmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }

            if (scan.status == ScanStatus.APPROVED) {
                IconButton(onClick = onTimeline) {
                    Icon(
                        Icons.Default.Timeline,
                        contentDescription = "المخطط الزمني",
                        tint = JeninBlue
                    )
                }
            }
        }
    }
}

@Composable
private fun DrawerMenuItem(
    icon: ImageVector,
    label: String,
    onClick: () -> Unit
) {
    NavigationDrawerItem(
        icon = { Icon(icon, contentDescription = null) },
        label = { Text(label, style = ArabicBodyMedium) },
        selected = false,
        onClick = onClick,
        modifier = Modifier.padding(horizontal = 12.dp, vertical = 2.dp),
        colors = NavigationDrawerItemDefaults.colors(
            unselectedContainerColor = Color.Transparent,
            selectedContainerColor = MaterialTheme.colorScheme.primaryContainer
        )
    )
}

@Composable
private fun StreakCard(
    currentStreak: Int,
    longestStreak: Int,
    todayScanned: Boolean
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(
                Brush.linearGradient(
                    colors = listOf(
                        RoseGold.copy(alpha = 0.15f),
                        TealAccent.copy(alpha = 0.1f)
                    )
                )
            )
            .padding(16.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceEvenly,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(
                    Icons.Default.LocalFireDepartment,
                    contentDescription = null,
                    tint = RoseGold,
                    modifier = Modifier.size(28.dp)
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "$currentStreak",
                    style = MaterialTheme.typography.headlineMedium.copy(
                        fontWeight = FontWeight.Bold,
                        color = RoseGold
                    )
                )
                Text(
                    text = "أيام متتالية",
                    style = ArabicBodySmall,
                    color = Color(0xFFB0B8C1)
                )
            }

            Box(
                modifier = Modifier
                    .width(1.dp)
                    .height(50.dp)
                    .background(Color(0xFF2D3748))
            )

            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Icon(
                    Icons.Default.TrendingUp,
                    contentDescription = null,
                    tint = TealAccent,
                    modifier = Modifier.size(28.dp)
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "$longestStreak",
                    style = MaterialTheme.typography.headlineMedium.copy(
                        fontWeight = FontWeight.Bold,
                        color = TealAccent
                    )
                )
                Text(
                    text = "أطول سلسلة",
                    style = ArabicBodySmall,
                    color = Color(0xFFB0B8C1)
                )
            }

            Box(
                modifier = Modifier
                    .width(1.dp)
                    .height(50.dp)
                    .background(Color(0xFF2D3748))
            )

            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Box(
                    modifier = Modifier
                        .size(28.dp)
                        .clip(CircleShape)
                        .background(if (todayScanned) ScoreExcellent.copy(alpha = 0.2f) else Color(0xFF2D3748)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        Icons.Default.CameraAlt,
                        contentDescription = null,
                        tint = if (todayScanned) ScoreExcellent else Color(0xFF4A5568),
                        modifier = Modifier.size(16.dp)
                    )
                }
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = if (todayScanned) "تم" else "لم يتم",
                    style = MaterialTheme.typography.labelSmall.copy(
                        fontWeight = FontWeight.Bold,
                        color = if (todayScanned) ScoreExcellent else Color(0xFF4A5568)
                    )
                )
                Text(
                    text = "فحص اليوم",
                    style = ArabicBodySmall,
                    color = Color(0xFFB0B8C1)
                )
            }
        }
    }
}

@Composable
private fun DailyTipCard(tip: String) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(
                Brush.linearGradient(
                    colors = listOf(BgSurface, BgElevated)
                )
            )
            .padding(16.dp)
    ) {
        Row(
            verticalAlignment = Alignment.CenterVertically,
            modifier = Modifier.fillMaxWidth()
        ) {
            Box(
                modifier = Modifier
                    .size(40.dp)
                    .clip(CircleShape)
                    .background(TealAccent.copy(alpha = 0.15f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    Icons.Default.Lightbulb,
                    contentDescription = null,
                    tint = TealAccent,
                    modifier = Modifier.size(22.dp)
                )
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = "نصيحة اليوم",
                    style = ArabicTitleSmall,
                    color = TealAccent
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = tip,
                    style = ArabicBodyMedium,
                    color = Color(0xFFB0B8C1)
                )
            }
        }
    }
}

@Composable
private fun SkinJourneyChart(scans: List<Scan>) {
    val recentScans = scans.takeLast(10)
    if (recentScans.isEmpty()) return

    val maxScore = 100f
    val chartHeight = 120.dp

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(
                Brush.linearGradient(colors = listOf(BgSurface, BgElevated))
            )
            .padding(16.dp)
    ) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.Timeline,
                    contentDescription = null,
                    tint = RoseGold,
                    modifier = Modifier.size(20.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "رحلة بشرتك",
                    style = ArabicTitleSmall,
                    color = Color.White
                )
            }

            Spacer(modifier = Modifier.height(16.dp))

            androidx.compose.foundation.Canvas(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(chartHeight)
            ) {
                val barWidth = size.width / (recentScans.size * 2 + 1)
                val spacing = barWidth

                recentScans.forEachIndexed { index, scan ->
                    val barHeight = (scan.overallScore / maxScore) * size.height
                    val x = spacing + index * (barWidth + spacing)
                    val y = size.height - barHeight

                    val barColor = when {
                        scan.overallScore >= 75 -> ScoreExcellent
                        scan.overallScore >= 50 -> ScoreGood
                        scan.overallScore >= 25 -> ScoreFair
                        else -> ScorePoor
                    }

                    drawRoundRect(
                        color = barColor.copy(alpha = 0.3f),
                        topLeft = androidx.compose.ui.geometry.Offset(x, 0f),
                        size = androidx.compose.ui.geometry.Size(barWidth, size.height),
                        cornerRadius = androidx.compose.ui.geometry.CornerRadius(4f)
                    )

                    drawRoundRect(
                        color = barColor,
                        topLeft = androidx.compose.ui.geometry.Offset(x, y),
                        size = androidx.compose.ui.geometry.Size(barWidth, barHeight),
                        cornerRadius = androidx.compose.ui.geometry.CornerRadius(4f)
                    )

                    drawContext.canvas.nativeCanvas.drawText(
                        "${scan.overallScore}",
                        x + barWidth / 2,
                        y - 8f,
                        android.graphics.Paint().apply {
                            color = android.graphics.Color.WHITE
                            textAlign = android.graphics.Paint.Align.CENTER
                            textSize = 10f * density
                            isFakeBoldText = true
                        }
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Text(
                    text = recentScans.first().createdAt.take(10),
                    style = MaterialTheme.typography.labelSmall,
                    color = Color(0xFF4A5568)
                )
                Text(
                    text = recentScans.last().createdAt.take(10),
                    style = MaterialTheme.typography.labelSmall,
                    color = Color(0xFF4A5568)
                )
            }
        }
    }
}
