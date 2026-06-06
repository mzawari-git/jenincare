package com.jenincare.skinanalyzer.data.remote

import com.jenincare.skinanalyzer.data.remote.dto.AddToCartRequest
import com.jenincare.skinanalyzer.data.remote.dto.AppConfigResponse
import com.jenincare.skinanalyzer.data.remote.dto.AppUpdateResponse
import com.jenincare.skinanalyzer.data.remote.dto.DeviceRegisterRequest
import com.jenincare.skinanalyzer.data.remote.dto.LoginRequest
import com.jenincare.skinanalyzer.data.remote.dto.LoginResponse
import com.jenincare.skinanalyzer.data.remote.dto.MultipleScansResponse
import com.jenincare.skinanalyzer.data.remote.dto.ProductsResponse
import com.jenincare.skinanalyzer.data.remote.dto.ProfileResponse
import com.jenincare.skinanalyzer.data.remote.dto.RegisterRequest
import com.jenincare.skinanalyzer.data.remote.dto.RegisterResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanStatusResponse
import com.jenincare.skinanalyzer.data.remote.dto.ScanTimelineResponse
import com.jenincare.skinanalyzer.data.remote.dto.UnlockRequest
import com.jenincare.skinanalyzer.data.remote.dto.UnlockResponse
import com.jenincare.skinanalyzer.data.remote.dto.UploadChunkResponse
import com.jenincare.skinanalyzer.data.remote.dto.UploadResponse
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

interface ApiService {

    @Multipart
    @POST("v1/scans/upload")
    suspend fun uploadScan(
        @Part image: MultipartBody.Part
    ): Response<UploadResponse>

    @Multipart
    @POST("v1/scans/upload/with-progress")
    suspend fun uploadScanWithProgress(
        @Part file: MultipartBody.Part,
        @Part("metadata") metadata: RequestBody
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

    @GET("v1/scans")
    suspend fun getScans(): Response<MultipleScansResponse>

    @GET("v1/scans/{id}")
    suspend fun getScan(
        @Path("id") scanId: String
    ): Response<ScanResponse>

    @GET("v1/scans/{id}/timeline")
    suspend fun getScanTimeline(
        @Path("id") scanId: String
    ): Response<ScanTimelineResponse>

    @POST("v1/scans/{id}/unlock")
    suspend fun unlockScan(
        @Path("id") scanId: String,
        @Body request: UnlockRequest
    ): Response<UnlockResponse>

    @GET("v1/products/recommended/{scanId}")
    suspend fun getRecommendedProducts(
        @Path("scanId") scanId: String
    ): Response<ProductsResponse>

    @POST("v1/auth/login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    @POST("v1/auth/register")
    suspend fun register(
        @Body request: RegisterRequest
    ): Response<RegisterResponse>

    @GET("v1/profile")
    suspend fun getProfile(): Response<ProfileResponse>

    @POST("v1/device/register")
    suspend fun registerDevice(
        @Body request: DeviceRegisterRequest
    ): Response<Unit>

    @POST("v1/scans/{scanId}/add-to-cart")
    suspend fun addToCart(
        @Path("scanId") scanId: String,
        @Body request: AddToCartRequest
    ): Response<Unit>

    @GET("v1/app-config")
    suspend fun getAppConfig(): Response<AppConfigResponse>

    @GET("v1/app-update")
    suspend fun checkAppUpdate(): Response<AppUpdateResponse>
}
