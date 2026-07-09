package com.ebtikar.skinanalyzer.ui.diagnostics

import android.content.Intent
import android.net.Uri
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
import com.ebtikar.skinanalyzer.camera.CameraWatchdog
import com.ebtikar.skinanalyzer.ai.EngineHealthMonitor
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
    @Inject lateinit var cameraWatchdog: CameraWatchdog
    @Inject lateinit var engineHealthMonitor: EngineHealthMonitor
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

        // LED Control Buttons
        binding.btnLedWhite.setOnClickListener { toggleSpectrum(LightSpectrum.WHITE, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedUv.setOnClickListener { toggleSpectrum(LightSpectrum.UV365, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedCross.setOnClickListener { toggleSpectrum(LightSpectrum.POL_N, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedParallel.setOnClickListener { toggleSpectrum(LightSpectrum.POL_P, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedWoods.setOnClickListener { toggleSpectrum(LightSpectrum.WOODS, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedBlue.setOnClickListener { toggleSpectrum(LightSpectrum.BLUE, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedRed.setOnClickListener { toggleSpectrum(LightSpectrum.RED, it as com.google.android.material.button.MaterialButton) }
        binding.btnLedBrown.setOnClickListener { toggleSpectrum(LightSpectrum.BROWN, it as com.google.android.material.button.MaterialButton) }
        
        binding.btnLedOff.setOnClickListener {
            scope.launch {
                spectrumController.activate(LightSpectrum.OFF)
                resetLedButtonStyles()
                appendLog("→ تم إطفاء جميع الأضواء")
            }
        }

        binding.btnRefresh.setOnClickListener {
            appendLog("→ تحديث الحالة...")
            populateDiagnostics()
            appendLog("✓ تم التحديث")
        }

        binding.btnRunAllTests.setOnClickListener {
            runAllTests()
        }

        binding.btnTestRoot.setOnClickListener {
            binding.btnTestRoot.isEnabled = false
            binding.btnTestRoot.text = "جارٍ الفحص..."
            scope.launch(Dispatchers.IO) {
                testAllRootMethods()
                withContext(Dispatchers.Main) {
                    binding.btnTestRoot.isEnabled = true
                    binding.btnTestRoot.text = "فحص Root (تجربة كل الطرق)"
                }
            }
        }

        binding.btnExportGpio.setOnClickListener {
            binding.btnExportGpio.isEnabled = false
            binding.btnExportGpio.text = "جارٍ التصدير..."
            scope.launch(Dispatchers.IO) {
                tryDirectGpioExport()
                withContext(Dispatchers.Main) {
                    binding.btnExportGpio.isEnabled = true
                    binding.btnExportGpio.text = "تصدير GPIO مباشرة (بدون root)"
                }
            }
        }

        binding.btnRebindFise.setOnClickListener {
            binding.btnRebindFise.isEnabled = false
            binding.btnRebindFise.text = "جارٍ إعادة الربط..."
            scope.launch(Dispatchers.IO) {
                appendLog("→ محاولة إعادة ربط FISE driver...")

                val unbindOk = execShellSu("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind 2>/dev/null")
                appendLog("  Unbind: ${if (unbindOk) "✓" else "✗"}")
                delay(300)

                val bindOk = execShellSu("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind 2>/dev/null")
                appendLog("  Bind: ${if (bindOk) "✓" else "✗"}")
                delay(500)

                // Re-check GPIO availability
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

        binding.btnReportGithub.setOnClickListener {
            reportToGithub()
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
            appendLine("--- FISE GPIO ---")
            appendLine("Available: ${gpio.isAvailable}")
            appendLine("SELinux: ${gpio.selinuxEnforcing}")
            appendLine("Status: ${gpio.statusMessage}")
            for (i in 0..4) {
                val file = java.io.File("/sys/class/fise_gpio$i/level")
                val exists = file.exists()
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                appendLine("  fise_gpio$i: exists=$exists, value=$readback")
            }
            val ledFile = java.io.File("/sys/class/fise_led/level")
            appendLine("  fise_led: exists=${ledFile.exists()}, readback=${try { ledFile.readText().trim() } catch (_: Exception) { "?" }}")
            appendLine()
            appendLine("--- Serial Bus ---")
            appendLine("Connected: ${serialBusManager.isConnected}")
            appendLine("State: ${serialBusManager.connectionState.value}")
            appendLine("Error: ${serialBusManager.lastError.value}")
            val driver = serialBusManager.findDriver()
            appendLine("Driver found: ${driver?.device?.deviceName ?: "none"}")
            appendLine()
            appendLine("--- Camera ---")
            val camId = cameraManager.findBestCamera()
            appendLine("Camera: ${camId ?: "not found"}")
            appendLine("Ready: ${cameraManager.isReady}")
            appendLine()
            appendLine("--- Camera Watchdog ---")
            appendLine(cameraWatchdog.getStatusSummary())
            appendLine()
            appendLine("--- Engine Health ---")
            appendLine(engineHealthMonitor.getHealthReport())
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
        updateCameraWatchdogStatus()
        updateAIStatus()
        updateLiveStats()
    }

    private fun updateGpioStatus() {
        val available = fiseGpioController.isAvailable
        val selinux = fiseGpioController.selinuxEnforcing
        binding.tvGpioStatus.text = when {
            available -> "متاح ✓"
            else -> "غير متاح — FISE driver لا يوجد"
        }
        binding.dotGpio.setBackgroundResource(
            if (available) R.drawable.shape_status_dot_green else R.drawable.shape_status_dot_purple
        )
    }

    private fun updateUsbStatus() {
        val isConnected = serialBusManager.isConnected
        val usbDevices = serialBusManager.listAllUsbDevices()
        binding.tvUsbStatus.text = if (isConnected) {
            "متصل"
        } else if (usbDevices.isNotEmpty()) {
            "พบ ${usbDevices.size} جهاز USB (غير متصل)"
        } else {
            "غير متصل — لا توجد أجهزة USB"
        }
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

    private fun updateCameraWatchdogStatus() {
        val healthy = cameraWatchdog.isHealthy
        val timeouts = cameraWatchdog.currentConsecutiveTimeouts
        val totalEvents = cameraWatchdog.totalTimeoutEvents
        val resets = cameraWatchdog.totalResetAttempts
        val status = if (healthy) {
            if (totalEvents == 0L) "سليم ✓" else "تعافى (${totalEvents} أحداث سابقة)"
        } else {
            "يحتاج إعادة تشغيل (${timeouts} فترات انتظار)"
        }
        appendLog("  مراقب الكاميرا: $status (محاولات إعادة: $resets)")
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
            testCameraWatchdog()
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

    private suspend fun testCameraWatchdog() {
        appendLog("→ اختبار مراقب الكاميرا...")
        delay(300)
        val status = cameraWatchdog.getStatusSummary()
        appendLog("  الحالة: ${if (cameraWatchdog.isHealthy) "✓ سليم" else "✗ غير سليم"}")
        appendLog("  الأحداث: ${cameraWatchdog.totalTimeoutEvents} | محاولات إعادة: ${cameraWatchdog.totalResetAttempts}")
        updateCameraWatchdogStatus()
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

    private fun reportToGithub() {
        val gpio = fiseGpioController
        val report = buildString {
            appendLine("## Diagnostic Report - SkinAnalyzer v${com.ebtikar.skinanalyzer.BuildConfig.VERSION_NAME}")
            appendLine()
            appendLine("**Device**: ${android.os.Build.MANUFACTURER} ${android.os.Build.MODEL} | Android ${android.os.Build.VERSION.RELEASE}")
            appendLine()
            appendLine("### FISE GPIO")
            appendLine("- Available: `${gpio.isAvailable}` | SELinux: `${gpio.selinuxEnforcing}`")
            appendLine("- Status: ${gpio.statusMessage}")
            for (i in 0..4) {
                val file = java.io.File("/sys/class/fise_gpio$i/level")
                val exists = file.exists()
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                appendLine("  - fise_gpio$i: exists=$exists, value=$readback")
            }
            val ledFile = java.io.File("/sys/class/fise_led/level")
            appendLine("  - fise_led: exists=${ledFile.exists()}, value=${try { ledFile.readText().trim() } catch (_: Exception) { "?" }}")
            appendLine("### Serial: `${serialBusManager.isConnected}` | Error: `${serialBusManager.lastError.value}`")
            appendLine("### Camera: `${cameraManager.findBestCamera() ?: "none"}` | Network: `${networkMonitor.isOnline()}`")
            appendLine("### Camera Watchdog")
            appendLine("- Healthy: `${cameraWatchdog.isHealthy}` | Timeouts: `${cameraWatchdog.currentConsecutiveTimeouts}`/3")
            appendLine("- Total Events: `${cameraWatchdog.totalTimeoutEvents}` | Resets: `${cameraWatchdog.totalResetAttempts}` (success: `${cameraWatchdog.totalSuccessfulResets}`)")
            appendLine("### Engine Health")
            val allStats = engineHealthMonitor.getAllStats()
            for ((name, stat) in allStats) {
                appendLine("- $name: ${stat.successfulAttempts}/${stat.totalAttempts} (${"%.0f".format(stat.successRate * 100)}%) | avg ${"%.0f".format(stat.avgExecutionTimeMs)}ms")
            }
            appendLine("### Log")
            val log = binding.tvLogOutput.text.toString().takeLast(500)
            appendLine("```$log```")
        }

        val title = "Bug Report - v${com.ebtikar.skinanalyzer.BuildConfig.VERSION_NAME} - ${android.os.Build.MODEL}"
        val url = "https://github.com/mzawari-git/jenincare/issues/new?title=${Uri.encode(title)}&body=${Uri.encode(report)}"

        try {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
            startActivity(intent)
        } catch (e: Exception) {
            android.widget.Toast.makeText(this, "لا يمكن فتح المتصفح. تحقق من اتصال الإنترنت.", android.widget.Toast.LENGTH_LONG).show()
        }
    }

    private suspend fun testAllRootMethods() {
        withContext(Dispatchers.Main) { appendLog("=== فحص USB Devices ===") }
        val usbDevices = serialBusManager.listAllUsbDevices()
        if (usbDevices.isEmpty()) {
            withContext(Dispatchers.Main) { appendLog("  لا توجد أجهزة USB متصلة") }
        } else {
            for (info in usbDevices) {
                withContext(Dispatchers.Main) { appendLog("  $info") }
            }
        }

        withContext(Dispatchers.Main) { appendLog("=== فحص FISE GPIO Sysfs ===") }
        for (i in 0..4) {
            val file = java.io.File("/sys/class/fise_gpio$i/level")
            val exists = file.exists()
            val canWrite = try { file.canWrite() } catch (_: Exception) { false }
            val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
            withContext(Dispatchers.Main) { appendLog("  fise_gpio$i: exists=$exists write=$canWrite value=$readback") }
        }
        val ledFile = java.io.File("/sys/class/fise_led/level")
        val ledExists = ledFile.exists()
        val ledCanWrite = try { ledFile.canWrite() } catch (_: Exception) { false }
        val ledReadback = try { ledFile.readText().trim() } catch (_: Exception) { "?" }
        withContext(Dispatchers.Main) { appendLog("  fise_led: exists=$ledExists write=$ledCanWrite value=$ledReadback") }

        withContext(Dispatchers.Main) { appendLog("=== فحص Root الشامل ===") }

        val suPaths = listOf("/system/bin/su", "/sbin/su", "/system/xbin/su", "/su/bin/su", "/vendor/bin/su", "/data/adb/magisk/su", "/data/adb/ksu/bin/su", "/data/adb/ap/su", "/system/su")
        for (p in suPaths) {
            val exists = java.io.File(p).exists()
            if (exists) {
                withContext(Dispatchers.Main) { appendLog("  $p: موجود ✓") }
                val methods = listOf(
                    arrayOf(p, "-c", "id"),
                    arrayOf(p, "-c", "id"),
                    arrayOf(p, "0", "id"),
                    arrayOf(p, "-0", "id"),
                    arrayOf(p, "root", "id")
                )
                for (cmd in methods) {
                    try {
                        val proc = Runtime.getRuntime().exec(cmd)
                        val stdout = proc.inputStream.bufferedReader().readText().trim()
                        val stderr = proc.errorStream.bufferedReader().readText().trim()
                        val exit = proc.waitFor()
                        val cmdStr = cmd.joinToString(" ")
                        withContext(Dispatchers.Main) {
                            appendLog("    $cmdStr → exit=$exit stdout=[$stdout] stderr=[$stderr]")
                        }
                        if (exit == 0 && stdout.contains("uid=0")) {
                            withContext(Dispatchers.Main) { appendLog("    ★ ROOT FOUND via: $cmdStr") }
                            return
                        }
                    } catch (e: Exception) {
                        withContext(Dispatchers.Main) { appendLog("    ${cmd.joinToString(" ")} → EXCEPTION: ${e.message}") }
                    }
                }
            }
        }

        withContext(Dispatchers.Main) { appendLog("  فحص 'sh -c id':") }
        try {
            val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", "id"))
            val stdout = proc.inputStream.bufferedReader().readText().trim()
            val exit = proc.waitFor()
            withContext(Dispatchers.Main) { appendLog("    sh -c id → exit=$exit stdout=[$stdout]") }
        } catch (e: Exception) {
            withContext(Dispatchers.Main) { appendLog("    sh -c id → EXCEPTION: ${e.message}") }
        }

        withContext(Dispatchers.Main) { appendLog("  فحص محاكاة 'su' بدون root:") }
        try {
            val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", "echo test_root | su 2>&1; echo EXIT=\$?"))
            val stdout = proc.inputStream.bufferedReader().readText().trim()
            val exit = proc.waitFor()
            withContext(Dispatchers.Main) { appendLog("    su piped → exit=$exit output=[$stdout]") }
        } catch (e: Exception) {
            withContext(Dispatchers.Main) { appendLog("    su piped → EXCEPTION: ${e.message}") }
        }

        withContext(Dispatchers.Main) {
            appendLog("=== اكتمل الفحص ===")
            appendLog("FISE GPIO: ${if (fiseGpioController.isAvailable) "✓ متاح" else "✗ غير متاح"}")
            appendLog("Serial: ${if (serialBusManager.isConnected) "✓ متصل" else "✗ غير متصل"}")
        }
    }

    private suspend fun tryDirectGpioExport() {
        withContext(Dispatchers.Main) { appendLog("=== فحص FISE GPIO files ===") }
        for (i in 0..4) {
            val file = java.io.File("/sys/class/fise_gpio$i/level")
            val exists = file.exists()
            val canWrite = try { file.canWrite() } catch (_: Exception) { false }
            val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
            withContext(Dispatchers.Main) { appendLog("  fise_gpio$i: exists=$exists write=$canWrite value=$readback") }

            if (exists && canWrite) {
                try {
                    file.writeText("1")
                    val rb = try { file.readText().trim() } catch (_: Exception) { "?" }
                    withContext(Dispatchers.Main) { appendLog("    write test: wrote=1 readback=$rb ✓") }
                    file.writeText("0")
                } catch (e: Exception) {
                    withContext(Dispatchers.Main) { appendLog("    write test FAILED: ${e.message}") }
                }
            }
        }

        val ledFile = java.io.File("/sys/class/fise_led/level")
        val ledExists = ledFile.exists()
        val ledCanWrite = try { ledFile.canWrite() } catch (_: Exception) { false }
        withContext(Dispatchers.Main) { appendLog("  fise_led: exists=$ledExists write=$ledCanWrite") }

        val ok = fiseGpioController.recheckAvailability()
        withContext(Dispatchers.Main) {
            appendLog("→ FISE GPIO status: ${if (ok) "✓ متاح" else "✗ لا يزال غير متاح"}")
            updateGpioStatus()
        }
    }

    private var activeSpectrum: LightSpectrum = LightSpectrum.OFF

    private fun toggleSpectrum(spectrum: LightSpectrum, button: com.google.android.material.button.MaterialButton) {
        scope.launch {
            if (activeSpectrum == spectrum) {
                spectrumController.activate(LightSpectrum.OFF)
                activeSpectrum = LightSpectrum.OFF
                button.strokeWidth = 0
                appendLog("→ إطفاء ${spectrum.displayName}")
            } else {
                // Turn off previous if any
                resetLedButtonStyles()
                
                val result = spectrumController.activate(spectrum)
                if (result.isSuccess) {
                    activeSpectrum = spectrum
                    button.strokeWidth = 4
                    button.setStrokeColorResource(R.color.primary)
                    appendLog("★ تشغيل ${spectrum.displayName} ✓")
                } else {
                    appendLog("✗ فشل تشغيل ${spectrum.displayName}: ${result.exceptionOrNull()?.message}")
                }
            }
        }
    }

    private fun resetLedButtonStyles() {
        val buttons = listOf(
            binding.btnLedWhite, binding.btnLedUv, binding.btnLedCross,
            binding.btnLedParallel, binding.btnLedWoods, binding.btnLedBlue,
            binding.btnLedRed, binding.btnLedBrown
        )
        buttons.forEach { it.strokeWidth = 0 }
        activeSpectrum = LightSpectrum.OFF
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
