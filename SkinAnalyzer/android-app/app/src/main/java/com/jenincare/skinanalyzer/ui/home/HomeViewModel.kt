package com.jenincare.skinanalyzer.ui.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.jenincare.skinanalyzer.data.local.StreakManager
import com.jenincare.skinanalyzer.data.local.AppRatingManager
import com.jenincare.skinanalyzer.data.local.TokenManager
import com.jenincare.skinanalyzer.data.repository.ScanRepository
import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.util.Result
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class HomeUiState(
    val isLoading: Boolean = true,
    val scans: List<Scan> = emptyList(),
    val error: String? = null,
    val currentStreak: Int = 0,
    val longestStreak: Int = 0,
    val totalScans: Int = 0,
    val todayScanned: Boolean = false,
    val dailyTip: String = "",
    val showRatingPrompt: Boolean = false
)

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val tokenManager: TokenManager,
    private val scanRepository: ScanRepository,
    private val streakManager: StreakManager,
    private val appRatingManager: AppRatingManager
) : ViewModel() {

    private val _uiState = MutableStateFlow(HomeUiState())
    val uiState: StateFlow<HomeUiState> = _uiState.asStateFlow()

    init {
        loadScans()
        loadStreak()
        checkRating()
    }

    fun loadScans() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)

            when (val result = scanRepository.getScans(page = 1)) {
                is Result.Success -> {
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        scans = result.data
                    )
                }
                else -> {
                    val errorMsg = if (result is Result.Error) {
                        result.exception.message ?: "Failed to load scans"
                    } else {
                        "Failed to load scans"
                    }
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = errorMsg
                    )
                }
            }
        }
    }

    private fun loadStreak() {
        viewModelScope.launch {
            val info = streakManager.getStreakInfo()
            _uiState.value = _uiState.value.copy(
                currentStreak = info.currentStreak,
                longestStreak = info.longestStreak,
                totalScans = info.totalScans,
                todayScanned = info.todayScanned,
                dailyTip = getDailyTip(info.currentStreak)
            )
        }
    }

    fun logout() {
        android.util.Log.d("HomeVM", "Logging out, clearing token")
        tokenManager.clearToken()
    }

    private fun checkRating() {
        viewModelScope.launch {
            if (appRatingManager.shouldShowRating()) {
                _uiState.value = _uiState.value.copy(showRatingPrompt = true)
            }
        }
    }

    fun dismissRating() {
        viewModelScope.launch {
            appRatingManager.dismissRating()
            _uiState.value = _uiState.value.copy(showRatingPrompt = false)
        }
    }

    fun rateApp() {
        viewModelScope.launch {
            appRatingManager.markAsRated()
            _uiState.value = _uiState.value.copy(showRatingPrompt = false)
        }
    }

    private fun getDailyTip(streak: Int): String {
        val tips = listOf(
            "اشربي 8 أكواب ماء يومياً لترطيب بشرتك من الداخل",
            "استخدمي واقي شمس SPF50 يومياً حتى في الأيام الغائمة",
            "نظفي بشرتك مرتين يومياً بغسول لطيف مناسب",
            "احصلي على 7-8 ساعات نوم لبشرة مشرقة",
            "تناولي الفواكه والخضروات الغنية بفيتامين C",
            "تجنبي لمس وجهك بيديك لتقليل البكتيريا",
            "استخدمي سيروم الهيالورونيك للترطيب العميق",
            "قومي بعمل ماسك مرطب مرة أسبوعياً",
            "قللي من السكريات لتحسين مظهر البشرة",
            "مارسي الرياضة 30 دقيقة يومياً لتنشيط الدورة الدموية"
        )
        return tips[streak % tips.size]
    }
}
