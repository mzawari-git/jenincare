package com.jenincare.skinanalyzer.ui.scan

import android.content.Context
import android.util.Log
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.intPreferencesKey
import androidx.datastore.preferences.core.longPreferencesKey
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import java.io.File
import java.io.RandomAccessFile
import java.security.MessageDigest
import javax.inject.Inject
import javax.inject.Singleton

private val Context.uploadStateDataStore: DataStore<Preferences> by preferencesDataStore(
    name = "upload_state",
)

data class ChunkUploadState(
    val scanId: String,
    val filePath: String,
    val totalChunks: Int,
    val completedChunkIndex: Int,
    val fileMd5: String,
    val lastUpdatedMs: Long,
    val totalBytes: Long,
    val uploadedBytes: Long,
) {
    val progressPercent: Int
        get() = if (totalBytes > 0) {
            ((uploadedBytes.toFloat() / totalBytes.toFloat()) * 100).toInt()
        } else {
            if (totalChunks > 0) {
                ((completedChunkIndex.toFloat() / totalChunks.toFloat()) * 100).toInt()
            } else {
                0
            }
        }

    val isComplete: Boolean
        get() = completedChunkIndex >= totalChunks - 1

    val isResumable: Boolean
        get() = completedChunkIndex >= 0 && !isComplete
}

