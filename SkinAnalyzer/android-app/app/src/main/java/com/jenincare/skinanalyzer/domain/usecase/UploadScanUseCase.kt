package com.jenincare.skinanalyzer.domain.usecase

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Matrix
import android.media.ExifInterface
import android.net.Uri
import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.data.remote.dto.FaceBoundingBoxDto
import com.jenincare.skinanalyzer.data.remote.dto.LandmarkDto
import com.jenincare.skinanalyzer.data.remote.dto.UploadScanRequest
import com.jenincare.skinanalyzer.data.remote.dto.UploadScanResponse
import com.jenincare.skinanalyzer.ui.camera.TFLiteFaceDetector
import com.jenincare.skinanalyzer.util.SecurityUtil
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.ByteArrayOutputStream
import java.io.File
import java.io.FileOutputStream
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class UploadScanUseCase @Inject constructor(
    private val scanApiService: ScanApiService,
    private val faceDetector: TFLiteFaceDetector,
    private val securityUtil: SecurityUtil,
    @ApplicationContext private val context: Context
) {
    companion object {
        private const val JPEG_QUALITY = 92
        private const val MIN_QUALITY_SCORE = 0.3f
        private const val CHUNK_SIZE = 1024 * 1024
    }

    suspend operator fun invoke(
        imageUri: Uri,
        spectralUris: List<Uri> = emptyList(),
        onProgress: (Float) -> Unit = {}
    ): Result<UploadScanResponse> = withContext(Dispatchers.IO) {
        try {
            android.util.Log.d("UploadScanUC", "Starting upload from URI: $imageUri")
            val bitmap = loadBitmap(imageUri)
            if (bitmap == null) {
                android.util.Log.e("UploadScanUC", "Failed to load bitmap from URI")
                return@withContext Result.failure(Exception("Failed to load image"))
            }
            android.util.Log.d("UploadScanUC", "Bitmap loaded: ${bitmap.width}x${bitmap.height}")
            onProgress(0.05f)

            val compressed = compressImage(bitmap)
            onProgress(0.1f)

            val qualityResult = faceDetector.detectFace(compressed)
            if (qualityResult.confidence < MIN_QUALITY_SCORE) {
                android.util.Log.w("UploadScanUC", "Face confidence low (${qualityResult.confidence}), attempting upload anyway")
            }

            val lightingQuality = faceDetector.checkLightingQuality(compressed)
            if (lightingQuality < 0.1f) {
                android.util.Log.w("UploadScanUC", "Poor lighting: $lightingQuality")
                return@withContext Result.failure(Exception("Poor lighting conditions. Please move to a brighter area."))
            }
            onProgress(0.15f)

            val imageBytes = bitmapToBytes(compressed)
            onProgress(0.2f)

            val tempFile = File(context.cacheDir, "scan_upload_${System.currentTimeMillis()}.jpg")
            FileOutputStream(tempFile).use { it.write(imageBytes) }
            val requestBody = tempFile.asRequestBody("image/jpeg".toMediaTypeOrNull())
            val multipart = MultipartBody.Part.createFormData("file", "scan_${System.currentTimeMillis()}.jpg", requestBody)

            onProgress(0.3f)

            val spectralFiles = mutableListOf<MultipartBody.Part>()
            spectralUris.forEachIndexed { index, uri ->
                try {
                    val spectralBitmap = loadBitmap(uri)
                    if (spectralBitmap != null) {
                        val spectralBytes = bitmapToBytes(compressImage(spectralBitmap))
                        val sFile = File(context.cacheDir, "spectral_${index}_${System.currentTimeMillis()}.jpg")
                        FileOutputStream(sFile).use { it.write(spectralBytes) }
                        val sBody = sFile.asRequestBody("image/jpeg".toMediaTypeOrNull())
                        val part = MultipartBody.Part.createFormData(
                            "spectral_$index",
                            "spectral_${index}_${System.currentTimeMillis()}.jpg",
                            sBody
                        )
                        spectralFiles.add(part)
                    }
                } catch (e: Exception) {
                    android.util.Log.w("UploadScanUC", "Failed to process spectral image $index: ${e.message}")
                }
            }

            val landmarks = qualityResult.landmarks.map {
                LandmarkDto(x = it.first, y = it.second)
            }

            val boundingBox = FaceBoundingBoxDto(
                left = qualityResult.boundingBox.left,
                top = qualityResult.boundingBox.top,
                right = qualityResult.boundingBox.right,
                bottom = qualityResult.boundingBox.bottom
            )

            val metadata = UploadScanRequest(
                lightingQuality = lightingQuality,
                faceConfidence = qualityResult.confidence,
                imageWidth = compressed.width,
                imageHeight = compressed.height,
                hasSpectralCaptures = spectralUris.isNotEmpty(),
                faceBoundingBox = boundingBox,
                faceLandmarks = landmarks
            )

            val moshi = Moshi.Builder().add(KotlinJsonAdapterFactory()).build()
            val jsonAdapter = moshi.adapter(UploadScanRequest::class.java)
            val metadataJson = jsonAdapter.toJson(metadata)
            val metadataRequestBody = metadataJson.toRequestBody("application/json".toMediaTypeOrNull())

            android.util.Log.d("UploadScanUC", "Sending to v1/scans/upload/with-progress")
            val response = scanApiService.uploadScanWithProgress(
                image = multipart,
                metadata = metadataRequestBody,
                spectral = spectralFiles
            )
            tempFile.delete()
            onProgress(1.0f)

            if (response.isSuccessful) {
                val body = response.body()
                android.util.Log.d("UploadScanUC", "Upload success: scanId=${body?.scanId}, status=${body?.status}")
                Result.success(body ?: throw Exception("Empty response body"))
            } else {
                val errorBody = response.errorBody()?.string()
                android.util.Log.e("UploadScanUC", "Upload failed: ${response.code()} ${response.message()} body=$errorBody")
                Result.failure(Exception("Upload failed: ${response.code()} ${response.message()}"))
            }
        } catch (e: Throwable) {
            android.util.Log.e("UploadScanUC", "Upload exception: ${e.message}", e)
            Result.failure(Exception(e.message ?: "Upload failed"))
        }
    }

    fun uploadWithChunkedResume(
        imageUri: Uri,
        scanId: String? = null,
        onProgress: (Float) -> Unit = {}
    ): Flow<Result<UploadScanResponse>> = flow {
        try {
            android.util.Log.d("UploadScanUC", "Chunked upload starting for URI: $imageUri")
            val bitmap = loadBitmap(imageUri) ?: throw Exception("Failed to load image")
            val compressed = compressImage(bitmap)
            val imageBytes = bitmapToBytes(compressed)

            val totalChunks = (imageBytes.size + CHUNK_SIZE - 1) / CHUNK_SIZE
            android.util.Log.d("UploadScanUC", "Chunked upload: ${imageBytes.size} bytes, $totalChunks chunks")
            for (i in 0 until totalChunks) {
                val start = i * CHUNK_SIZE
                val end = minOf(start + CHUNK_SIZE, imageBytes.size)
                val chunk = imageBytes.copyOfRange(start, end)
                val isLastChunk = i == totalChunks - 1

                val response = scanApiService.uploadChunk(
                    scanId = scanId ?: "",
                    chunkIndex = i,
                    totalChunks = totalChunks,
                    isLastChunk = isLastChunk,
                    chunk = MultipartBody.Part.createFormData(
                        "chunk",
                        "chunk_$i",
                        chunk.toRequestBody("application/octet-stream".toMediaTypeOrNull())
                    )
                )

                if (response.isSuccessful) {
                    onProgress((i + 1).toFloat() / totalChunks)
                    if (isLastChunk) {
                        val chunkResponse = response.body()!!
                        emit(Result.success(UploadScanResponse(scanId = chunkResponse.scanId, status = chunkResponse.status, message = null)))
                    }
                } else {
                    throw Exception("Chunk upload failed at chunk $i: ${response.code()}")
                }
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    private fun loadBitmap(uri: Uri): Bitmap? {
        return try {
            val inputStream = context.contentResolver.openInputStream(uri)
            val bitmap = inputStream?.use { stream ->
                BitmapFactory.decodeStream(stream)
            } ?: return null

            val exifInputStream = context.contentResolver.openInputStream(uri)
            val rotation = exifInputStream?.use { stream ->
                try {
                    val exif = ExifInterface(stream)
                    when (exif.getAttributeInt(ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL)) {
                        ExifInterface.ORIENTATION_ROTATE_90 -> 90f
                        ExifInterface.ORIENTATION_ROTATE_180 -> 180f
                        ExifInterface.ORIENTATION_ROTATE_270 -> 270f
                        else -> 0f
                    }
                } catch (_: Exception) { 0f }
            } ?: 0f

            if (rotation != 0f) {
                val matrix = Matrix()
                matrix.postRotate(rotation)
                val rotated = Bitmap.createBitmap(bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true)
                if (rotated != bitmap) bitmap.recycle()
                rotated
            } else {
                bitmap
            }
        } catch (e: Exception) {
            null
        }
    }

    private fun compressImage(bitmap: Bitmap): Bitmap {
        val targetSize = 1024
        val width = bitmap.width
        val height = bitmap.height
        if (width <= targetSize && height <= targetSize) return bitmap
        val scale = minOf(targetSize.toFloat() / width, targetSize.toFloat() / height)
        return try {
            Bitmap.createScaledBitmap(bitmap, (width * scale).toInt(), (height * scale).toInt(), true)
        } catch (_: OutOfMemoryError) {
            bitmap
        }
    }

    private fun bitmapToBytes(bitmap: Bitmap): ByteArray {
        val stream = ByteArrayOutputStream()
        bitmap.compress(Bitmap.CompressFormat.JPEG, JPEG_QUALITY, stream)
        return stream.toByteArray()
    }

}
