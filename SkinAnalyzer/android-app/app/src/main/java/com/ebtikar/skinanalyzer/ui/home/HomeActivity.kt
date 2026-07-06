package com.ebtikar.skinanalyzer.ui.home

import android.Manifest
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.content.pm.PackageManager
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.app.PendingIntent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.lifecycleScope
import com.ebtikar.skinanalyzer.databinding.ActivityHomeBinding
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.ui.analysis.AnalysisActivity
import com.ebtikar.skinanalyzer.ui.history.HistoryActivity
import com.ebtikar.skinanalyzer.ui.settings.SettingsActivity
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.UpdateChecker
import com.ebtikar.skinanalyzer.util.UpdateInfo
import com.ebtikar.skinanalyzer.BuildConfig
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import javax.inject.Inject

@AndroidEntryPoint
class HomeActivity : AppCompatActivity() {

    private lateinit var binding: ActivityHomeBinding
    private val viewModel: HomeViewModel by viewModels()
    private lateinit var recentAdapter: HomeRecentAdapter
    private val clockHandler = Handler(Looper.getMainLooper())
    private val clockRunnable = object : Runnable {
        override fun run() {
            updateClock()
            clockHandler.postDelayed(this, 30000)
        }
    }

    private val scope = CoroutineScope(Dispatchers.Main + SupervisorJob())

    @Inject lateinit var serialBusManager: SerialBusManager
    @Inject lateinit var spectrumController: SpectrumController
    @Inject lateinit var fiseGpioController: FiseGpioController
    @Inject lateinit var updateChecker: UpdateChecker
    @Inject lateinit var preferencesManager: PreferencesManager

    private val ACTION_USB_PERMISSION = "com.ebtikar.skinanalyzer.USB_PERMISSION"

