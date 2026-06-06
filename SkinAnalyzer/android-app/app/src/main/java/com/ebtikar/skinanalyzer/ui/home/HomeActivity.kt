package com.ebtikar.skinanalyzer.ui.home

import android.content.Intent
import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.ebtikar.skinanalyzer.databinding.ActivityHomeBinding
import com.ebtikar.skinanalyzer.ui.analysis.AnalysisActivity
import com.ebtikar.skinanalyzer.ui.history.HistoryActivity
import com.ebtikar.skinanalyzer.ui.settings.SettingsActivity
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class HomeActivity : AppCompatActivity() {

    private lateinit var binding: ActivityHomeBinding
    private val viewModel: HomeViewModel by viewModels()

    @Inject
    lateinit var networkMonitor: NetworkMonitor

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

        setupUI()
        observeViewModel()
    }

    override fun onResume() {
        super.onResume()
        viewModel.loadHistory()
    }

    private fun setupUI() {
        binding.btnStartAnalysis.setOnClickListener {
            startActivity(Intent(this, AnalysisActivity::class.java))
        }

        binding.btnSettings.setOnClickListener {
            startActivity(Intent(this, SettingsActivity::class.java))
        }

        binding.btnHistory.setOnClickListener {
            startActivity(Intent(this, HistoryActivity::class.java))
        }
    }

    private fun observeViewModel() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.connectionStatus.collect { status ->
                        binding.tvConnectionStatus.text = status
                        binding.dotConnection.setBackgroundResource(
                            if (networkMonitor.isOnline())
                                com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_green
                            else
                                com.ebtikar.skinanalyzer.R.drawable.shape_status_dot_purple
                        )
                    }
                }

                launch {
                    viewModel.hardwareStatus.collect { status ->
                        binding.tvHardwareStatus.text = status
                    }
                }

                launch {
                    viewModel.analysisMode.collect { mode ->
                        binding.tvAnalysisMode.text = mode
                    }
                }

                launch {
                    viewModel.historyCount.collect { count ->
                        binding.tvHistoryCount.text = count.toString()
                        binding.btnHistory.isEnabled = count > 0
                    }
                }
            }
        }
    }
}
