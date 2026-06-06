package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.data.remote.dto.UserDto
import com.jenincare.skinanalyzer.util.Result

interface AuthRepository {

    suspend fun login(email: String, password: String): Result<UserDto>

    suspend fun loginWithBiometric(): Result<UserDto>

    suspend fun register(
        name: String,
        email: String,
        password: String,
        passwordConfirmation: String,
        phone: String? = null,
        gender: String? = null,
        birthDate: String? = null
    ): Result<UserDto>

    suspend fun getProfile(): Result<UserDto>

    suspend fun updateFcmToken(fcmToken: String): Result<Unit>

    suspend fun isLoggedIn(): Boolean

    suspend fun logout()
}
