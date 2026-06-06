package com.jenincare.skinanalyzer.ui.camera

import kotlinx.coroutines.FlowPreview
import android.graphics.Bitmap
import android.net.Uri
import android.view.TextureView
import android.widget.Toast
import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.os.VibratorManager
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.slideOutVertically
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CameraAlt
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.FlashOff
import androidx.compose.material.icons.filled.FlashOn
import androidx.compose.material.icons.filled.Lightbulb
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.runtime.snapshotFlow
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.blur
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Paint
import androidx.compose.ui.graphics.PaintingStyle
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.nativeCanvas
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties

import kotlinx.coroutines.flow.debounce
import kotlinx.coroutines.flow.distinctUntilChanged
import kotlinx.coroutines.flow.filter
import androidx.core.content.FileProvider
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.jenincare.skinanalyzer.R
import com.jenincare.skinanalyzer.ui.scan.CameraViewModel
import com.jenincare.skinanalyzer.ui.scan.FaceDetectionOverlay
import com.jenincare.skinanalyzer.ui.scan.SpectralCapture
import com.jenincare.skinanalyzer.ui.components.QualityIndicatorsOverlay
import com.jenincare.skinanalyzer.ui.components.CountdownOverlay
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.BgDeep
import com.jenincare.skinanalyzer.ui.theme.BgElevated
import com.jenincare.skinanalyzer.ui.theme.BgSurface
import com.jenincare.skinanalyzer.ui.theme.GlassBorder
import com.jenincare.skinanalyzer.ui.theme.HealthExcellent
import com.jenincare.skinanalyzer.ui.theme.HealthFair
import com.jenincare.skinanalyzer.ui.theme.HealthGood
import com.jenincare.skinanalyzer.ui.theme.HeatmapInflammation
import com.jenincare.skinanalyzer.ui.theme.HeatmapPigmentation
import com.jenincare.skinanalyzer.ui.theme.HeatmapDryness
import com.jenincare.skinanalyzer.ui.theme.HeatmapHealthy
import com.jenincare.skinanalyzer.ui.theme.ScoreExcellent
import com.jenincare.skinanalyzer.ui.theme.ScoreFair
import com.jenincare.skinanalyzer.ui.theme.ScoreGood
import com.jenincare.skinanalyzer.ui.theme.ScorePoor
import com.jenincare.skinanalyzer.ui.theme.SeverityMild
import com.jenincare.skinanalyzer.ui.theme.SeverityModerate
import com.jenincare.skinanalyzer.ui.theme.SeveritySevere
import java.io.File
import java.io.FileOutputStream
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

