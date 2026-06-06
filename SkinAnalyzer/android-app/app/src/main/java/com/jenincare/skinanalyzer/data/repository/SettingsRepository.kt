package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.data.local.WhiteLabelManager
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.remote.dto.AppConfigData
import com.jenincare.skinanalyzer.util.NetworkMonitor
import com.jenincare.skinanalyzer.util.Result
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

data class WhiteLabelConfig(
    val appName: String,
    val primaryColor: String,
    val secondaryColor: String,
    val logoUrl: String?,
    val serverUrl: String
)

data class AppConfig(
    val loginEnabled: Boolean,
    val registrationEnabled: Boolean,
    val maintenanceMode: Boolean,
    val maintenanceMessageAr: String?,
    val appName: String,
    val appNameEn: String,
    val primaryColor: String,
    val accentColor: String,
    val logoUrl: String?,
    val serverUrl: String?,
    val minAppVersion: String?,
    val latestAppVersion: String?
)

interface SettingsRepository {

    suspend fun getWhiteLabelConfig(): Result<WhiteLabelConfig>

    suspend fun fetchAndUpdateWhiteLabelConfig(): Result<WhiteLabelConfig>

    suspend fun fetchAppConfig(): Result<AppConfig>

    fun observeWhiteLabelConfig(): Flow<WhiteLabelConfig>
}

@Singleton
class SettingsRepositoryImpl @Inject constructor(
    private val apiService: ApiService,
    private val whiteLabelManager: WhiteLabelManager,
    private val networkMonitor: NetworkMonitor
) : SettingsRepository {

    override suspend fun getWhiteLabelConfig(): Result<WhiteLabelConfig> {
        return try {
            val config = whiteLabelManager.getConfig()
            Result.Success(config)
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun fetchAndUpdateWhiteLabelConfig(): Result<WhiteLabelConfig> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.getAppConfig()

            if (response.isSuccessful) {
                val config = response.body()?.data
                if (config != null) {
                    whiteLabelManager.saveConfig(config)
                    Result.Success(
                        WhiteLabelConfig(
                            appName = config.appName,
                            primaryColor = config.primaryColor,
                            secondaryColor = config.accentColor,
                            logoUrl = config.logoUrl,
                            serverUrl = config.serverUrl ?: ""
                        )
                    )
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                Result.Error(IOException("Failed to fetch config: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override suspend fun fetchAppConfig(): Result<AppConfig> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.getAppConfig()

            if (response.isSuccessful) {
                val data = response.body()?.data
                if (data != null) {
                    Result.Success(
                        AppConfig(
                            loginEnabled = data.loginEnabled,
                            registrationEnabled = data.registrationEnabled,
                            maintenanceMode = data.maintenanceMode,
                            maintenanceMessageAr = data.maintenanceMessageAr,
                            appName = data.appName,
                            appNameEn = data.appNameEn,
                            primaryColor = data.primaryColor,
                            accentColor = data.accentColor,
                            logoUrl = data.logoUrl,
                            serverUrl = data.serverUrl,
                            minAppVersion = data.minAppVersion,
                            latestAppVersion = data.latestAppVersion
                        )
                    )
                } else {
                    Result.Error(IOException("Empty response body"))
                }
            } else {
                Result.Error(IOException("Failed to fetch app config: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }

    override fun observeWhiteLabelConfig(): Flow<WhiteLabelConfig> {
        return whiteLabelManager.observeConfig()
    }
}
