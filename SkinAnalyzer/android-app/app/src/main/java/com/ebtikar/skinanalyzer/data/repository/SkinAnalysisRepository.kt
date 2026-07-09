package com.ebtikar.skinanalyzer.data.repository

import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.StateFlow
import java.io.File

interface SkinAnalysisRepository {
    fun getAnalysisState(): StateFlow<AnalysisState>
    suspend fun startAnalysis(outputDir: java.io.File, diagnosisMode: String = "all", previewSurface: android.view.Surface? = null): Result<Map<com.ebtikar.skinanalyzer.hardware.LightSpectrum, java.io.File>>
    suspend fun analyzeImages(frames: Map<com.ebtikar.skinanalyzer.hardware.LightSpectrum, java.io.File>, mode: String = "auto"): Result<com.ebtikar.skinanalyzer.model.SkinAnalysisReport>
    suspend fun saveReport(report: SkinAnalysisReport): Result<String>
    suspend fun getReport(id: String): SkinReportEntity?
    fun getAllReports(): Flow<List<SkinReportEntity>>
    fun getRecentReports(limit: Int): Flow<List<SkinReportEntity>>
    suspend fun deleteReport(id: String)
    suspend fun getReportCount(): Int
    fun getCapturedImages(reportId: String): Map<LightSpectrum, File>
    fun getReportsSince(sinceTimestamp: Long): Flow<List<SkinReportEntity>>
}
