package com.ebtikar.skinanalyzer.camera

data class CameraSettings(
    val userRotationOffset: Int = 0,
    val zoomRatio: Float = 1.0f,
) {
    val displayRotation: Int
        get() = ((userRotationOffset % 360) + 360) % 360
}
