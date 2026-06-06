package com.ebtikar.skinanalyzer.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "skin_reports")
data class SkinReportEntity(
    @PrimaryKey
    val id: String,
    val timestamp: Long,
    val providerName: String,
    val overallScore: Float,
    val executionTimeMs: Long,
    val metricsJson: String,
    val capturedImagesJson: String = "[]",
    val deviceModel: String = "ZMLH02",
    val notes: String = "",
    val aiAnalysisText: String = "",
    val expertTipsJson: String = "[]",
    val productsJson: String = "[]",
    val skinProfileJson: String = "{}",
    val confidence: Float = 0.85f,
    val scanId: String = ""
)
