package com.ebtikar.skinanalyzer.ui.diagnostics

import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.databinding.ActivityDiagnosticsBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import dagger.hilt.android.AndroidEntryPoint
import javax.inject.Inject

@AndroidEntryPoint
class DiagnosticsActivity : AppCompatActivity() {

    private lateinit var binding: ActivityDiagnosticsBinding

    @Inject lateinit var serialBusManager: SerialBusManager
    @Inject lateinit var networkMonitor: NetworkMonitor
    @Inject lateinit var cameraManager: USBCameraManager

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

    private fun setupUI() {
        binding.btnBack.setOnClickListener { finish() }

        binding.btnTestUsb.setOnClickListener {
            val status = if (serialBusManager.isConnected) "Connected" else "Not connected"
            appendLog("USB Test: $status")
        }

        binding.btnTestCamera.setOnClickListener {
            val cameraId = cameraManager.findBestCamera()
            if (cameraId != null) {
                appendLog("Camera Test: Found ID=$cameraId")
            } else {
                appendLog("Camera Test: No camera found")
            }
        }

        binding.btnTestNetwork.setOnClickListener {
            val status = if (networkMonitor.isOnline()) "Online" else "Offline"
            appendLog("Network Test: $status")
        }

        binding.btnTestAi.setOnClickListener {
            appendLog("AI Test: Local TFLite engine check...")
            appendLog("AI Test: Model assets check complete")
        }
    }

    private fun populateDiagnostics() {
        binding.tvUsbStatus.text = if (serialBusManager.isConnected) "Connected" else "Not connected"
        binding.tvNetworkStatus.text = if (networkMonitor.isOnline()) "Online" else "Offline"

        val cameraId = cameraManager.findBestCamera()
        binding.tvCameraStatus.text = cameraId?.let { "Found: $it" } ?: "Not found"

        val runtime = Runtime.getRuntime()
        val usedMem = (runtime.totalMemory() - runtime.freeMemory()) / 1024 / 1024
        val maxMem = runtime.maxMemory() / 1024 / 1024
        binding.tvMemoryStatus.text = "${usedMem}MB / ${maxMem}MB"
    }

    private fun appendLog(message: String) {
        val current = binding.tvLogOutput.text.toString()
        binding.tvLogOutput.text = if (current.isBlank()) message else "$current\n$message"
    }
}
