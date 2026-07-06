package com.ebtikar.skinanalyzer

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.hilt.android.HiltAndroidApp
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@HiltAndroidApp
class SkinAnalyzerApp : Application() {

    @Inject
    lateinit var providerManager: AnalysisProviderManager

    @Inject
    lateinit var preferencesManager: PreferencesManager

    override fun onCreate() {
        super.onCreate()

        if (BuildConfig.DEBUG) {
            Timber.plant(Timber.DebugTree())
        }

        providerManager.initializeAll()

        CoroutineScope(Dispatchers.IO).launch {
            preferencesManager.runDiagnosisModeMigration()
        }

        createUpdateNotificationChannel()

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
