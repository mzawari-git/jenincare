package com.jenincare.skinanalyzer.ui

import android.content.res.Configuration
import android.os.Bundle
import android.view.View
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.core.view.ViewCompat
import androidx.lifecycle.lifecycleScope
import androidx.navigation.compose.rememberNavController
import com.jenincare.skinanalyzer.ui.navigation.SkinAnalyzerNavGraph
import com.jenincare.skinanalyzer.ui.theme.SkinAnalyzerTheme
import com.jenincare.skinanalyzer.ui.update.UpdateDialog
import com.jenincare.skinanalyzer.ui.update.UpdateManager
import com.jenincare.skinanalyzer.ui.update.UpdateProgress
import dagger.hilt.android.AndroidEntryPoint
import java.util.Locale
import androidx.compose.ui.platform.LocalConfiguration
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class MainActivity : ComponentActivity() {

    @Inject
    lateinit var updateManager: UpdateManager

    private var updateCheckDone = false
    private val pendingDeepLink = mutableStateOf<String?>(null)

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        applyAppLocale()
        enableEdgeToEdge()

        setContent {
            SkinAnalyzerTheme {
                val navController = rememberNavController()

                val deepLinkScanId by remember { pendingDeepLink }
                LaunchedEffect(deepLinkScanId) {
                    if (deepLinkScanId != null) {
                        pendingDeepLink.value = null
                        navController.navigate(com.jenincare.skinanalyzer.ui.navigation.Routes.report(deepLinkScanId!!)) {
                            popUpTo(0) { inclusive = false }
                        }
                    }
                }

                val initialDeepLinkScanId = remember {
                    com.jenincare.skinanalyzer.fcm.PushNotificationDeepLinkHandler.handleDeepLinkIntent(intent)
                }
                LaunchedEffect(initialDeepLinkScanId) {
                    if (initialDeepLinkScanId != null) {
                        navController.navigate(com.jenincare.skinanalyzer.ui.navigation.Routes.report(initialDeepLinkScanId)) {
                            popUpTo(0) { inclusive = false }
                        }
                    }
                }

                val configuration = LocalConfiguration.current

                LaunchedEffect(configuration) {
                    val locale = configuration.locales[0]
                    if (locale.language == "ar") {
                        window.decorView.layoutDirection = View.LAYOUT_DIRECTION_RTL
                    }
                }

                var showUpdateDialog by remember { mutableStateOf(false) }
                var updateResponse by remember { mutableStateOf<com.jenincare.skinanalyzer.data.remote.dto.AppUpdateResponse?>(null) }
                var updateProgress by remember { mutableStateOf(UpdateProgress()) }

                LaunchedEffect(Unit) {
                    if (!updateCheckDone) {
                        updateCheckDone = true
                        val response = updateManager.checkForUpdate()
                        if (response != null && updateManager.isUpdateAvailable(response)) {
                            updateResponse = response
                            showUpdateDialog = true
                        }
                    }
                }

                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    SkinAnalyzerNavGraph(
                        navController = navController,
                        startDestination = com.jenincare.skinanalyzer.ui.navigation.Routes.CAMERA
                    )
                }

                val currentUpdate = updateResponse
                if (showUpdateDialog && currentUpdate != null) {
                    UpdateDialog(
                        update = currentUpdate,
                        progress = updateProgress,
                        onUpdate = {
                            val url = currentUpdate.downloadUrl ?: return@UpdateDialog
                            if (!updateManager.canRequestInstallPackages()) {
                                updateManager.requestInstallPermission(
                                    this@MainActivity,
                                    INSTALL_PERMISSION_REQUEST_CODE
                                )
                                return@UpdateDialog
                            }
                            lifecycleScope.launch {
                                val result = updateManager.downloadApk(url) { progress ->
                                    updateProgress = progress
                                }
                                result.onSuccess { uri ->
                                    updateProgress = UpdateProgress(downloading = false, progress = 100)
                                    updateManager.installApk(uri)
                                }
                            }
                        },
                        onDismiss = {
                            showUpdateDialog = false
                        },
                        onOpenSettings = {
                            updateManager.requestInstallPermission(
                                this@MainActivity,
                                INSTALL_PERMISSION_REQUEST_CODE
                            )
                        }
                    )
                }
            }
        }
    }

    override fun onNewIntent(intent: android.content.Intent?) {
        super.onNewIntent(intent)
        setIntent(intent)
        val scanId = com.jenincare.skinanalyzer.fcm.PushNotificationDeepLinkHandler.handleDeepLinkIntent(intent)
        if (scanId != null) {
            pendingDeepLink.value = scanId
        }
    }

    @Suppress("DEPRECATION")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: android.content.Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == INSTALL_PERMISSION_REQUEST_CODE) {
            if (updateManager.canRequestInstallPackages()) {
                lifecycleScope.launch {
                    val update = updateManager.checkForUpdate()
                    if (update != null && updateManager.isUpdateAvailable(update) && update.downloadUrl != null) {
                        val result = updateManager.downloadApk(update.downloadUrl) {}
                        result.onSuccess { uri ->
                            updateManager.installApk(uri)
                        }
                    }
                }
            }
        }
    }

    private fun applyAppLocale() {
        val sharedPrefs = getSharedPreferences("skin_analyzer_prefs", MODE_PRIVATE)
        val languageCode = sharedPrefs.getString("language", "ar") ?: "ar"

        val locale = Locale(languageCode)
        Locale.setDefault(locale)

        val config = resources.configuration
        config.setLocale(locale)
        @Suppress("DEPRECATION")
        resources.updateConfiguration(config, resources.displayMetrics)

        @Suppress("DEPRECATION")
        baseContext.resources.updateConfiguration(config, baseContext.resources.displayMetrics)
    }

    companion object {
        private const val INSTALL_PERMISSION_REQUEST_CODE = 1001
    }
}
