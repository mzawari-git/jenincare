package com.jenincare.skinanalyzer.data.local

import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import com.jenincare.skinanalyzer.data.remote.dto.AppConfigData
import com.jenincare.skinanalyzer.data.repository.WhiteLabelConfig
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class WhiteLabelManager @Inject constructor(
    private val dataStore: DataStore<Preferences>
) {
    companion object {
        private val KEY_APP_NAME = stringPreferencesKey("wl_app_name")
        private val KEY_PRIMARY_COLOR = stringPreferencesKey("wl_primary_color")
        private val KEY_SECONDARY_COLOR = stringPreferencesKey("wl_secondary_color")
        private val KEY_LOGO_URL = stringPreferencesKey("wl_logo_url")
        private val KEY_SERVER_URL = stringPreferencesKey("wl_server_url")

        private const val DEFAULT_APP_NAME = "SkinAnalyzer"
        private const val DEFAULT_PRIMARY_COLOR = "#4CAF50"
        private const val DEFAULT_SECONDARY_COLOR = "#81C784"
        private const val DEFAULT_SERVER_URL = "https://jenincare.shop"
    }

    suspend fun saveConfig(config: AppConfigData) {
        dataStore.edit { preferences ->
            preferences[KEY_APP_NAME] = config.appName
            preferences[KEY_PRIMARY_COLOR] = config.primaryColor
            preferences[KEY_SECONDARY_COLOR] = config.accentColor
            preferences[KEY_LOGO_URL] = config.logoUrl ?: ""
            preferences[KEY_SERVER_URL] = config.serverUrl ?: DEFAULT_SERVER_URL
        }
    }

    suspend fun getConfig(): WhiteLabelConfig {
        val prefs = dataStore.data.first()

        return WhiteLabelConfig(
            appName = prefs[KEY_APP_NAME] ?: DEFAULT_APP_NAME,
            primaryColor = prefs[KEY_PRIMARY_COLOR] ?: DEFAULT_PRIMARY_COLOR,
            secondaryColor = prefs[KEY_SECONDARY_COLOR] ?: DEFAULT_SECONDARY_COLOR,
            logoUrl = prefs[KEY_LOGO_URL]?.takeIf { it.isNotEmpty() },
            serverUrl = prefs[KEY_SERVER_URL] ?: DEFAULT_SERVER_URL
        )
    }

    fun observeConfig(): Flow<WhiteLabelConfig> {
        return dataStore.data.map { preferences ->
            WhiteLabelConfig(
                appName = preferences[KEY_APP_NAME] ?: DEFAULT_APP_NAME,
                primaryColor = preferences[KEY_PRIMARY_COLOR] ?: DEFAULT_PRIMARY_COLOR,
                secondaryColor = preferences[KEY_SECONDARY_COLOR] ?: DEFAULT_SECONDARY_COLOR,
                logoUrl = preferences[KEY_LOGO_URL]?.takeIf { it.isNotEmpty() },
                serverUrl = preferences[KEY_SERVER_URL] ?: DEFAULT_SERVER_URL
            )
        }
    }
}
