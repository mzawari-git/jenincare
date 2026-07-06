package com.ebtikar.skinanalyzer.ui.webanalysis

import android.content.Intent
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Matrix
import android.media.ExifInterface
import android.os.Bundle
import android.view.View
import android.webkit.JavascriptInterface
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.camera.core.CameraSelector
import androidx.camera.core.ImageCapture
import androidx.camera.core.ImageCaptureException
import androidx.camera.core.Preview
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.lifecycleScope
import com.ebtikar.skinanalyzer.databinding.ActivityWebAnalysisBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinProfile
import com.ebtikar.skinanalyzer.model.SkinZone
import com.ebtikar.skinanalyzer.ui.report.ReportActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.jsonArray
import kotlinx.serialization.json.jsonObject
import kotlinx.serialization.json.jsonPrimitive
import timber.log.Timber
import java.io.File
import java.io.FileOutputStream
import java.util.Base64
import java.util.UUID
import java.util.concurrent.ExecutorService
import java.util.concurrent.Executors
import javax.inject.Inject
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository

@AndroidEntryPoint
class WebAnalysisActivity : AppCompatActivity() {

    private lateinit var binding: ActivityWebAnalysisBinding

    @Inject
    lateinit var repository: SkinAnalysisRepository

    private var cameraProvider: ProcessCameraProvider? = null
    private var imageCapture: ImageCapture? = null
    private var webViewReady = false
    private var capturedPhotoFile: File? = null
    private val json = Json { ignoreUnknownKeys = true; coerceInputValues = true }
    private val cameraExecutor: ExecutorService = Executors.newSingleThreadExecutor()

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityWebAnalysisBinding.inflate(layoutInflater)
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

        binding.btnBack.setOnClickListener { finish() }
        binding.btnCapturePhoto.setOnClickListener { capturePhoto() }
        binding.loadingIndicator.visibility = View.GONE

