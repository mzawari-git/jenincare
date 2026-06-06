package com.jenincare.skinanalyzer.ui.scan

import android.content.Context
import android.util.Log
import androidx.hilt.work.HiltWorker
import androidx.work.BackoffPolicy
import androidx.work.Constraints
import androidx.work.CoroutineWorker
import androidx.work.Data
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.repository.UploadRepository
import dagger.assisted.Assisted
import dagger.assisted.AssistedInject
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import java.io.File
import java.io.RandomAccessFile
import java.security.MessageDigest
import java.util.concurrent.TimeUnit

@HiltWorker
class UploadWorker @AssistedInject constructor(
    @Assisted context: Context,
    @Assisted params: WorkerParameters,
    private val uploadRepository: UploadRepository,
    private val chunkedUploadManager: ChunkedUploadManager,
) : CoroutineWorker(context, params) {

    companion object {
        const val TAG = "UploadWorker"
        const val WORK_NAME_PREFIX = "skin_scan_upload_"
        const val KEY_FILE_PATH = "file_path"
        const val KEY_SCAN_ID = "scan_id"
        const val KEY_PROGRESS = "progress"
        const val KEY_CHUNK_INDEX = "chunk_index"
        const val KEY_TOTAL_CHUNKS = "total_chunks"
        const val KEY_UPLOAD_URL = "upload_url"
        const val CHUNK_SIZE_DEFAULT = 1024 * 1024
        const val MAX_RETRIES = 5
        const val BASE_BACKOFF_MS = 15_000L

        fun enqueue(
            context: Context,
            filePath: String,
            scanId: String,
        ) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build()

            val inputData = Data.Builder()
                .putString(KEY_FILE_PATH, filePath)
                .putString(KEY_SCAN_ID, scanId)
                .build()

            val workRequest = OneTimeWorkRequestBuilder<UploadWorker>()
                .setConstraints(constraints)
                .setInputData(inputData)
                .setBackoffCriteria(
                    BackoffPolicy.EXPONENTIAL,
                    BASE_BACKOFF_MS,
                    TimeUnit.MILLISECONDS,
                )
                .setInitialDelay(0, TimeUnit.SECONDS)
                .build()

            WorkManager.getInstance(context).enqueueUniqueWork(
                "${WORK_NAME_PREFIX}${scanId}",
                ExistingWorkPolicy.KEEP,
                workRequest,
            )

            Log.d(TAG, "Upload enqueued for scan $scanId: $filePath")
        }

        fun observeProgress(context: Context, scanId: String): StateFlow<UploadProgress> {
            return MutableStateFlow(UploadProgress()).also { flow ->
                WorkManager.getInstance(context)
                    .getWorkInfosForUniqueWorkLiveData("${WORK_NAME_PREFIX}${scanId}")
                    .observeForever { workInfos ->
                        if (workInfos.isNullOrEmpty()) return@observeForever

                        val workInfo = workInfos.first()
                        val currentProgress = workInfo.progress.getInt(KEY_PROGRESS, 0)
                        val chunkIndex = workInfo.progress.getInt(KEY_CHUNK_INDEX, 0)
                        val totalChunks = workInfo.progress.getInt(KEY_TOTAL_CHUNKS, 0)

                        flow.value = UploadProgress(
                            progress = currentProgress,
                            chunkIndex = chunkIndex,
                            totalChunks = totalChunks,
                            state = when {
                                workInfo.state.isFinished -> UploadState.COMPLETED
                                currentProgress > 0 -> UploadState.UPLOADING
                                else -> UploadState.PENDING
                            },
                        )

                        if (workInfo.state.isFinished) return@observeForever
                    }
            }
        }
    }

    override suspend fun doWork(): Result = withContext(Dispatchers.IO) {
        val filePath = inputData.getString(KEY_FILE_PATH) ?: run {
            Log.e(TAG, "No file path provided")
            return@withContext Result.failure()
        }

        val scanId = inputData.getString(KEY_SCAN_ID) ?: "unknown"

        Log.d(TAG, "Starting upload for scan $scanId: $filePath")

        val file = File(filePath)
        if (!file.exists()) {
            Log.e(TAG, "File not found: $filePath")
            return@withContext Result.failure()
        }

        try {
            if (file.length() > CHUNK_SIZE_DEFAULT) {
                uploadInChunks(file, scanId)
            } else {
                uploadSinglePart(file, scanId)
            }

            uploadRepository.notifyUploadComplete(scanId)

            Log.d(TAG, "Upload completed for scan $scanId")
            Result.success()
        } catch (e: CancellationException) {
            Log.w(TAG, "Upload cancelled for scan $scanId")
            Result.failure()
        } catch (e: Exception) {
            Log.e(TAG, "Upload failed for scan $scanId", e)
            if (runAttemptCount < MAX_RETRIES) {
                Result.retry()
            } else {
                Result.failure()
            }
        }
    }

    private suspend fun uploadSinglePart(file: File, scanId: String) {
        setProgress(Data.Builder().putInt(KEY_PROGRESS, 0).putInt(KEY_TOTAL_CHUNKS, 1).build())

        val requestBody = file.asRequestBody("image/jpeg".toMediaTypeOrNull())
        val imagePart = MultipartBody.Part.createFormData("image", file.name, requestBody)
        val scanIdPart = MultipartBody.Part.createFormData("scan_id", scanId)

        setProgress(Data.Builder().putInt(KEY_PROGRESS, 50).build())

        uploadRepository.uploadScan(imagePart, scanIdPart)

        setProgress(Data.Builder().putInt(KEY_PROGRESS, 100).build())
    }

    private suspend fun uploadInChunks(file: File, scanId: String) {
        val fileMd5 = computeMd5(file)
        val totalChunks = ((file.length() + CHUNK_SIZE_DEFAULT - 1) / CHUNK_SIZE_DEFAULT).toInt()

        Log.d(TAG, "Uploading in $totalChunks chunks, MD5: $fileMd5")

        // Get the last successfully uploaded chunk index (resume support)
        val lastCompletedChunk = chunkedUploadManager.getLastCompletedChunk(scanId)
        val startChunk = if (lastCompletedChunk >= 0) lastCompletedChunk + 1 else 0

        if (startChunk > 0) {
            Log.d(TAG, "Resuming from chunk $startChunk/$totalChunks")
        }

        for (chunkIndex in startChunk until totalChunks) {
            if (isStopped) {
                chunkedUploadManager.saveLastCompletedChunk(scanId, chunkIndex - 1)
                throw CancellationException("Upload cancelled by user")
            }

            val offset = chunkIndex.toLong() * CHUNK_SIZE_DEFAULT
            val chunkSize = minOf(CHUNK_SIZE_DEFAULT.toLong(), file.length() - offset).toInt()

            val chunk = readChunk(file, offset, chunkSize)
            val chunkFile = createTempChunkFile(scanId, chunkIndex, chunk)

            val progress = ((chunkIndex.toFloat() / totalChunks.toFloat()) * 100).toInt()

            setProgress(
                Data.Builder()
                    .putInt(KEY_PROGRESS, progress)
                    .putInt(KEY_CHUNK_INDEX, chunkIndex)
                    .putInt(KEY_TOTAL_CHUNKS, totalChunks)
                    .build()
            )

            val requestBody = chunkFile.asRequestBody("application/octet-stream".toMediaTypeOrNull())
            val chunkPart = MultipartBody.Part.createFormData(
                "chunk",
                "chunk_${scanId}_${chunkIndex}",
                requestBody,
            )
            val indexPart = MultipartBody.Part.createFormData("chunk_index", chunkIndex.toString())
            val totalPart = MultipartBody.Part.createFormData("total_chunks", totalChunks.toString())
            val scanPart = MultipartBody.Part.createFormData("scan_id", scanId)
            val md5Part = MultipartBody.Part.createFormData("file_md5", fileMd5)

            uploadRepository.uploadChunk(chunkPart, indexPart, totalPart, scanPart, md5Part)

            chunkedUploadManager.saveLastCompletedChunk(scanId, chunkIndex)

            if (!chunkFile.delete()) {
                Log.w(TAG, "Failed to delete temporary chunk file: ${chunkFile.name}")
            }
        }

        // Finalize upload on server
        uploadRepository.finalizeChunkedUpload(scanId, totalChunks, fileMd5)

        setProgress(
            Data.Builder()
                .putInt(KEY_PROGRESS, 100)
                .putInt(KEY_CHUNK_INDEX, totalChunks)
                .putInt(KEY_TOTAL_CHUNKS, totalChunks)
                .build()
        )

        // Clear local chunk state
        chunkedUploadManager.clearChunkState(scanId)
    }

    private fun readChunk(file: File, offset: Long, size: Int): ByteArray {
        val buffer = ByteArray(size)
        RandomAccessFile(file, "r").use { raf ->
            raf.seek(offset)
            raf.readFully(buffer)
        }
        return buffer
    }

    private fun createTempChunkFile(scanId: String, chunkIndex: Int, data: ByteArray): File {
        val tempDir = File(applicationContext.cacheDir, "upload_chunks")
        if (!tempDir.exists()) {
            tempDir.mkdirs()
        }
        val tempFile = File(tempDir, "${scanId}_chunk_$chunkIndex.tmp")
        tempFile.writeBytes(data)
        return tempFile
    }

    private fun computeMd5(file: File): String {
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

    data class UploadProgress(
        val progress: Int = 0,
        val chunkIndex: Int = 0,
        val totalChunks: Int = 0,
        val state: UploadState = UploadState.PENDING,
    )

    enum class UploadState {
        PENDING,
        UPLOADING,
        COMPLETED,
    }
}
