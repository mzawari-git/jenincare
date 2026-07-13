package com.ebtikar.skinanalyzer.data.remote

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable
import okhttp3.MultipartBody
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Part
import retrofit2.http.Path
import retrofit2.http.Query

interface CloudApiService {

    @POST("v1/auth/login")
    suspend fun login(@Body request: LoginRequest): AuthResponse

    @POST("v1/auth/register")
    suspend fun register(@Body request: RegisterRequest): AuthResponse

    @POST("v1/auth/logout")
    suspend fun logout()

    @GET("v1/profile")
    suspend fun getProfile(): ProfileResponse

    @PUT("v1/profile")
    suspend fun updateProfile(@Body request: UpdateProfileRequest): ProfileResponse

    @POST("v1/device/register")
    suspend fun registerDevice(@Body request: DeviceRegisterRequest): MessageResponse

    @GET("v1/app-config")
    suspend fun getAppConfig(): AppConfigResponse

    @GET("v1/app-update")
    suspend fun checkAppUpdate(): AppUpdateResponse

    @Multipart
    @POST("v1/scans")
    suspend fun uploadScan(@Part image: MultipartBody.Part): ScanUploadResponse

    @GET("v1/scans")
    suspend fun getScans(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 10
    ): ScanListResponse

    @GET("v1/scans/history")
    suspend fun getScanHistory(): ScanHistoryResponse

    @GET("v1/scans/{id}")
    suspend fun getScanDetail(@Path("id") id: Int): ScanDetailResponse

    @GET("v1/scans/{id}/status")
    suspend fun getScanStatus(@Path("id") id: Int): ScanStatusResponse

    @GET("v1/scans/{id}/report")
    suspend fun getScanReport(@Path("id") id: Int): ScanReportResponse

    @GET("v1/scans/{id}/timeline")
    suspend fun getScanTimeline(@Path("id") id: Int): TimelineResponse

    @POST("v1/scans/{id}/unlock")
    suspend fun unlockScan(@Path("id") id: Int, @Body request: UnlockRequest): UnlockResponse

    @GET("v1/products/recommended/{scanId}")
    suspend fun getRecommendedProducts(@Path("scanId") scanId: Int): ProductsResponse
}

@Serializable
data class LoginRequest(
    @SerialName("email") val email: String,
    @SerialName("password") val password: String,
    @SerialName("device_id") val deviceId: String? = null
)

@Serializable
data class RegisterRequest(
    @SerialName("name") val name: String,
    @SerialName("email") val email: String,
    @SerialName("password") val password: String,
    @SerialName("password_confirmation") val passwordConfirmation: String,
    @SerialName("phone") val phone: String? = null,
    @SerialName("device_id") val deviceId: String? = null
)

@Serializable
data class UpdateProfileRequest(
    @SerialName("name") val name: String? = null,
    @SerialName("email") val email: String? = null,
    @SerialName("phone") val phone: String? = null,
    @SerialName("current_password") val currentPassword: String? = null,
    @SerialName("new_password") val newPassword: String? = null,
    @SerialName("new_password_confirmation") val newPasswordConfirmation: String? = null
)

@Serializable
data class DeviceRegisterRequest(
    @SerialName("device_id") val deviceId: String
)

@Serializable
data class UnlockRequest(
    @SerialName("pin_code") val pinCode: String
)

@Serializable
data class AuthResponse(
    @SerialName("data") val data: AuthData? = null,
    @SerialName("message") val message: String? = null
)

@Serializable
data class AuthData(
    @SerialName("token") val token: String,
    @SerialName("user") val user: UserData
)

@Serializable
data class UserData(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String,
    @SerialName("email") val email: String,
    @SerialName("phone") val phone: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
    @SerialName("has_pending_analysis") val hasPendingAnalysis: Boolean = false,
    @SerialName("total_analyses") val totalAnalyses: Int = 0
)

@Serializable
data class ProfileResponse(
    @SerialName("data") val data: UserData? = null,
    @SerialName("message") val message: String? = null
)

@Serializable
data class MessageResponse(
    @SerialName("message") val message: String? = null
)

@Serializable
data class AppConfigResponse(
    @SerialName("data") val data: AppConfigData? = null
)