@Singleton
class ChunkedUploadManager @Inject constructor(
    @ApplicationContext private val context: Context,
) {

    companion object {
        private const val TAG = "ChunkedUploadManager"
        const val CHUNK_SIZE_DEFAULT = 1024 * 1024 // 1MB
        const val CHUNK_SIZE_LARGE = 5 * 1024 * 1024 // 5MB for fast networks
        const val MAX_CHUNK_SIZE = 10 * 1024 * 1024 // 10MB max
    }

    // DataStore keys
    private val LAST_CHUNK_KEY = intPreferencesKey("last_chunk_index")
    private val TOTAL_CHUNKS_KEY = intPreferencesKey("total_chunks")
    private val FILE_PATH_KEY = stringPreferencesKey("file_path")
    private val FILE_MD5_KEY = stringPreferencesKey("file_md5")
    private val TOTAL_BYTES_KEY = longPreferencesKey("total_bytes")
    private val UPLOADED_BYTES_KEY = longPreferencesKey("uploaded_bytes")
    private val LAST_UPDATED_KEY = longPreferencesKey("last_updated_ms")

    private fun getPreferencesKey(scanId: String): String = "upload_$scanId"

    suspend fun getLastCompletedChunk(scanId: String): Int {
        return try {
            context.uploadStateDataStore.data.map { prefs ->
                prefs[intPreferencesKey("${getPreferencesKey(scanId)}_${LAST_CHUNK_KEY.name}")] ?: -1
            }.first()
        } catch (e: Exception) {
            Log.e(TAG, "Failed to read last chunk for $scanId", e)
            -1
        }
    }

    suspend fun saveLastCompletedChunk(scanId: String, chunkIndex: Int) {
        try {
            context.uploadStateDataStore.edit { prefs ->
                prefs[intPreferencesKey("${getPreferencesKey(scanId)}_${LAST_CHUNK_KEY.name}")] = chunkIndex
                prefs[longPreferencesKey("${getPreferencesKey(scanId)}_${LAST_UPDATED_KEY.name}")] = System.currentTimeMillis()
            }
            Log.d(TAG, "Saved chunk progress: scan=$scanId, chunk=$chunkIndex")
        } catch (e: Exception) {
            Log.e(TAG, "Failed to save chunk progress for $scanId", e)
        }
    }

    suspend fun initUploadState(
        scanId: String,
        file: File,
        chunkSize: Int = CHUNK_SIZE_DEFAULT,
    ): ChunkUploadState {
        val totalChunks = ((file.length() + chunkSize - 1) / chunkSize).toInt()
        val fileMd5 = computeFileMd5(file)

        val state = ChunkUploadState(
            scanId = scanId,
            filePath = file.absolutePath,
            totalChunks = totalChunks,
            completedChunkIndex = -1,
            fileMd5 = fileMd5,
            lastUpdatedMs = System.currentTimeMillis(),
            totalBytes = file.length(),
            uploadedBytes = 0,
        )

        persistUploadState(scanId, state)
        Log.d(TAG, "Initialized upload state: $state")

        return state
    }

    suspend fun getUploadState(scanId: String): ChunkUploadState? {
        return try {
            val prefs = context.uploadStateDataStore.data.first()
            val prefix = getPreferencesKey(scanId)

            val filePath = prefs[stringPreferencesKey("${prefix}_${FILE_PATH_KEY.name}")] ?: return null
            val file = File(filePath)
            if (!file.exists()) {
                Log.w(TAG, "Upload file no longer exists: $filePath")
                return null
            }

            ChunkUploadState(
                scanId = scanId,
                filePath = filePath,
                totalChunks = prefs[intPreferencesKey("${prefix}_${TOTAL_CHUNKS_KEY.name}")] ?: 0,
                completedChunkIndex = prefs[intPreferencesKey("${prefix}_${LAST_CHUNK_KEY.name}")] ?: -1,
                fileMd5 = prefs[stringPreferencesKey("${prefix}_${FILE_MD5_KEY.name}")] ?: "",
                lastUpdatedMs = prefs[longPreferencesKey("${prefix}_${LAST_UPDATED_KEY.name}")] ?: 0,
                totalBytes = prefs[longPreferencesKey("${prefix}_${TOTAL_BYTES_KEY.name}")] ?: file.length(),
                uploadedBytes = prefs[longPreferencesKey("${prefix}_${UPLOADED_BYTES_KEY.name}")] ?: 0,
            )
        } catch (e: Exception) {
            Log.e(TAG, "Failed to get upload state for $scanId", e)
            null
        }
    }

    suspend fun updateUploadedBytes(scanId: String, additionalBytes: Long) {
        try {
            context.uploadStateDataStore.edit { prefs ->
                val key = longPreferencesKey("${getPreferencesKey(scanId)}_${UPLOADED_BYTES_KEY.name}")
                val current = prefs[key] ?: 0L
                prefs[key] = current + additionalBytes
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to update uploaded bytes for $scanId", e)
        }
    }

    suspend fun clearChunkState(scanId: String) {
        try {
            val prefix = getPreferencesKey(scanId)
            context.uploadStateDataStore.edit { prefs ->
                prefs.remove(intPreferencesKey("${prefix}_${LAST_CHUNK_KEY.name}"))
                prefs.remove(intPreferencesKey("${prefix}_${TOTAL_CHUNKS_KEY.name}"))
                prefs.remove(stringPreferencesKey("${prefix}_${FILE_PATH_KEY.name}"))
                prefs.remove(stringPreferencesKey("${prefix}_${FILE_MD5_KEY.name}"))
                prefs.remove(longPreferencesKey("${prefix}_${TOTAL_BYTES_KEY.name}"))
                prefs.remove(longPreferencesKey("${prefix}_${UPLOADED_BYTES_KEY.name}"))
                prefs.remove(longPreferencesKey("${prefix}_${LAST_UPDATED_KEY.name}"))
            }

            // Clean up any remaining temp chunk files
            val tempDir = File(context.cacheDir, "upload_chunks")
            if (tempDir.exists()) {
                tempDir.listFiles()
                    ?.filter { it.name.startsWith(scanId) }
                    ?.forEach { it.delete() }
            }

            Log.d(TAG, "Cleared upload state for scan $scanId")
        } catch (e: Exception) {
            Log.e(TAG, "Failed to clear upload state for $scanId", e)
        }
    }

    suspend fun hasPendingUpload(scanId: String): Boolean {
        val state = getUploadState(scanId) ?: return false
        return state.isResumable
    }

    fun computeFileMd5(file: File): String {
        val digest = MessageDigest.getInstance("MD5")
        RandomAccessFile(file, "r").use { raf ->
            val buffer = ByteArray(8192)
            var bytesRead: Int
            while (raf.read(buffer).also { bytesRead = it } != -1) {
                digest.update(buffer, 0, bytesRead)
            }
        }
        return digest.digest().joinToString("") { "%02x".format(it) }
    }

    fun verifyFileIntegrity(file: File, expectedMd5: String): Boolean {
        val actualMd5 = computeFileMd5(file)
        return actualMd5.equals(expectedMd5, ignoreCase = true)
    }

    fun calculateOptimalChunkSize(networkSpeedKbps: Int): Int {
        return when {
            networkSpeedKbps > 5000 -> CHUNK_SIZE_LARGE
            networkSpeedKbps > 1000 -> CHUNK_SIZE_DEFAULT
            else -> 256 * 1024 // 256KB for slow connections
        }
    }

    suspend fun getAllPendingUploads(): List<ChunkUploadState> {
        // This is a simplified implementation — in production you'd iterate DataStore keys
        return emptyList()
    }

    private suspend fun persistUploadState(scanId: String, state: ChunkUploadState) {
        val prefix = getPreferencesKey(scanId)
        context.uploadStateDataStore.edit { prefs ->
            prefs[intPreferencesKey("${prefix}_${TOTAL_CHUNKS_KEY.name}")] = state.totalChunks
            prefs[stringPreferencesKey("${prefix}_${FILE_PATH_KEY.name}")] = state.filePath
            prefs[stringPreferencesKey("${prefix}_${FILE_MD5_KEY.name}")] = state.fileMd5
            prefs[longPreferencesKey("${prefix}_${TOTAL_BYTES_KEY.name}")] = state.totalBytes
            prefs[longPreferencesKey("${prefix}_${UPLOADED_BYTES_KEY.name}")] = state.uploadedBytes
            prefs[longPreferencesKey("${prefix}_${LAST_UPDATED_KEY.name}")] = state.lastUpdatedMs
        }
    }
}
