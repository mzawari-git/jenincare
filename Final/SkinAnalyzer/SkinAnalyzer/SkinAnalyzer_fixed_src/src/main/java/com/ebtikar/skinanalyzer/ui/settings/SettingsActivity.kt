package com.ebtikar.skinanalyzer.ui.settings

import android.content.Intent
import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.databinding.ActivitySettingsBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.ui.calibration.CalibrationActivity
import com.ebtikar.skinanalyzer.ui.diagnostics.DiagnosticsActivity
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach
import kotlinx.coroutines.launch
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
            }
        }

        binding.tvDeviceInfo.text = "${Constants.DEVICE_BRAND} ${Constants.DEVICE_MODEL}\n${Constants.DEVICE_EDITION}"
        binding.tvResolution.text = "${Constants.SCREEN_WIDTH} x ${Constants.SCREEN_HEIGHT}"
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