@Serializable
data class AppConfigData(
    @SerialName("login_enabled") val loginEnabled: Boolean = true,
    @SerialName("registration_enabled") val registrationEnabled: Boolean = true,
    @SerialName("maintenance_mode") val maintenanceMode: Boolean = false,
    @SerialName("maintenance_message_ar") val maintenanceMessageAr: String? = null,
    @SerialName("maintenance_message_en") val maintenanceMessageEn: String? = null,
    @SerialName("min_app_version") val minAppVersion: String? = null,
    @SerialName("latest_app_version") val latestAppVersion: String? = null,
    @SerialName("app_name") val appName: String? = null,
    @SerialName("app_name_en") val appNameEn: String? = null,
    @SerialName("primary_color") val primaryColor: String? = null,
    @SerialName("accent_color") val accentColor: String? = null,
    @SerialName("logo_url") val logoUrl: String? = null,
    @SerialName("server_url") val serverUrl: String? = null
)

@Serializable
data class AppUpdateResponse(
    @SerialName("latest_version") val latestVersion: String? = null,
    @SerialName("version_code") val versionCode: Int = 0,
    @SerialName("download_url") val downloadUrl: String? = null,
    @SerialName("release_notes") val releaseNotes: String? = null,
    @SerialName("force_update") val forceUpdate: Boolean = false,
    @SerialName("update_available") val updateAvailable: Boolean = false
)

@Serializable
data class ScanUploadResponse(
    @SerialName("message") val message: String? = null,
    @SerialName("data") val data: ScanUploadData? = null
)

@Serializable
data class ScanUploadData(
    @SerialName("id") val id: Int,
    @SerialName("status") val status: String
)

@Serializable
data class ScanListResponse(
    @SerialName("data") val data: List<ScanItem>? = null,
    @SerialName("meta") val meta: PaginationMeta? = null
)

@Serializable
data class ScanHistoryResponse(
    @SerialName("scans") val scans: List<ScanItem>? = null
)

@Serializable
data class ScanItem(
    @SerialName("id") val id: String,
    @SerialName("user_id") val userId: String? = null,
    @SerialName("image_url") val imageUrl: String? = null,
    @SerialName("status") val status: String,
    @SerialName("analysis_status") val analysisStatus: String? = null,
    @SerialName("overall_score") val overallScore: Float? = null,
    @SerialName("confidence") val confidence: Float? = null,
    @SerialName("analyzed_by") val analyzedBy: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
    @SerialName("reviewed_at") val reviewedAt: String? = null,
    @SerialName("analyzed_at") val analyzedAt: String? = null
)

@Serializable
data class PaginationMeta(
    @SerialName("current_page") val currentPage: Int = 1,
    @SerialName("last_page") val lastPage: Int = 1,
    @SerialName("per_page") val perPage: Int = 10,
    @SerialName("total") val total: Int = 0
)

@Serializable
data class ScanDetailResponse(
    @SerialName("data") val data: ScanDetailData? = null
)

@Serializable
data class ScanDetailData(
    @SerialName("id") val id: Int,
    @SerialName("status") val status: String,
    @SerialName("is_locked") val isLocked: Boolean = false,
    @SerialName("overall_health_score") val overallHealthScore: Float? = null,
    @SerialName("formatted_score") val formattedScore: String? = null,
    @SerialName("radar_metrics") val radarMetrics: Map<String, Float>? = null,
    @SerialName("heatmap_coordinates") val heatmapCoordinates: List<HeatmapPoint>? = null,
    @SerialName("custom_arabic_analysis") val customArabicAnalysis: String? = null,
    @SerialName("expert_free_tips") val expertFreeTips: List<String>? = null,
    @SerialName("products") val products: List<ProductItem>? = null,
    @SerialName("pin") val pin: PinData? = null,
    @SerialName("approved_at") val approvedAt: String? = null,
    @SerialName("created_at") val createdAt: String? = null
)

@Serializable
data class ScanStatusResponse(
    @SerialName("scan_id") val scanId: String,
    @SerialName("status") val status: String,
    @SerialName("message") val message: String? = null
)

