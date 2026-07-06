package com.ebtikar.skinanalyzer.ui.camera

import android.app.Dialog
import android.os.Bundle
import android.widget.Button
import android.widget.SeekBar
import android.widget.TextView
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.DialogFragment
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.camera.CameraSettings
import timber.log.Timber

class CameraSettingsDialog : DialogFragment() {

    private var currentRotation: Int = 0
    private var currentZoom: Float = 1.0f
    private var maxZoom: Float = 4.0f
    private var onApply: ((CameraSettings) -> Unit)? = null

    fun setInitialSettings(settings: CameraSettings, maxZoom: Float) {
        currentRotation = settings.userRotationOffset
        currentZoom = settings.zoomRatio
        this.maxZoom = maxZoom.coerceAtLeast(1.0f)
    }

    fun onSettingsApplied(callback: (CameraSettings) -> Unit) {
        onApply = callback
    }

    override fun onCreateDialog(savedInstanceState: Bundle?): Dialog {
        val builder = AlertDialog.Builder(requireActivity())
        builder.setTitle("إعدادات الكاميرا")

        val view = layoutInflater.inflate(R.layout.dialog_camera_settings, null)
        builder.setView(view)

        val tvRotationLabel = view.findViewById<TextView>(R.id.tvRotationValue)
        val btnRotate0 = view.findViewById<Button>(R.id.btnRotate0)
        val btnRotate90 = view.findViewById<Button>(R.id.btnRotate90)
        val btnRotate180 = view.findViewById<Button>(R.id.btnRotate180)
        val btnRotate270 = view.findViewById<Button>(R.id.btnRotate270)
        val seekZoom = view.findViewById<SeekBar>(R.id.seekZoom)
        val tvZoomValue = view.findViewById<TextView>(R.id.tvZoomValue)
        val btnClose = view.findViewById<Button>(R.id.btnCloseSettings)

        fun updateRotationLabel() {
            tvRotationLabel.text = "${currentRotation}°"
        }
        fun selectRotationBtn(active: Int) {
            listOf(btnRotate0, btnRotate90, btnRotate180, btnRotate270).forEachIndexed { i, b ->
                b.alpha = if (i * 90 == active) 1.0f else 0.5f
            }
        }

        currentRotation = ((currentRotation % 360) + 360) % 360
        selectRotationBtn(currentRotation)
        updateRotationLabel()

        btnRotate0.setOnClickListener { currentRotation = 0; selectRotationBtn(0); updateRotationLabel() }
        btnRotate90.setOnClickListener { currentRotation = 90; selectRotationBtn(90); updateRotationLabel() }
        btnRotate180.setOnClickListener { currentRotation = 180; selectRotationBtn(180); updateRotationLabel() }
        btnRotate270.setOnClickListener { currentRotation = 270; selectRotationBtn(270); updateRotationLabel() }

        seekZoom.max = 120
        val initialLevel = if (currentZoom <= 1.0f) 0f
            else (currentZoom - 1.0f) * 6.0f / (maxZoom - 1.0f).coerceAtLeast(1f)
        seekZoom.progress = (initialLevel * 10 + 60).toInt().coerceIn(0, 120)
        tvZoomValue.text = String.format("%+.1fx", initialLevel)
        seekZoom.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(sb: SeekBar?, progress: Int, fromUser: Boolean) {
                val zoomLevel = (progress - 60) / 10f
                currentZoom = if (zoomLevel <= 0f) 1.0f else 1.0f + zoomLevel * (maxZoom - 1.0f) / 6.0f
                tvZoomValue.text = String.format("%+.1fx", zoomLevel)
            }
            override fun onStartTrackingTouch(sb: SeekBar?) {}
            override fun onStopTrackingTouch(sb: SeekBar?) {
                onApply?.invoke(CameraSettings(currentRotation, currentZoom))
            }
        })

        btnClose.setOnClickListener {
            onApply?.invoke(CameraSettings(currentRotation, currentZoom))
            dismiss()
        }

        return builder.create()
    }
}
