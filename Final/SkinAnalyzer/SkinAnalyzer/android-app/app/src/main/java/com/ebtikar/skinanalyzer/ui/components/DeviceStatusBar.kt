package com.ebtikar.skinanalyzer.ui.components

import android.content.Context
import android.util.AttributeSet
import android.view.LayoutInflater
import android.widget.LinearLayout
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ViewDeviceStatusBarBinding
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.flow.launchIn
import kotlinx.coroutines.flow.onEach

class DeviceStatusBar @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : LinearLayout(context, attrs, defStyleAttr) {

    private lateinit var binding: ViewDeviceStatusBarBinding
    private val scope = CoroutineScope(Dispatchers.Main + SupervisorJob())

    init {
        binding = ViewDeviceStatusBarBinding.inflate(LayoutInflater.from(context), this, true)
        orientation = HORIZONTAL
        setPadding(16, 8, 16, 8)
        setBackgroundColor(context.getColor(R.color.surface_card))
    }

    fun bind(networkMonitor: NetworkMonitor, serialBusManager: SerialBusManager, providerName: String = "") {
        networkMonitor.isOnlineFlow.onEach { isOnline ->
            binding.dotStatus.setBackgroundResource(
                if (isOnline) R.drawable.shape_status_dot_green else R.drawable.shape_status_dot_purple
            )
            binding.tvStatusBar.text = if (isOnline) "متصل — ZMLH02 Max" else "غير متصل"
        }.launchIn(scope)

        if (providerName.isNotEmpty()) {
            binding.tvEngine.text = providerName.replace("_", " ")
        }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        scope.cancel()
    }
}
