package com.ebtikar.skinanalyzer

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber
import java.util.concurrent.TimeUnit
import javax.inject.Inject

@AndroidEntryPoint
class BootReceiver : BroadcastReceiver() {

    companion object {
        private const val CHANNEL_ID = "gpio_setup_channel"
        private const val NOTIFICATION_ID = 9001
    }

    @Inject
    lateinit var fiseGpioController: FiseGpioController

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != Intent.ACTION_BOOT_COMPLETED) return

        Timber.i("Boot completed — starting GPIO setup")

        CoroutineScope(Dispatchers.IO + SupervisorJob()).launch {
            var success = false
            try {
                // Step 1: Check if GPIO already available (init.rc set it up)
                if (fiseGpioController.isAvailable) {
                    Timber.i("GPIO already available after boot (init.rc worked)")
                    success = true
                } else {
                    Timber.i("GPIO not available, trying shell setup...")

                    // Step 2: Try shell setup (requires permissions)
                    val shellResult = fiseGpioController.setupGpioViaShell()
                    Timber.i("Shell GPIO setup result: $shellResult")

                    if (shellResult) {
                        success = true
                    } else {
                        // Step 3: Try FISE rebind as last resort
                        Timber.i("Shell setup failed, trying FISE rebind...")
                        val rebindResult = tryFiseRebind()
                        if (rebindResult) {
                            // Re-check availability after rebind
                            delay(500)
                            val recheck = fiseGpioController.recheckAvailability()
                            Timber.i("FISE rebind + recheck: $recheck")
                            success = recheck
                        }
                    }
                }

                if (!success) {
                    Timber.w("All GPIO setup methods failed on boot — user must run setup_gpio.ps1")
                    withContext(Dispatchers.Main) {
                        showGpioFailureNotification(context)
                    }
                } else {
                    Timber.i("GPIO setup completed successfully on boot")
                }
            } catch (e: Exception) {
                Timber.e(e, "Boot GPIO setup failed with exception")
                withContext(Dispatchers.Main) {
                    showGpioFailureNotification(context)
                }
            }
        }
    }

    private suspend fun tryFiseRebind(): Boolean {
        return withContext(Dispatchers.IO) {
            try {
                val unbind = Runtime.getRuntime().exec(arrayOf("sh", "-c",
                    "echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind 2>/dev/null"
                ))
                unbind.waitFor(3, TimeUnit.SECONDS)
                delay(300)

                val bind = Runtime.getRuntime().exec(arrayOf("sh", "-c",
                    "echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind 2>/dev/null"
                ))
                val completed = bind.waitFor(3, TimeUnit.SECONDS)
                val exitCode = if (completed) bind.exitValue() else -1
                Timber.i("FISE rebind: completed=$completed, exit=$exitCode")
                completed && exitCode == 0
            } catch (e: Exception) {
                Timber.w(e, "FISE rebind failed")
                false
            }
        }
    }

    private fun showGpioFailureNotification(context: Context) {
        val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "إعداد GPIO",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "تنبيه فشل إعداد GPIO عند بدء التشغيل"
            }
            notificationManager.createNotificationChannel(channel)
        }

        val notification = NotificationCompat.Builder(context, CHANNEL_ID)
            .setSmallIcon(R.drawable.ic_diagnostics)
            .setContentTitle(" SkinAnalyzer — فشل إعداد GPIO")
            .setContentText("شغّل setup_gpio.ps1 من PowerShell لإعداد الأضواء")
            .setStyle(NotificationCompat.BigTextStyle()
                .bigText("لم يتمكن النظام من إعداد GPIO تلقائياً. شغّل السكربت التالي في PowerShell:\n.\\setup_gpio.ps1\n\nبدون هذا السكربت، الأضواء لن تعمل."))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()

        notificationManager.notify(NOTIFICATION_ID, notification)
    }
}
