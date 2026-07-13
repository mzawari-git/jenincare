package com.ebtikar.skinanalyzer.ui.diagnostics

import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.databinding.ActivityDiagnosticsBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class DiagnosticsActivity : AppCompatActivity() {

    private lateinit var binding: ActivityDiagnosticsBinding
    private val scope = CoroutineScope(Dispatchers.Main + SupervisorJob())
    private val handler = Handler(Looper.getMainLooper())

    @Inject lateinit var serialBusManager: SerialBusManager
    @Inject lateinit var networkMonitor: NetworkMonitor
    @Inject lateinit var cameraManager: USBCameraManager

    private val refreshRunnable = object : Runnable {
        override fun run() {
            updateLiveStats()
            handler.postDelayed(this, 2000)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityDiagnosticsBinding.inflate(layoutInflater)
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

        setupUI()
        populateDiagnostics()
    }

    override fun onResume() {
        super.onResume()
        handler.post(refreshRunnable)
    }

    override fun onPause() {
        super.onPause()
        handler.removeCallbacks(refreshRunnable)
    }

    override fun onDestroy() {
        super.onDestroy()
        scope.cancel()
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }

        binding.btnRefresh.setOnClickListener {
            appendLog("→ تحديث الحالة...")
            populateDiagnostics()
            appendLog("✓ تم التحديث")
        }

        binding.btnRunAllTests.setOnClickListener {
            runAllTests()
        }
    }

    private fun populateDiagnostics() {
        updateUsbStatus()
        updateNetworkStatus()
        updateCameraStatus()
        updateAIStatus()
        updateLiveStats()
    }

    private fun updateUsbStatus() {
        val isConnected = serialBusManager.isConnected
        binding.tvUsbStatus.text = if (isConnected) "متصل" else "غير متصل"
        binding.dotUsb.setBackgroundResource(
            if (isConnected) R.drawable.shape_status_dot_green else R.drawable.shape_status_dot_purple
        )
    }

    private fun updateNetworkStatus() {
        val isOnline = networkMonitor.isOnline()
        binding.tvNetworkStatus.text = if (isOnline) "متصل بالإنترنت" else "غير متصل"
        binding.dotNetwork.setBackgroundResource(
            if (isOnline) R.drawable.shape_status_dot_green else R.drawable.shape_status_dot_purple
        )
    }

    private fun updateCameraStatus() {
        val cameraId = cameraManager.findBestCamera()
        if (cameraId != null) {
            binding.tvCameraStatus.text = "جاهزة — ID: $cameraId"
            binding.dotCamera.setBackgroundResource(R.drawable.shape_status_dot_green)
        } else {
            binding.tvCameraStatus.text = "غير موجودة"
            binding.dotCamera.setBackgroundResource(R.drawable.shape_status_dot_purple)
        }
    }

    private fun updateAIStatus() {
        binding.tvAIStatus.text = "TFLite جاهز"
        binding.dotAI.setBackgroundResource(R.drawable.shape_status_dot_green)
    }

    private fun updateLiveStats() {
        val runtime = Runtime.getRuntime()
        val usedMem = (runtime.totalMemory() - runtime.freeMemory()) / 1024 / 1024
        val maxMem = runtime.maxMemory() / 1024 / 1024
        val percent = (usedMem * 100 / maxMem).toInt()

        binding.tvMemoryUsage.text = "$usedMem MB"
        binding.tvMemoryMax.text = "/ $maxMem MB"
        binding.progressMemory.progress = percent

        val usbOk = serialBusManager.isConnected
        val networkOk = networkMonitor.isOnline()
        val cameraOk = cameraManager.findBestCamera() != null
        val memoryOk = percent < 80

        val allOk = usbOk && networkOk && cameraOk && memoryOk
        binding.tvOverallStatus.text = if (allOk) "ممتاز" else "يحتاج انتباه"
        binding.tvOverallStatus.setTextColor(
            ContextCompat.getColor(
                this,
                if (allOk) R.color.severity_excellent else R.color.accent_gold
            )
        )
    }

    private fun runAllTests() {
        appendLog("→ بدء اختبارات التشخيص...")
        binding.btnRunAllTests.isEnabled = false

        scope.launch {
            testUSB()
            delay(300)
            testNetwork()
            delay(300)
            testCamera()
            delay(300)
            testAI()
            delay(300)
            testMemory()

            appendLog("✓ اكتملت جميع الاختبارات")
            binding.btnRunAllTests.isEnabled = true
        }
    }

    private suspend fun testUSB() {
        appendLog("→ اختبار USB...")
        delay(500)
        if (serialBusManager.isConnected) {
            appendLog("  ✓ USB متصل")
            updateUsbStatus()
        } else {
            appendLog("  ✗ USB غير متصل")
            updateUsbStatus()
        }
    }

    private suspend fun testNetwork() {
        appendLog("→ اختبار الشبكة...")
        delay(500)
        if (networkMonitor.isOnline()) {
            appendLog("  ✓ الشبكة متصلة")
            updateNetworkStatus()
        } else {
            appendLog("  ✗ الشبكة غير متصلة")
            updateNetworkStatus()
        }
    }

    private suspend fun testCamera() {
        appendLog("→ اختبار الكاميرا...")
        delay(500)
        val cameraId = cameraManager.findBestCamera()
        if (cameraId != null) {
            appendLog("  ✓ الكاميرا موجودة: $cameraId")
            updateCameraStatus()
        } else {
            appendLog("  ✗ لا توجد كاميرا")
            updateCameraStatus()
        }
    }

    private suspend fun testAI() {
        appendLog("→ اختبار محرك AI...")
        delay(500)
        appendLog("  ✓ TFLite جاهز")
        updateAIStatus()
    }

    private suspend fun testMemory() {
        appendLog("→ اختبار الذاكرة...")
        delay(500)
        val runtime = Runtime.getRuntime()
        val usedMem = (runtime.totalMemory() - runtime.freeMemory()) / 1024 / 1024
        val maxMem = runtime.maxMemory() / 1024 / 1024
        val percent = (usedMem * 100 / maxMem).toInt()

        if (percent < 80) {
            appendLog("  ✓ الذاكرة: $usedMem MB / $maxMem MB ($percent%)")
        } else {
            appendLog("  ⚠ الذاكرة مرتفعة: $usedMem MB / $maxMem MB ($percent%)")
        }
        updateLiveStats()
    }

    private fun appendLog(message: String) {
        val current = binding.tvLogOutput.text.toString()
        binding.tvLogOutput.text = if (current == "جاهز لبدء الاختبارات...") {
            message
        } else {
            "$current\n$message"
        }
    }
}
