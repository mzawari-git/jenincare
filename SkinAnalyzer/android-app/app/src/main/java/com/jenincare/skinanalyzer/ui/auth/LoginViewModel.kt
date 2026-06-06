package com.jenincare.skinanalyzer.ui.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.repository.AuthRepository
import com.jenincare.skinanalyzer.util.Result
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class LoginUiState(
    val isLoading: Boolean = false,
    val error: String? = null,
    val isLoggedIn: Boolean = false
)

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val authRepository: AuthRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow(LoginUiState())
    val uiState: StateFlow<LoginUiState> = _uiState.asStateFlow()

    fun login(email: String, password: String) {
        if (email.isBlank() || password.isBlank()) {
            _uiState.value = _uiState.value.copy(error = "يرجى ملء جميع الحقول")
            return
        }

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)

            when (val result = authRepository.login(email, password)) {
                is Result.Success -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        isLoggedIn = true
                    )
                }
                is Result.Error -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = result.exception.message ?: "فشل تسجيل الدخول"
                    )
                }
                is Result.Loading -> {}
            }
        }
    }

    fun register(
        name: String,
        email: String,
        password: String,
        passwordConfirmation: String,
        phone: String? = null
    ) {
        if (name.isBlank() || email.isBlank() || password.isBlank()) {
            _uiState.value = _uiState.value.copy(error = "يرجى ملء جميع الحقول")
            return
        }
        if (password != passwordConfirmation) {
            _uiState.value = _uiState.value.copy(error = "كلمات المرور غير متطابقة")
            return
        }
        if (password.length < 6) {
            _uiState.value = _uiState.value.copy(error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل")
            return
        }

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)

            when (val result = authRepository.register(name, email, password, passwordConfirmation, phone)) {
                is Result.Success -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        isLoggedIn = true
                    )
                }
                is Result.Error -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = result.exception.message ?: "فشل إنشاء الحساب"
                    )
                }
                is Result.Loading -> {}
            }
        }
    }

    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }

    fun loginWithBiometric() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, error = null)
            when (val result = authRepository.loginWithBiometric()) {
                is Result.Success -> {
                    _uiState.value = _uiState.value.copy(isLoading = false, isLoggedIn = true)
                }
                is Result.Error -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = result.exception.message ?: "فشل تسجيل الدخول بالبصمة"
                    )
                }
                is Result.Loading -> {}
            }
        }
    }
}
