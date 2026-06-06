package com.jenincare.skinanalyzer.data.repository

import android.os.Build
import com.jenincare.skinanalyzer.BuildConfig
import com.jenincare.skinanalyzer.data.local.TokenManager
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.remote.dto.DeviceRegisterRequest
import com.jenincare.skinanalyzer.data.remote.dto.LoginRequest
import com.jenincare.skinanalyzer.data.remote.dto.RegisterRequest
import com.jenincare.skinanalyzer.data.remote.dto.UserDto
import com.jenincare.skinanalyzer.util.NetworkMonitor
import com.jenincare.skinanalyzer.util.Result
import kotlinx.coroutines.flow.first
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepositoryImpl @Inject constructor(
    private val apiService: ApiService,
    private val tokenManager: TokenManager,
    private val networkMonitor: NetworkMonitor
) : AuthRepository {

    override suspend fun login(email: String, password: String): Result<UserDto> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.login(LoginRequest(email, password))

            if (response.isSuccessful) {
                val loginResponse = response.body()
                if (loginResponse != null) {
                    tokenManager.saveToken(loginResponse.data.token)
                    tokenManager.saveUserId(loginResponse.data.user.id.toString())
                    Result.Success(loginResponse.data.user)
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                val errorBody = response.errorBody()?.string() ?: "Invalid credentials"
                Result.Error(IOException(errorBody))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun loginWithBiometric(): Result<UserDto> {
        val existingToken = tokenManager.getToken()
        if (existingToken.isNullOrBlank()) {
            return Result.Error(IOException("لا توجد بيانات دخول محفوظة"))
        }
        return try {
            val response = apiService.getProfile()
            if (response.isSuccessful) {
                val profileResponse = response.body()
                if (profileResponse != null) {
                    Result.Success(profileResponse.data)
                } else {
                    Result.Error(IOException("فشل التحقق من الهوية"))
                }
            } else {
                tokenManager.clearToken()
                Result.Error(IOException("انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun register(
        name: String,
        email: String,
        password: String,
        passwordConfirmation: String,
        phone: String?,
        gender: String?,
        birthDate: String?
    ): Result<UserDto> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val request = RegisterRequest(
                name = name,
                email = email,
                password = password,
                passwordConfirmation = passwordConfirmation,
                phone = phone
            )
            val response = apiService.register(request)

            if (response.isSuccessful) {
                val registerResponse = response.body()
                if (registerResponse != null) {
                    tokenManager.saveToken(registerResponse.data.token)
                    tokenManager.saveUserId(registerResponse.data.user.id.toString())
                    Result.Success(registerResponse.data.user)
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                val errorBody = response.errorBody()?.string() ?: "Registration failed"
                Result.Error(IOException(errorBody))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun getProfile(): Result<UserDto> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.getProfile()

            if (response.isSuccessful) {
                val profileResponse = response.body()
                if (profileResponse != null) {
                    Result.Success(profileResponse.data)
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                Result.Error(IOException("Failed to fetch profile: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun updateFcmToken(fcmToken: String): Result<Unit> {
        return try {
            val request = DeviceRegisterRequest(
                deviceId = fcmToken,
                deviceModel = Build.MODEL,
                osVersion = Build.VERSION.RELEASE,
                appVersion = BuildConfig.VERSION_NAME
            )
            val response = apiService.registerDevice(request)

            if (response.isSuccessful) {
                Result.Success(Unit)
            } else {
                Result.Error(IOException("Failed to register device"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun isLoggedIn(): Boolean {
        val token = tokenManager.getToken()
        return !token.isNullOrBlank()
    }

    override suspend fun logout() {
        tokenManager.clearToken()
    }
}
