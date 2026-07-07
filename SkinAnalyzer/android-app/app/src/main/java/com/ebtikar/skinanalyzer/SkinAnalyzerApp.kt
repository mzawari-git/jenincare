package com.ebtikar.skinanalyzer

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import androidx.work.Configuration
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.ScanReminderWorker
import dagger.hilt.android.HiltAndroidApp
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@HiltAndroidApp
class SkinAnalyzerApp : Application(), Configuration.Provider {

    @Inject
    lateinit var providerManager: AnalysisProviderManager

    @Inject
    lateinit var preferencesManager: PreferencesManager

    override val workManagerConfiguration: Configuration
        get() = Configuration.Builder()
            .setMinimumLoggingLevel(if (BuildConfig.DEBUG) android.util.Log.DEBUG else android.util.Log.ERROR)
            .build()

    override fun onCreate() {
        super.onCreate()

        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            Timber.e(throwable, "Uncaught exception in thread ${thread.name}")
            android.util.Log.e("SkinAnalyzer", "FATAL: ${throwable.message}", throwable)
        }

        if (BuildConfig.DEBUG) {
            Timber.plant(Timber.DebugTree())
        }

        providerManager.initializeAll()

        CoroutineScope(Dispatchers.IO + kotlinx.coroutines.SupervisorJob()).launch {
            try {
                preferencesManager.runDiagnosisModeMigration()
            } catch (e: Exception) {
                Timber.e(e, "Diagnosis mode migration failed")
            }
        }

        createUpdateNotificationChannel()
        ScanReminderWorker.createChannel(this)

        CoroutineScope(Dispatchers.IO + kotlinx.coroutines.SupervisorJob()).launch {
            try {
                val enabled = preferencesManager.scanReminderEnabledFlow.first()
                if (enabled) {
                    val hours = preferencesManager.scanReminderIntervalHoursFlow.first()
                    ScanReminderWorker.schedule(this@SkinAnalyzerApp, hours)
                }
            } catch (e: Exception) {
                Timber.e(e, "Scan reminder scheduling failed")
            }
        }

        Timber.i("SkinAnalyzer App initialized - ${com.ebtikar.skinanalyzer.util.Constants.DEVICE_NAME}")
        Timber.i("Device: ${com.ebtikar.skinanalyzer.util.Constants.DEVICE_BRAND} ${com.ebtikar.skinanalyzer.util.Constants.DEVICE_MODEL}")
        Timber.i("Edition: ${com.ebtikar.skinanalyzer.util.Constants.DEVICE_EDITION}")
    }

    private fun createUpdateNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                UPDATE_CHANNEL_ID,
                getString(R.string.update_notification_channel),
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = getString(R.string.update_notification_channel_desc)
                enableVibration(false)
                setShowBadge(false)
            }
            val manager = getSystemService(NotificationManager::class.java)
            manager.createNotificationChannel(channel)
            Timber.i("Update notification channel created")
        }
    }

    companion object {
        const val UPDATE_CHANNEL_ID = "app_update_channel"
    }
}
