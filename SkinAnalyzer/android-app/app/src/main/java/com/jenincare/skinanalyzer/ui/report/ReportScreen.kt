package com.jenincare.skinanalyzer.ui.report

import android.content.Intent
import android.graphics.Bitmap
import android.widget.Toast
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
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
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.DateRange
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.QrCodeScanner
import androidx.compose.material.icons.filled.Share
import androidx.compose.material.icons.filled.Timeline
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
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
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.jenincare.skinanalyzer.BuildConfig
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.ui.components.ErrorView
import com.jenincare.skinanalyzer.ui.components.GlassCard
import com.jenincare.skinanalyzer.ui.components.LoadingOverlay
import com.jenincare.skinanalyzer.ui.components.SkinAgeCard
import com.jenincare.skinanalyzer.ui.components.RoutineBuilderCard
import com.jenincare.skinanalyzer.ui.theme.*
import com.jenincare.skinanalyzer.util.PdfReportGenerator
import com.google.zxing.BarcodeFormat
import com.google.zxing.qrcode.QRCodeWriter
import kotlinx.coroutines.launch

private val SPLIT_THRESHOLD = 600.dp

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ReportScreen(
    scanId: String,
    onNavigateToTimeline: (String) -> Unit,
    onNavigateBack: () -> Unit,
    viewModel: ReportViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val context = LocalContext.current
    val snackbarHostState = remember { SnackbarHostState() }

    LaunchedEffect(scanId) {
        viewModel.loadReport(scanId)
    }

    LaunchedEffect(uiState.cartMessage) {
        uiState.cartMessage?.let {
            snackbarHostState.showSnackbar(it)
            viewModel.clearCartMessage()
        }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        topBar = {
            TopAppBar(
                title = {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(
                            Icons.Default.Person,
                            contentDescription = null,
                            modifier = Modifier.size(18.dp),
                            tint = RoseGold
                        )
                        Spacer(modifier = Modifier.width(6.dp))
                        Text("تقرير تحليل البشرة", style = ArabicTitleSmall)
                    }
                },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "رجوع")
                    }
                },
                actions = {
                    IconButton(onClick = { onNavigateToTimeline(scanId) }) {
                        Icon(Icons.Default.Timeline, contentDescription = "المخطط الزمني")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = BgDeep,
                    titleContentColor = MaterialTheme.colorScheme.onSurface
                )
            )
        }
    ) { padding ->
        when {
            uiState.isLoading -> {
                LoadingOverlay(message = "جاري تحميل التقرير...")
            }
            uiState.error != null -> {
                ErrorView(
                    message = uiState.error ?: "حدث خطأ",
                    onRetry = { viewModel.retry(scanId) },
                    modifier = Modifier.padding(padding)
                )
            }
            uiState.report != null -> {
                val report = uiState.report!!
                BoxWithConstraints(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(padding)
                        .background(BgDeep)
                ) {
                    if (maxWidth > SPLIT_THRESHOLD) {
                        LandscapeSplitPane(report = report, context = context, onAddToCart = { productId -> viewModel.addToCart(scanId, productId) })
                    } else {
                        PortraitScroll(report = report, context = context, onAddToCart = { productId -> viewModel.addToCart(scanId, productId) })
                    }
                }
            }
        }
    }
}

