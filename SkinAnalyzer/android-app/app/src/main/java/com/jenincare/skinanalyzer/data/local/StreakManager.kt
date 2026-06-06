package com.jenincare.skinanalyzer.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.intPreferencesKey
import androidx.datastore.preferences.core.longPreferencesKey
import androidx.datastore.preferences.core.stringSetPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale
import javax.inject.Inject
import javax.inject.Singleton

private val Context.streakDataStore: DataStore<Preferences> by preferencesDataStore(name = "streak_prefs")

@Singleton
class StreakManager @Inject constructor(
    private val context: Context
) {
    private val currentStreakKey = intPreferencesKey("current_streak")
    private val longestStreakKey = intPreferencesKey("longest_streak")
    private val lastScanDateKey = longPreferencesKey("last_scan_date")
    private val totalScansKey = intPreferencesKey("total_scans_count")
    private val scanDatesKey = stringSetPreferencesKey("scan_dates")

    data class StreakInfo(
        val currentStreak: Int,
        val longestStreak: Int,
        val totalScans: Int,
        val todayScanned: Boolean,
        val scanDates: Set<String>
    )

    suspend fun getStreakInfo(): StreakInfo {
        val prefs = context.streakDataStore.data.first()
        val currentStreak = prefs[currentStreakKey] ?: 0
        val longestStreak = prefs[longestStreakKey] ?: 0
        val lastScan = prefs[lastScanDateKey] ?: 0L
        val totalScans = prefs[totalScansKey] ?: 0
        val dates = prefs[scanDatesKey] ?: emptySet()
        val todayScanned = dates.contains(getTodayString())
        return StreakInfo(currentStreak, longestStreak, totalScans, todayScanned, dates)
    }

    suspend fun recordScan() {
        val info = getStreakInfo()
        val today = getTodayString()
        val yesterday = getYesterdayString()

        val newStreak = when {
            info.scanDates.contains(today) -> info.currentStreak
            info.scanDates.contains(yesterday) -> info.currentStreak + 1
            else -> 1
        }
        val newLongest = maxOf(newStreak, info.longestStreak)
        val newTotal = info.totalScans + 1
        val newDates = info.scanDates + today

        context.streakDataStore.edit { prefs ->
            prefs[currentStreakKey] = newStreak
            prefs[longestStreakKey] = newLongest
            prefs[lastScanDateKey] = System.currentTimeMillis()
            prefs[totalScansKey] = newTotal
            prefs[scanDatesKey] = newDates
        }
    }

    fun observeStreak(): Flow<StreakInfo> {
        return context.streakDataStore.data.map { prefs ->
            StreakInfo(
                currentStreak = prefs[currentStreakKey] ?: 0,
                longestStreak = prefs[longestStreakKey] ?: 0,
                totalScans = prefs[totalScansKey] ?: 0,
                todayScanned = (prefs[scanDatesKey] ?: emptySet()).contains(getTodayString()),
                scanDates = prefs[scanDatesKey] ?: emptySet()
            )
        }
    }

    private fun getTodayString(): String {
        return SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(Date())
    }

    private fun getYesterdayString(): String {
        val cal = Calendar.getInstance()
        cal.add(Calendar.DAY_OF_YEAR, -1)
        return SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(cal.time)
    }
}
