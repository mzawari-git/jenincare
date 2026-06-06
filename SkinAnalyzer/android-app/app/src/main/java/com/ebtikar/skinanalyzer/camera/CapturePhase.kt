package com.ebtikar.skinanalyzer.camera

import com.ebtikar.skinanalyzer.hardware.LightSpectrum

data class CapturePhase(
    val index: Int,
    val spectrum: LightSpectrum,
    val settlingWindowMs: Long,
    val status: Status = Status.PENDING
) {
    enum class Status {
        PENDING,
        ACTIVATING,
        SETTLING,
        CAPTURING,
        COMPLETE,
        FAILED
    }
}