        setupWebView()
        startCamera()
    }

    override fun onDestroy() {
        super.onDestroy()
        cameraProvider?.unbindAll()
        cameraExecutor.shutdown()
        capturedPhotoFile?.delete()
    }

    private fun setupWebView() {
        val webView = binding.webviewAnalysis
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            allowFileAccess = true
            allowFileAccessFromFileURLs = true
            allowUniversalAccessFromFileURLs = true
            mediaPlaybackRequiresUserGesture = false
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = false
            setSupportZoom(false)
            allowContentAccess = true
            mixedContentMode = android.webkit.WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }

        webView.setLayerType(View.LAYER_TYPE_HARDWARE, null)

        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                binding.loadingIndicator.visibility = View.GONE
            }
        }

        webView.addJavascriptInterface(WebAppInterface(), "AndroidApp")
        webView.loadUrl("file:///android_asset/skin_analysis_web.html")
    }

    private fun startCamera() {
        val cameraProviderFuture = ProcessCameraProvider.getInstance(this)
        cameraProviderFuture.addListener({
            try {
                cameraProvider = cameraProviderFuture.get()

                val preview = Preview.Builder()
                    .build()
                    .also { it.setSurfaceProvider(binding.cameraPreview.surfaceProvider) }

                imageCapture = ImageCapture.Builder()
                    .setCaptureMode(ImageCapture.CAPTURE_MODE_MINIMIZE_LATENCY)
                    .setTargetRotation(binding.cameraPreview.display?.rotation ?: 0)
                    .build()

                val cameraSelector = CameraSelector.DEFAULT_BACK_CAMERA

                cameraProvider?.unbindAll()
                cameraProvider?.bindToLifecycle(
                    this,
                    cameraSelector,
                    preview,
                    imageCapture
                )

                Timber.i("Camera preview started for web analysis")
            } catch (e: Exception) {
                Timber.e(e, "Failed to start camera")
                Toast.makeText(this, "فشل تشغيل الكاميرا", Toast.LENGTH_LONG).show()
            }
        }, ContextCompat.getMainExecutor(this))
    }

    private fun capturePhoto() {
        val imageCapture = imageCapture ?: return
        binding.btnCapturePhoto.isEnabled = false
        binding.tvCameraInstruction.visibility = View.GONE
        binding.loadingIndicator.visibility = View.VISIBLE

        val photoFile = File(cacheDir, "web_capture_${System.currentTimeMillis()}.jpg")
        capturedPhotoFile = photoFile

        val outputOptions = ImageCapture.OutputFileOptions.Builder(photoFile).build()

        imageCapture.takePicture(
            outputOptions,
            cameraExecutor,
            object : ImageCapture.OnImageSavedCallback {
                override fun onImageSaved(output: ImageCapture.OutputFileResults) {
                    runOnUiThread {
                        binding.loadingIndicator.visibility = View.GONE
                        onPhotoCaptured(photoFile)
                    }
                }

                override fun onError(exc: ImageCaptureException) {
                    Timber.e(exc, "Photo capture failed")
                    runOnUiThread {
                        binding.loadingIndicator.visibility = View.GONE
                        binding.btnCapturePhoto.isEnabled = true
                        binding.tvCameraInstruction.visibility = View.VISIBLE
                        Toast.makeText(this@WebAnalysisActivity, "فشل التقاط الصورة", Toast.LENGTH_SHORT).show()
                    }
                }
            }
        )
    }

    private fun onPhotoCaptured(photoFile: File) {
        val bitmap = BitmapFactory.decodeFile(photoFile.absolutePath) ?: return

        val rotation = try {
            val exif = ExifInterface(photoFile.absolutePath)
            val orient = exif.getAttributeInt(ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL)
            when (orient) {
                ExifInterface.ORIENTATION_ROTATE_90 -> 90f
                ExifInterface.ORIENTATION_ROTATE_180 -> 180f
                ExifInterface.ORIENTATION_ROTATE_270 -> 270f
                else -> 0f
            }
        } catch (_: Exception) { 0f }

        val oriented = if (rotation != 0f) {
            val m = Matrix().apply { postRotate(rotation) }
            Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, m, true)
        } else bitmap

        val resized = Bitmap.createScaledBitmap(oriented, 1280, 960, true)
        if (oriented !== resized && oriented !== bitmap) oriented.recycle()
        if (bitmap !== resized) bitmap.recycle()

        val tempFile = File(cacheDir, "web_analysis_input.jpg")
        FileOutputStream(tempFile).use { out ->
            resized.compress(Bitmap.CompressFormat.JPEG, 90, out)
        }
        resized.recycle()

        val bytes = tempFile.readBytes()
        val base64 = Base64.getEncoder().encodeToString(bytes)
        tempFile.delete()

        binding.cameraPreview.visibility = View.GONE
        binding.btnCapturePhoto.visibility = View.GONE
        binding.tvCameraInstruction.visibility = View.GONE

        binding.webviewAnalysis.visibility = View.VISIBLE
        binding.loadingIndicator.visibility = View.VISIBLE

        binding.webviewAnalysis.evaluateJavascript(
            "window.receiveNativeImage('data:image/jpeg;base64,$base64')",
            null
        )
    }

    inner class WebAppInterface {

        @JavascriptInterface
        fun onWebViewReady() {
            webViewReady = true
            Timber.i("WebView analysis page ready")
        }

        @JavascriptInterface
        fun onAnalysisDone() {
            binding.loadingIndicator.visibility = View.GONE
            Timber.i("Web analysis completed")
        }

        @JavascriptInterface
        fun receiveSkinAnalysisData(resultJson: String) {
            Timber.i("Received web analysis data: ${resultJson.take(100)}...")
            lifecycleScope.launch {
                try {
                    val reportId = withContext(Dispatchers.IO) {
                        saveReportFromJson(resultJson)
                    }
                    withContext(Dispatchers.Main) {
                        val intent = Intent(this@WebAnalysisActivity, ReportActivity::class.java).apply {
                            putExtra("report_id", reportId)
                        }
                        startActivity(intent)
                    }
                } catch (e: Exception) {
                    Timber.e(e, "Failed to process web analysis results")
                }
            }
        }
    }

    private suspend fun saveReportFromJson(jsonString: String): String {
        val obj = json.parseToJsonElement(jsonString).jsonObject

        val overallScore = obj["overallScore"]?.jsonPrimitive?.content?.toFloatOrNull() ?: 0f
        val metricsArray = obj["metrics"]?.jsonArray ?: JsonArray(emptyList())
        val profileObj = obj["skinProfile"]?.jsonObject
        val aiText = obj["aiAnalysisText"]?.jsonPrimitive?.content ?: ""
        val tipsArray = obj["expertTips"]?.jsonArray ?: JsonArray(emptyList())
        val productsArray = obj["products"]?.jsonArray ?: JsonArray(emptyList())

        val metrics = metricsArray.map { parseMetric(it.jsonObject) }
        val tips = tipsArray.map { it.jsonPrimitive.content }
        val products = productsArray.map { parseProduct(it.jsonObject) }

        val profile = if (profileObj != null) {
            parseProfile(profileObj)
        } else {
            SkinProfile()
        }

        val report = com.ebtikar.skinanalyzer.model.SkinAnalysisReport(
            id = UUID.randomUUID().toString(),
            timestamp = System.currentTimeMillis(),
            providerName = "WebAnalysis",
            overallScore = overallScore,
            metrics = metrics,
            skinProfile = profile,
            aiAnalysisTextAr = aiText,
            expertTipsAr = tips,
            productRecommendations = products,
            deviceModel = "ZMLH02",
            executionTimeMs = 0L
        )

        val result = repository.saveReport(report)
        return result.getOrThrow()
    }

    private fun parseMetric(obj: JsonObject): SkinMetric {
        val typeName = obj["type"]?.jsonPrimitive?.content ?: "MOISTURE"
        val type = try { SkinMetric.Type.valueOf(typeName) } catch (_: Exception) { SkinMetric.Type.MOISTURE }
        val score = obj["score"]?.jsonPrimitive?.content?.toFloatOrNull() ?: 50f
        val severityName = obj["severity"]?.jsonPrimitive?.content ?: "FAIR"
        val severity = try { MetricSeverity.valueOf(severityName) } catch (_: Exception) { MetricSeverity.FAIR }
        val zoneName = obj["zone"]?.jsonPrimitive?.content ?: "FULL_FACE"
        val zone = try { SkinZone.valueOf(zoneName) } catch (_: Exception) { SkinZone.FULL_FACE }
        val details = obj["details"]?.jsonPrimitive?.content ?: ""
        val confidence = obj["confidence"]?.jsonPrimitive?.content?.toFloatOrNull() ?: 0.8f

        return SkinMetric(
            type = type,
            score = score,
            severity = severity,
            zone = zone,
            details = details,
            confidence = confidence
        )
    }

    private fun parseProfile(obj: JsonObject): SkinProfile {
        val skinType = obj["skinType"]?.jsonPrimitive?.content ?: "mixed"
        val skinTypeAr = obj["skinTypeAr"]?.jsonPrimitive?.content ?: "مختلطة"
        val hydration = obj["hydrationLevel"]?.jsonPrimitive?.content ?: "moderate"
        val sensitivity = obj["sensitivityLevel"]?.jsonPrimitive?.content ?: "low"
        val concerns = obj["primaryConcerns"]?.jsonArray?.map { it.jsonPrimitive.content } ?: emptyList()
        val concernsAr = obj["primaryConcernsAr"]?.jsonArray?.map { it.jsonPrimitive.content } ?: emptyList()

        return SkinProfile(
            skinType = skinType,
            skinTypeAr = skinTypeAr,
            hydrationLevel = hydration,
            sensitivityLevel = sensitivity,
            primaryConcerns = concerns,
            primaryConcernsAr = concernsAr
        )
    }

    private fun parseProduct(obj: JsonObject): com.ebtikar.skinanalyzer.model.ProductRecommendation {
        return com.ebtikar.skinanalyzer.model.ProductRecommendation(
            name = obj["name"]?.jsonPrimitive?.content ?: "",
            nameAr = obj["nameAr"]?.jsonPrimitive?.content ?: "",
            brand = obj["brand"]?.jsonPrimitive?.content ?: "",
            category = obj["category"]?.jsonPrimitive?.content ?: "",
            price = obj["price"]?.jsonPrimitive?.content?.toFloatOrNull() ?: 0f
        )
    }
}
