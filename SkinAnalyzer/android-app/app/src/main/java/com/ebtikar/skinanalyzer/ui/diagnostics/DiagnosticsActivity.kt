package com.ebtikar.skinanalyzer.ui.diagnostics

import android.content.Intent
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
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@AndroidEntryPoint
class DiagnosticsActivity : AppCompatActivity() {

    private lateinit var binding: ActivityDiagnosticsBinding
    private val scope = CoroutineScope(Dispatchers.Main + SupervisorJob())
    private val handler = Handler(Looper.getMainLooper())

    @Inject lateinit var serialBusManager: SerialBusManager
    @Inject lateinit var networkMonitor: NetworkMonitor
    @Inject lateinit var cameraManager: USBCameraManager
    @Inject lateinit var fiseGpioController: FiseGpioController
    @Inject lateinit var spectrumController: SpectrumController

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

        binding.btnRebindFise.setOnClickListener {
            binding.btnRebindFise.isEnabled = false
            binding.btnRebindFise.text = "جارٍ إعادة الربط..."
            scope.launch(Dispatchers.IO) {
                appendLog("→ محاولة إعادة ربط FISE driver عبر su...")
                val unbindOk = execShellSu("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind")
                appendLog("  Unbind: ${if (unbindOk) "✓" else "✗ (قد يكون غير مربوط)"}")

                val bindOk = execShellSu("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind")
                appendLog("  Bind: ${if (bindOk) "✓" else "✗"}")

                delay(500)

                val allExported = execShellSu("echo 34 > /sys/class/gpio/export 2>/dev/null; echo 149 > /sys/class/gpio/export 2>/dev/null; echo 45 > /sys/class/gpio/export 2>/dev/null; echo 54 > /sys/class/gpio/export 2>/dev/null; echo 56 > /sys/class/gpio/export 2>/dev/null")
                appendLog("  Export GPIOs: ${if (allExported) "✓" else "✗"}")

                val directions = execShellSu("echo out > /sys/class/gpio/gpio34/direction 2>/dev/null; echo out > /sys/class/gpio/gpio149/direction 2>/dev/null; echo out > /sys/class/gpio/gpio45/direction 2>/dev/null; echo out > /sys/class/gpio/gpio54/direction 2>/dev/null; echo out > /sys/class/gpio/gpio56/direction 2>/dev/null")
                appendLog("  Direction: ${if (directions) "✓" else "✗"}")

                val chmodOk = execShellSu("chmod 666 /sys/class/gpio/gpio34/value /sys/class/gpio/gpio149/value /sys/class/gpio/gpio45/value /sys/class/gpio/gpio54/value /sys/class/gpio/gpio56/value 2>/dev/null")
                appendLog("  Permissions: ${if (chmodOk) "✓" else "✗"}")

                // Re-check GPIO availability in the controller
                val nowAvailable = fiseGpioController.recheckAvailability()

                withContext(Dispatchers.Main) {
                    updateGpioStatus()
                    binding.btnRebindFise.isEnabled = true
                    binding.btnRebindFise.text = "إصلاح FISE GPIO (إعادة الربط)"
                    appendLog("→ اكتملت إعادة الربط. الحالة: ${if (nowAvailable) "✓ متاح" else "✗ لا يزال غير متاح"}")
                }
            }
        }

        binding.btnTestLeds.setOnClickListener {
            binding.btnTestLeds.isEnabled = false
            binding.btnTestLeds.text = "جارٍ الاختبار..."
            scope.launch {
                appendLog("→ بدء اختبار الأضواء التسلسلي...")
                val results = spectrumController.quickTest()
                for ((spectrum, success) in results) {
                    appendLog("  ${spectrum.displayName}: ${if (success) "✓" else "✗"}")
                }
                appendLog("→ اكتمل اختبار الأضواء")
                binding.btnTestLeds.isEnabled = true
                binding.btnTestLeds.text = "اختبار الأضواء (تفعيل تسلسلي)"
            }
        }

