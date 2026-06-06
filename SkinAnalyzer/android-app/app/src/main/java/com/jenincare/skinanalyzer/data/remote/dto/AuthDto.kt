package com.jenincare.skinanalyzer.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = false)
data class LoginRequest(
    @Json(name = "email") val email: String,
    @Json(name = "password") val password: String
)

@JsonClass(generateAdapter = false)
data class LoginResponseData(
    @Json(name = "token") val token: String,
    @Json(name = "user") val user: UserDto
)

@JsonClass(generateAdapter = false)
data class LoginResponse(
    @Json(name = "data") val data: LoginResponseData
)

@JsonClass(generateAdapter = false)
data class RegisterRequest(
    @Json(name = "name") val name: String,
    @Json(name = "email") val email: String,
    @Json(name = "password") val password: String,
    @Json(name = "password_confirmation") val passwordConfirmation: String,
    @Json(name = "phone") val phone: String? = null
)

@JsonClass(generateAdapter = false)
data class RegisterResponseData(
    @Json(name = "token") val token: String,
    @Json(name = "user") val user: UserDto,
    @Json(name = "message") val message: String?
)

@JsonClass(generateAdapter = false)
data class RegisterResponse(
    @Json(name = "data") val data: RegisterResponseData
)

@JsonClass(generateAdapter = false)
data class UserDto(
    @Json(name = "id") val id: Int,
    @Json(name = "name") val name: String,
    @Json(name = "email") val email: String,
    @Json(name = "phone") val phone: String?,
    @Json(name = "created_at") val createdAt: String,
    @Json(name = "has_pending_analysis") val hasPendingAnalysis: Boolean = false,
    @Json(name = "total_analyses") val totalAnalyses: Int = 0
)

@JsonClass(generateAdapter = false)
data class AddToCartRequest(
    @Json(name = "product_id") val productId: String,
    @Json(name = "quantity") val quantity: Int = 1
)

@JsonClass(generateAdapter = false)
data class DeviceRegisterRequest(
    @Json(name = "device_id") val deviceId: String,
    @Json(name = "platform") val platform: String = "android",
    @Json(name = "device_model") val deviceModel: String,
    @Json(name = "os_version") val osVersion: String,
    @Json(name = "app_version") val appVersion: String
)
