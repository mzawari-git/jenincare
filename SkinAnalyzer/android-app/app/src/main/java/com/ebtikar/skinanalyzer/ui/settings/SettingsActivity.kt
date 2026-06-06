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
import com.ebtikar.skinanalyzer.ui.calibration.CalibrationActivity
import com.ebtikar.skinanalyzer.ui.diagnostics.DiagnosticsActivity
import com.ebtikar.skinanalyzer.util.Constants
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class SettingsActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySettingsBinding

    @Inject
    lateinit var preferencesManager: PreferencesManager

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
        }

        binding.btnCalibration.setOnClickListener {
            startActivity(Intent(this, CalibrationActivity::class.java))
        }

        binding.btnDiagnostics.setOnClickListener {
            startActivity(Intent(this, DiagnosticsActivity::class.java))
        }
    }

    private fun loadCurrentSettings() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    preferencesManager.analysisModeFlow.collect { mode ->
                        when (mode) {
                            Constants.ANALYSIS_LOCAL -> binding.rbLocal.isChecked = true
                            Constants.ANALYSIS_CLOUD -> binding.rbCloud.isChecked = true
                            else -> binding.rbAuto.isChecked = true
                        }
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
            }
        }

        binding.tvDeviceInfo.text = "${Constants.DEVICE_BRAND} ${Constants.DEVICE_MODEL}\n${Constants.DEVICE_EDITION}"
        binding.tvResolution.text = "${Constants.SCREEN_WIDTH} x ${Constants.SCREEN_HEIGHT}"
    }
}
