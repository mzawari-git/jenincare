package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.data.local.TokenManager
import com.jenincare.skinanalyzer.data.local.dao.ScanDao
import com.jenincare.skinanalyzer.data.local.entity.ScanEntity
import com.jenincare.skinanalyzer.data.remote.ApiService
import java.io.IOException
import com.jenincare.skinanalyzer.data.remote.dto.ScanDto
import com.jenincare.skinanalyzer.data.remote.dto.UnlockRequest
import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.domain.model.ScanStatus
import com.jenincare.skinanalyzer.util.NetworkMonitor
import com.jenincare.skinanalyzer.util.Result
import com.jenincare.skinanalyzer.util.ImageCompressor
import kotlinx.coroutines.flow.first
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ScanRepositoryImpl @Inject constructor(
    private val apiService: ApiService,
    private val tokenManager: TokenManager,
    private val networkMonitor: NetworkMonitor,
    private val imageCompressor: ImageCompressor,
    private val scanDao: ScanDao
) : ScanRepository {

    override suspend fun uploadScan(imageFilePath: String): Result<Scan> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val compressedFile = imageCompressor.compress(File(imageFilePath))

            val requestBody = compressedFile.asRequestBody("image/jpeg".toMediaTypeOrNull())
            val imagePart = MultipartBody.Part.createFormData(
                "image",
                compressedFile.name,
                requestBody
            )

            val response = apiService.uploadScan(imagePart)

            if (response.isSuccessful) {
                val uploadResponse = response.body()
                if (uploadResponse != null) {
                    val scan = uploadResponse.scan.toDomain()
                    scanDao.insert(ScanEntity.fromDomain(scan))
                    Result.Success(scan)
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                val errorBody = response.errorBody()?.string() ?: "Unknown error"
                Result.Error(IOException("Upload failed: ${response.code()} $errorBody"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        } finally {
            File(imageFilePath).delete()
        }
    }

    override suspend fun getScans(page: Int): Result<List<Scan>> {
        if (networkMonitor.isConnected.first()) {
            try {
                val response = apiService.getScans()
                if (response.isSuccessful) {
                    val scansResponse = response.body()
                    val scans = scansResponse?.scans?.map { it.toDomain() } ?: emptyList()
                    scanDao.insertAll(scans.map { ScanEntity.fromDomain(it) })
                    return Result.Success(scans)
                }
            } catch (_: Exception) { }
        }

        val cached = scanDao.getAllScansList()
        if (cached.isNotEmpty()) {
            return Result.Success(cached.map { it.toDomain() })
        }
        return Result.Error(IOException("No internet connection and no cached data"))
    }

    override suspend fun getScan(scanId: String): Result<Scan> {
        if (networkMonitor.isConnected.first()) {
            try {
                val response = apiService.getScan(scanId)
                if (response.isSuccessful) {
                    val scanResponse = response.body()
                    if (scanResponse != null) {
                        val scan = scanResponse.scan.toDomain()
                        scanDao.insert(ScanEntity.fromDomain(scan))
                        return Result.Success(scan)
                    }
                }
            } catch (_: Exception) { }
        }

        val cached = scanDao.getScanById(scanId)
        return if (cached != null) {
            Result.Success(cached.toDomain())
        } else {
            Result.Error(IOException("Scan not found offline"))
        }
    }

    override suspend fun unlockScan(scanId: String, pinCode: String): Result<Scan> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.unlockScan(scanId, UnlockRequest(pinCode))
            if (response.isSuccessful) {
                val unlockResponse = response.body()
                if (unlockResponse != null) {
                    val scan = unlockResponse.scan.toDomain()
                    scanDao.insert(ScanEntity.fromDomain(scan))
                    Result.Success(scan)
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                val errorBody = response.errorBody()?.string() ?: "Unknown error"
                Result.Error(IOException("Unlock failed: ${response.code()} $errorBody"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun getTimeline(scanId: String): Result<List<ScanRepository.TimelineEvent>> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.getScanTimeline(scanId)
            if (response.isSuccessful) {
                val timelineResponse = response.body()
                val events = timelineResponse?.events?.map { event ->
                    ScanRepository.TimelineEvent(
                        id = event.id,
                        status = event.status,
                        timestamp = event.timestamp,
                        description = event.description,
                        descriptionAr = event.descriptionAr
                    )
                } ?: emptyList()
                Result.Success(events)
            } else {
                Result.Error(IOException("Failed to fetch timeline: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    private fun ScanDto.toDomain(): Scan = Scan(
        id = id,
        userId = "",
        imageUrl = imageUrl,
        status = ScanStatus.fromString(status),
        overallScore = overallHealthScore.toInt(),
        createdAt = createdAt,
        reviewedAt = approvedAt
    )
}