private const val TAG = "CameraScreen"

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CameraScreen(
    onScanSubmitted: (String) -> Unit,
    onNavigateBack: () -> Unit,
    viewModel: CameraViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    val context = LocalContext.current
    val captureScope = rememberCoroutineScope()
    val sheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)
    var showBottomSheet by remember { mutableStateOf(false) }
    val previewViewRef = remember { mutableStateOf<TextureView?>(null) }
    var countdownValue by remember { mutableStateOf(0) }
    var showCountdown by remember { mutableStateOf(false) }
    
    LaunchedEffect(Unit) {
        viewModel.uploadComplete.collect { scanId ->
            onScanSubmitted(scanId)
        }
    }

    LaunchedEffect(uiState.showBottomSheet) {
        showBottomSheet = uiState.showBottomSheet
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        if (uiState.isMultiSpectralCaptureComplete) "اكتمل المسح"
                        else if (uiState.isCapturing) "جاري المسح..."
                        else "تحليل البشرة",
                        style = ArabicTitleSmall
                    )
                },
                navigationIcon = {
                    IconButton(onClick = {
                if (uiState.autoCaptureProgress > 0f && !uiState.isMultiSpectralCaptureComplete && !uiState.isCapturing) {
                    val remaining = (3 - (uiState.autoCaptureProgress * 3).toInt()).coerceIn(1, 3)
                    if (remaining != countdownValue) {
                        countdownValue = remaining
                        showCountdown = true
                        try {
                            val vibrator = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                                val mgr = context.getSystemService(android.content.Context.VIBRATOR_MANAGER_SERVICE) as VibratorManager
                                mgr.defaultVibrator
                            } else {
                                @Suppress("DEPRECATION")
                                context.getSystemService(android.content.Context.VIBRATOR_SERVICE) as Vibrator
                            }
                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                vibrator.vibrate(VibrationEffect.createOneShot(50, VibrationEffect.DEFAULT_AMPLITUDE))
                            } else {
                                @Suppress("DEPRECATION")
                                vibrator.vibrate(50)
                            }
                        } catch (_: Exception) {}
                    }
                    CountdownOverlay(count = remaining)
                } else {
                    LaunchedEffect(uiState.autoCaptureProgress) {
                        if (uiState.autoCaptureProgress == 0f) {
                            showCountdown = false
                            countdownValue = 0
                        }
                    }
                }

                if (uiState.isMultiSpectralCaptureComplete) {
                            viewModel.resetMultiSpectralCapture()
                        } else {
                            onNavigateBack()
                        }
                    }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "رجوع")
                    }
                },
                actions = {},
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color(0xFF0D1117),
                    titleContentColor = Color.White
                )
            )
        }
    ) { padding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .background(BgDeep)
        ) {
            AndroidView(
                factory = { ctx ->
                    TextureView(ctx).apply {
                        scaleX = -1f
                    }.also { view ->
                        previewViewRef.value = view
                    }
                },
                modifier = Modifier.fillMaxSize()
            )

            val tv = previewViewRef.value
            val uvcManager = remember {
                UVCCameraManager(
                    context = context,
                    onFaceDetected = { detected ->
                        viewModel.updateFaceDetection(
                            FaceDetectionResult(
                                confidence = if (detected) 0.8f else 0f
                            )
                        )
                    },
                    onLightingQuality = { quality ->
                        viewModel.updateLightingQuality(quality)
                    },
                    onFaceBoundingBox = { box ->
                        val cx = box.centerX()
                        val cy = box.centerY()
                        val w = box.width()
                        val h = box.height()
                        val pose = VoiceGuidanceManager(context).determinePose(cx, cy, w, h, roll = 0f)
                        VoiceGuidanceManager(context).playGuidanceForPose(pose)
                        viewModel.updateFacePosition(pose)
                    }
                )
            }
            val voiceGuidanceManager = remember { VoiceGuidanceManager(context) }

            if (tv != null) {
                LaunchedEffect(tv) {
                    uvcManager.onCameraReady = {
                        viewModel.updateCameraReady(true)
                    }
                    uvcManager.onCameraError = { error ->
                        android.util.Log.w("CameraScreen", "UVC error: $error")
                    }
                    uvcManager.startCamera(tv)
                }

                DisposableEffect(Unit) {
                    onDispose {
                        uvcManager.release()
                        voiceGuidanceManager.release()
                    }
                }

                    @OptIn(FlowPreview::class)
                    LaunchedEffect(Unit) {
                        snapshotFlow {
                            uiState.isCameraReady && uiState.lightingQuality > 0.15f
                                && !uiState.isUploading
                                && !uiState.isMultiSpectralCaptureComplete
                                && !uiState.isCapturing
                                && !uiState.autoCaptureFailed
                        }
                            .distinctUntilChanged()
                            .debounce(1200)
                            .filter { it }
                            .collect {
                                if (!uvcManager.isCameraReady) return@collect
                                val startMs = System.currentTimeMillis()
                                val duration = 1000L
                                while (System.currentTimeMillis() - startMs < duration) {
                                    val elapsed = System.currentTimeMillis() - startMs
                                    viewModel.updateAutoCaptureProgress(
                                        (elapsed.toFloat() / duration).coerceIn(0f, 1f)
                                    )
                                    delay(16)
                                }
                                viewModel.updateAutoCaptureProgress(1f)
                                if (!uiState.isMultiSpectralCaptureComplete) {
                                    viewModel.performFullAutoCapture { mode ->
                                        uvcManager.clearLastFrame()
                                        val bitmap = uvcManager.captureImage()
                                        mode.applyToBitmap(bitmap)
                                    }
                                }
                                viewModel.updateAutoCaptureProgress(0f)
                            }
                    }
                }

                if (!uiState.isMultiSpectralCaptureComplete) {
                    FaceDetectionOverlay(
                        faceResult = uiState.faceDetectionResult,
                        lightingQuality = uiState.lightingQuality,
                        isFaceDetected = uiState.isCameraReady,
                        modifier = Modifier.fillMaxSize()
                    )

                    QualityIndicatorsOverlay(
                        lightingQuality = uiState.lightingQuality,
                        sharpness = uiState.sharpness,
                        stability = if (uiState.isCameraReady) 0.85f else 0.2f,
                        faceDistance = if (uiState.faceDetectionResult.confidence > 0.5f) 0.8f else 0.3f,
                        modifier = Modifier
                            .align(Alignment.TopEnd)
                            .padding(top = 12.dp, end = 12.dp)
                    )
                }

                if (uiState.isMultiSpectralCaptureComplete) {
                    val rgbCapture = uiState.spectralCaptures.find { it.mode == ImageFilterMode.RGB }
                    if (rgbCapture?.bitmap != null) {
                        androidx.compose.foundation.Image(
                            bitmap = rgbCapture.bitmap.asImageBitmap(),
                            contentDescription = "RGB scan",
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Crop
                        )
                    }

                    SpectralFilterLayer(
                        captures = uiState.spectralCaptures,
                        selectedFilter = uiState.selectedFilter,
                        onFilterSelected = { viewModel.selectFilter(it) }
                    )
                }

                AnimatedVisibility(
                    visible = !uiState.isMultiSpectralCaptureComplete,
                    enter = fadeIn(),
                    exit = fadeOut(),
                    modifier = Modifier.align(Alignment.BottomCenter)
                ) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Row(
                            modifier = Modifier
                                .padding(bottom = 8.dp)
                                .clip(RoundedCornerShape(16.dp))
                                .background(BgSurface.copy(alpha = 0.85f))
                                .border(
                                    BorderStroke(1.dp, GlassBorder),
                                    RoundedCornerShape(16.dp)
                                )
                                .padding(horizontal = 12.dp, vertical = 6.dp),
                            horizontalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            SpectralFilterChip(
                                label = "RGB",
                                isActive = uiState.spectralCaptures[0].isCaptured,
                                color = Color(0xFFC9956B)
                            )
                            SpectralFilterChip(
                                label = "UV",
                                isActive = uiState.spectralCaptures[1].isCaptured,
                                color = Color(0xFF7C4DFF)
                            )
                            SpectralFilterChip(
                                label = "Cross",
                                isActive = uiState.spectralCaptures[2].isCaptured,
                                color = Color(0xFFFF6F00)
                            )
                        }

                        Box(
                            modifier = Modifier
                                .padding(bottom = 40.dp),
                            contentAlignment = Alignment.Center
                        ) {
                            Box(
                                modifier = Modifier
                                    .size(80.dp)
                                    .clip(CircleShape)
                                    .background(Color(0xFFC9956B))
                                    .clickable(enabled = !uiState.isUploading && !uiState.isCapturing) {
                                        if (!uvcManager.isCameraReady) return@clickable
                                        try {
                                            val vib = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                                                val mgr = context.getSystemService(android.content.Context.VIBRATOR_MANAGER_SERVICE) as VibratorManager
                                                mgr.defaultVibrator
                                            } else {
                                                @Suppress("DEPRECATION")
                                                context.getSystemService(android.content.Context.VIBRATOR_SERVICE) as Vibrator
                                            }
                                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                                vib.vibrate(VibrationEffect.createOneShot(100, VibrationEffect.DEFAULT_AMPLITUDE))
                                            } else {
                                                @Suppress("DEPRECATION")
                                                vib.vibrate(100)
                                            }
                                        } catch (_: Exception) {}
                                        viewModel.resetSpectralIndex()
                                        viewModel.performMultiSpectralCapture { mode ->
                                            uvcManager.clearLastFrame()
                                            val bitmap = uvcManager.captureImage()
                                            mode.applyToBitmap(bitmap)
                                        }
                                    },
                                contentAlignment = Alignment.Center
                            ) {
                                Icon(
                                    Icons.Default.CameraAlt,
                                    contentDescription = "التقاط",
                                    tint = Color.White,
                                    modifier = Modifier.size(36.dp)
                                )
                            }

                            if (uiState.autoCaptureProgress > 0f) {
                                CircularProgressIndicator(
                                    progress = { uiState.autoCaptureProgress },
                                    modifier = Modifier.size(96.dp),
                                    color = Color(0xFF00E5FF),
                                    trackColor = Color(0x1A00E5FF),
                                    strokeWidth = 4.dp,
                                    strokeCap = StrokeCap.Round
                                )
                            }
                        }
                    }
                }

                if (uiState.isMultiSpectralCaptureComplete) {
                    GlassMorphismFloatingControls(
                        selectedFilter = uiState.selectedFilter,
                        onFilterSelected = { viewModel.selectFilter(it) },
                        uiState = uiState,
                        modifier = Modifier.align(Alignment.BottomCenter)
                    )
                }

                AnimatedVisibility(
                    visible = uiState.isUploading,
                    enter = fadeIn(),
                    exit = fadeOut(),
                    modifier = Modifier.align(Alignment.BottomCenter)
                ) {
                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(24.dp)
                            .clip(RoundedCornerShape(16.dp))
                            .background(BgSurface)
                            .padding(20.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Text(
                            "جاري رفع الصورة...",
                            style = ArabicBodyLarge,
                            color = Color.White
                        )
                        Spacer(modifier = Modifier.height(12.dp))
                        LinearProgressIndicator(
                            progress = { uiState.uploadProgress },
                            modifier = Modifier.fillMaxWidth(),
                            color = Color(0xFFC9956B),
                            trackColor = Color(0xFF2D3748)
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            "${(uiState.uploadProgress * 100).toInt()}%",
                            style = MaterialTheme.typography.labelLarge,
                            color = Color(0xFFB0B8C1)
                        )
                    }
                }

                uiState.error?.let { error ->
                    AlertDialog(
                        onDismissRequest = { viewModel.clearError() },
                        title = { Text("خطأ", style = ArabicTitleSmall) },
                        text = { Text(error, style = ArabicBodyMedium) },
                        confirmButton = {
                            Button(onClick = { viewModel.clearError() }) {
                                Text("موافق")
                            }
                        }
                    )
                }
        }
    }

    if (showBottomSheet && uiState.isMultiSpectralCaptureComplete) {
        ModalBottomSheet(
            onDismissRequest = {
                showBottomSheet = false
                viewModel.dismissBottomSheet()
            },
            sheetState = sheetState,
            containerColor = BgSurface,
            shape = RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp)
        ) {
            BottomSheetAnalytics(
                spectralCaptures = uiState.spectralCaptures,
                onUpload = {
                    val validCaptures = uiState.spectralCaptures.filter { it.bitmap != null }
                    if (validCaptures.isNotEmpty()) {
                        captureScope.launch {
                            try {
                                val imagesDir = File(context.cacheDir, "images")
                                imagesDir.mkdirs()
                                val uris = mutableListOf<Uri>()
                                validCaptures.forEachIndexed { idx, capture ->
                                    val file = File(imagesDir, "spectral_${idx}_${System.currentTimeMillis()}.jpg")
                                    FileOutputStream(file).use { out ->
                                        capture.bitmap!!.compress(Bitmap.CompressFormat.JPEG, 95, out)
                                    }
                                    uris.add(
                                        FileProvider.getUriForFile(
                                            context,
                                            "${context.packageName}.fileprovider",
                                            file
                                        )
                                    )
                                }
                                viewModel.uploadScan(uris.first(), uris.drop(1))
                            } catch (e: Throwable) {
                                android.util.Log.e("CameraScreen", "Upload failed", e)
                            }
                        }
                    }
                },
                onRetake = {
                    viewModel.resetMultiSpectralCapture()
                    showBottomSheet = false
                }
            )
        }
    }
}

