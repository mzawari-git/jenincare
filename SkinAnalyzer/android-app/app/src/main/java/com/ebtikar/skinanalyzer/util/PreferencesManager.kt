package com.ebtikar.skinanalyzer.util

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.floatPreferencesKey
import androidx.datastore.preferences.core.intPreferencesKey
import androidx.datastore.preferences.core.longPreferencesKey
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
        private val KEY_CAMERA_ROTATION = intPreferencesKey("camera_rotation")
        private val KEY_CAMERA_ZOOM_PROGRESS = intPreferencesKey("camera_zoom_progress")
        private val KEY_FACE_VALIDATION_ENABLED = booleanPreferencesKey("face_validation_enabled")
        private val KEY_FACE_VALIDATION_THRESHOLD = intPreferencesKey("face_validation_threshold")
        private val KEY_AUTO_UPDATE_ENABLED = booleanPreferencesKey("auto_update_enabled")
        private val KEY_UPDATE_CHANNEL = stringPreferencesKey("update_channel")
        private val KEY_LAST_UPDATE_CHECK = longPreferencesKey("last_update_check")
        private val KEY_SCAN_OVERLAY_STYLE = stringPreferencesKey("scan_overlay_style")
        private val KEY_SHOW_FACE_MESH = booleanPreferencesKey("show_face_mesh")
        private val KEY_SHOW_MEDICAL_LENS = booleanPreferencesKey("show_medical_lens")
        private val KEY_SHOW_SCAN_GRID = booleanPreferencesKey("show_scan_grid")
        private val KEY_SHOW_SCAN_RINGS = booleanPreferencesKey("show_scan_rings")
        private val KEY_SHOW_SPECTRAL_GRAPH = booleanPreferencesKey("show_spectral_graph")
        private val KEY_SHOW_MEDICAL_INDICATORS = booleanPreferencesKey("show_medical_indicators")
        private val KEY_SHOW_SCAN_DATA_PANEL = booleanPreferencesKey("show_scan_data_panel")
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

    val cameraRotationFlow: Flow<Int> = context.dataStore.data.map { prefs ->
        prefs[KEY_CAMERA_ROTATION] ?: 0
    }

    val cameraZoomProgressFlow: Flow<Int> = context.dataStore.data.map { prefs ->
        prefs[KEY_CAMERA_ZOOM_PROGRESS] ?: 60
    }

    val faceValidationEnabledFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_FACE_VALIDATION_ENABLED] ?: true
    }

    val faceValidationThresholdFlow: Flow<Int> = context.dataStore.data.map { prefs ->
        (prefs[KEY_FACE_VALIDATION_THRESHOLD] ?: 60).coerceAtMost(85)
    }

    val autoUpdateEnabledFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_AUTO_UPDATE_ENABLED] ?: true
    }

    val updateChannelFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_UPDATE_CHANNEL] ?: "stable"
    }

    val lastUpdateCheckFlow: Flow<Long> = context.dataStore.data.map { prefs ->
        prefs[KEY_LAST_UPDATE_CHECK] ?: 0L
    }

    val scanOverlayStyleFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_SCAN_OVERLAY_STYLE] ?: "professional"
    }

    val showFaceMeshFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_FACE_MESH] ?: true
    }

    val showMedicalLensFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_MEDICAL_LENS] ?: true
    }

    val showScanGridFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_SCAN_GRID] ?: false
    }

    val showScanRingsFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_SCAN_RINGS] ?: false
    }

    val showSpectralGraphFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_SPECTRAL_GRAPH] ?: true
    }

    val showMedicalIndicatorsFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_MEDICAL_INDICATORS] ?: true
    }

    val showScanDataPanelFlow: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SHOW_SCAN_DATA_PANEL] ?: true
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

    suspend fun setCameraRotation(rotation: Int) {
        context.dataStore.edit { prefs -> prefs[KEY_CAMERA_ROTATION] = rotation }
    }

    suspend fun setCameraZoomProgress(progress: Int) {
        context.dataStore.edit { prefs -> prefs[KEY_CAMERA_ZOOM_PROGRESS] = progress }
    }

    suspend fun setFaceValidationEnabled(enabled: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_FACE_VALIDATION_ENABLED] = enabled }
    }

    suspend fun setFaceValidationThreshold(threshold: Int) {
        context.dataStore.edit { prefs -> prefs[KEY_FACE_VALIDATION_THRESHOLD] = threshold }
    }

    suspend fun setAutoUpdateEnabled(enabled: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_AUTO_UPDATE_ENABLED] = enabled }
    }

    suspend fun setUpdateChannel(channel: String) {
        context.dataStore.edit { prefs -> prefs[KEY_UPDATE_CHANNEL] = channel }
    }

    suspend fun setLastUpdateCheck(timestamp: Long) {
        context.dataStore.edit { prefs -> prefs[KEY_LAST_UPDATE_CHECK] = timestamp }
    }

    suspend fun setScanOverlayStyle(style: String) {
        context.dataStore.edit { prefs -> prefs[KEY_SCAN_OVERLAY_STYLE] = style }
    }

    suspend fun setShowFaceMesh(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_FACE_MESH] = shown }
    }

    suspend fun setShowMedicalLens(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_MEDICAL_LENS] = shown }
    }

    suspend fun setShowScanGrid(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_SCAN_GRID] = shown }
    }

    suspend fun setShowScanRings(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_SCAN_RINGS] = shown }
    }

    suspend fun setShowSpectralGraph(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_SPECTRAL_GRAPH] = shown }
    }

    suspend fun setShowMedicalIndicators(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_MEDICAL_INDICATORS] = shown }
    }

    suspend fun setShowScanDataPanel(shown: Boolean) {
        context.dataStore.edit { prefs -> prefs[KEY_SHOW_SCAN_DATA_PANEL] = shown }
    }

    suspend fun applyScanOverlayStyle(style: String) {
        context.dataStore.edit { prefs ->
            prefs[KEY_SCAN_OVERLAY_STYLE] = style
            when (style) {
                "professional" -> {
                    prefs[KEY_SHOW_FACE_MESH] = true
                    prefs[KEY_SHOW_MEDICAL_LENS] = true
                    prefs[KEY_SHOW_SCAN_GRID] = false
                    prefs[KEY_SHOW_SCAN_RINGS] = false
                    prefs[KEY_SHOW_SPECTRAL_GRAPH] = true
                    prefs[KEY_SHOW_MEDICAL_INDICATORS] = true
                    prefs[KEY_SHOW_SCAN_DATA_PANEL] = true
                }
                "minimal" -> {
                    prefs[KEY_SHOW_FACE_MESH] = false
                    prefs[KEY_SHOW_MEDICAL_LENS] = false
                    prefs[KEY_SHOW_SCAN_GRID] = true
                    prefs[KEY_SHOW_SCAN_RINGS] = false
                    prefs[KEY_SHOW_SPECTRAL_GRAPH] = false
                    prefs[KEY_SHOW_MEDICAL_INDICATORS] = true
                    prefs[KEY_SHOW_SCAN_DATA_PANEL] = true
                }
            }
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