    private val usbReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context, intent: Intent) {
            when (intent.action) {
                ACTION_USB_PERMISSION -> {
                    synchronized(this) {
                        val device = intent.getParcelableExtra<UsbDevice>(UsbManager.EXTRA_DEVICE)
                        if (intent.getBooleanExtra(UsbManager.EXTRA_PERMISSION_GRANTED, false)) {
                            device?.let {
                                Timber.i("USB permission granted for ${it.deviceName}")
                                connectUsbDevice()
                            }
                        } else {
                            Timber.w("USB permission denied")
                            updateUsbStatus(false)
                        }
                    }
                }
                UsbManager.ACTION_USB_DEVICE_ATTACHED -> {
                    Timber.i("USB device attached")
                    connectUsbDevice()
                }
                UsbManager.ACTION_USB_DEVICE_DETACHED -> {
                    Timber.w("USB device detached")
                    serialBusManager.disconnect()
                    updateUsbStatus(false)
                }
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityHomeBinding.inflate(layoutInflater)
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

        registerUsbReceiver()
        setupUI()
        setupRecentReports()
        observeViewModel()
        updateClock()
        connectUsbDevice()
        runStartupLedTest()
        checkForUpdates()
    }

    override fun onResume() {
        super.onResume()
        viewModel.loadHistory()
        clockHandler.post(clockRunnable)
    }

    override fun onPause() {
        super.onPause()
        clockHandler.removeCallbacks(clockRunnable)
    }

    override fun onDestroy() {
        super.onDestroy()
        scope.cancel()
        try {
            unregisterReceiver(usbReceiver)
        } catch (e: Exception) {
            Timber.w(e, "Error unregistering USB receiver")
        }
    }

    private fun registerUsbReceiver() {
        val filter = IntentFilter().apply {
            addAction(ACTION_USB_PERMISSION)
            addAction(UsbManager.ACTION_USB_DEVICE_ATTACHED)
            addAction(UsbManager.ACTION_USB_DEVICE_DETACHED)
        }
        registerReceiver(usbReceiver, filter)
    }

    private fun connectUsbDevice() {
        try {
            val driver = serialBusManager.findDriver()
            if (driver != null) {
                val usbManager = getSystemService(Context.USB_SERVICE) as UsbManager
                if (!usbManager.hasPermission(driver.device)) {
                    val permissionIntent = PendingIntent.getBroadcast(
                        this, 0,
                        Intent(ACTION_USB_PERMISSION).apply { setPackage(packageName) },
                        PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT
                    )
                    usbManager.requestPermission(driver.device, permissionIntent)
                    Timber.i("Requesting USB permission for ${driver.device.deviceName}")
                } else {
                    val result = serialBusManager.connect(driver)
                    if (result.isSuccess) {
                        Timber.i("USB serial connected successfully")
                        updateUsbStatus(true)
                    } else {
                        Timber.w("Failed to connect USB serial: ${result.exceptionOrNull()?.message}")
                        updateUsbStatus(false)
                    }
                }
            } else {
                Timber.w("No USB serial driver found - LED hardware not detected")
                updateUsbStatus(false)
            }
        } catch (e: Exception) {
            Timber.e(e, "Error connecting USB device")
            updateUsbStatus(false)
        }
    }

    private fun updateUsbStatus(connected: Boolean) {
        runOnUiThread {
            if (connected) {
                binding.tvConnectionStatus.text = "متصل"
                binding.tvConnectionStatus.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.status_online))
            } else {
                binding.tvConnectionStatus.text = "غير متصل"
                binding.tvConnectionStatus.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.severity_critical))
            }
        }
    }

    private fun setupRecentReports() {
        recentAdapter = HomeRecentAdapter { reportId ->
            val intent = Intent(this, com.ebtikar.skinanalyzer.ui.report.ReportActivity::class.java).apply {
                putExtra("report_id", reportId)
            }
            startActivity(intent)
        }
        binding.rvRecentReports.apply {
            layoutManager = androidx.recyclerview.widget.LinearLayoutManager(this@HomeActivity)
            adapter = this@HomeActivity.recentAdapter
        }
    }

    private fun updateClock() {
        val dayFormat = SimpleDateFormat("EEEE، d MMMM yyyy", Locale("ar"))
        val timeFormat = SimpleDateFormat("hh:mm a", Locale("ar"))
        val now = Date()
        binding.tvDateTime.text = "${dayFormat.format(now)} — ${timeFormat.format(now)}"
    }

    private fun runStartupLedTest() {
        scope.launch(Dispatchers.IO) {
            if (!fiseGpioController.isAvailable) {
                Timber.w("FISE GPIO not available at startup, trying recheck...")
                val recheck = fiseGpioController.recheckAvailability()
                Timber.i("Startup FISE GPIO re-init: available=$recheck")
            }

            val gpioAvailable = fiseGpioController.isAvailable
            val serialAvailable = serialBusManager.isConnected
            Timber.i("Startup LED test: gpio=$gpioAvailable, serial=$serialAvailable")

            if (!gpioAvailable && !serialAvailable) {
                Timber.e("No LED hardware detected. LEDs will not fire during scan.")
                return@launch
            }

            val testSpectra = listOf(
                LightSpectrum.WHITE, LightSpectrum.UV365, LightSpectrum.WOODS,
                LightSpectrum.POL_P, LightSpectrum.POL_N
            )

            for (spectrum in testSpectra) {
                try {
                    val result = spectrumController.activate(spectrum)
                    Timber.d("Startup LED test: ${spectrum.name} -> ${if (result.isSuccess) "OK" else "FAIL"}")
                    delay(200)
                } catch (e: Exception) {
                    Timber.w(e, "Startup LED test failed for ${spectrum.name}")
                }
            }
            spectrumController.activate(LightSpectrum.OFF)
            Timber.i("Startup LED self-test complete: gpio=$gpioAvailable, serial=$serialAvailable")
        }
    }

    private fun autoReportToGithub() {
        val gpio = fiseGpioController
        val report = buildString {
            appendLine("## Auto Report: LED Hardware Not Available")
            appendLine()
            appendLine("**Device**: ${android.os.Build.MANUFACTURER} ${android.os.Build.MODEL} | Android ${android.os.Build.VERSION.RELEASE}")
            appendLine("**App**: v${BuildConfig.VERSION_NAME} (${BuildConfig.VERSION_CODE})")
            appendLine()
            appendLine("### FISE GPIO")
            appendLine("- Available: `${gpio.isAvailable}` | SELinux: `${gpio.selinuxEnforcing}`")
            appendLine("- Status: ${gpio.statusMessage}")
            for (i in 0..4) {
                val exists = java.io.File("/sys/class/fise_gpio$i/level").exists()
                val readback = try { java.io.File("/sys/class/fise_gpio$i/level").readText().trim() } catch (_: Exception) { "?" }
                appendLine("  - fise_gpio$i: exists=$exists, value=$readback")
            }
            val ledExists = java.io.File("/sys/class/fise_led/level").exists()
            appendLine("  - fise_led: exists=$ledExists")
            appendLine("### Serial: ${serialBusManager.isConnected}")
            val camMgr = getSystemService(Context.CAMERA_SERVICE) as? android.hardware.camera2.CameraManager
            appendLine("### Camera: ${camMgr?.cameraIdList?.joinToString() ?: "none"}")
        }

        val title = "LED Not Available - v${BuildConfig.VERSION_NAME} - ${android.os.Build.MODEL}"
        val url = "https://github.com/mzawari-git/jenincare/issues/new?title=${android.net.Uri.encode(title)}&body=${android.net.Uri.encode(report)}"
        try {
            startActivity(Intent(Intent.ACTION_VIEW, android.net.Uri.parse(url)))
        } catch (e: Exception) {
            Timber.e(e, "Failed to open GitHub issue")
        }
    }

    private fun checkForUpdates() {
        scope.launch(Dispatchers.Main) {
            delay(5000)
            val autoEnabled = preferencesManager.autoUpdateEnabledFlow.first()
            val channel = preferencesManager.updateChannelFlow.first()
            try {
                val updateInfo = updateChecker.checkForUpdate(channel)
                if (updateInfo != null && updateChecker.isNewerVersion(updateInfo.latestVersion)) {
                    Timber.i("Update available: v${updateInfo.latestVersion}")
                    if (autoEnabled) {
                        downloadInBackground(updateInfo)
                    } else {
                        showUpdateDialog(updateInfo)
                    }
                } else {
                    Timber.i("No update available (current: ${updateChecker.getCurrentVersion()})")
                }
            } catch (e: Exception) {
                Timber.e(e, "Update check failed")
            }
            preferencesManager.setLastUpdateCheck(System.currentTimeMillis())
        }
    }

    private fun downloadInBackground(updateInfo: UpdateInfo) {
        scope.launch(Dispatchers.IO) {
            val uri = updateChecker.downloadApkWithNotification(updateInfo)
            withContext(Dispatchers.Main) {
                if (uri != null) {
                    updateChecker.showInstallNotification(updateInfo, uri)
                } else {
                    Timber.w("Auto-download failed for v${updateInfo.latestVersion}, showing manual dialog")
                    showUpdateDialog(updateInfo)
                }
            }
        }
    }

    private fun showUpdateDialog(updateInfo: UpdateInfo) {
        val message = buildString {
            append("الإصدار الجديد: v${updateInfo.latestVersion}")
            if (!updateInfo.releaseNotes.isNullOrBlank()) {
                append("\n\n${updateInfo.releaseNotes}")
            }
        }

        android.app.AlertDialog.Builder(this)
            .setTitle("تحديث متوفر")
            .setMessage(message)
            .setCancelable(false)
            .setPositiveButton("تحميل وتثبيت") { _, _ ->
                downloadAndInstall(updateInfo)
            }
            .setNegativeButton("لاحقاً") { dialog, _ ->
                dialog.dismiss()
            }
            .show()
    }

    private fun downloadAndInstall(updateInfo: UpdateInfo) {
        val progressDialog = android.app.ProgressDialog(this).apply {
            setTitle("جاري التحميل")
            setMessage("يرجى الانتظار...")
            setProgressStyle(android.app.ProgressDialog.STYLE_HORIZONTAL)
            setMax(100)
            setCancelable(false)
            show()
        }

        scope.launch(Dispatchers.IO) {
            val uri = updateChecker.downloadApk(updateInfo) { progress ->
                this@HomeActivity.runOnUiThread {
                    progressDialog.progress = (progress * 100).toInt()
                }
            }

            withContext(Dispatchers.Main) {
                progressDialog.dismiss()
                if (uri != null) {
                    updateChecker.installApk(uri)
                } else {
                    android.app.AlertDialog.Builder(this@HomeActivity)
                        .setTitle("خطأ")
                        .setMessage("فشل تحميل التحديث. تأكد من اتصالك بالإنترنت وحاول مرة أخرى.")
                        .setPositiveButton("حسناً", null)
                        .show()
                }
            }
        }
    }

    private fun startDiagnosis(mode: String) {
        viewModel.setDiagnosisMode(mode)
        val intent = Intent(this, AnalysisActivity::class.java).apply {
            putExtra("diagnosis_mode", mode)
        }
        startActivity(intent)
    }

    private fun setupUI() {
        binding.btnStartAnalysis.setOnClickListener {
            val intent = Intent(this, AnalysisActivity::class.java).apply {
                putExtra("diagnosis_mode", Constants.DIAGNOSIS_ALL)
            }
            startActivity(intent)
        }

        binding.navSettings.setOnClickListener {
            startActivity(Intent(this, SettingsActivity::class.java))
        }

        binding.navHistory.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.navScan.setOnClickListener {
            val intent = Intent(this, AnalysisActivity::class.java).apply {
                putExtra("diagnosis_mode", Constants.DIAGNOSIS_ALL)
            }
            startActivity(intent)
        }

        binding.cardDiagnosisWhite.setOnClickListener { startDiagnosis(Constants.DIAGNOSIS_WHITE) }
        binding.cardDiagnosisUv.setOnClickListener { startDiagnosis(Constants.DIAGNOSIS_UV) }
        binding.cardDiagnosisCrossPol.setOnClickListener { startDiagnosis(Constants.DIAGNOSIS_CROSS_POL) }
        binding.cardDiagnosisParallelPol.setOnClickListener { startDiagnosis(Constants.DIAGNOSIS_PARALLEL_POL) }
        binding.cardDiagnosisWoods.setOnClickListener { startDiagnosis(Constants.DIAGNOSIS_WOODS) }

        binding.cardHistoryQuick.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.cardComparison.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.cardReport.setOnClickListener {
            val reports = viewModel.recentReports.value
            if (reports.isNotEmpty()) {
                val intent = Intent(this@HomeActivity, com.ebtikar.skinanalyzer.ui.report.ReportActivity::class.java)
                intent.putExtra("report_id", reports.first().id)
                startActivity(intent)
            } else {
                android.widget.Toast.makeText(this@HomeActivity, "لا توجد تقارير سابقة", android.widget.Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            viewModel.todayCount.collect { count ->
                binding.tvTodayCount.text = "$count"
            }
        }
        lifecycleScope.launch {
            viewModel.avgScore.collect { score ->
                binding.tvAvgScore.text = if (score != null) "%.0f".format(score) else "--"
            }
        }
        lifecycleScope.launch {
            viewModel.historyCount.collect { count ->
                binding.tvHistoryCount.text = "$count"
            }
        }
        lifecycleScope.launch {
            viewModel.recentReports.collect { reports ->
                recentAdapter.submitList(reports)
            }
        }
        lifecycleScope.launch {
            viewModel.hardwareStatus.collect { status ->
                updateUsbStatus(status == "Ready" || serialBusManager.isConnected)
            }
        }
    }
}