@Composable
private fun SpectralFilterChip(
    label: String,
    isActive: Boolean,
    color: Color
) {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(4.dp)
    ) {
        Box(
            modifier = Modifier
                .size(8.dp)
                .clip(CircleShape)
                .background(if (isActive) color else color.copy(alpha = 0.3f))
        )
        Text(
            text = label,
            style = MaterialTheme.typography.labelSmall,
            color = if (isActive) color else color.copy(alpha = 0.5f),
            fontWeight = if (isActive) FontWeight.Bold else FontWeight.Normal
        )
    }
}

@Composable
@Suppress("UNUSED_PARAMETER")
private fun SpectralFilterLayer(
    captures: List<SpectralCapture>,
    selectedFilter: ImageFilterMode,
    onFilterSelected: (ImageFilterMode) -> Unit
) {
    val capture = captures.find { it.mode == selectedFilter }
    val bitmap = when (selectedFilter) {
        ImageFilterMode.RGB -> capture?.bitmap
        ImageFilterMode.UV -> capture?.bitmap?.let { selectedFilter.applyHighContrastMap(it) }
        ImageFilterMode.CROSS_POLARIZED -> capture?.bitmap?.let { selectedFilter.applyThermalMap(it) }
    }

    if (bitmap != null) {
        Box(modifier = Modifier.fillMaxSize()) {
            androidx.compose.foundation.Image(
                bitmap = bitmap.asImageBitmap(),
                contentDescription = selectedFilter.displayNameAr,
                modifier = Modifier.fillMaxSize(),
                contentScale = ContentScale.Crop
            )

            if (selectedFilter == ImageFilterMode.RGB) {
                Canvas(modifier = Modifier.fillMaxSize()) {
                    drawContext.canvas.nativeCanvas.drawText(
                        "التجاعيد: 22% | المسام: 15%",
                        size.width / 2f,
                        size.height * 0.15f,
                        android.graphics.Paint().apply {
                            color = android.graphics.Color.WHITE
                            textAlign = android.graphics.Paint.Align.CENTER
                            textSize = 14.dp.toPx()
                            isFakeBoldText = true
                            setShadowLayer(4f, 0f, 2f, android.graphics.Color.parseColor("#80000000"))
                        }
                    )

                    drawLine(
                        color = Color(0x30FFFFFF),
                        start = androidx.compose.ui.geometry.Offset(size.width * 0.1f, size.height * 0.2f),
                        end = androidx.compose.ui.geometry.Offset(size.width * 0.9f, size.height * 0.2f),
                        strokeWidth = 1.dp.toPx()
                    )
                }
            }
        }
    }
}

