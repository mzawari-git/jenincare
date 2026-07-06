package com.ebtikar.skinanalyzer.util

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.FileProvider
import com.ebtikar.skinanalyzer.BuildConfig
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.SkinAnalyzerApp
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.serialization.Serializable
import kotlinx.serialization.json.Json
import okhttp3.OkHttpClient
import java.io.File
import timber.log.Timber
import java.io.FileOutputStream
import java.util.concurrent.TimeUnit
import java.util.concurrent.atomic.AtomicInteger
import javax.inject.Inject
import javax.inject.Singleton

@Serializable
data class GitHubRelease(
    val tag_name: String,
    val name: String? = null,
    val body: String? = null,
    val assets: List<GitHubAsset> = emptyList(),
    val prerelease: Boolean = false
)

@Serializable
data class GitHubAsset(
    val id: Long = 0,
    val name: String,
    val browser_download_url: String,
    val size: Long = 0
)

data class UpdateInfo(
    val latestVersion: String,
    val downloadUrl: String,
    val releaseNotes: String?,
    val assetName: String,
    val isPrerelease: Boolean,
    val assetId: Long = 0
)

@Singleton
class UpdateChecker @Inject constructor(
    private val context: Context
) {
    private val client = OkHttpClient.Builder()
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .build()

    private val json = Json { ignoreUnknownKeys = true; coerceInputValues = true }
    private val notificationIdCounter = AtomicInteger(1000)

    companion object {
        private const val REPO_OWNER = "mzawari-git"
        private const val REPO_NAME = "jenincare"
        private const val GITHUB_API = "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME/releases/latest"
        private const val NOTIFICATION_PROGRESS_ID = 1001
        private const val NOTIFICATION_READY_ID = 1002
    }

    suspend fun checkForUpdate(channel: String = "stable"): UpdateInfo? = withContext(Dispatchers.IO) {
        try {
            val requestBuilder = okhttp3.Request.Builder()
                .url(GITHUB_API)
                .header("Accept", "application/vnd.github.v3+json")
            if (BuildConfig.GITHUB_TOKEN.isNotBlank() && BuildConfig.GITHUB_TOKEN != "mock_token") {
                requestBuilder.header("Authorization", "token ${BuildConfig.GITHUB_TOKEN}")
            }
            val request = requestBuilder.build()

            val response = client.newCall(request).execute()
            if (!response.isSuccessful) {
                Timber.w("GitHub API returned ${response.code}")
                return@withContext null
            }

            val body = response.body?.string() ?: return@withContext null
            val release = json.decodeFromString<GitHubRelease>(body)

            if (channel == "stable" && release.prerelease) {
                Timber.i("Skipping prerelease for stable channel")
                return@withContext null
            }

            val apkAsset = release.assets.firstOrNull { it.name.endsWith(".apk") }
                ?: return@withContext null

            UpdateInfo(
                latestVersion = release.tag_name.removePrefix("v"),
                downloadUrl = apkAsset.browser_download_url,
                releaseNotes = release.body,
                assetName = apkAsset.name,
                isPrerelease = release.prerelease,
                assetId = apkAsset.id
            )
        } catch (e: Exception) {
            Timber.w(e, "Update check failed")
            null
        }
    }

    fun getCurrentVersion(): String {
        return try {
            val pkg = context.packageManager.getPackageInfo(context.packageName, 0)
            val raw = pkg.versionName ?: "1.0.0"
            raw.removeSuffix("-max").removeSuffix("-debug").removePrefix("v")
                .takeIf { it.isNotEmpty() } ?: "1.0.0"
        } catch (e: Exception) {
            "1.0.0"
        }
    }

    fun isNewerVersion(latest: String): Boolean {
        val current = getCurrentVersion()
        return compareSemver(latest, current) > 0
    }

    private fun compareSemver(v1: String, v2: String): Int {
        val parts1 = v1.trimStart('v').split(".").map { it.toIntOrNull() ?: 0 }
        val parts2 = v2.trimStart('v').split(".").map { it.toIntOrNull() ?: 0 }
        for (i in 0 until maxOf(parts1.size, parts2.size)) {
            val a = parts1.getOrElse(i) { 0 }
            val b = parts2.getOrElse(i) { 0 }
            if (a != b) return a.compareTo(b)
        }
        return 0
    }

    private fun okHttpRequestBuilder(url: String): okhttp3.Request.Builder {
        val builder = okhttp3.Request.Builder()
            .url(url)
            .header("Accept", "application/octet-stream")
        if (BuildConfig.GITHUB_TOKEN.isNotBlank() && BuildConfig.GITHUB_TOKEN != "mock_token") {
            builder.header("Authorization", "token ${BuildConfig.GITHUB_TOKEN}")
        }
        return builder
    }

    suspend fun downloadApk(
        updateInfo: UpdateInfo,
        onProgress: (Float) -> Unit
    ): Uri? = withContext(Dispatchers.IO) {
        try {
            val request = okHttpRequestBuilder(updateInfo.downloadUrl).build()

            val response = client.newCall(request).execute()
            if (!response.isSuccessful) {
                Timber.w("Download response: ${response.code} ${response.message}")
                return@withContext null
            }

            val body = response.body ?: run {
                Timber.w("Download response body null")
                return@withContext null
            }
            val totalBytes = body.contentLength()
            val targetDir = File(context.cacheDir, "update")
            targetDir.mkdirs()
            val downloadedFile = File(targetDir, updateInfo.assetName)

            FileOutputStream(downloadedFile).use { output ->
                val buffer = ByteArray(8192)
                var bytesRead = 0L
                body.byteStream().use { input ->
                    var read = input.read(buffer)
                    while (read != -1) {
                        output.write(buffer, 0, read)
                        bytesRead += read
                        if (totalBytes > 0) {
                            onProgress(bytesRead.toFloat() / totalBytes)
                        }
                        read = input.read(buffer)
                    }
                }
            }

            FileProvider.getUriForFile(
                context,
                "${context.packageName}.fileprovider",
                downloadedFile
            )
        } catch (e: Exception) {
            Timber.w(e, "APK download failed")
            null
        }
    }

    suspend fun downloadApkWithNotification(updateInfo: UpdateInfo): Uri? = withContext(Dispatchers.IO) {
        val notificationManager = NotificationManagerCompat.from(context)
        val startNotification = NotificationCompat.Builder(context, SkinAnalyzerApp.UPDATE_CHANNEL_ID)
            .setSmallIcon(android.R.drawable.stat_sys_download)
            .setContentTitle(context.getString(R.string.update_downloading))
            .setContentText("v${updateInfo.latestVersion}")
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .setOngoing(true)
            .setProgress(100, 0, true)
            .build()
        notificationManager.notify(NOTIFICATION_PROGRESS_ID, startNotification)

        try {
            val request = okHttpRequestBuilder(updateInfo.downloadUrl).build()

            val response = client.newCall(request).execute()
            if (!response.isSuccessful) {
                Timber.w("Download response: ${response.code} ${response.message}")
                notificationManager.cancel(NOTIFICATION_PROGRESS_ID)
                return@withContext null
            }

            val body = response.body ?: run {
                Timber.w("Download response body null")
                notificationManager.cancel(NOTIFICATION_PROGRESS_ID)
                return@withContext null
            }

            val totalBytes = body.contentLength()
            val targetDir = File(context.cacheDir, "update")
            targetDir.mkdirs()
            val downloadedFile = File(targetDir, updateInfo.assetName)

            FileOutputStream(downloadedFile).use { output ->
                val buffer = ByteArray(8192)
                var bytesRead = 0L
                body.byteStream().use { input ->
                    var read = input.read(buffer)
                    while (read != -1) {
                        output.write(buffer, 0, read)
                        bytesRead += read
                        if (totalBytes > 0 && bytesRead % (totalBytes / 20 + 1) < 8192) {
                            val progress = ((bytesRead * 100) / totalBytes).toInt()
                            val progressNotification = NotificationCompat.Builder(context, SkinAnalyzerApp.UPDATE_CHANNEL_ID)
                                .setSmallIcon(android.R.drawable.stat_sys_download)
                                .setContentTitle(context.getString(R.string.update_downloading))
                                .setContentText("v${updateInfo.latestVersion} — $progress%")
                                .setPriority(NotificationCompat.PRIORITY_LOW)
                                .setOngoing(true)
                                .setProgress(100, progress, false)
                                .build()
                            notificationManager.notify(NOTIFICATION_PROGRESS_ID, progressNotification)
                        }
                        read = input.read(buffer)
                    }
                }
            }

            notificationManager.cancel(NOTIFICATION_PROGRESS_ID)
            return@withContext FileProvider.getUriForFile(
                context,
                "${context.packageName}.fileprovider",
                downloadedFile
            )
        } catch (e: Exception) {
            Timber.w(e, "APK download with notification failed")
            notificationManager.cancel(NOTIFICATION_PROGRESS_ID)
            null
        }
    }

    fun showInstallNotification(updateInfo: UpdateInfo, apkUri: Uri) {
        val installIntent = Intent(Intent.ACTION_VIEW).apply {
            setDataAndType(apkUri, "application/vnd.android.package-archive")
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_GRANT_READ_URI_PERMISSION
        }
        val pendingIntent = PendingIntent.getActivity(
            context,
            notificationIdCounter.incrementAndGet(),
            installIntent,
            PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT
        )

        val notification = NotificationCompat.Builder(context, SkinAnalyzerApp.UPDATE_CHANNEL_ID)
            .setSmallIcon(android.R.drawable.stat_sys_download_done)
            .setContentTitle(context.getString(R.string.update_ready))
            .setContentText("v${updateInfo.latestVersion}")
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .addAction(
                android.R.drawable.stat_sys_download_done,
                context.getString(android.R.string.ok),
                pendingIntent
            )
            .build()

        NotificationManagerCompat.from(context).notify(NOTIFICATION_READY_ID, notification)
    }

    fun installApk(apkUri: Uri) {
        val intent = Intent(Intent.ACTION_VIEW).apply {
            setDataAndType(apkUri, "application/vnd.android.package-archive")
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_GRANT_READ_URI_PERMISSION
        }
        context.startActivity(intent)
    }

    /**
     * Fetches all available GitHub releases for rollback support.
     * Returns a list of UpdateInfo sorted newest-first, excluding the current version.
     */
    suspend fun fetchAllReleases(): List<UpdateInfo> = withContext(Dispatchers.IO) {
        try {
            val allReleasesUrl = "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME/releases?per_page=20"
            val requestBuilder = okhttp3.Request.Builder()
                .url(allReleasesUrl)
                .header("Accept", "application/vnd.github.v3+json")
            if (BuildConfig.GITHUB_TOKEN.isNotBlank() && BuildConfig.GITHUB_TOKEN != "mock_token") {
                requestBuilder.header("Authorization", "token ${BuildConfig.GITHUB_TOKEN}")
            }
            val response = client.newCall(requestBuilder.build()).execute()
            if (!response.isSuccessful) {
                Timber.w("fetchAllReleases: GitHub API returned ${response.code}")
                return@withContext emptyList()
            }
            val body = response.body?.string() ?: return@withContext emptyList()
            val releases = json.decodeFromString<List<GitHubRelease>>(body)
            val current = getCurrentVersion()

            releases
                .filter { release ->
                    // Only include releases that have an APK asset
                    release.assets.any { it.name.endsWith(".apk") }
                }
                .mapNotNull { release ->
                    val apkAsset = release.assets.firstOrNull { it.name.endsWith(".apk") }
                        ?: return@mapNotNull null
                    val version = release.tag_name.removePrefix("v")
                    UpdateInfo(
                        latestVersion = version,
                        downloadUrl = apkAsset.browser_download_url,
                        releaseNotes = release.body,
                        assetName = apkAsset.name,
                        isPrerelease = release.prerelease,
                        assetId = apkAsset.id
                    )
                }
                .filter { it.latestVersion != current } // exclude current version
                .sortedWith(compareByDescending { it.latestVersion }) // newest first
        } catch (e: Exception) {
            Timber.w(e, "fetchAllReleases failed")
            emptyList()
        }
    }
}