@Composable
private fun LandscapeSplitPane(
    report: ScanReport,
    context: android.content.Context,
    onAddToCart: (String) -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxSize()
            .padding(12.dp),
        horizontalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        // ── LEFT PANE: Face Image + Heatmap ──
        Column(
            modifier = Modifier
                .weight(1f)
                .fillMaxHeight(),
            verticalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            GlassCard(
                modifier = Modifier.weight(1f).fillMaxWidth(),
                contentPadding = 0.dp
            ) {
                Box(modifier = Modifier.fillMaxSize()) {
                    if (report.scan.imageUrl != null) {
                        AsyncImage(
                            model = report.scan.imageUrl,
                            contentDescription = "صورة الوجه",
                            contentScale = ContentScale.Crop,
                            modifier = Modifier.fillMaxSize()
                        )
                    }
                    if (report.heatmapPoints.isNotEmpty()) {
                        HeatmapOverlay(
                            imageUrl = null,
                            heatmapPoints = report.heatmapPoints,
                            modifier = Modifier.fillMaxSize()
                        )
                    }
                }
            }

            // Filter toggle row
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                listOf("RGB", "UV", "Polarized").forEach { label ->
                    GlassCard(
                        modifier = Modifier.weight(1f),
                        borderColor = RoseGold.copy(alpha = 0.2f),
                        contentPadding = 8.dp,
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text(
                            text = "● $label",
                            style = ArabicBodySmall,
                            color = RoseGold,
                            textAlign = TextAlign.Center,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            }

            // Severity legend
            GlassCard(
                borderColor = RoseGold.copy(alpha = 0.15f),
                contentPadding = 10.dp
            ) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceEvenly
                ) {
                    LegendDot(color = HeatmapInflammation, label = "التهاب")
                    LegendDot(color = HeatmapDryness, label = "جفاف")
                    LegendDot(color = HeatmapHealthy, label = "صحي")
                }
            }
        }

        // ── RIGHT PANE: Score + Metrics + Products ──
        Column(
            modifier = Modifier
                .weight(1f)
                .fillMaxHeight()
                .verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            ScoreSection(report = report)

            RadarSection(report = report)

            MetricsList(report = report, onAddToCart = onAddToCart)

            SkinAgeCard(report = report)

            RoutineBuilderCard(report = report)

        if (report.advancedMetrics.isNotEmpty()) {
            AdvancedMetricsSection(report = report)
        }

        if (report.spectralAnalysis.isNotEmpty()) {
            SpectralAnalysisSection(report = report)
        }

        if (report.facialZoneAnalysis.isNotEmpty()) {
            FacialZoneSection(report = report)
        }

        if (report.customArabicAnalysis != null) {
            ArabicAnalysisSection(report = report)
        }

        ProductsSection(report = report, onAddToCart = onAddToCart)

            ActionButtons(report = report, context = context, onAddToCart = onAddToCart)
        }
    }
}

@Composable
private fun PortraitScroll(
    report: ScanReport,
    context: android.content.Context,
    onAddToCart: (String) -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(12.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        // Face image card
        GlassCard(
            modifier = Modifier.fillMaxWidth(),
            contentPadding = 0.dp
        ) {
            Box(modifier = Modifier.fillMaxWidth().height(300.dp)) {
                if (report.scan.imageUrl != null) {
                    AsyncImage(
                        model = report.scan.imageUrl,
                        contentDescription = "صورة الوجه",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }
                if (report.heatmapPoints.isNotEmpty()) {
                    HeatmapOverlay(
                        imageUrl = null,
                        heatmapPoints = report.heatmapPoints,
                        modifier = Modifier.fillMaxSize()
                    )
                }
            }
        }

        ScoreSection(report = report)
        RadarSection(report = report)
        MetricsList(report = report, onAddToCart = onAddToCart)
        SkinAgeCard(report = report)
        RoutineBuilderCard(report = report)

        if (report.advancedMetrics.isNotEmpty()) {
            AdvancedMetricsSection(report = report)
        }

        if (report.spectralAnalysis.isNotEmpty()) {
            SpectralAnalysisSection(report = report)
        }

        if (report.facialZoneAnalysis.isNotEmpty()) {
            FacialZoneSection(report = report)
        }

        if (report.customArabicAnalysis != null) {
            ArabicAnalysisSection(report = report)
        }

        ProductsSection(report = report, onAddToCart = onAddToCart)

        ActionButtons(report = report, context = context, onAddToCart = onAddToCart)
    }
}

@Composable
private fun ScoreSection(report: ScanReport) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "صحة البشرة",
                style = ArabicHeadlineMedium,
                color = MaterialTheme.colorScheme.onSurface
            )

            Spacer(modifier = Modifier.height(8.dp))

            val scoreColor = when {
                report.scan.overallScore >= 80 -> HealthExcellent
                report.scan.overallScore >= 60 -> HealthGood
                else -> HealthFair
            }

            Row(verticalAlignment = Alignment.Bottom) {
                Text(
                    text = "${report.scan.overallScore}",
                    style = ScoreNumberStyle,
                    color = scoreColor
                )
                Spacer(modifier = Modifier.width(4.dp))
                Text(
                    text = "/ 100",
                    style = ScoreSubtitleStyle,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(bottom = 8.dp)
                )
            }

            Spacer(modifier = Modifier.height(4.dp))

            val scoreLabel = when {
                report.scan.overallScore >= 80 -> "ممتاز"
                report.scan.overallScore >= 60 -> "جيد"
                report.scan.overallScore >= 40 -> "متوسط"
                else -> "يحتاج عناية"
            }

            Text(
                text = scoreLabel,
                style = ArabicBodyLarge,
                color = scoreColor,
                textAlign = TextAlign.Center
            )

            Spacer(modifier = Modifier.height(8.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    Icons.Default.DateRange,
                    contentDescription = null,
                    modifier = Modifier.size(14.dp),
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Spacer(modifier = Modifier.width(4.dp))
                Text(
                    text = report.scan.createdAt,
                    style = ArabicBodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }
    }
}

