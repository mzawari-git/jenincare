package com.jenincare.skinanalyzer.ui.update

import android.app.Activity
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.provider.Settings
import androidx.core.content.FileProvider
import com.jenincare.skinanalyzer.BuildConfig
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.remote.dto.AppUpdateResponse
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.OkHttpClient
import okhttp3.Request
import java.io.File
import java.io.FileOutputStream
import java.io.IOException
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

data class UpdateProgress(
    val downloading: Boolean = false,
    val progress: Int = 0,
    val totalBytes: Long = 0L,
    val downloadedBytes: Long = 0L
)

@Singleton
class UpdateManager @Inject constructor(
    @ApplicationContext private val context: Context,
    private val apiService: ApiService
) {
    private val client = OkHttpClient.Builder()
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(120, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .build()

    private val updatesDir: File
        get() = File(context.cacheDir, "updates").also { it.mkdirs() }

    private val apkFile: File
        get() = File(updatesDir, "SkinAnalyzer.apk")

    private var currentVersionCode: Int = BuildConfig.VERSION_CODE

    suspend fun checkForUpdate(): AppUpdateResponse? {
        return try {
            val response = apiService.checkAppUpdate()
            if (response.isSuccessful) {
                response.body()
            } else null
        } catch (e: Exception) {
            null
        }
    }

    fun isUpdateAvailable(update: AppUpdateResponse): Boolean {
        if (!update.updateAvailable || update.versionCode == null) return false
        return update.versionCode > currentVersionCode
    }

    suspend fun downloadApk(
        downloadUrl: String,
        onProgress: (UpdateProgress) -> Unit
    ): Result<Uri> = withContext(Dispatchers.IO) {
        try {
            onProgress(UpdateProgress(downloading = true, progress = 0))

            updatesDir.listFiles()?.filter { it.extension == "apk" }?.forEach { it.delete() }

            val request = Request.Builder()
                .url(downloadUrl)
                .build()

            val response = client.newCall(request).execute()
            if (!response.isSuccessful) {
                return@withContext Result.failure(IOException("Download failed: ${response.code}"))
            }

            val body = response.body ?: return@withContext Result.failure(IOException("Empty response body"))

            val contentLength = body.contentLength()
            val inputStream = body.byteStream()
            val outputStream = FileOutputStream(apkFile)
            val buffer = ByteArray(8192)
            var bytesRead: Int
            var totalRead: Long = 0

            while (inputStream.read(buffer).also { bytesRead = it } != -1) {
                outputStream.write(buffer, 0, bytesRead)
                totalRead += bytesRead
                val progress = if (contentLength > 0) {
                    ((totalRead.toDouble() / contentLength) * 100).toInt()
                } else 0
                onProgress(
                    UpdateProgress(
                        downloading = true,
                        progress = progress,
                        totalBytes = contentLength,
                        downloadedBytes = totalRead
                    )
                )
            }

            outputStream.flush()
            outputStream.close()
            inputStream.close()

            val uri = FileProvider.getUriForFile(
                context,
                "${BuildConfig.APPLICATION_ID}.fileprovider",
                apkFile
            )
            onProgress(UpdateProgress(downloading = false, progress = 100))
            Result.success(uri)
        } catch (e: Exception) {
            e.printStackTrace()
            onProgress(UpdateProgress(downloading = false))
            Result.failure(e)
        }
    }

    fun installApk(apkUri: Uri) {
        val intent = Intent(Intent.ACTION_VIEW).apply {
            setDataAndType(apkUri, "application/vnd.android.package-archive")
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_GRANT_READ_URI_PERMISSION
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            }
        }
        context.startActivity(intent)
    }

    fun requestInstallPermission(activity: Activity, requestCode: Int) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            if (!canRequestInstallPackages()) {
                val intent = Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES)
                    .setData(Uri.parse("package:${BuildConfig.APPLICATION_ID}"))
                activity.startActivityForResult(intent, requestCode)
            }
        }
    }

    fun canRequestInstallPackages(): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.packageManager.canRequestPackageInstalls()
        } else true
    }
}
