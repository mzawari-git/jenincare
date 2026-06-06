package com.jenincare.skinanalyzer.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.jenincare.skinanalyzer.domain.model.Defect
import com.jenincare.skinanalyzer.domain.model.HeatmapPoint
import com.jenincare.skinanalyzer.domain.model.ProductRecommendation
import com.jenincare.skinanalyzer.domain.model.RadarMetric
import com.jenincare.skinanalyzer.domain.model.ScanReport

@Entity(tableName = "reports")
data class ReportEntity(
    @PrimaryKey val scanId: String,
    val hydration: Float,
    val sebum: Float,
    val pigmentation: Float,
    val pores: Float,
    val elasticity: Float
) {
    fun toDomain(
        scanEntity: ScanEntity,
        defects: List<DefectEntity>,
        heatmapPoints: List<HeatmapPointEntity>,
        tips: List<TipEntity>
    ): ScanReport = ScanReport(
        scan = scanEntity.toDomain(),
        radarMetrics = listOf(
            RadarMetric("ترطيب", "Hydration", hydration),
            RadarMetric("دهون", "Sebum", sebum),
            RadarMetric("تصبغات", "Pigmentation", pigmentation),
            RadarMetric("مسام", "Pores", pores),
            RadarMetric("مرونة", "Elasticity", elasticity)
        ),
        heatmapPoints = heatmapPoints.map { it.toDomain() },
        defects = defects.map { defect ->
            Defect(
                id = defect.id,
                nameAr = defect.nameAr,
                nameEn = defect.nameEn,
                severity = defect.severity,
                tipAr = defect.tipAr,
                tipEn = defect.tipEn,
                iconName = defect.iconName,
                products = emptyList()
            )
        },
        tips = tips.map { it.text }
    )

    companion object {
        fun fromDomain(report: ScanReport): ReportEntity = ReportEntity(
            scanId = report.scan.id,
            hydration = report.radarMetrics.getOrNull(0)?.value ?: 0f,
            sebum = report.radarMetrics.getOrNull(1)?.value ?: 0f,
            pigmentation = report.radarMetrics.getOrNull(2)?.value ?: 0f,
            pores = report.radarMetrics.getOrNull(3)?.value ?: 0f,
            elasticity = report.radarMetrics.getOrNull(4)?.value ?: 0f
        )
    }
}

@Entity(tableName = "defects")
data class DefectEntity(
    @PrimaryKey val id: String,
    val scanId: String,
    val nameAr: String,
    val nameEn: String,
    val severity: Float,
    val tipAr: String,
    val tipEn: String,
    val iconName: String
) {
    fun toDomain(): Defect = Defect(
        id = id,
        nameAr = nameAr,
        nameEn = nameEn,
        severity = severity,
        tipAr = tipAr,
        tipEn = tipEn,
        iconName = iconName,
        products = emptyList()
    )
}

@Entity(tableName = "heatmap_points")
data class HeatmapPointEntity(
    @PrimaryKey(autoGenerate = true) val uid: Int = 0,
    val scanId: String,
    val x: Float,
    val y: Float,
    val severity: Float,
    val label: String,
    val description: String
) {
    fun toDomain(): HeatmapPoint = HeatmapPoint(
        x = x, y = y, severity = severity, label = label, description = description
    )
}

@Entity(tableName = "tips")
data class TipEntity(
    @PrimaryKey(autoGenerate = true) val uid: Int = 0,
    val scanId: String,
    val text: String
)
