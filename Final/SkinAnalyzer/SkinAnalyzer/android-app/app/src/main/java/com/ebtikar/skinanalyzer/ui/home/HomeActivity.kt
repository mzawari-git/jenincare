package com.ebtikar.skinanalyzer.ui.home

import android.Manifest
import android.app.PendingIntent
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
import android.view.View
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.camera.core.CameraSelector
import androidx.camera.core.Preview
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.databinding.ActivityHomeBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.ui.analysis.AnalysisActivity
import com.ebtikar.skinanalyzer.ui.history.HistoryActivity
import com.ebtikar.skinanalyzer.ui.settings.SettingsActivity
import com.ebtikar.skinanalyzer.util.Constants
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
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

    private var cameraProvider: ProcessCameraProvider? = null

    @Inject
    lateinit var serialBusManager: SerialBusManager

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

    private val cameraPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { isGranted ->
        if (isGranted) {
            startCameraPreview()
        } else {
            Timber.w("Camera permission denied")
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
        checkCameraPermission()
        connectUsbDevice()
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
        cameraProvider?.unbindAll()
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
                binding.tvUsbStatus.text = "LED متصل"
                binding.tvUsbStatus.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.severity_excellent))
                binding.dotUsb.setBackgroundResource(com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_green)
            } else {
                binding.tvUsbStatus.text = "LED غير متصل"
                binding.tvUsbStatus.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.severity_critical))
                binding.dotUsb.setBackgroundResource(com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_purple)
            }
        }
    }

    private fun checkCameraPermission() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA)
            == PackageManager.PERMISSION_GRANTED
        ) {
            startCameraPreview()
        } else {
            cameraPermissionLauncher.launch(Manifest.permission.CAMERA)
        }
    }

    private fun startCameraPreview() {
        val cameraProviderFuture = ProcessCameraProvider.getInstance(this)
        cameraProviderFuture.addListener({
            try {
                cameraProvider = cameraProviderFuture.get()
                val preview = Preview.Builder().build()
                preview.setSurfaceProvider(binding.previewView.surfaceProvider)

                val cameraSelector = CameraSelector.DEFAULT_BACK_CAMERA

                cameraProvider?.unbindAll()
                cameraProvider?.bindToLifecycle(
                    this,
                    cameraSelector,
                    preview
                )
                Timber.i("Camera preview started")
            } catch (e: Exception) {
                Timber.e(e, "Failed to start camera preview")
            }
        }, ContextCompat.getMainExecutor(this))
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

    private fun setupUI() {
        binding.btnStartAnalysis.setOnClickListener {
            val intent = Intent(this, AnalysisActivity::class.java).apply {
                putExtra("diagnosis_mode", viewModel.diagnosisMode.value)
            }
            startActivity(intent)
        }

        binding.btnSettings.setOnClickListener {
            startActivity(Intent(this, SettingsActivity::class.java))
        }

        binding.btnHistory.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.cardComparison.setOnClickListener {
            val reports = recentAdapter.currentList
            if (reports.size >= 2) {
                val intent = Intent(this, com.ebtikar.skinanalyzer.ui.comparison.ComparisonActivity::class.java).apply {
                    putExtra("before_id", reports[1].id)
                    putExtra("after_id", reports[0].id)
                }
                startActivity(intent)
            } else if (reports.size == 1) {
                val intent = Intent(this, com.ebtikar.skinanalyzer.ui.comparison.ComparisonActivity::class.java).apply {
                    putExtra("before_id", reports[0].id)
                }
                startActivity(intent)
            } else {
                com.google.android.material.snackbar.Snackbar.make(
                    binding.root, "قم بإجراء تحليلين على الأقل للمقارنة",
                    com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                ).show()
            }
        }

        binding.cardHistoryQuick.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.cardReport.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }

        binding.layoutDiagnosisWhite.setOnClickListener {
            viewModel.setDiagnosisMode(Constants.DIAGNOSIS_WHITE)
        }

        binding.layoutDiagnosisUV.setOnClickListener {
            viewModel.setDiagnosisMode(Constants.DIAGNOSIS_UV)
        }

        binding.layoutDiagnosisCrossPol.setOnClickListener {
            viewModel.setDiagnosisMode(Constants.DIAGNOSIS_CROSS_POL)
        }

        binding.layoutDiagnosisParallelPol.setOnClickListener {
            viewModel.setDiagnosisMode(Constants.DIAGNOSIS_PARALLEL_POL)
        }

        binding.layoutDiagnosisWoods.setOnClickListener {
            viewModel.setDiagnosisMode(Constants.DIAGNOSIS_WOODS)
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.diagnosisMode.collect { mode ->
                        updateDiagnosisSelection(mode)
                    }
                }

                launch {
                    viewModel.historyCount.collect { count ->
                        binding.tvHistoryCount.text = count.toString()
                        binding.tvHistoryCountQuick.text = "السجل ($count)"
                        binding.btnHistory.isEnabled = count > 0
                    }
                }

                launch {
                    viewModel.todayCount.collect { count ->
                        binding.tvTodayCount.text = count.toString()
                    }
                }

                launch {
                    viewModel.recentReports.collect { reports ->
                        recentAdapter.submitList(reports)
                    }
                }

                launch {
                    viewModel.connectionStatus.collect { status ->
                        binding.tvConnectionStatus.text = status
                    }
                }

                launch {
                    viewModel.hardwareStatus.collect { status ->
                        binding.tvHardwareStatus.text = status
                    }
                }
            }
        }
    }

    private fun updateDiagnosisSelection(mode: String) {
        binding.checkWhite.visibility = View.GONE
        binding.checkUV.visibility = View.GONE
        binding.checkCrossPol.visibility = View.GONE
        binding.checkParallelPol.visibility = View.GONE
        binding.checkWoods.visibility = View.GONE

        when (mode) {
            Constants.DIAGNOSIS_WHITE -> {
                binding.checkWhite.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_white)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.primary))
            }
            Constants.DIAGNOSIS_UV -> {
                binding.checkUV.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_uv)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.accent_purple))
            }
            Constants.DIAGNOSIS_CROSS_POL -> {
                binding.checkCrossPol.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_cross_pol)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.accent_coral))
            }
            Constants.DIAGNOSIS_PARALLEL_POL -> {
                binding.checkParallelPol.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_parallel_pol)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.accent_gold))
            }
            Constants.DIAGNOSIS_WOODS -> {
                binding.checkWoods.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_woods)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.accent_purple))
            }
            Constants.DIAGNOSIS_ALL -> {
                binding.checkWhite.visibility = View.VISIBLE
                binding.checkUV.visibility = View.VISIBLE
                binding.checkCrossPol.visibility = View.VISIBLE
                binding.checkParallelPol.visibility = View.VISIBLE
                binding.checkWoods.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_all)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.primary))
            }
            else -> {
                binding.checkWhite.visibility = View.VISIBLE
                binding.tvSelectedDiagnosisMode.text = getString(com.ebtikar.skinanalyzer.R.string.diagnosis_white)
                binding.tvSelectedDiagnosisMode.setTextColor(getColor(com.ebtikar.skinanalyzer.R.color.primary))
            }
        }
    }
}
