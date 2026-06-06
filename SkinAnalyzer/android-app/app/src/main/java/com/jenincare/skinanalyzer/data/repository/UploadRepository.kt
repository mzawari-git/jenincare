package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.data.remote.ApiService
import okhttp3.MultipartBody
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class UploadRepository @Inject constructor(
    private val apiService: ApiService
) {
    suspend fun uploadScan(image: MultipartBody.Part, @Suppress("UNUSED_PARAMETER") scanId: MultipartBody.Part) {
        apiService.uploadScan(image)
    }

    suspend fun uploadChunk(
        chunk: MultipartBody.Part,
        chunkIndex: MultipartBody.Part,
        totalChunks: MultipartBody.Part,
        scanId: MultipartBody.Part,
        @Suppress("UNUSED_PARAMETER") fileMd5: MultipartBody.Part,
    ) {
        val scanIdVal = extractPartValue(scanId)
        val chunkIdx = extractPartValue(chunkIndex).toIntOrNull() ?: 0
        val total = extractPartValue(totalChunks).toIntOrNull() ?: 1
        apiService.uploadChunk(
            scanId = scanIdVal,
            chunkIndex = chunkIdx,
            totalChunks = total,
            isLastChunk = chunkIdx == total - 1,
            chunk = chunk
        )
    }

    @Suppress("UNUSED_PARAMETER")
    suspend fun finalizeChunkedUpload(scanId: String, totalChunks: Int, fileMd5: String) {
    }

    @Suppress("UNUSED_PARAMETER")
    suspend fun notifyUploadComplete(scanId: String) {
    }

    private fun extractPartValue(part: MultipartBody.Part): String {
        val headers = part.headers ?: return ""
        val disposition = headers.get("Content-Disposition") ?: return ""
        val nameMatch = Regex("name=\"([^\"]+)\"").find(disposition)
        return nameMatch?.groupValues?.getOrElse(1) { "" } ?: ""
    }
}
