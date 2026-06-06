package com.jenincare.skinanalyzer.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.intPreferencesKey
import androidx.datastore.preferences.core.longPreferencesKey
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

private val Context.onboardingDataStore: DataStore<Preferences> by preferencesDataStore(name = "onboarding_prefs")

@Singleton
class OnboardingManager @Inject constructor(
    private val context: Context
) {
    private val completedKey = booleanPreferencesKey("onboarding_completed")
    private val versionKey = intPreferencesKey("onboarding_version")

    private val currentVersion = 1

    suspend fun isOnboardingCompleted(): Boolean {
        val prefs = context.onboardingDataStore.data.first()
        val completed = prefs[completedKey] ?: false
        val version = prefs[versionKey] ?: 0
        return completed && version >= currentVersion
    }

    suspend fun completeOnboarding() {
        context.onboardingDataStore.edit { prefs ->
            prefs[completedKey] = true
            prefs[versionKey] = currentVersion
        }
    }

    fun observeOnboardingStatus(): Flow<Boolean> {
        return context.onboardingDataStore.data.map { prefs ->
            val completed = prefs[completedKey] ?: false
            val version = prefs[versionKey] ?: 0
            completed && version >= currentVersion
        }
    }
}