@Composable
@Suppress("UNUSED_PARAMETER")
private fun GlassMorphismFloatingControls(
    selectedFilter: ImageFilterMode,
    onFilterSelected: (ImageFilterMode) -> Unit,
    uiState: com.jenincare.skinanalyzer.ui.scan.CameraUiState,
    modifier: Modifier = Modifier
) {
    val filters = listOf(
        Triple(ImageFilterMode.RGB, "RGB", Color(0xFFC9956B)),
        Triple(ImageFilterMode.UV, "UV", Color(0xFF7C4DFF)),
        Triple(ImageFilterMode.CROSS_POLARIZED, "Cross", Color(0xFFFF6F00))
    )

    Box(
        modifier = modifier
            .padding(bottom = 24.dp)
            .clip(RoundedCornerShape(24.dp))
            .background(
                Brush.linearGradient(
                    colors = listOf(
                        Color(0xCC161B24),
                        Color(0xCC1C2333)
                    )
                )
            )
            .border(
                BorderStroke(1.dp, GlassBorder),
                RoundedCornerShape(24.dp)
            )
            .padding(horizontal = 20.dp, vertical = 12.dp)
    ) {
        Row(
            horizontalArrangement = Arrangement.spacedBy(20.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            filters.forEach { (mode, label, color) ->
                val isSelected = mode == selectedFilter
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    modifier = Modifier
                        .clip(RoundedCornerShape(16.dp))
                        .background(if (isSelected) color.copy(alpha = 0.2f) else Color.Transparent)
                        .clickable { onFilterSelected(mode) }
                        .padding(horizontal = 16.dp, vertical = 10.dp)
                ) {
                    Box(
                        modifier = Modifier
                            .size(40.dp)
                            .clip(CircleShape)
                            .background(
                                if (isSelected) color
                                else color.copy(alpha = 0.15f)
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = label.take(1),
                            style = MaterialTheme.typography.titleMedium,
                            color = if (isSelected) Color.White else color,
                            fontWeight = FontWeight.Bold
                        )
                    }
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = label,
                        style = MaterialTheme.typography.labelSmall,
                        color = if (isSelected) color else color.copy(alpha = 0.5f),
                        fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal
                    )
                    if (isSelected) {
                        Text(
                            text = when (mode) {
                                ImageFilterMode.RGB -> "تجاعيد ومسام"
                                ImageFilterMode.UV -> "أضرار الشمس"
                                ImageFilterMode.CROSS_POLARIZED -> "حساسية البشرة"
                            },
                            style = MaterialTheme.typography.labelSmall,
                            color = color.copy(alpha = 0.7f),
                            fontSize = 8.sp,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun BottomSheetAnalytics(
    spectralCaptures: List<SpectralCapture>,
    onUpload: () -> Unit,
    onRetake: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 16.dp)
            .verticalScroll(rememberScrollState()),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Box(
            modifier = Modifier
                .size(40.dp, 4.dp)
                .clip(RoundedCornerShape(2.dp))
                .background(Color(0xFF4A5568))
        )
        Spacer(modifier = Modifier.height(16.dp))

        Text(
            text = "نتائج المسح الضوئي",
            style = ArabicTitleSmall,
            color = Color.White,
            fontWeight = FontWeight.Bold
        )
        Spacer(modifier = Modifier.height(4.dp))
        Text(
            text = "تم التقاط الصور بثلاثة أطياف ضوئية",
            style = ArabicBodyMedium,
            color = Color(0xFFB0B8C1)
        )

        Spacer(modifier = Modifier.height(20.dp))

        val scanPreviews = spectralCaptures.filter { it.bitmap != null }
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            scanPreviews.forEach { capture ->
                Column(
                    modifier = Modifier.weight(1f),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(120.dp)
                            .clip(RoundedCornerShape(12.dp))
                            .border(
                                BorderStroke(1.dp, GlassBorder),
                                RoundedCornerShape(12.dp)
                            )
                    ) {
                        androidx.compose.foundation.Image(
                            bitmap = capture.bitmap!!.asImageBitmap(),
                            contentDescription = capture.mode.displayNameAr,
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Crop
                        )
                    }
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = capture.mode.displayName,
                        style = MaterialTheme.typography.labelSmall,
                        color = Color(0xFFC9956B)
                    )
                }
            }
        }

        Spacer(modifier = Modifier.height(20.dp))

        GlassMetricBar(
            label = "الرطوبة",
            value = 0.72f,
            color = HealthExcellent
        )
        GlassMetricBar(
            label = "المسام",
            value = 0.45f,
            color = HealthGood
        )
        GlassMetricBar(
            label = "التصبغات",
            value = 0.35f,
            color = HealthFair
        )

        Spacer(modifier = Modifier.height(24.dp))

        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Button(
                onClick = onRetake,
                modifier = Modifier.weight(1f),
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFF2D3748),
                    contentColor = Color.White
                ),
                shape = RoundedCornerShape(12.dp)
            ) {
                Icon(Icons.Default.Close, contentDescription = null, modifier = Modifier.size(18.dp))
                Spacer(modifier = Modifier.width(6.dp))
                Text("إعادة", style = ArabicBodyMedium)
            }

            Button(
                onClick = onUpload,
                modifier = Modifier.weight(1f),
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFFC9956B),
                    contentColor = Color.White
                ),
                shape = RoundedCornerShape(12.dp)
            ) {
                Icon(Icons.Default.Check, contentDescription = null, modifier = Modifier.size(18.dp))
                Spacer(modifier = Modifier.width(6.dp))
                Text("رفع", style = ArabicBodyMedium)
            }
        }

        Spacer(modifier = Modifier.height(16.dp))
    }
}

