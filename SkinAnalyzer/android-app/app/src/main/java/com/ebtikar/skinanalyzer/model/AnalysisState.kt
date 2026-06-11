package com.ebtikar.skinanalyzer.model

import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import java.io.File

sealed class AnalysisState {
    data object Idle : AnalysisState()
    data object Initializing : AnalysisState()
    data object WaitingForFace : AnalysisState()
    data class Capturing(
        val phase: LightSpectrum,
        val progress: Int,
        val step: Int = 0,
        val totalSteps: Int = 0,
        val spectrumDisplayAr: String = ""
    ) : AnalysisState()
    data class Analyzing(val provider: String) : AnalysisState()
    data object Saving : AnalysisState()
    data class Complete(val reportId: String) : AnalysisState()
    data class Error(val message: String) : AnalysisState()
}
