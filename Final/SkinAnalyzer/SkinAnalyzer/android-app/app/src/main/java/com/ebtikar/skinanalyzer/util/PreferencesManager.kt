package com.ebtikar.skinanalyzer.util

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "settings")

@Singleton
class PreferencesManager @Inject constructor(
    @ApplicationContext private val context: Context
) {

    companion object {
        private val KEY_LANGUAGE = stringPreferencesKey("app_language")
        private val KEY_ANALYSIS_MODE = stringPreferencesKey("analysis_mode")
        private val KEY_BRIGHTNESS = stringPreferencesKey("screen_brightness")
        private val KEY_DIAGNOSIS_MODE = stringPreferencesKey("diagnosis_mode")
        private val KEY_API_URL = stringPreferencesKey("api_url")
        private val KEY_API_KEY = stringPreferencesKey("api_key")
        private val KEY_PROVIDER_SELECTION = stringPreferencesKey("provider_selection")
        private val KEY_DIAGNOSIS_MODE_MIGRATED = booleanPreferencesKey("_diagnosis_mode_migrated_v1")
    }

    val languageFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_LANGUAGE] ?: Constants.LANG_ENGLISH
    }

    val analysisModeFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_ANALYSIS_MODE] ?: Constants.ANALYSIS_AUTO
    }

    val brightnessFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_BRIGHTNESS] ?: "100"
    }

    val diagnosisModeFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_DIAGNOSIS_MODE] ?: Constants.DIAGNOSIS_ALL
    }

    val apiUrlFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_API_URL] ?: ""
    }

    val apiKeyFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_API_KEY] ?: ""
    }

    val providerSelectionFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_PROVIDER_SELECTION] ?: "local"
    }

    suspend fun setLanguage(language: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_LANGUAGE] = language
        }
    }

    suspend fun setAnalysisMode(mode: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_ANALYSIS_MODE] = mode
        }
    }

    suspend fun setBrightness(brightness: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_BRIGHTNESS] = brightness
        }
    }

    suspend fun setDiagnosisMode(mode: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_DIAGNOSIS_MODE] = mode
        }
    }

    suspend fun setApiUrl(url: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_API_URL] = url
        }
    }

    suspend fun setApiKey(key: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_API_KEY] = key
        }
    }

    suspend fun setProviderSelection(provider: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_PROVIDER_SELECTION] = provider
        }
    }

    suspend fun runDiagnosisModeMigration() {
        context.dataStore.edit { prefs ->
            if (prefs[KEY_DIAGNOSIS_MODE_MIGRATED] != true) {
                prefs[KEY_DIAGNOSIS_MODE] = Constants.DIAGNOSIS_ALL
                prefs[KEY_DIAGNOSIS_MODE_MIGRATED] = true
            }
        }
    }
}
