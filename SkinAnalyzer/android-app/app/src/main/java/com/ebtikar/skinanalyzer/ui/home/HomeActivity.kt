package com.ebtikar.skinanalyzer.ui.home

import android.content.Intent
import android.content.res.ColorStateList
import android.graphics.Color
import android.os.Bundle
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityHomeBinding
import com.ebtikar.skinanalyzer.ui.analysis.AnalysisActivity
import com.ebtikar.skinanalyzer.ui.diagnostics.DiagnosticsActivity
import com.ebtikar.skinanalyzer.ui.history.HistoryActivity
import com.ebtikar.skinanalyzer.ui.settings.SettingsActivity
import com.ebtikar.skinanalyzer.util.Constants
import com.google.android.material.button.MaterialButton
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import timber.log.Timber

@AndroidEntryPoint
class HomeActivity : AppCompatActivity() {

    private lateinit var binding: ActivityHomeBinding
    private val viewModel: HomeViewModel by viewModels()
    private var scanEnabled = true

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityHomeBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupUI()
        observeViewModel()
    }

    private fun setupUI() {
        val startScan = {
            if (scanEnabled) {
                scanEnabled = false
                binding.btnStartScan.postDelayed({ scanEnabled = true }, 1000)
                try {
                    val intent = Intent(this, AnalysisActivity::class.java)
                        .putExtra("diagnosis_mode", viewModel.diagnosisMode.value)
                    startActivity(intent)
                } catch (e: Exception) {
                    scanEnabled = true
                    Timber.e(e, "Failed to start scan")
                }
            }
        }
        binding.cardQuickScan.setOnClickListener { startScan() }
        binding.btnStartScan.setOnClickListener { startScan() }

        binding.btnHistory.setOnClickListener {
            val intent = Intent(this, HistoryActivity::class.java)
            startActivity(intent)
        }

        binding.btnSettings.setOnClickListener {
            val intent = Intent(this, SettingsActivity::class.java)
            startActivity(intent)
        }

        binding.btnDiagnostics.setOnClickListener {
            val intent = Intent(this, DiagnosticsActivity::class.java)
            startActivity(intent)
        }

        binding.btnModeAll.setOnClickListener { viewModel.setDiagnosisMode(Constants.DIAGNOSIS_ALL) }
        binding.btnModeWhite.setOnClickListener { viewModel.setDiagnosisMode(Constants.DIAGNOSIS_WHITE) }
        binding.btnModeUv.setOnClickListener { viewModel.setDiagnosisMode(Constants.DIAGNOSIS_UV) }
        binding.btnModeWoods.setOnClickListener { viewModel.setDiagnosisMode(Constants.DIAGNOSIS_WOODS) }
        binding.btnModePol.setOnClickListener { viewModel.setDiagnosisMode(Constants.DIAGNOSIS_CROSS_POL) }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.avgScore.collect { score ->
                        binding.tvAvgScore.text = score?.let { "${it.toInt()}%" } ?: "--"
                    }
                }
                launch {
                    viewModel.todayCount.collect { count ->
                        binding.tvTodayCount.text = count.toString()
                    }
                }
                launch {
                    viewModel.historyCount.collect { count ->
                        binding.tvHistoryCount.text = count.toString()
                    }
                }
                launch {
                    viewModel.connectionStatus.collect { status ->
                        binding.tvConnectionStatus.text = "الشبكة: ${status.toArabicStatus()}"
                    }
                }
                launch {
                    viewModel.hardwareStatus.collect { status ->
                        binding.tvHardwareStatus.text = "الإضاءة: ${status.toArabicStatus()}"
                    }
                }
                launch {
                    viewModel.diagnosisMode.collect { mode ->
                        updateDiagnosisButtons(mode)
                    }
                }
            }
        }
    }

    private fun updateDiagnosisButtons(mode: String) {
        val buttons = mapOf(
            Constants.DIAGNOSIS_ALL to binding.btnModeAll,
            Constants.DIAGNOSIS_WHITE to binding.btnModeWhite,
            Constants.DIAGNOSIS_UV to binding.btnModeUv,
            Constants.DIAGNOSIS_WOODS to binding.btnModeWoods,
            Constants.DIAGNOSIS_CROSS_POL to binding.btnModePol
        )
        buttons.forEach { (buttonMode, button) ->
            val selected = buttonMode == mode
            styleModeButton(button, selected)
        }
    }

    private fun styleModeButton(button: MaterialButton, selected: Boolean) {
        val primary = getColor(R.color.primary)
        val text = getColor(R.color.text_primary)
        button.backgroundTintList = ColorStateList.valueOf(if (selected) primary else Color.TRANSPARENT)
        button.strokeColor = ColorStateList.valueOf(if (selected) primary else getColor(R.color.border_card))
        button.setTextColor(if (selected) Color.WHITE else text)
        button.iconTint = ColorStateList.valueOf(if (selected) Color.WHITE else text)
    }

    private fun String.toArabicStatus(): String = when (this) {
        "Connected" -> "متصل"
        "Disconnected" -> "غير متصل"
        "Ready" -> "جاهزة"
        "No USB Device" -> "USB غير متصل"
        "Checking..." -> "جاري الفحص..."
        "Initializing..." -> "جاري التهيئة..."
        else -> this
    }
}