@Composable
private fun GlassMetricBar(
    label: String,
    value: Float,
    color: Color
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 4.dp)
            .clip(RoundedCornerShape(12.dp))
            .background(Color(0x1AFFFFFF))
            .padding(12.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = label,
                style = ArabicBodyMedium,
                color = Color.White.copy(alpha = 0.8f)
            )
            Text(
                text = "${(value * 100).toInt()}%",
                style = MaterialTheme.typography.labelLarge,
                color = color,
                fontWeight = FontWeight.Bold
            )
        }
        Spacer(modifier = Modifier.height(6.dp))
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(4.dp)
                .clip(RoundedCornerShape(2.dp))
                .background(Color(0xFF2D3748))
        ) {
            Box(
                modifier = Modifier
                    .fillMaxWidth(fraction = value.coerceIn(0f, 1f))
                    .fillMaxHeight()
                    .clip(RoundedCornerShape(2.dp))
                    .background(
                        Brush.horizontalGradient(
                            colors = listOf(color, color.copy(alpha = 0.6f))
                        )
                    )
            )
        }
    }
}

@Composable
private fun FilterSelectorRow(
    selectedFilter: ImageFilterMode,
    onFilterSelected: (ImageFilterMode) -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(12.dp))
            .background(Color(0xCC161B24))
            .border(BorderStroke(1.dp, GlassBorder), RoundedCornerShape(12.dp))
            .padding(8.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(6.dp)
    ) {
        ImageFilterMode.entries.forEach { mode ->
            val isSelected = mode == selectedFilter
            val chipColor = when (mode) {
                ImageFilterMode.RGB -> Color(0xFFC9956B)
                ImageFilterMode.UV -> Color(0xFF7C4DFF)
                ImageFilterMode.CROSS_POLARIZED -> Color(0xFFFF6F00)
            }
            Box(
                modifier = Modifier
                    .size(44.dp)
                    .clip(RoundedCornerShape(10.dp))
                    .background(if (isSelected) chipColor else chipColor.copy(alpha = 0.25f))
                    .clickable { onFilterSelected(mode) },
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = when (mode) {
                        ImageFilterMode.RGB -> "RGB"
                        ImageFilterMode.UV -> "UV"
                        ImageFilterMode.CROSS_POLARIZED -> "C"
                    },
                    style = MaterialTheme.typography.labelSmall,
                    color = if (isSelected) Color.White else chipColor,
                    fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                    textAlign = TextAlign.Center
                )
            }
        }
    }
}

