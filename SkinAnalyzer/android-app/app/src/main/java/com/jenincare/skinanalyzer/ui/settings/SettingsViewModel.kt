package com.jenincare.skinanalyzer.ui.settings

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.local.AppDatabase
import com.jenincare.skinanalyzer.data.remote.dto.UserDto
import com.jenincare.skinanalyzer.data.repository.AuthRepository
import com.jenincare.skinanalyzer.util.BaseUrlProvider
import com.jenincare.skinanalyzer.util.Result
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.io.File
import javax.inject.Inject

data class SettingsUiState(
    val isLoading: Boolean = false,
    val userProfile: UserDto? = null,
    val profileError: String? = null,
    val cacheSize: String = "",
    val isClearingCache: Boolean = false,
    val isLoggingOut: Boolean = false,
    val logoutComplete: Boolean = false,
    val serverUrl: String = ""
)

@HiltViewModel
class SettingsViewModel @Inject constructor(
    private val authRepository: AuthRepository,
    private val baseUrlProvider: BaseUrlProvider,
    private val database: AppDatabase,
    @ApplicationContext private val context: Context
) : ViewModel() {

    private val _state = MutableStateFlow(SettingsUiState(serverUrl = baseUrlProvider.getBaseUrl()))
    val state: StateFlow<SettingsUiState> = _state.asStateFlow()

    init {
        loadProfile()
        updateCacheSize()
    }

    fun getServerUrl(): String = baseUrlProvider.getBaseUrl()

    fun updateServerUrl(url: String) {
        baseUrlProvider.setBaseUrl(url)
        _state.value = _state.value.copy(serverUrl = url)
    }

    fun loadProfile() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, profileError = null)
            when (val result = authRepository.getProfile()) {
                is Result.Success -> {
                    _state.value = _state.value.copy(
                        isLoading = false,
                        userProfile = result.data
                    )
                }
                is Result.Error -> {
                    _state.value = _state.value.copy(
                        isLoading = false,
                        profileError = result.exception.message
                    )
                }
                else -> {}
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoggingOut = true)
            authRepository.logout()
            _state.value = _state.value.copy(isLoggingOut = false, logoutComplete = true)
        }
    }

    fun clearCache() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isClearingCache = true)
            try {
                database.clearAllTables()
                val cacheDir = context.cacheDir
                cacheDir.listFiles()?.forEach { it.deleteRecursively() }
                updateCacheSize()
            } catch (_: Exception) {}
            _state.value = _state.value.copy(isClearingCache = false)
        }
    }

    private fun updateCacheSize() {
        val size = calculateCacheSize()
        _state.value = _state.value.copy(cacheSize = formatSize(size))
    }

    private fun calculateCacheSize(): Long {
        var size = 0L
        val dbPath = context.getDatabasePath("skin_analyzer_db")
        if (dbPath.exists()) size += dbPath.length()
        val cacheDir = context.cacheDir
        if (cacheDir.exists()) {
            cacheDir.listFiles()?.forEach { size += it.length() }
        }
        return size
    }

    private fun formatSize(bytes: Long): String {
        return when {
            bytes < 1024 -> "$bytes B"
            bytes < 1024 * 1024 -> "${bytes / 1024} KB"
            else -> "${"%.1f".format(bytes.toDouble() / (1024 * 1024))} MB"
        }
    }
}
