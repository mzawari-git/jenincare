package com.ebtikar.skinanalyzer.ui.settings

import android.content.Intent
import android.os.Bundle
import android.widget.SeekBar
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.camera.CameraSettings
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.data.knowledge.SkinKnowledgeRepository
import com.ebtikar.skinanalyzer.databinding.ActivitySettingsBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.ui.calibration.CalibrationActivity
import com.ebtikar.skinanalyzer.ui.diagnostics.DiagnosticsActivity
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.BuildConfig
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.UpdateChecker
import com.ebtikar.skinanalyzer.util.UpdateInfo
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@AndroidEntryPoint
class SettingsActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySettingsBinding

    @Inject
    lateinit var preferencesManager: PreferencesManager

    @Inject
    lateinit var networkMonitor: NetworkMonitor

    @Inject
    lateinit var serialBusManager: SerialBusManager

    @Inject
    lateinit var knowledgeRepository: SkinKnowledgeRepository

    @Inject
    lateinit var cameraManager: USBCameraManager

    @Inject
    lateinit var updateChecker: UpdateChecker

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivitySettingsBinding.inflate(layoutInflater)
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
        loadCurrentSettings()
        observeDeviceStatus()
    }

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }

        binding.rgAnalysisMode.setOnCheckedChangeListener { _, checkedId ->
            val mode = when (checkedId) {
                com.ebtikar.skinanalyzer.R.id.rb_local -> Constants.ANALYSIS_LOCAL
                com.ebtikar.skinanalyzer.R.id.rb_cloud -> Constants.ANALYSIS_CLOUD
                else -> Constants.ANALYSIS_AUTO
            }
            lifecycleScope.launch {
                preferencesManager.setAnalysisMode(mode)
            }
        }

        binding.rgLanguage.setOnCheckedChangeListener { _, checkedId ->
            val lang = when (checkedId) {
                com.ebtikar.skinanalyzer.R.id.rb_arabic -> Constants.LANG_ARABIC
                else -> Constants.LANG_ENGLISH
            }
            lifecycleScope.launch {
                preferencesManager.setLanguage(lang)
            }
            applyLocale(lang)
        }

        binding.rgProviderSelection.setOnCheckedChangeListener { _, checkedId ->
            val provider = when (checkedId) {
                com.ebtikar.skinanalyzer.R.id.rb_provider_cloud -> "cloud"
                else -> "local"
            }
            lifecycleScope.launch {
                preferencesManager.setProviderSelection(provider)
            }
        }

        binding.btnCalibration.setOnClickListener {
            startActivity(Intent(this, CalibrationActivity::class.java))
        }

        binding.btnDiagnostics.setOnClickListener {
            startActivity(Intent(this, DiagnosticsActivity::class.java))
        }

        binding.btnDemo.setOnClickListener {
            startActivity(Intent(this, com.ebtikar.skinanalyzer.ui.demo.DemoActivity::class.java))
        }

        binding.btnSaveApi.setOnClickListener {
            val url = binding.etApiUrl.text.toString().trim()
            val key = binding.etApiKey.text.toString().trim()
            lifecycleScope.launch {
                preferencesManager.setApiUrl(url)
                preferencesManager.setApiKey(key)
            }
            com.google.android.material.snackbar.Snackbar.make(binding.root, "تم حفظ إعدادات API", com.google.android.material.snackbar.Snackbar.LENGTH_SHORT).show()
        }

        binding.btnCheckKnowledgeUpdate.setOnClickListener {
            binding.btnCheckKnowledgeUpdate.isEnabled = false
            binding.btnCheckKnowledgeUpdate.text = "جارٍ التحقق..."
            lifecycleScope.launch {
                val updated = knowledgeRepository.refreshFromRemote()
                binding.btnCheckKnowledgeUpdate.isEnabled = true
                binding.btnCheckKnowledgeUpdate.text = getString(R.string.action_check_update)
                if (updated) {
                    updateKnowledgeDisplay()
                    com.google.android.material.snackbar.Snackbar.make(
                        binding.root,
                        getString(R.string.knowledge_updated, knowledgeRepository.getVersion()),
                        com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                    ).show()
                } else {
                    com.google.android.material.snackbar.Snackbar.make(
                        binding.root,
                        R.string.knowledge_up_to_date,
                        com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                    ).show()
                }
            }
        }

        // --- Camera Rotation + Zoom (load from prefs, apply to cameraManager + UI) ---
        lifecycleScope.launch {
            val savedRotation = preferencesManager.cameraRotationFlow.first()
            val savedZoomProgress = preferencesManager.cameraZoomProgressFlow.first()
            val zoomRatio = 1.0f + (savedZoomProgress / 120f) * (cameraManager.maxZoom - 1.0f)
            cameraManager.cameraSettings = CameraSettings(
                userRotationOffset = savedRotation,
                zoomRatio = zoomRatio
            )
            selectRotationBtn(savedRotation)
            binding.seekZoom.progress = savedZoomProgress.coerceIn(0, 120)
            binding.tvZoomValue.text = String.format("%.1fx", zoomRatio)
        }

        binding.btnRotate0.setOnClickListener {
            selectRotationBtn(0)
            cameraManager.cameraSettings = cameraManager.cameraSettings.copy(userRotationOffset = 0)
            lifecycleScope.launch { preferencesManager.setCameraRotation(0) }
        }
        binding.btnRotate90.setOnClickListener {
            selectRotationBtn(90)
            cameraManager.cameraSettings = cameraManager.cameraSettings.copy(userRotationOffset = 90)
            lifecycleScope.launch { preferencesManager.setCameraRotation(90) }
        }
        binding.btnRotate180.setOnClickListener {
            selectRotationBtn(180)
            cameraManager.cameraSettings = cameraManager.cameraSettings.copy(userRotationOffset = 180)
            lifecycleScope.launch { preferencesManager.setCameraRotation(180) }
        }
        binding.btnRotate270.setOnClickListener {
            selectRotationBtn(270)
            cameraManager.cameraSettings = cameraManager.cameraSettings.copy(userRotationOffset = 270)
            lifecycleScope.launch { preferencesManager.setCameraRotation(270) }
        }

        // --- Digital Zoom (live preview but save on button click) ---
        binding.seekZoom.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(sb: SeekBar?, progress: Int, fromUser: Boolean) {
                val zoomRatio = 1.0f + (progress / 120f) * (cameraManager.maxZoom - 1.0f)
                binding.tvZoomValue.text = String.format("%.1fx", zoomRatio)
                if (fromUser) {
                    cameraManager.cameraSettings = cameraManager.cameraSettings.copy(zoomRatio = zoomRatio)
                }
            }
            override fun onStartTrackingTouch(sb: SeekBar?) {}
            override fun onStopTrackingTouch(sb: SeekBar?) {}
        })

        // --- Auto Update Toggle ---
        lifecycleScope.launch {
            val enabled = preferencesManager.autoUpdateEnabledFlow.first()
            binding.switchAutoUpdate.isChecked = enabled
        }
        binding.switchAutoUpdate.setOnCheckedChangeListener { _, isChecked ->
            lifecycleScope.launch {
                preferencesManager.setAutoUpdateEnabled(isChecked)
            }
        }

        // --- Update Channel ---
        binding.rgUpdateChannel.setOnCheckedChangeListener { _, checkedId ->
            val channel = when (checkedId) {
                com.ebtikar.skinanalyzer.R.id.rb_beta -> "beta"
                else -> "stable"
            }
            lifecycleScope.launch {
                preferencesManager.setUpdateChannel(channel)
            }
        }

        // --- Manual Check for Update ---
        binding.btnCheckAppUpdate.setOnClickListener {
            binding.btnCheckAppUpdate.isEnabled = false
            binding.btnCheckAppUpdate.text = "جارٍ الفحص..."
            lifecycleScope.launch {
                val channel = preferencesManager.updateChannelFlow.first()
                val updateInfo = updateChecker.checkForUpdate(channel)
                binding.btnCheckAppUpdate.isEnabled = true
                binding.btnCheckAppUpdate.text = getString(R.string.action_check_app_update)
                if (updateInfo != null && updateChecker.isNewerVersion(updateInfo.latestVersion)) {
                    binding.btnCheckAppUpdate.text = "جارٍ التحديث..."
                    binding.btnCheckAppUpdate.isEnabled = false
                    if (isFinishing || isDestroyed) return@launch
                    val progressDialog = android.app.ProgressDialog(this@SettingsActivity).apply {
                        setTitle("جاري تحميل الإصدار v${updateInfo.latestVersion}")
                        setMessage("يرجى الانتظار...")
                        setProgressStyle(android.app.ProgressDialog.STYLE_HORIZONTAL)
                        setMax(100)
                        setCancelable(false)
                        show()
                    }
                    val uri: android.net.Uri? = withContext(Dispatchers.IO) {
                        updateChecker.downloadApk(updateInfo) { progress ->
                            runOnUiThread { progressDialog.progress = (progress * 100).toInt() }
                        }
                    }
                    progressDialog.dismiss()
                    binding.btnCheckAppUpdate.isEnabled = true
                    binding.btnCheckAppUpdate.text = getString(R.string.action_check_app_update)
                    if (uri != null) {
                        updateChecker.installApk(uri)
                    } else {
                        android.app.AlertDialog.Builder(this@SettingsActivity)
                            .setTitle("خطأ")
                            .setMessage("فشل تحميل التحديث. تأكد من اتصالك بالإنترنت وحاول مرة أخرى.")
                            .setPositiveButton("حسناً", null)
                            .show()
                    }
                } else {
                    com.google.android.material.snackbar.Snackbar.make(
                        binding.root,
                        "التطبيق محدث — الإصدار ${updateChecker.getCurrentVersion()}",
                        com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                    ).show()
                }
                preferencesManager.setLastUpdateCheck(System.currentTimeMillis())
                updateLastCheckDisplay()
            }
        }

        // --- Rollback: رجوع لإصدار سابق ---
        binding.btnRollback.setOnClickListener {
            binding.btnRollback.isEnabled = false
            binding.btnRollback.text = "جارٍ تحميل الإصدارات..."
            lifecycleScope.launch {
                val releases = updateChecker.fetchAllReleases()
                binding.btnRollback.isEnabled = true
                binding.btnRollback.text = "⬇ رجوع لإصدار سابق"
                if (releases.isEmpty()) {
                    com.google.android.material.snackbar.Snackbar.make(
                        binding.root,
                        "لا توجد إصدارات سابقة متاحة أو تأكد من اتصالك بالإنترنت",
                        com.google.android.material.snackbar.Snackbar.LENGTH_LONG
                    ).show()
                } else {
                    showRollbackDialog(releases)
                }
            }
        }

        // --- Save Camera Settings Button ---
        binding.btnSaveCameraSettings.setOnClickListener {
            lifecycleScope.launch {
                preferencesManager.setCameraRotation(cameraManager.cameraSettings.userRotationOffset)
                preferencesManager.setCameraZoomProgress(binding.seekZoom.progress)
                preferencesManager.setFaceValidationEnabled(binding.switchFaceValidation.isChecked)
                preferencesManager.setFaceValidationThreshold(binding.seekFaceThreshold.progress)
                com.google.android.material.snackbar.Snackbar.make(
                    binding.root,
                    "تم حفظ إعدادات الكاميرا",
                    com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                ).show()
            }
        }

        // --- Face Validation Toggle ---
        lifecycleScope.launch {
            val enabled = preferencesManager.faceValidationEnabledFlow.first()
            binding.switchFaceValidation.isChecked = enabled
        }
        binding.switchFaceValidation.setOnCheckedChangeListener { _, isChecked ->
            lifecycleScope.launch {
                preferencesManager.setFaceValidationEnabled(isChecked)
            }
        }

        // --- Face Validation Threshold ---
        lifecycleScope.launch {
            val threshold = preferencesManager.faceValidationThresholdFlow.first()
            binding.seekFaceThreshold.progress = threshold
            binding.tvFaceThreshold.text = "$threshold"
        }
        binding.seekFaceThreshold.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(sb: SeekBar?, progress: Int, fromUser: Boolean) {
                binding.tvFaceThreshold.text = "$progress"
                if (fromUser) {
                    lifecycleScope.launch { preferencesManager.setFaceValidationThreshold(progress) }
                }
            }
            override fun onStartTrackingTouch(sb: SeekBar?) {}
            override fun onStopTrackingTouch(sb: SeekBar?) {}
        })

        // --- Scan Display Settings ---
        binding.rgScanOverlayStyle.setOnCheckedChangeListener { _, checkedId ->
            val style = when (checkedId) {
                com.ebtikar.skinanalyzer.R.id.rbOverlayProfessional -> Constants.SCAN_OVERLAY_PROFESSIONAL
                com.ebtikar.skinanalyzer.R.id.rbOverlayMinimal -> Constants.SCAN_OVERLAY_MINIMAL
                else -> Constants.SCAN_OVERLAY_CUSTOM
            }
            if (style != Constants.SCAN_OVERLAY_CUSTOM) {
                applyOverlayPreset(style)
            }
        }

        binding.switchScanReminder.setOnCheckedChangeListener { _, isChecked ->
            binding.layoutReminderInterval.visibility = if (isChecked) android.view.View.VISIBLE else android.view.View.GONE
        }

        binding.btnSaveScanDisplay.setOnClickListener {
            lifecycleScope.launch {
                val style = when (binding.rgScanOverlayStyle.checkedRadioButtonId) {
                    com.ebtikar.skinanalyzer.R.id.rbOverlayProfessional -> Constants.SCAN_OVERLAY_PROFESSIONAL
                    com.ebtikar.skinanalyzer.R.id.rbOverlayMinimal -> Constants.SCAN_OVERLAY_MINIMAL
                    else -> Constants.SCAN_OVERLAY_CUSTOM
                }
                preferencesManager.setScanOverlayStyle(style)
                preferencesManager.setShowFaceMesh(binding.switchShowFaceMesh.isChecked)
                preferencesManager.setShowMedicalLens(binding.switchShowMedicalLens.isChecked)
                preferencesManager.setShowScanGrid(binding.switchShowScanGrid.isChecked)
                preferencesManager.setShowScanRings(binding.switchShowScanRings.isChecked)
                preferencesManager.setShowSpectralGraph(binding.switchShowSpectralGraph.isChecked)
                preferencesManager.setShowMedicalIndicators(binding.switchShowMedicalIndicators.isChecked)
                preferencesManager.setShowScanDataPanel(binding.switchShowScanDataPanel.isChecked)
                preferencesManager.setVoiceGuideEnabled(binding.switchVoiceGuide.isChecked)

                val reminderEnabled = binding.switchScanReminder.isChecked
                preferencesManager.setScanReminderEnabled(reminderEnabled)
                val intervalHours = binding.sliderReminderInterval.value.toInt()
                preferencesManager.setScanReminderIntervalHours(intervalHours)
                if (reminderEnabled) {
                    com.ebtikar.skinanalyzer.util.ScanReminderWorker.schedule(this@SettingsActivity, intervalHours)
                } else {
                    com.ebtikar.skinanalyzer.util.ScanReminderWorker.cancel(this@SettingsActivity)
                }

                com.google.android.material.snackbar.Snackbar.make(
                    binding.root,
                    "تم حفظ إعدادات شاشة الفحص",
                    com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
                ).show()
            }
        }
    }

    private fun showUpdateFoundDialog(updateInfo: UpdateInfo) {
        val message = buildString {
            append("الإصدار الجديد: v${updateInfo.latestVersion}")
            if (!updateInfo.releaseNotes.isNullOrBlank()) {
                append("\n\n${updateInfo.releaseNotes}")
            }
        }

        android.app.AlertDialog.Builder(this)
            .setTitle("تحديث متوفر")
            .setMessage(message)
            .setPositiveButton("تحميل") { _, _ ->
                val progressDialog = android.app.ProgressDialog(this).apply {
                    setTitle("جاري التحميل")
                    setMessage("يرجى الانتظار...")
                    setProgressStyle(android.app.ProgressDialog.STYLE_HORIZONTAL)
                    setMax(100)
                    setCancelable(false)
                    show()
                }
                lifecycleScope.launch {
                    val uri: android.net.Uri? = withContext(Dispatchers.IO) {
                        updateChecker.downloadApk(updateInfo) { progress ->
                            runOnUiThread { progressDialog.progress = (progress * 100).toInt() }
                        }
                    }
                    progressDialog.dismiss()
                    if (uri != null) {
                        updateChecker.installApk(uri)
                    } else {
                        android.app.AlertDialog.Builder(this@SettingsActivity)
                            .setTitle("خطأ")
                            .setMessage("فشل تحميل التحديث. تأكد من اتصالك بالإنترنت وحاول مرة أخرى.")
                            .setPositiveButton("حسناً", null)
                            .show()
                    }
                }
            }
            .setNegativeButton("لاحقاً", null)
            .show()
    }

    /**
     * Shows a list dialog of all available previous versions.
     * The user picks a version → confirmation dialog → download & install.
     */
    private fun showRollbackDialog(releases: List<UpdateInfo>) {
        val current = updateChecker.getCurrentVersion()
        val labels = releases.map { release ->
            val suffix = if (release.isPrerelease) " (تجريبي)" else " (مستقر)"
            "v${release.latestVersion}$suffix"
        }.toTypedArray()

        android.app.AlertDialog.Builder(this)
            .setTitle("اختر الإصدار للرجوع إليه")
            .setMessage("الإصدار الحالي: v$current\nسيتم تنزيل الإصدار المحدد وتثبيته.")
            .setItems(labels) { _, index ->
                val selected = releases[index]
                // Confirmation step
                android.app.AlertDialog.Builder(this)
                    .setTitle("تأكيد الرجوع")
                    .setMessage(
                        "هل تريد الرجوع من v$current إلى v${selected.latestVersion}؟\n\n" +
                        "سيتم تنزيل الإصدار وتثبيته. قد يحتاج التطبيق لإعادة التشغيل."
                    )
                    .setPositiveButton("رجوع") { _, _ ->
                        downloadAndInstallVersion(selected)
                    }
                    .setNegativeButton("إلغاء", null)
                    .show()
            }
            .setNegativeButton("إلغاء", null)
            .show()
    }

    /**
     * Downloads a specific release APK and triggers installation.
     */
    private fun downloadAndInstallVersion(updateInfo: UpdateInfo) {
        val progressDialog = android.app.ProgressDialog(this).apply {
            setTitle("جاري تنزيل v${updateInfo.latestVersion}")
            setMessage("يرجى الانتظار...")
            setProgressStyle(android.app.ProgressDialog.STYLE_HORIZONTAL)
            setMax(100)
            setCancelable(false)
            show()
        }
        lifecycleScope.launch {
            val uri: android.net.Uri? = withContext(Dispatchers.IO) {
                updateChecker.downloadApk(updateInfo) { progress ->
                    runOnUiThread { progressDialog.progress = (progress * 100).toInt() }
                }
            }
            progressDialog.dismiss()
            if (uri != null) {
                updateChecker.installApk(uri)
            } else {
                android.app.AlertDialog.Builder(this@SettingsActivity)
                    .setTitle("خطأ في التنزيل")
                    .setMessage("فشل تنزيل الإصدار v${updateInfo.latestVersion}. تأكد من اتصالك بالإنترنت وحاول مرة أخرى.")
                    .setPositiveButton("حسناً", null)
                    .show()
            }
        }
    }

    private fun selectRotationBtn(active: Int) {
        val buttons = listOf(binding.btnRotate0, binding.btnRotate90, binding.btnRotate180, binding.btnRotate270)
        buttons.forEachIndexed { i, b ->
            b.alpha = if (i * 90 == active) 1.0f else 0.4f
            b.isChecked = i * 90 == active
        }
    }

    private fun updateKnowledgeDisplay() {
        lifecycleScope.launch {
            val knowledge = knowledgeRepository.getKnowledge()
            binding.tvKnowledgeVersion.text = "v${knowledge.version}"
            binding.tvKnowledgeLastUpdated.text = knowledge.lastUpdated.ifBlank { "—" }
            val metrics = knowledge.metrics.size
            val conditions = knowledge.skinConditions.size
            val ingredients = knowledge.ingredients.size
            val products = knowledge.products.size
            binding.tvKnowledgeStats.text = getString(
                R.string.label_knowledge_stats,
                metrics, conditions, ingredients, products
            )
        }
    }

    private fun observeDeviceStatus() {
        networkMonitor.isOnlineFlow.onEach { isOnline ->
            binding.tvConnectionStatus.text = if (isOnline) "متصل" else "غير متصل"
            binding.dotConnection.setBackgroundResource(
                if (isOnline)
                    com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_green
                else
                    com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_purple
            )
        }.launchIn(lifecycleScope)

        binding.tvHardwareStatus.text = if (serialBusManager.isConnected) "العتاد جاهز" else "لا يوجد جهاز USB"
    }

    private fun loadCurrentSettings() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    preferencesManager.analysisModeFlow.collect { mode ->
                        val modeText = when (mode) {
                            Constants.ANALYSIS_LOCAL -> {
                                binding.rbLocal.isChecked = true
                                "محلي"
                            }
                            Constants.ANALYSIS_CLOUD -> {
                                binding.rbCloud.isChecked = true
                                "سحابي"
                            }
                            else -> {
                                binding.rbAuto.isChecked = true
                                "تلقائي"
                            }
                        }
                        binding.tvAnalysisMode.text = modeText
                    }
                }

                launch {
                    preferencesManager.languageFlow.collect { lang ->
                        when (lang) {
                            Constants.LANG_ARABIC -> binding.rbArabic.isChecked = true
                            else -> binding.rbEnglish.isChecked = true
                        }
                    }
                }

                launch {
                    preferencesManager.providerSelectionFlow.collect { provider ->
                        when (provider) {
                            "cloud" -> binding.rbProviderCloud.isChecked = true
                            else -> binding.rbProviderLocal.isChecked = true
                        }
                    }
                }

                launch {
                    preferencesManager.apiUrlFlow.collect { url ->
                        binding.etApiUrl.setText(url)
                    }
                }

                launch {
                    preferencesManager.apiKeyFlow.collect { key ->
                        binding.etApiKey.setText(key)
                    }
                }

                launch {
                    preferencesManager.autoUpdateEnabledFlow.collect { enabled ->
                        binding.switchAutoUpdate.isChecked = enabled
                    }
                }

                launch {
                    preferencesManager.scanOverlayStyleFlow.collect { style ->
                        applyOverlayPreset(style)
                    }
                }

                launch {
                    preferencesManager.showFaceMeshFlow.collect { shown ->
                        binding.switchShowFaceMesh.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showMedicalLensFlow.collect { shown ->
                        binding.switchShowMedicalLens.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showScanGridFlow.collect { shown ->
                        binding.switchShowScanGrid.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showScanRingsFlow.collect { shown ->
                        binding.switchShowScanRings.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showSpectralGraphFlow.collect { shown ->
                        binding.switchShowSpectralGraph.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showMedicalIndicatorsFlow.collect { shown ->
                        binding.switchShowMedicalIndicators.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.showScanDataPanelFlow.collect { shown ->
                        binding.switchShowScanDataPanel.isChecked = shown
                    }
                }

                launch {
                    preferencesManager.voiceGuideFlow.collect { enabled ->
                        binding.switchVoiceGuide.isChecked = enabled
                    }
                }

                launch {
                    preferencesManager.scanReminderEnabledFlow.collect { enabled ->
                        binding.switchScanReminder.isChecked = enabled
                        binding.layoutReminderInterval.visibility = if (enabled) android.view.View.VISIBLE else android.view.View.GONE
                    }
                }

                launch {
                    preferencesManager.scanReminderIntervalHoursFlow.collect { hours ->
                        binding.sliderReminderInterval.value = hours.toFloat()
                    }
                }

                launch {
                    preferencesManager.updateChannelFlow.collect { channel ->
                        when (channel) {
                            "beta" -> binding.rbBeta.isChecked = true
                            else -> binding.rbStable.isChecked = true
                        }
                    }
                }

                launch {
                    preferencesManager.lastUpdateCheckFlow.collect {
                        updateLastCheckDisplay()
                    }
                }
            }
        }

        binding.tvDeviceInfo.text = "${Constants.DEVICE_BRAND} ${Constants.DEVICE_MODEL}\n${Constants.DEVICE_EDITION}"
        binding.tvResolution.text = "${Constants.SCREEN_WIDTH} x ${Constants.SCREEN_HEIGHT}"
        binding.tvCurrentVersion.text = "v${BuildConfig.VERSION_NAME}"
        updateLastCheckDisplay()
        updateKnowledgeDisplay()
    }

    private fun applyOverlayPreset(style: String) {
        val faceMesh = style == "professional"
        val medLens = style == "professional"
        val scanGrid = style == "minimal"
        val scanRings = false
        val spectralGraph = style == "professional"
        val medIndicators = true
        val scanDataPanel = true
        binding.switchShowFaceMesh.isChecked = faceMesh
        binding.switchShowMedicalLens.isChecked = medLens
        binding.switchShowScanGrid.isChecked = scanGrid
        binding.switchShowScanRings.isChecked = scanRings
        binding.switchShowSpectralGraph.isChecked = spectralGraph
        binding.switchShowMedicalIndicators.isChecked = medIndicators
        binding.switchShowScanDataPanel.isChecked = scanDataPanel
        binding.rgScanOverlayStyle.check(
            when (style) {
                "professional" -> com.ebtikar.skinanalyzer.R.id.rbOverlayProfessional
                "minimal" -> com.ebtikar.skinanalyzer.R.id.rbOverlayMinimal
                else -> com.ebtikar.skinanalyzer.R.id.rbOverlayCustom
            }
        )
    }

    private fun updateLastCheckDisplay() {
        lifecycleScope.launch {
            val timestamp = preferencesManager.lastUpdateCheckFlow.first()
            binding.tvLastUpdateCheck.text = if (timestamp > 0L) {
                val sdf = java.text.SimpleDateFormat("yyyy-MM-dd HH:mm", java.util.Locale("ar"))
                sdf.format(java.util.Date(timestamp))
            } else {
                getString(R.string.never_checked)
            }
        }
    }

    private fun applyLocale(lang: String) {
        val locale = java.util.Locale(lang)
        java.util.Locale.setDefault(locale)
        val config = android.content.res.Configuration(resources.configuration)
        config.setLocale(locale)
        resources.updateConfiguration(config, resources.displayMetrics)
        com.google.android.material.snackbar.Snackbar.make(
            binding.root,
            if (lang == Constants.LANG_ARABIC) "تم تغيير اللغة — أعد تشغيل التطبيق للتطبيق الكامل" else "Language changed — restart app for full effect",
            com.google.android.material.snackbar.Snackbar.LENGTH_SHORT
        ).show()
    }
}
