package com.ebtikar.skinanalyzer.core.provider

import com.ebtikar.skinanalyzer.model.SkinMetric

data class AnalysisResult(
    val providerName: String,
    val executionTimeMs: Long,
    val metrics: Map<SkinMetric.Type, SkinMetric>,
    val confidence: Float = 0f,
    val warnings: List<String> = emptyList(),
    val rawResult: Map<String, String> = emptyMap()
) {
    val isSuccess: Boolean get() = metrics.isNotEmpty() || rawResult.isNotEmpty()
    val metricCount: Int get() = metrics.size
    val scanId: String? get() = rawResult["scan_id"]
    val scanStatus: String? get() = rawResult["status"]
}
