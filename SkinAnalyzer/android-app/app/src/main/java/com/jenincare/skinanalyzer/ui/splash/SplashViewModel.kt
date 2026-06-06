package com.jenincare.skinanalyzer.ui.splash

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.local.TokenManager
import com.jenincare.skinanalyzer.data.repository.SettingsRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject

sealed class SplashDestination {
    data object Login : SplashDestination()
    data object Home : SplashDestination()
}

data class SplashUiState(
    val isLoading: Boolean = true,
    val destination: SplashDestination? = null
)

@HiltViewModel
class SplashViewModel @Inject constructor(
    private val settingsRepository: SettingsRepository,
    private val tokenManager: TokenManager
) : ViewModel() {

    private val _uiState = MutableStateFlow(SplashUiState())
    val uiState: StateFlow<SplashUiState> = _uiState.asStateFlow()

    fun checkStartDestination() {
        val existingToken = tokenManager.getToken()
        android.util.Log.d("SplashVM", "Existing token: ${existingToken?.take(20)}...")
        val hasToken = !existingToken.isNullOrBlank()
        android.util.Log.d("SplashVM", "Has token: $hasToken")

        _uiState.value = SplashUiState(
            isLoading = false,
            destination = if (hasToken) SplashDestination.Home else SplashDestination.Login
        )
    }
}