@Composable
private fun RadarSection(report: ScanReport) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "تحليل الأبعاد",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))

            RadarChart(
                metrics = report.radarMetrics,
                modifier = Modifier.fillMaxWidth()
            )
        }
    }
}

@Composable
@Suppress("UNUSED_PARAMETER")
private fun MetricsList(report: ScanReport, onAddToCart: (String) -> Unit) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "المؤشرات التفصيلية",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(10.dp))

            report.radarMetrics.forEach { metric ->
                val pct = (metric.value * 100).toInt()
                val barColor = when {
                    pct >= 70 -> HealthExcellent
                    pct >= 50 -> HealthGood
                    else -> HealthFair
                }

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 3.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = metric.nameAr,
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.weight(1f)
                    )
                    Spacer(modifier = Modifier.width(8.dp))

                    Box(
                        modifier = Modifier
                            .widthIn(max = 120.dp)
                            .height(6.dp)
                            .clip(RoundedCornerShape(3.dp))
                            .background(MaterialTheme.colorScheme.surfaceVariant)
                    ) {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth(fraction = metric.value)
                                .fillMaxHeight()
                                .clip(RoundedCornerShape(3.dp))
                                .background(
                                    Brush.horizontalGradient(
                                        colors = listOf(barColor, barColor.copy(alpha = 0.6f))
                                    )
                                )
                        )
                    }

                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "$pct%",
                        style = MetricValueStyle,
                        color = barColor
                    )
                }
            }
        }
    }
}

@Composable
private fun ProductsSection(report: ScanReport, onAddToCart: (String) -> Unit) {
    val recommendedProducts = report.defects.flatMap { it.products }.take(4)
    if (recommendedProducts.isEmpty()) return

    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "المنتجات المقترحة",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))

            recommendedProducts.forEach { product ->
                ProductRecommendationCard(
                    product = product,
                    onAddToCart = {
                        onAddToCart(product.id)
                    }
                )
            }
        }
    }
}