@Serializable
data class ScanReportResponse(
    @SerialName("scan") val scan: ReportScanData? = null,
    @SerialName("metrics") val metrics: ReportMetrics? = null,
    @SerialName("advanced_metrics") val advancedMetrics: Map<String, Float>? = null,
    @SerialName("heatmap_points") val heatmapPoints: List<HeatmapPoint>? = null,
    @SerialName("defects") val defects: List<DefectItem>? = null,
    @SerialName("general_tips") val generalTips: List<String>? = null,
    @SerialName("custom_arabic_analysis") val customArabicAnalysis: String? = null
)

@Serializable
data class ReportScanData(
    @SerialName("id") val id: String,
    @SerialName("user_id") val userId: String? = null,
    @SerialName("image_url") val imageUrl: String? = null,
    @SerialName("status") val status: String,
    @SerialName("analysis_status") val analysisStatus: String? = null,
    @SerialName("overall_score") val overallScore: Float? = null,
    @SerialName("confidence") val confidence: Float? = null,
    @SerialName("analyzed_by") val analyzedBy: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
    @SerialName("reviewed_at") val reviewedAt: String? = null,
    @SerialName("analyzed_at") val analyzedAt: String? = null
)

@Serializable
data class ReportMetrics(
    @SerialName("hydration") val hydration: Float = 0f,
    @SerialName("sebum") val sebum: Float = 0f,
    @SerialName("pigmentation") val pigmentation: Float = 0f,
    @SerialName("pores") val pores: Float = 0f,
    @SerialName("elasticity") val elasticity: Float = 0f
)

@Serializable
data class HeatmapPoint(
    @SerialName("x") val x: Float = 0f,
    @SerialName("y") val y: Float = 0f,
    @SerialName("value") val value: Float = 0f,
    @SerialName("label") val label: String? = null
)

@Serializable
data class DefectItem(
    @SerialName("type") val type: String? = null,
    @SerialName("severity") val severity: String? = null,
    @SerialName("area") val area: Float? = null,
    @SerialName("coordinates") val coordinates: List<Float>? = null
)

@Serializable
data class ProductItem(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("name_ar") val nameAr: String? = null,
    @SerialName("brand") val brand: String? = null,
    @SerialName("price") val price: Float? = null,
    @SerialName("currency") val currency: String? = null,
    @SerialName("image_url") val imageUrl: String? = null,
    @SerialName("matching_reason") val matchingReason: String? = null
)

@Serializable
data class PinData(
    @SerialName("pin_code") val pinCode: String? = null,
    @SerialName("is_used") val isUsed: Boolean = false,
    @SerialName("expires_at") val expiresAt: String? = null
)

@Serializable
data class TimelineResponse(
    @SerialName("events") val events: List<TimelineEvent>? = null
)

@Serializable
data class TimelineEvent(
    @SerialName("event") val event: String,
    @SerialName("status") val status: String,
    @SerialName("status_en") val statusEn: String? = null,
    @SerialName("timestamp") val timestamp: String? = null
)

@Serializable
data class UnlockResponse(
    @SerialName("message") val message: String? = null,
    @SerialName("data") val data: UnlockData? = null
)

@Serializable
data class UnlockData(
    @SerialName("scan_id") val scanId: Int,
    @SerialName("overall_health_score") val overallHealthScore: Float? = null,
    @SerialName("radar_metrics") val radarMetrics: Map<String, Float>? = null,
    @SerialName("custom_arabic_analysis") val customArabicAnalysis: String? = null,
    @SerialName("expert_free_tips") val expertFreeTips: List<String>? = null
)

@Serializable
data class ProductsResponse(
    @SerialName("products") val products: List<ProductItem>? = null,
    @SerialName("scan_id") val scanId: Int? = null
)

@Serializable
data class SkinAnalysisResponse(
    @SerialName("success") val success: Boolean = false,
    @SerialName("metrics") val metrics: Map<String, MetricData>? = null,
    @SerialName("overall_score") val overallScore: Float? = null,
    @SerialName("message") val message: String? = null
)

@Serializable
data class MetricData(
    @SerialName("score") val score: Float? = null,
    @SerialName("severity") val severity: String? = null,
    @SerialName("details") val details: String? = null
)
