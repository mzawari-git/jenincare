package com.jenincare.skinanalyzer

import android.app.Application
import androidx.hilt.work.HiltWorkerFactory
import androidx.work.Configuration
import com.google.firebase.FirebaseApp
import com.google.firebase.crashlytics.FirebaseCrashlytics
import dagger.hilt.android.HiltAndroidApp
import javax.inject.Inject

@HiltAndroidApp
class SkinAnalyzerApp : Application(), Configuration.Provider {

    @Inject
    lateinit var workerFactory: HiltWorkerFactory

    override fun onCreate() {
        super.onCreate()
        instance = this
        android.util.Log.d("SkinAnalyzer", "App onCreate")
        initializeFirebase()
        initializeWhiteLabelDefaults()
    }

    override val workManagerConfiguration: Configuration
        get() = Configuration.Builder()
            .setWorkerFactory(workerFactory)
            .setMinimumLoggingLevel(android.util.Log.INFO)
            .build()

    private fun initializeFirebase() {
        try {
            FirebaseApp.initializeApp(this)
            android.util.Log.d("SkinAnalyzer", "Firebase initialized")
        } catch (e: Exception) {
            android.util.Log.w("SkinAnalyzer", "FirebaseApp init failed: ${e.message}")
        }
        try {
            FirebaseCrashlytics.getInstance().setCrashlyticsCollectionEnabled(true)
            android.util.Log.d("SkinAnalyzer", "Crashlytics enabled")
        } catch (e: Exception) {
            android.util.Log.w("SkinAnalyzer", "Crashlytics not available: ${e.message}")
        }
    }

    private fun initializeWhiteLabelDefaults() {
        if (!getSharedPreferences("white_label_prefs", MODE_PRIVATE)
                .contains("initialized")
        ) {
            getSharedPreferences("white_label_prefs", MODE_PRIVATE)
                .edit()
                .putBoolean("initialized", true)
                .putString("app_name", "SkinAnalyzer")
                .putString("primary_color", "#4CAF50")
                .putString("secondary_color", "#81C784")
                .putString("server_url", "https://jenincare.shop")
                .apply()
            android.util.Log.d("SkinAnalyzer", "White label defaults initialized")
        }
    }

    companion object {
        lateinit var instance: SkinAnalyzerApp
            private set
    }
}