@Composable
private fun ActionButtons(
    report: ScanReport,
    context: android.content.Context,
    onAddToCart: (String) -> Unit
) {
    var showQrDialog by remember { mutableStateOf(false) }
    var qrBitmap by remember { mutableStateOf<Bitmap?>(null) }
    var isGeneratingPdf by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        Button(
            onClick = {
                val shareText = buildString {
                    appendLine("تقرير تحليل البشرة - Jenin Care")
                    appendLine("نتيجة الصحة العامة: ${report.scan.overallScore}/100")
                    appendLine()
                    report.tips.forEach { tip -> appendLine("- $tip") }
                }
                val intent = Intent(Intent.ACTION_SEND).apply {
                    type = "text/plain"
                    putExtra(Intent.EXTRA_TEXT, shareText)
                    putExtra(Intent.EXTRA_SUBJECT, "تقرير تحليل البشرة")
                }
                context.startActivity(Intent.createChooser(intent, "مشاركة التقرير"))
            },
            modifier = Modifier.weight(1f),
            colors = ButtonDefaults.buttonColors(
                containerColor = RoseGold,
                contentColor = Color.White
            ),
            shape = RoundedCornerShape(12.dp)
        ) {
            Icon(Icons.Default.Share, contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(modifier = Modifier.width(6.dp))
            Text("مشاركة", style = ArabicBodyMedium)
        }

        Button(
            onClick = {
                isGeneratingPdf = true
                scope.launch {
                    PdfReportGenerator.generate(context, report).fold(
                        onSuccess = { fileName ->
                            Toast.makeText(context, "تم حفظ التقرير: $fileName", Toast.LENGTH_LONG).show()
                        },
                        onFailure = { error ->
                            Toast.makeText(context, "فشل إنشاء PDF: ${error.message}", Toast.LENGTH_SHORT).show()
                        }
                    )
                    isGeneratingPdf = false
                }
            },
            enabled = !isGeneratingPdf,
            modifier = Modifier.weight(1f),
            colors = ButtonDefaults.buttonColors(
                containerColor = TealAccent.copy(alpha = 0.15f),
                contentColor = TealAccent,
                disabledContainerColor = TealAccent.copy(alpha = 0.05f),
                disabledContentColor = TealAccent.copy(alpha = 0.5f)
            ),
            shape = RoundedCornerShape(12.dp)
        ) {
            Icon(Icons.Default.Description, contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(modifier = Modifier.width(6.dp))
            Text(if (isGeneratingPdf) "جاري..." else "PDF", style = ArabicBodyMedium)
        }

        Button(
            onClick = {
                val baseUrl = BuildConfig.API_BASE_URL.replace("/api/", "/")
                val reportUrl = "${baseUrl}report/${report.scan.id}"
                val qr = generateQrBitmap(reportUrl)
                if (qr != null) {
                    qrBitmap = qr
                    showQrDialog = true
                } else {
                    Toast.makeText(context, "فشل في إنشاء رمز QR", Toast.LENGTH_SHORT).show()
                }
            },
            modifier = Modifier.weight(1f),
            colors = ButtonDefaults.buttonColors(
                containerColor = RoseGold.copy(alpha = 0.15f),
                contentColor = RoseGold
            ),
            shape = RoundedCornerShape(12.dp)
        ) {
            Icon(Icons.Default.QrCodeScanner, contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(modifier = Modifier.width(6.dp))
            Text("QR", style = ArabicBodyMedium)
        }
    }

    if (showQrDialog && qrBitmap != null) {
        AlertDialog(
            onDismissRequest = { showQrDialog = false },
            title = {
                Text("رمز QR للتقرير", style = ArabicTitleSmall)
            },
            text = {
                Image(
                    bitmap = qrBitmap!!.asImageBitmap(),
                    contentDescription = "QR Code",
                    modifier = Modifier.size(250.dp)
                )
            },
            confirmButton = {
                TextButton(onClick = { showQrDialog = false }) {
                    Text("إغلاق")
                }
            }
        )
    }

    Spacer(modifier = Modifier.height(4.dp))

    // Tips section
    if (report.tips.isNotEmpty()) {
        GlassCard(modifier = Modifier.fillMaxWidth()) {
            Column(modifier = Modifier.fillMaxWidth()) {
                Text(
                    text = "نصائح عامة",
                    style = ArabicTitleSmall,
                    color = MaterialTheme.colorScheme.onSurface
                )
                Spacer(modifier = Modifier.height(8.dp))

                report.tips.forEachIndexed { index, tip ->
                    Row(
                        modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
                        verticalAlignment = Alignment.Top
                    ) {
                        Box(
                            modifier = Modifier
                                .size(20.dp)
                                .clip(CircleShape)
                                .background(TealAccent.copy(alpha = 0.2f)),
                            contentAlignment = Alignment.Center
                        ) {
                            Text(
                                text = "${index + 1}",
                                style = ArabicBodySmall,
                                color = TealAccent
                            )
                        }
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(
                            text = tip,
                            style = ArabicBodyMedium,
                            color = MaterialTheme.colorScheme.onSurface,
                            modifier = Modifier.weight(1f)
                        )
                    }
                    if (index < report.tips.lastIndex) {
                        HorizontalDivider(
                            color = MaterialTheme.colorScheme.surfaceVariant,
                            thickness = 0.5.dp,
                            modifier = Modifier.padding(vertical = 2.dp)
                        )
                    }
                }
            }
        }
    }

    // Defects section
    if (report.defects.isNotEmpty()) {
        Text(
            text = "عيوب البشرة",
            style = ArabicTitleLarge,
            color = MaterialTheme.colorScheme.onSurface,
            modifier = Modifier.padding(top = 4.dp)
        )

        report.defects.forEach { defect ->
            DefectCard(
                defect = defect,
                onAddToCart = { productId ->
                    onAddToCart(productId)
                }
            )
        }
    }
}

@Composable
private fun LegendDot(color: Color, label: String) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Box(
            modifier = Modifier
                .size(8.dp)
                .clip(CircleShape)
                .background(color)
        )
        Spacer(modifier = Modifier.width(4.dp))
        Text(
            text = label,
            style = ArabicBodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
    }
}

@Composable
private fun AdvancedMetricsSection(report: ScanReport) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "المؤشرات المتقدمة",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))
            report.advancedMetrics.forEach { (key, value) ->
                val label = when (key) {
                    "brightness" -> "الإشراق"
                    "texture" -> "الملمس"
                    "redness" -> "الإحمرار"
                    "sensitivity" -> "الحساسية"
                    "oiliness" -> "الدهون"
                    else -> key
                }
                Row(
                    modifier = Modifier.fillMaxWidth().padding(vertical = 2.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = label,
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.weight(1f)
                    )
                    Text(
                        text = "$value%",
                        style = MetricValueStyle,
                        color = when {
                            value >= 70 -> HealthExcellent
                            value >= 50 -> HealthGood
                            else -> HealthFair
                        }
                    )
                }
                if (key != report.advancedMetrics.keys.last()) {
                    HorizontalDivider(color = MaterialTheme.colorScheme.surfaceVariant, thickness = 0.5.dp)
                }
            }
        }
    }
}