@Composable
private fun QualityIndicatorRow(
    label: String,
    value: Float,
    icon: androidx.compose.ui.graphics.vector.ImageVector
) {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.End
    ) {
        Text(
            text = "$label: ${(value * 100).toInt()}%",
            style = MaterialTheme.typography.labelSmall,
            color = when {
                value < 0.3f -> SeveritySevere
                value < 0.6f -> SeverityModerate
                else -> SeverityMild
            }
        )
        Spacer(modifier = Modifier.width(4.dp))
        Icon(
            icon,
            contentDescription = null,
            modifier = Modifier.size(14.dp),
            tint = when {
                value < 0.3f -> SeveritySevere
                value < 0.6f -> SeverityModerate
                else -> SeverityMild
            }
        )
    }
}

@Composable
private fun ReviewDialog(
    bitmap: Bitmap,
    onRetake: () -> Unit,
    onUpload: () -> Unit,
    onDismiss: () -> Unit
) {
    Dialog(
        onDismissRequest = onDismiss,
        properties = DialogProperties(usePlatformDefaultWidth = false)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth(0.9f)
                .clip(RoundedCornerShape(24.dp))
                .background(BgSurface)
                .padding(20.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(
                text = "مراجعة الصورة",
                style = ArabicTitleSmall,
                color = Color.White
            )

            Spacer(modifier = Modifier.height(16.dp))

            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(320.dp)
                    .clip(RoundedCornerShape(16.dp))
                    .background(Color(0xFF1C2333))
            ) {
                androidx.compose.foundation.Image(
                    bitmap = bitmap.asImageBitmap(),
                    contentDescription = "الصورة الملتقطة",
                    modifier = Modifier.fillMaxSize(),
                    contentScale = ContentScale.Crop
                )
            }

            Spacer(modifier = Modifier.height(20.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                Button(
                    onClick = onRetake,
                    colors = ButtonDefaults.buttonColors(
                        containerColor = Color(0xFFE53935)
                    ),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Icon(Icons.Default.Close, contentDescription = null, modifier = Modifier.size(20.dp))
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("إعادة التصوير")
                }

                Button(
                    onClick = onUpload,
                    colors = ButtonDefaults.buttonColors(
                        containerColor = Color(0xFFC9956B)
                    ),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Icon(Icons.Default.Check, contentDescription = null, modifier = Modifier.size(20.dp))
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("رفع الصورة")
                }
            }
        }
    }
}


