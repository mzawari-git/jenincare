package com.jenincare.skinanalyzer.fcm

import android.content.Intent
import android.net.Uri

object PushNotificationDeepLinkHandler {

    private const val SCHEME = "skinanalyzer"
    private const val HOST = "report"

    fun extractScanId(uri: Uri?): String? {
        if (uri == null) return null
        if (uri.scheme != SCHEME) return null
        val segments = uri.pathSegments
        if (segments.size == 2 && segments[0] == HOST) {
            return segments[1]
        }
        return null
    }

    fun handleDeepLinkIntent(intent: Intent?): String? {
        val uri = intent?.data ?: return null
        return extractScanId(uri)
    }
}