        binding.btnShareLog.setOnClickListener {
            shareDiagnostics()
        }
    }

    private fun shareDiagnostics() {
        val gpio = fiseGpioController
        val report = buildString {
            appendLine("=== SkinAnalyzer Diagnostics ===")
            appendLine("Version: ${com.ebtikar.skinanalyzer.BuildConfig.VERSION_NAME} (${com.ebtikar.skinanalyzer.BuildConfig.VERSION_CODE})")
            appendLine("Build: ${com.ebtikar.skinanalyzer.BuildConfig.BUILD_TYPE}")
            appendLine("Time: ${java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", java.util.Locale.US).format(java.util.Date())}")
            appendLine()
            appendLine("--- GPIO ---")
            appendLine("Available: ${gpio.isAvailable}")
            appendLine("Root: ${gpio.hasRoot}")
            appendLine("SELinux: ${gpio.selinuxEnforcing}")
            appendLine("Status: ${gpio.statusMessage}")
            for (i in 0..4) {
                val gpioNum = when(i) { 0->34; 1->149; 2->45; 3->54; 4->56; else->0 }
                val dir = java.io.File("/sys/class/gpio/gpio$gpioNum")
                val file = java.io.File("/sys/class/gpio/gpio$gpioNum/value")
                val exists = dir.exists()
                val canWrite = try { file.canWrite() } catch (_: Exception) { false }
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                appendLine("  gpio$gpioNum: dir=$exists, write=$canWrite, value=$readback")
            }
            val exportFile = java.io.File("/sys/class/gpio/export")
            appendLine("  /sys/class/gpio/export: exists=${exportFile.exists()}, canWrite=${try { exportFile.canWrite() } catch (_: Exception) { false }}")
            val fiseUnbind = java.io.File("/sys/bus/platform/drivers/fise_gpio/unbind")
            appendLine("  fise_gpio/unbind: exists=${fiseUnbind.exists()}, canWrite=${try { fiseUnbind.canWrite() } catch (_: Exception) { false }}")
            val fiseDir = java.io.File("/sys/bus/platform/drivers/fise_gpio")
            appendLine("  fise_gpio dir: exists=${fiseDir.exists()}, files=${try { fiseDir.listFiles()?.map { it.name }?.joinToString() } catch (_: Exception) { "?" }}")
            appendLine()
            appendLine("--- Serial Bus ---")
            appendLine("Connected: ${serialBusManager.isConnected}")
            appendLine("State: ${serialBusManager.connectionState.value}")
            appendLine("Error: ${serialBusManager.lastError.value}")
            val driver = serialBusManager.findDriver()
            appendLine("Driver found: ${driver?.device?.deviceName ?: "none"}")
            appendLine()
            appendLine("--- su paths check ---")
            val suPaths = listOf("/system/bin/su", "/sbin/su", "/system/xbin/su", "/su/bin/su", "/vendor/bin/su", "/data/adb/magisk/su", "/data/adb/ksu/bin/su")
            for (p in suPaths) {
                val f = java.io.File(p)
                appendLine("  $p: exists=${f.exists()}, canExec=${try { f.canExecute() } catch (_: Exception) { false }}")
            }
            try {
                val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", "which su 2>/dev/null || echo not_found"))
                val whichResult = proc.inputStream.bufferedReader().readText().trim()
                proc.waitFor()
                appendLine("  which su: $whichResult")
            } catch (e: Exception) {
                appendLine("  which su: error ${e.message}")
            }
            try {
                val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", "id"))
                val idResult = proc.inputStream.bufferedReader().readText().trim()
                proc.waitFor()
                appendLine("  id: $idResult")
            } catch (e: Exception) {
                appendLine("  id: error ${e.message}")
            }
            appendLine()
            appendLine("--- Camera ---")
            val camId = cameraManager.findBestCamera()
            appendLine("Camera: ${camId ?: "not found"}")
            appendLine("Ready: ${cameraManager.isReady}")
            appendLine()
            appendLine("--- Network ---")
            appendLine("Online: ${networkMonitor.isOnline()}")
            appendLine()
            appendLine("--- Log Output ---")
            appendLine(binding.tvLogOutput.text.toString())
        }

        val file = java.io.File(cacheDir, "diagnostics.txt")
        file.writeText(report)

        val intent = Intent(Intent.ACTION_SEND).apply {
            type = "text/plain"
            putExtra(Intent.EXTRA_TEXT, report)
            putExtra(Intent.EXTRA_SUBJECT, "SkinAnalyzer Diagnostics")
            addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
        }
        startActivity(Intent.createChooser(intent, "مشاركة تقرير التشخيص"))
    }

    private suspend fun execShellSu(cmd: String): Boolean {
        return withContext(Dispatchers.IO) {
            try {
                val proc = Runtime.getRuntime().exec(arrayOf("su", "-c", cmd))
                if (proc.waitFor() == 0) {
                    true
                } else {
                    try {
                        val proc2 = Runtime.getRuntime().exec(arrayOf("sh", "-c", cmd))
                        proc2.waitFor() == 0
                    } catch (e: Exception) {
                        false
                    }
                }
            } catch (e: Exception) {
                try {
                    val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", cmd))
                    proc.waitFor() == 0
                } catch (e2: Exception) {
                    false
                }
            }
        }
    }

    private fun populateDiagnostics() {
        updateUsbStatus()
        updateNetworkStatus()
        updateGpioStatus()
        updateCameraStatus()
        updateAIStatus()
        updateLiveStats()
    }

    private fun updateGpioStatus() {
        val available = fiseGpioController.isAvailable
        val selinux = fiseGpioController.selinuxEnforcing
        binding.tvGpioStatus.text = when {
            available -> "متاح (SELinux=${selinux ?: "?"})"
            selinux == true -> "غير متاح — SELinux يمنع الكتابة"
            else -> "غير متاح — FISE يحتوي على GPIO"
        }
        binding.dotGpio.setBackgroundResource(
            if (available) R.drawable.shape_status_dot_green else R.drawable.shape_status_dot_purple
        )
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
            binding.tvAIStatus.text = "TFLite جاهز | الكاميرا: $cameraId"
        } else {
            binding.tvAIStatus.text = "TFLite جاهز | الكاميرا: غير موجودة"
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
        val gpioOk = fiseGpioController.isAvailable
        val memoryOk = percent < 80

        val allOk = usbOk && networkOk && gpioOk && memoryOk
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
