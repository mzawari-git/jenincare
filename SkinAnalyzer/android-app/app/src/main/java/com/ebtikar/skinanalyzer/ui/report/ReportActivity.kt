package com.ebtikar.skinanalyzer.ui.report

import android.content.Intent
import android.content.pm.ResolveInfo
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Color
import android.graphics.Matrix
import android.graphics.RectF
import com.ebtikar.skinanalyzer.ai.CVUtils
import android.media.ExifInterface
import android.os.Bundle
import timber.log.Timber
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import androidx.activity.enableEdgeToEdge
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.LinearLayoutManager
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityReportBinding
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.HeatmapPoint
import com.ebtikar.skinanalyzer.ui.components.HeatmapOverlayView
import com.google.android.material.card.MaterialCardView
import com.google.android.material.chip.Chip
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.File

@AndroidEntryPoint
class ReportActivity : AppCompatActivity() {

    private lateinit var binding: ActivityReportBinding
    private val viewModel: ReportViewModel by viewModels()
    private lateinit var metricsAdapter: ReportMetricAdapter
    private lateinit var productAdapter: ProductAdapter

    private var decodedImageBitmaps: MutableList<Bitmap> = mutableListOf()

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityReportBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.updatePadding(
                left = systemBars.left,
                top = systemBars.top,
                right = systemBars.right,
                bottom = systemBars.bottom
            )
            insets
        }

        val reportId = intent.getStringExtra("report_id") ?: run {
            finish()
            return
        }

        setupRecyclerViews()
        setupUI()
        observeViewModel()
        viewModel.loadReport(reportId)
    }

    private fun setupRecyclerViews() {
        metricsAdapter = ReportMetricAdapter()
        binding.rvMetrics.apply {
            layoutManager = GridLayoutManager(this@ReportActivity, 4)
            adapter = metricsAdapter
        }

        productAdapter = ProductAdapter()
        binding.rvProducts.apply {
            layoutManager = LinearLayoutManager(this@ReportActivity)
            adapter = productAdapter
        }
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }
        binding.btnShare.setOnClickListener { viewModel.shareReport() }
        binding.btnSave.setOnClickListener { viewModel.saveReport() }
        binding.btnNewAnalysis.setOnClickListener { finish() }
        binding.btnExport.setOnClickListener { showExportDialog() }
    }

    private fun showExportDialog() {
        val options = arrayOf(
            "حفظ PDF على الجهاز",
            "مشاركة PDF عبر Bluetooth",
            "مشاركة PDF عبر التطبيقات",
            "تصدير CSV",
            "تصدير JSON"
        )
        android.app.AlertDialog.Builder(this)
            .setTitle("اختر طريقة التصدير")
            .setItems(options) { _, which ->
                lifecycleScope.launch {
                    when (which) {
                        0 -> {
                            val file = viewModel.savePdf()
                            if (file != null) {
                                android.widget.Toast.makeText(
                                    this@ReportActivity,
                                    "تم حفظ التقرير: ${file.name}",
                                    android.widget.Toast.LENGTH_LONG
                                ).show()
                            } else {
                                android.widget.Toast.makeText(this@ReportActivity, "فشل الحفظ", android.widget.Toast.LENGTH_SHORT).show()
                            }
                        }
                        1 -> {
                            val file = viewModel.getPdfFile()
                            if (file != null) {
                                shareViaBluetooth(file)
                            } else {
                                android.widget.Toast.makeText(this@ReportActivity, "فشل إنشاء التقرير", android.widget.Toast.LENGTH_SHORT).show()
                            }
                        }
                        2 -> {
                            val file = viewModel.getPdfFile()
                            if (file != null) {
                                sharePdf(file)
                            } else {
                                android.widget.Toast.makeText(this@ReportActivity, "فشل إنشاء التقرير", android.widget.Toast.LENGTH_SHORT).show()
                            }
                        }
                        3 -> {
                            val file = viewModel.exportCsv()
                            if (file != null) {
                                shareFile(file, "text/csv")
                            } else {
                                android.widget.Toast.makeText(this@ReportActivity, "فشل التصدير", android.widget.Toast.LENGTH_SHORT).show()
                            }
                        }
                        4 -> {
                            val file = viewModel.exportJson()
                            if (file != null) {
                                shareFile(file, "application/json")
                            } else {
                                android.widget.Toast.makeText(this@ReportActivity, "فشل التصدير", android.widget.Toast.LENGTH_SHORT).show()
                            }
                        }
                    }
                }
            }
            .show()
    }

    private fun sharePdf(file: java.io.File) {
        try {
            val uri = androidx.core.content.FileProvider.getUriForFile(
                this, "${packageName}.fileprovider", file
            )
            val intent = android.content.Intent(android.content.Intent.ACTION_SEND).apply {
                type = "application/pdf"
                putExtra(android.content.Intent.EXTRA_STREAM, uri)
                putExtra(android.content.Intent.EXTRA_SUBJECT, "تقرير تحليل البشرة - DERMA AI")
                putExtra(android.content.Intent.EXTRA_TEXT, "تقرير تحليل البشرة by DERMA AI - Advanced Skin Analysis System")
                addFlags(android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION or android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
            }
            startActivity(android.content.Intent.createChooser(intent, "مشاركة تقرير PDF"))
        } catch (e: Exception) {
            Timber.e(e, "Failed to share PDF")
            android.widget.Toast.makeText(this, "خطأ في المشاركة", android.widget.Toast.LENGTH_SHORT).show()
        }
    }

    private fun shareViaBluetooth(file: java.io.File) {
        try {
            val uri = androidx.core.content.FileProvider.getUriForFile(
                this, "${packageName}.fileprovider", file
            )
            val intent = android.content.Intent(android.content.Intent.ACTION_SEND).apply {
                type = "application/pdf"
                putExtra(android.content.Intent.EXTRA_STREAM, uri)
                putExtra(android.content.Intent.EXTRA_SUBJECT, "DERMA AI Skin Report")
                packageManager.getLaunchIntentForPackage("com.android.bluetooth")?.let {
                    setPackage("com.android.bluetooth")
                }
                addFlags(android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION or android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
            }

            val bluetoothApps = mutableListOf<ResolveInfo>()
            val allHandlers = packageManager.queryIntentActivities(intent, 0)
            for (handler in allHandlers) {
                val pkg = handler.activityInfo.packageName.lowercase()
                if (pkg.contains("bluetooth") || pkg.contains("opp") || pkg.contains("share")) {
                    bluetoothApps.add(handler)
                }
            }

            if (bluetoothApps.isNotEmpty()) {
                val chooserIntent = android.content.Intent.createChooser(intent, "إرسال عبر Bluetooth")
                if (bluetoothApps.size == 1) {
                    chooserIntent.putExtra(
                        android.content.Intent.EXTRA_INITIAL_INTENTS,
                        arrayOf(android.content.Intent(intent).apply {
                            setPackage(bluetoothApps.first().activityInfo.packageName)
                        })
                    )
                }
                startActivity(chooserIntent)
            } else {
                val fallbackIntent = android.content.Intent(android.content.Intent.ACTION_SEND).apply {
                    type = "application/pdf"
                    putExtra(android.content.Intent.EXTRA_STREAM, uri)
                    addFlags(android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION or android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                startActivity(android.content.Intent.createChooser(fallbackIntent, "مشاركة عبر Bluetooth"))
            }
            Timber.i("Bluetooth share initiated for: ${file.name}")
        } catch (e: Exception) {
            Timber.e(e, "Bluetooth share failed")
            android.widget.Toast.makeText(this, "Bluetooth غير متوفر", android.widget.Toast.LENGTH_SHORT).show()
        }
    }

    private fun shareFile(file: java.io.File, mimeType: String) {
        try {
            val uri = androidx.core.content.FileProvider.getUriForFile(
                this, "${packageName}.fileprovider", file
            )
            val intent = android.content.Intent(android.content.Intent.ACTION_SEND).apply {
                type = mimeType
                putExtra(android.content.Intent.EXTRA_STREAM, uri)
                addFlags(android.content.Intent.FLAG_GRANT_READ_URI_PERMISSION or android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
            }
            startActivity(android.content.Intent.createChooser(intent, "مشاركة الملف"))
        } catch (e: Exception) {
            android.widget.Toast.makeText(this, "خطأ في المشاركة", android.widget.Toast.LENGTH_SHORT).show()
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.overallScore.collect { score ->
                        binding.tvOverallScore.text = "%.1f".format(score)
                        binding.chipScoreLabel.text = getScoreLabel(score)
                        binding.gaugeOverallScore.setScore(score)
                    }
                }

                launch {
                    viewModel.metrics.collect { metrics ->
                        metricsAdapter.submitMetrics(metrics)
                        binding.tvMetricCount.text = "${metrics.size}/${com.ebtikar.skinanalyzer.model.SkinMetric.TOTAL_METRICS}"
                    }
                }

                launch {
                    viewModel.aiAnalysisText.collect { text ->
                        binding.tvAiAnalysis.text = text
                    }
                }

                launch {
                    viewModel.skinProfile.collect { profile ->
                        binding.tvSkinType.text = profile.skinTypeAr
                    }
                }

                launch {
                    viewModel.expertTips.collect { tips ->
                        populateTips(tips)
                    }
                }

                launch {
                    viewModel.productRecommendations.collect { products ->
                        productAdapter.submitList(products)
                    }
                }

                launch {
                    viewModel.capturedImages.collect { images ->
                        populateCapturedImages(images)
                    }
                }

                launch {
                    viewModel.heatmapPoints.collect { points ->
                        populateHeatmap(points, viewModel.capturedImages.value)
                    }
                }

                launch {
                    viewModel.radarValues.collect { values ->
                        if (values.isNotEmpty()) {
                            binding.radarChart.setData(values, viewModel.radarLabels.value)
                        }
                    }
                }

                launch {
                    viewModel.topConcerns.collect { concerns ->
                        populateConcernChips(concerns.map { getArabicName(it.type) })
                    }
                }

                launch {
                    viewModel.providerName.collect { name ->
                    }
                }

                launch {
                    viewModel.analysisTime.collect { time ->
                        binding.tvAnalysisTime.text = if (time >= 1000) "${"%.1f".format(time / 1000f)}s" else "${time}ms"
                    }
                }

                launch {
                    viewModel.reportDate.collect { date ->
                        binding.tvReportDate.text = date
                    }
                }
            }
        }
    }

    private fun populateTips(tips: List<String>) {
        binding.containerTips.removeAllViews()
        tips.forEachIndexed { index, tip ->
            val cardView = com.google.android.material.card.MaterialCardView(this).apply {
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply {
                    bottomMargin = resources.getDimensionPixelSize(R.dimen.space_8)
                }
                setCardBackgroundColor(resources.getColor(R.color.surface_card, theme))
                radius = resources.getDimension(R.dimen.corner_lg)
                strokeWidth = 1
                setStrokeColor(resources.getColor(R.color.border_card, theme))
                cardElevation = 0f
            }

            val layout = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                setPadding(36, 24, 36, 24)
                gravity = android.view.Gravity.CENTER_VERTICAL
            }

            val numberView = TextView(this).apply {
                text = "${index + 1}"
                setTextColor(Color.parseColor("#FF00D4FF"))
                textSize = 14f
                setTypeface(typeface, android.graphics.Typeface.BOLD)
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply {
                    marginEnd = resources.getDimensionPixelSize(R.dimen.space_12)
                }
            }

            val tipView = TextView(this).apply {
                text = tip
                setTextColor(resources.getColor(R.color.text_secondary, theme))
                textSize = 12f
                setLineSpacing(0f, 1.5f)
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
            }

            layout.addView(numberView)
            layout.addView(tipView)
            cardView.addView(layout)
            binding.containerTips.addView(cardView)
        }
    }

    private fun populateConcernChips(concerns: List<String>) {
        binding.chipGroupConcerns.removeAllViews()
        concerns.forEach { concern ->
            val chip = Chip(this).apply {
                text = concern
                setTextColor(Color.parseColor("#FFF43F5E"))
                chipBackgroundColor = android.content.res.ColorStateList.valueOf(Color.parseColor("#1AF43F5E"))
                chipCornerRadius = resources.getDimension(R.dimen.corner_md)
                textSize = 12f
                isClickable = false
                isCheckable = false
            }
            binding.chipGroupConcerns.addView(chip)
        }
    }

    private fun populateCapturedImages(images: Map<LightSpectrum, File>) {
        binding.containerCapturedImages.removeAllViews()
        for (bmp in decodedImageBitmaps) {
            if (!bmp.isRecycled) bmp.recycle()
        }
        decodedImageBitmaps.clear()
        if (images.isEmpty()) {
            binding.containerCapturedImages.visibility = android.view.View.GONE
            return
        }
        binding.containerCapturedImages.visibility = android.view.View.VISIBLE

        val spectra = LightSpectrum.CAPTURE_SEQUENCE.filter { images.containsKey(it) }

        lifecycleScope.launch {
            val decodedImages = withContext(Dispatchers.IO) {
                spectra.associateWith { spectrum ->
                    val file = images[spectrum] ?: return@associateWith null
                    try { loadBitmapWithRotation(file) } catch (_: Exception) { null }
                }
            }
            decodedImageBitmaps.addAll(decodedImages.values.filterNotNull())

            val rowSize = 2
            spectra.chunked(rowSize).forEach { rowSpectra ->
                val rowLayout = LinearLayout(this@ReportActivity).apply {
                    layoutParams = LinearLayout.LayoutParams(
                        LinearLayout.LayoutParams.MATCH_PARENT,
                        LinearLayout.LayoutParams.WRAP_CONTENT
                    )
                    orientation = LinearLayout.HORIZONTAL
                }
                for (spectrum in rowSpectra) {
                    val bitmap = decodedImages[spectrum]
                    val card = MaterialCardView(this@ReportActivity).apply {
                        layoutParams = LinearLayout.LayoutParams(
                            0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f
                        ).apply {
                            marginEnd = 6
                            bottomMargin = 6
                        }
                        radius = resources.getDimension(R.dimen.corner_sm)
                        cardElevation = 0f
                        setCardBackgroundColor(resources.getColor(R.color.surface_card, theme))
                        strokeWidth = 1
                        setStrokeColor(resources.getColor(R.color.border_card, theme))
                    }

                    val innerLayout = LinearLayout(this@ReportActivity).apply {
                        orientation = LinearLayout.VERTICAL
                        setPadding(4, 4, 4, 4)
                    }

                    if (bitmap != null) {
                        val iv = ZoomableImageView(this@ReportActivity).apply {
                            layoutParams = LinearLayout.LayoutParams(
                                LinearLayout.LayoutParams.MATCH_PARENT,
                                280.dpToPx()
                            )
                            setImageBitmap(bitmap)
                        }
                        innerLayout.addView(iv)
                    }

                    val label = TextView(this@ReportActivity).apply {
                        text = spectrum.displayName
                        textSize = 9f
                        setTextColor(resources.getColor(R.color.text_muted, theme))
                        gravity = android.view.Gravity.CENTER_HORIZONTAL
                        layoutParams = LinearLayout.LayoutParams(
                            LinearLayout.LayoutParams.MATCH_PARENT,
                            LinearLayout.LayoutParams.WRAP_CONTENT
                        )
                    }
                    innerLayout.addView(label)
                    card.addView(innerLayout)
                    rowLayout.addView(card)
                }
                binding.containerCapturedImages.addView(rowLayout)
            }
        }
    }

    private fun populateHeatmap(points: List<HeatmapPoint>, images: Map<LightSpectrum, File>) {
        if (points.isEmpty()) {
            binding.tvHeatmapTitle.visibility = android.view.View.GONE
            binding.containerHeatmap.visibility = android.view.View.GONE
            return
        }
        binding.tvHeatmapTitle.visibility = android.view.View.VISIBLE
        binding.containerHeatmap.visibility = android.view.View.VISIBLE

        val whiteImage = images[LightSpectrum.WHITE] ?: images[LightSpectrum.POL_P] ?: return

        lifecycleScope.launch {
            val bitmap = withContext(Dispatchers.IO) {
                try { loadBitmapWithRotation(whiteImage) } catch (_: Exception) { null }
            }
            if (bitmap == null || bitmap.isRecycled) return@launch

            binding.containerHeatmap.removeAllViews()

            val imageView = android.widget.ImageView(this@ReportActivity).apply {
                layoutParams = android.widget.FrameLayout.LayoutParams(
                    android.widget.FrameLayout.LayoutParams.MATCH_PARENT,
                    android.widget.FrameLayout.LayoutParams.WRAP_CONTENT
                )
                adjustViewBounds = true
                setImageBitmap(bitmap)
            }

            val heatmapView = HeatmapOverlayView(this@ReportActivity).apply {
                layoutParams = android.widget.FrameLayout.LayoutParams(
                    android.widget.FrameLayout.LayoutParams.MATCH_PARENT,
                    android.widget.FrameLayout.LayoutParams.MATCH_PARENT
                )
            }

            val frame = android.widget.FrameLayout(this@ReportActivity).apply {
                layoutParams = android.widget.LinearLayout.LayoutParams(
                    android.widget.LinearLayout.LayoutParams.MATCH_PARENT,
                    android.widget.LinearLayout.LayoutParams.WRAP_CONTENT
                )
                addView(imageView)
                addView(heatmapView)
            }

            binding.containerHeatmap.addView(frame)

            frame.post {
                val imgRect = RectF(
                    imageView.left.toFloat(),
                    imageView.top.toFloat(),
                    imageView.right.toFloat(),
                    imageView.bottom.toFloat()
                )
                heatmapView.setHeatmapData(points, imgRect)
            }
        }
    }

    private fun loadBitmapWithRotation(file: File): Bitmap? {
        val bitmap = CVUtils.decodeSampled(file, 640) ?: return null
        if (bitmap.isRecycled) { Timber.w("loadBitmapWithRotation: decoded bitmap already recycled"); return null }
        Timber.d("loadBitmapWithRotation: decoded ${bitmap.width}x${bitmap.height} from ${file.name}")
        try {
            val exif = ExifInterface(file.absolutePath)
            val orientation = exif.getAttributeInt(
                ExifInterface.TAG_ORIENTATION,
                ExifInterface.ORIENTATION_NORMAL
            )
            val degree = when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> 90f
                ExifInterface.ORIENTATION_ROTATE_180 -> 180f
                ExifInterface.ORIENTATION_ROTATE_270 -> 270f
                else -> 0f
            }
            if (degree != 0f) {
                val matrix = Matrix().apply { postRotate(degree) }
                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                if (rotated !== bitmap) bitmap.recycle()
                return rotated
            }
        } catch (_: Exception) { }
        return bitmap
    }

    override fun onDestroy() {
        super.onDestroy()
        for (bmp in decodedImageBitmaps) {
            if (!bmp.isRecycled) bmp.recycle()
        }
        decodedImageBitmaps.clear()
    }

    private fun Int.dpToPx(): Int =
        (this * resources.displayMetrics.density).toInt()

    private fun getScoreLabel(score: Float): String {
        return when {
            score >= 72f -> getString(R.string.score_excellent)
            score >= 55f -> getString(R.string.score_good)
            score >= 35f -> getString(R.string.score_fair)
            score >= 20f -> getString(R.string.score_poor)
            else -> getString(R.string.score_critical)
        }
    }

    private fun getArabicName(type: com.ebtikar.skinanalyzer.model.SkinMetric.Type): String = when (type) {
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.MOISTURE -> "الرطوبة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.PORES -> "المسام"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SEBUM -> "الدهنية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.WRINKLES -> "التجاعيد"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.TEXTURE -> "الملمس"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.PIGMENTATION -> "التصبغ"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.ACNE -> "حب الشباب"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.SENSITIVITY -> "الحساسية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.ROSACEA -> "الوردية"
        com.ebtikar.skinanalyzer.model.SkinMetric.Type.MELASMA -> "الكلف"
    }
}
