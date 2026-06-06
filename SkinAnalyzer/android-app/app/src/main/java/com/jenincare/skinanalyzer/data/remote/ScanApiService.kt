package com.jenincare.skinanalyzer.data.remote

import com.jenincare.skinanalyzer.data.remote.dto.ScanHistoryResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanReportResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanStatusResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanTimelineResponse
import com.jenincare.skinanalyzer.data.remote.dto.UnlockScanRequest
import com.jenincare.skinanalyzer.data.remote.dto.UnlockScanResponse
import com.jenincare.skinanalyzer.data.remote.dto.UploadChunkResponse
import com.jenincare.skinanalyzer.data.remote.dto.UploadScanResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part
import retrofit2.http.Path
import retrofit2.http.Query

interface ScanApiService {

    @Multipart
    @POST("v1/scans/upload")
    suspend fun uploadScan(
        @Part image: MultipartBody.Part,
        @Part("metadata") metadata: RequestBody
    ): Response<UploadScanResponse>

    @Multipart
    @POST("v1/scans/upload/with-progress")
    suspend fun uploadScanWithProgress(
        @Part image: MultipartBody.Part,
        @Part("metadata") metadata: RequestBody,
        @Part spectral: List<MultipartBody.Part> = emptyList()
    ): Response<UploadScanResponse>

    @Multipart
    @POST("v1/scans/upload/chunk")
    suspend fun uploadChunk(
        @Query("scan_id") scanId: String,
        @Query("chunk_index") chunkIndex: Int,
        @Query("total_chunks") totalChunks: Int,
        @Query("is_last_chunk") isLastChunk: Boolean,
        @Part chunk: MultipartBody.Part
    ): Response<UploadChunkResponse>

    @GET("v1/scans/{scanId}/status")
    suspend fun getScanStatus(
        @Path("scanId") scanId: String
    ): Response<ScanStatusResponse>

    @POST("v1/scans/{scanId}/unlock")
    suspend fun unlockScan(
        @Path("scanId") scanId: String,
        @Body request: UnlockScanRequest
    ): Response<UnlockScanResponse>

    @GET("v1/scans/{scanId}/report")
    suspend fun getScanReport(
        @Path("scanId") scanId: String
    ): Response<ScanReportResponse>

    @GET("v1/scans/history")
    suspend fun getScanHistory(): Response<ScanHistoryResponse>

    @GET("v1/scans/{scanId}/timeline")
    suspend fun getScanTimeline(
        @Path("scanId") scanId: String
    ): Response<ScanTimelineResponse>
}
