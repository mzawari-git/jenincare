package com.jenincare.skinanalyzer.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.intPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.first
import javax.inject.Inject
import javax.inject.Singleton

private val Context.ratingDataStore: DataStore<Preferences> by preferencesDataStore(name = "rating_prefs")

@Singleton
class AppRatingManager @Inject constructor(
    private val context: Context
) {
    private val scanCountKey = intPreferencesKey("scan_count_for_rating")
    private val ratedKey = booleanPreferencesKey("already_rated")
    private val dismissedKey = booleanPreferencesKey("rating_dismissed")

    private val threshold = 5

    suspend fun incrementScanCount(): Boolean {
        val prefs = context.ratingDataStore.data.first()
        val alreadyRated = prefs[ratedKey] ?: false
        val dismissed = prefs[dismissedKey] ?: false
        if (alreadyRated || dismissed) return false

        val count = (prefs[scanCountKey] ?: 0) + 1
        context.ratingDataStore.edit { p ->
            p[scanCountKey] = count
        }
        return count >= threshold
    }

    suspend fun markAsRated() {
        context.ratingDataStore.edit { it[ratedKey] = true }
    }

    suspend fun dismissRating() {
        context.ratingDataStore.edit { it[dismissedKey] = true }
    }

    suspend fun shouldShowRating(): Boolean {
        val prefs = context.ratingDataStore.data.first()
        val alreadyRated = prefs[ratedKey] ?: false
        val dismissed = prefs[dismissedKey] ?: false
        val count = prefs[scanCountKey] ?: 0
        return !alreadyRated && !dismissed && count >= threshold
    }
}
