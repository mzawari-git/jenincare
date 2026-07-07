package com.ebtikar.skinanalyzer.util

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.work.CoroutineWorker
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import com.ebtikar.skinanalyzer.ui.home.HomeActivity
import timber.log.Timber
import java.util.concurrent.TimeUnit

class ScanReminderWorker(
    context: Context,
    params: WorkerParameters
) : CoroutineWorker(context, params) {

    override suspend fun doWork(): Result {
        Timber.i("Scan reminder fired")
        createChannel(applicationContext)
        showNotification()
        return Result.success()
    }

    private fun showNotification() {
        val channelId = REMINDER_CHANNEL_ID

        val intent = Intent(applicationContext, HomeActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
        }
        val pendingIntent = PendingIntent.getActivity(
            applicationContext, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val notification = NotificationCompat.Builder(applicationContext, channelId)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentTitle("\u062a\u0642\u0637\u064a\u0631 \u0627\u0644\u0628\u0634\u0631\u0629")
            .setContentText("\u062d\u0627\u0646 \u0627\u0644\u0648\u0642\u062a \u0644\u0641\u062d\u0635 \u0628\u0634\u0631\u0643\u062a\u0643\u060c \u0627\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u0635\u062d\u0629 \u0627\u0644\u0628\u0634\u0631\u0629 \u064a\u062a\u0643\u0631\u0631 \u0645\u0639 \u0627\u0644\u0632\u0645\u0646.")
            .setStyle(NotificationCompat.BigTextStyle()
                .bigText("\u062d\u0627\u0646 \u0627\u0644\u0648\u0642\u062a \u0644\u0641\u062d\u0635 \u0628\u0634\u0631\u0643\u062a\u0643\u060c \u0627\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u0635\u062d\u0629 \u0627\u0644\u0628\u0634\u0631\u0629 \u064a\u062a\u0643\u0631\u0631 \u0645\u0639 \u0627\u0644\u0632\u0645\u0646. \u0627\u0644\u062a\u0642\u064a\u064a\u0645 \u0627\u0644\u062f\u0648\u0631\u064a \u064a\u0633\u0627\u0639\u062f\u0643 \u0641\u064a \u0627\u0643\u062a\u0634\u0627\u0641 \u0645\u0628\u0643\u0631 \u0639\u0646 \u062a\u063a\u064a\u064a\u0631\u0627\u062a \u0627\u0644\u0628\u0634\u0631\u0629."))
            .setContentIntent(pendingIntent)
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_DEFAULT)
            .build()

        val manager = applicationContext.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        manager.notify(REMINDER_NOTIFICATION_ID, notification)
        Timber.i("Scan reminder notification shown")
    }

    companion object {
        const val REMINDER_CHANNEL_ID = "scan_reminder_channel"
        const val REMINDER_NOTIFICATION_ID = 2001
        const val WORK_NAME = "scan_reminder_periodic"

        fun createChannel(context: Context) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                val channel = NotificationChannel(
                    REMINDER_CHANNEL_ID,
                    "\u062a\u0630\u0643\u064a\u0631 \u0627\u0644\u0641\u062d\u0635",
                    NotificationManager.IMPORTANCE_DEFAULT
                ).apply {
                    description = "\u062a\u0630\u0643\u0627\u0626\u0631 \u062f\u0648\u0631\u064a \u0644\u0641\u062d\u0635 \u0627\u0644\u0628\u0634\u0631\u0629"
                    enableVibration(true)
                    setShowBadge(true)
                }
                val manager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
                manager.createNotificationChannel(channel)
                Timber.i("Scan reminder notification channel created")
            }
        }

        fun schedule(context: Context, intervalHours: Int) {
            val request = PeriodicWorkRequestBuilder<ScanReminderWorker>(
                intervalHours.toLong(), TimeUnit.HOURS
            ).build()

            WorkManager.getInstance(context).enqueueUniquePeriodicWork(
                WORK_NAME,
                ExistingPeriodicWorkPolicy.UPDATE,
                request
            )
            Timber.i("Scan reminder scheduled: every ${intervalHours}h")
        }

        fun cancel(context: Context) {
            WorkManager.getInstance(context).cancelUniqueWork(WORK_NAME)
            Timber.i("Scan reminder cancelled")
        }
    }
}
