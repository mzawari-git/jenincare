package com.jenincare.skinanalyzer.ui.onboarding

import androidx.lifecycle.ViewModel
import com.jenincare.skinanalyzer.data.local.OnboardingManager
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class OnboardingViewModel @Inject constructor(
    val manager: OnboardingManager
) : ViewModel()