@Composable
private fun SpectralAnalysisSection(report: ScanReport) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "تحليل الأطياف",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))
            report.spectralAnalysis.forEach { entry ->
                Row(
                    modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Column(modifier = Modifier.weight(1f)) {
                        Text(
                            text = entry.labelAr.ifEmpty { entry.label },
                            style = com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        if (entry.analysisFocus.isNotEmpty()) {
                            Text(
                                text = entry.analysisFocus,
                                style = ArabicBodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    }
                    Text(
                        text = "${entry.score}%",
                        style = MetricValueStyle,
                        color = when {
                            entry.score >= 70 -> HealthExcellent
                            entry.score >= 50 -> HealthGood
                            else -> HealthFair
                        }
                    )
                }
                if (entry != report.spectralAnalysis.last()) {
                    HorizontalDivider(color = MaterialTheme.colorScheme.surfaceVariant, thickness = 0.5.dp)
                }
            }
        }
    }
}

@Composable
private fun FacialZoneSection(report: ScanReport) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "تحليل مناطق الوجه",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))
            report.facialZoneAnalysis.forEach { zone ->
                Row(
                    modifier = Modifier.fillMaxWidth().padding(vertical = 3.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = zone.nameAr.ifEmpty { zone.name },
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.weight(1f)
                    )
                    Text(
                        text = "${zone.severity}%",
                        style = MetricValueStyle,
                        color = when {
                            zone.severity >= 70 -> HealthFair
                            zone.severity >= 50 -> HealthGood
                            else -> HealthExcellent
                        }
                    )
                }
            }
        }
    }
}

@Composable
private fun ArabicAnalysisSection(report: ScanReport) {
    if (report.customArabicAnalysis.isNullOrEmpty()) return
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "تحليل مخصص",
                style = ArabicTitleSmall,
                color = MaterialTheme.colorScheme.onSurface
            )
            Spacer(modifier = Modifier.height(8.dp))
            Text(
                text = report.customArabicAnalysis,
                style = ArabicBodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}

private fun generateQrBitmap(content: String, size: Int = 512): Bitmap? {
    return try {
        val writer = QRCodeWriter()
        val bitMatrix = writer.encode(content, BarcodeFormat.QR_CODE, size, size)
        val width = bitMatrix.width
        val height = bitMatrix.height
        val pixels = IntArray(width * height)
        for (y in 0 until height) {
            for (x in 0 until width) {
                pixels[y * width + x] = if (bitMatrix[x, y]) android.graphics.Color.BLACK else android.graphics.Color.WHITE
            }
        }
        Bitmap.createBitmap(pixels, width, height, Bitmap.Config.RGB_565)
    } catch (e: Exception) {
        null
    }
}
