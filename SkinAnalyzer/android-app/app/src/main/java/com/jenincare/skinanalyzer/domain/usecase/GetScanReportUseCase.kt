package com.jenincare.skinanalyzer.domain.usecase

import com.jenincare.skinanalyzer.data.local.dao.ReportDao
import com.jenincare.skinanalyzer.data.local.dao.ScanDao
import com.jenincare.skinanalyzer.data.local.entity.DefectEntity
import com.jenincare.skinanalyzer.data.local.entity.HeatmapPointEntity
import com.jenincare.skinanalyzer.data.local.entity.ReportEntity
import com.jenincare.skinanalyzer.data.local.entity.ScanEntity
import com.jenincare.skinanalyzer.data.local.entity.TipEntity
import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.data.remote.dto.ScanReportResponse
import com.jenincare.skinanalyzer.domain.model.Defect
import com.jenincare.skinanalyzer.domain.model.FacialZoneEntry
import com.jenincare.skinanalyzer.domain.model.HeatmapPoint
import com.jenincare.skinanalyzer.domain.model.ProductRecommendation
import com.jenincare.skinanalyzer.domain.model.RadarMetric
import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.domain.model.ScanReport
import com.jenincare.skinanalyzer.domain.model.ScanStatus
import com.jenincare.skinanalyzer.domain.model.SpectralAnalysisEntry
import com.jenincare.skinanalyzer.util.NetworkMonitor
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.withContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class GetScanReportUseCase @Inject constructor(
    private val scanApiService: ScanApiService,
    private val scanDao: ScanDao,
    private val reportDao: ReportDao,
    private val networkMonitor: NetworkMonitor
) {
    suspend operator fun invoke(scanId: String): Result<ScanReport> =
        withContext(Dispatchers.IO) {
            if (networkMonitor.isConnected.first()) {
                try {
                    val response = scanApiService.getScanReport(scanId)
                    if (response.isSuccessful) {
                        val dto = response.body() ?: throw Exception("Empty response")
                        val report = dto.toDomain()
                        cacheReport(scanId, dto)
                        return@withContext Result.success(report)
                    }
                } catch (_: Exception) { }
            }

            val cached = loadCachedReport(scanId)
            if (cached != null) {
                return@withContext Result.success(cached)
            }

            return@withContext Result.failure(Exception("تعذر تحميل التقرير من الخادم أو من ذاكرة التخزين المؤقت"))
        }

    private suspend fun cacheReport(scanId: String, dto: ScanReportResponse) {
        scanDao.insert(ScanEntity.fromDomain(dto.toScanSummary()))
        reportDao.insertReport(ReportEntity.fromDomain(dto.toDomain()))
        reportDao.deleteDefects(scanId)
        reportDao.insertDefects(dto.defects.map { defect ->
            DefectEntity(
                id = defect.id,
                scanId = scanId,
                nameAr = defect.nameAr,
                nameEn = defect.nameEn,
                severity = defect.severity,
                tipAr = defect.tipAr,
                tipEn = defect.tipEn,
                iconName = defect.iconName
            )
        })
        reportDao.deleteHeatmapPoints(scanId)
        reportDao.insertHeatmapPoints(dto.heatmapPoints.map { point ->
            HeatmapPointEntity(
                scanId = scanId,
                x = point.x,
                y = point.y,
                severity = point.severity,
                label = (point.labelAr ?: point.label) ?: "",
                description = (point.descriptionAr ?: point.description) ?: ""
            )
        })
        reportDao.deleteTips(scanId)
        reportDao.insertTips(dto.generalTips.map { tip ->
            TipEntity(scanId = scanId, text = tip.ar ?: tip.en ?: "")
        })
    }

    private suspend fun loadCachedReport(scanId: String): ScanReport? {
        val scanEntity = scanDao.getScanById(scanId) ?: return null
        val reportEntity = reportDao.getReport(scanId) ?: return null
        val defects = reportDao.getDefects(scanId)
        val heatmapPoints = reportDao.getHeatmapPoints(scanId)
        val tips = reportDao.getTips(scanId)
        return reportEntity.toDomain(scanEntity, defects, heatmapPoints, tips)
    }
}

private fun ScanReportResponse.toScanSummary(): Scan = Scan(
    id = scan.id,
    userId = scan.userId,
    imageUrl = scan.imageUrl,
    status = ScanStatus.fromString(scan.status),
    overallScore = scan.overallScore,
    createdAt = scan.createdAt,
    reviewedAt = scan.reviewedAt
)

private fun ScanReportResponse.toDomain(): ScanReport {
    val adv = advancedMetrics
    val advancedMap = if (adv != null) mapOf(
        "brightness" to (adv.brightness ?: 0),
        "texture" to (adv.texture ?: 0),
        "redness" to (adv.redness ?: 0),
        "sensitivity" to (adv.sensitivity ?: 0),
        "oiliness" to (adv.oiliness ?: 0)
    ) else emptyMap()

    return ScanReport(
        scan = toScanSummary(),
        radarMetrics = listOf(
            RadarMetric("ترطيب", "Hydration", metrics.hydration / 100f),
            RadarMetric("دهون", "Sebum", metrics.sebum / 100f),
            RadarMetric("تصبغات", "Pigmentation", metrics.pigmentation / 100f),
            RadarMetric("مسام", "Pores", metrics.pores / 100f),
            RadarMetric("مرونة", "Elasticity", metrics.elasticity / 100f)
        ),
        advancedMetrics = advancedMap,
        heatmapPoints = heatmapPoints.map { point ->
            HeatmapPoint(
                x = point.x,
                y = point.y,
                severity = point.severity,
                label = (point.labelAr ?: point.label) ?: "",
                description = (point.descriptionAr ?: point.description) ?: ""
            )
        },
        defects = defects.map { defect ->
            Defect(
                id = defect.id,
                nameAr = defect.nameAr,
                nameEn = defect.nameEn,
                severity = defect.severity,
                tipAr = defect.tipAr,
                tipEn = defect.tipEn,
                iconName = defect.iconName,
                products = defect.recommendedProducts.map { product ->
                    ProductRecommendation(
                        id = product.id,
                        nameAr = product.nameAr,
                        nameEn = product.nameEn,
                        price = product.price,
                        imageUrl = product.imageUrl,
                        shopUrl = product.shopUrl,
                        reason = (product.matchingReasonAr ?: product.matchingReason) ?: ""
                    )
                }
            )
        },
        tips = generalTips.map { it.ar ?: it.en ?: "" },
        spectralAnalysis = spectralAnalysis?.map { sa ->
            SpectralAnalysisEntry(
                mode = sa.mode,
                label = sa.label ?: sa.mode,
                labelAr = sa.labelAr ?: "",
                analysisFocus = sa.analysisFocus ?: "",
                score = sa.score ?: 0
            )
        } ?: emptyList(),
        facialZoneAnalysis = facialZoneAnalysis?.map { fz ->
            FacialZoneEntry(
                zone = fz.zone,
                name = fz.name ?: fz.zone,
                nameAr = fz.nameAr ?: "",
                severity = fz.severity ?: 0,
                note = fz.note,
                noteAr = fz.noteAr
            )
        } ?: emptyList(),
        customArabicAnalysis = customArabicAnalysis,
        spectralImageUrls = scan.spectralImageUrls ?: emptyList()
    )
}
