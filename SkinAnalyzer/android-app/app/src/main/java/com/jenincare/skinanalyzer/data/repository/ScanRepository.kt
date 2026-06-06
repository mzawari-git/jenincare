package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.util.Result

interface ScanRepository {

    suspend fun uploadScan(imageFilePath: String): Result<Scan>

    suspend fun getScans(page: Int = 1): Result<List<Scan>>

    suspend fun getScan(scanId: String): Result<Scan>

    suspend fun unlockScan(scanId: String, pinCode: String): Result<Scan>

    suspend fun getTimeline(scanId: String): Result<List<TimelineEvent>>

    data class TimelineEvent(
        val id: String,
        val status: String,
        val timestamp: String,
        val description: String,
        val descriptionAr: String?
    )
}
