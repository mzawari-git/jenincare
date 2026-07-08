package com.ebtikar.skinanalyzer

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class BootReceiver : BroadcastReceiver() {

    @Inject
    lateinit var fiseGpioController: FiseGpioController

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != Intent.ACTION_BOOT_COMPLETED) return

        Timber.i("Boot completed — starting GPIO setup")

        CoroutineScope(Dispatchers.IO + SupervisorJob()).launch {
            try {
                if (!fiseGpioController.isAvailable) {
                    Timber.i("GPIO not available after boot, running shell setup...")
                    val result = fiseGpioController.setupGpioViaShell()
                    Timber.i("Boot GPIO setup result: $result")
                } else {
                    Timber.i("GPIO already available after boot")
                }
            } catch (e: Exception) {
                Timber.e(e, "Boot GPIO setup failed")
            }
        }
    }
}
