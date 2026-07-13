package com.ebtikar.skinanalyzer

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import androidx.work.Configuration
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.ScanReminderWorker
import com.ebtikar.skinanalyzer.util.UpdateChecker
import dagger.hilt.android.HiltAndroidApp
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber
import javax.inject.Inject

@HiltAndroidApp
class SkinAnalyzerApp : Application(), Configuration.Provider {

    @Inject
    lateinit var providerManager: AnalysisProviderManager

    @Inject
    lateinit var preferencesManager: PreferencesManager

    @Inject
    lateinit var updateChecker: UpdateChecker

    @Inject
    lateinit var fiseGpioController: FiseGpioController

    override val workManagerConfiguration: Configuration
        get() = Configuration.Builder()
            .setMinimumLoggingLevel(if (BuildConfig.DEBUG) android.util.Log.DEBUG else android.util.Log.ERROR)
            .build()

    private val appScope = CoroutineScope(Dispatchers.IO + SupervisorJob())

    override fun onCreate() {
        super.onCreate()

        val defaultHandler = Thread.getDefaultUncaughtExceptionHandler()
        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            Timber.e(throwable, "Uncaught exception in thread ${thread.name}")
            android.util.Log.e("SkinAnalyzer", "FATAL: ${throwable.message}", throwable)
            defaultHandler?.uncaughtException(thread, throwable)
        }

        if (BuildConfig.DEBUG) {
            Timber.plant(Timber.DebugTree())
        }

        createUpdateNotificationChannel()
        ScanReminderWorker.createChannel(this)

        appScope.launch {
            try {
                withContext(Dispatchers.IO) { providerManager.initializeAll() }
            } catch (e: Exception) {
                Timber.e(e, "Provider initialization failed")
            }
        }

        appScope.launch {
            try {
                preferencesManager.runDiagnosisModeMigration()
            } catch (e: Exception) {
                Timber.e(e, "Diagnosis mode migration failed")
            }
        }

        appScope.launch {
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

        appScope.launch {
            try {
                fiseGpioController.awaitInitialized()
                if (!fiseGpioController.isAvailable) {
                    Timber.i("GPIO not available, attempting shell setup...")
                    fiseGpioController.setupGpioViaShell()
                }
            } catch (e: Exception) {
                Timber.w(e, "GPIO shell setup failed on startup")
            }
        }

        appScope.launch {
            try {
                val autoUpdateEnabled = preferencesManager.autoUpdateEnabledFlow.first()
                if (!autoUpdateEnabled) return@launch

                val lastCheck = preferencesManager.lastUpdateCheckFlow.first()
                val hoursSinceLastCheck = (System.currentTimeMillis() - lastCheck) / 3600000L
                if (hoursSinceLastCheck < 24) {
                    Timber.i("Auto-update: skipped (${hoursSinceLastCheck}h since last check, need 24h)")
                    return@launch
                }

                val channel = preferencesManager.updateChannelFlow.first()
                Timber.i("Auto-update check: enabled=$autoUpdateEnabled, channel=$channel")
                val updateInfo = updateChecker.checkForUpdate(channel)
                if (updateInfo != null && updateChecker.isNewerVersion(updateInfo.latestVersion)) {
                    Timber.i("Auto-update found: v${updateInfo.latestVersion} (current: ${updateChecker.getCurrentVersion()})")
                    val uri = updateChecker.downloadApkWithNotification(updateInfo)
                    if (uri != null) {
                        updateChecker.showInstallNotification(updateInfo, uri)
                        Timber.i("Auto-update downloaded, install notification shown")
                    }
                } else {
                    Timber.i("Auto-update: app is up to date (v${updateChecker.getCurrentVersion()})")
                }
                preferencesManager.setLastUpdateCheck(System.currentTimeMillis())
            } catch (e: Exception) {
                Timber.w(e, "Auto-update check failed")
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
