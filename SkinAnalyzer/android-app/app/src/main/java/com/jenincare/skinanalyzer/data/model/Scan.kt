package com.jenincare.skinanalyzer.data.model

import com.jenincare.skinanalyzer.domain.model.ScanStatus

data class Scan(
    val id: String,
    val status: ScanStatus,
    val imageUri: String,
    val overallHealthScore: Float,
    val radarMetrics: RadarMetrics,
    val heatmapCoordinates: List<HeatmapPoint>,
    val customArabicAnalysis: String?,
    val expertFreeTips: List<String>,
    val recommendedProducts: List<Product>,
    val createdAt: String,
    val approvedAt: String?
)
