package com.jenincare.skinanalyzer.domain.usecase

import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.data.remote.dto.UnlockScanRequest
import com.jenincare.skinanalyzer.data.remote.dto.UnlockScanResponse
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class UnlockScanUseCase @Inject constructor(
    private val scanApiService: ScanApiService
) {
    suspend operator fun invoke(scanId: String, pin: String): Result<UnlockScanResponse> =
        withContext(Dispatchers.IO) {
            try {
                val response = scanApiService.unlockScan(
                    scanId = scanId,
                    request = UnlockScanRequest(pin = pin)
                )
                if (response.isSuccessful) {
                    Result.success(response.body() ?: throw Exception("Empty response"))
                } else {
                    Result.failure(
                        when (response.code()) {
                            403 -> Exception("رمز PIN غير صحيح")
                            404 -> Exception("الفحص غير موجود")
                            429 -> Exception("تم تجاوز عدد المحاولات. يرجى الانتظار")
                            else -> Exception("فشل إلغاء القفل: ${response.code()}")
                        }
                    )
                }
            } catch (e: Exception) {
                Result.failure(e)
            }
        }
}
