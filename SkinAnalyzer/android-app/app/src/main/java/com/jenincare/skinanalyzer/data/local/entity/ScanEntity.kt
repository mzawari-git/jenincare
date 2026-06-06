package com.jenincare.skinanalyzer.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.jenincare.skinanalyzer.domain.model.Scan
import com.jenincare.skinanalyzer.domain.model.ScanStatus

@Entity(tableName = "scans")
data class ScanEntity(
    @PrimaryKey val id: String,
    val userId: String,
    val imageUrl: String?,
    val status: String,
    val overallScore: Int,
    val createdAt: String,
    val reviewedAt: String?
) {
    fun toDomain(): Scan = Scan(
        id = id,
        userId = userId,
        imageUrl = imageUrl,
        status = ScanStatus.fromString(status),
        overallScore = overallScore,
        createdAt = createdAt,
        reviewedAt = reviewedAt
    )

    companion object {
        fun fromDomain(scan: Scan): ScanEntity = ScanEntity(
            id = scan.id,
            userId = scan.userId,
            imageUrl = scan.imageUrl,
            status = scan.status.name.lowercase(),
            overallScore = scan.overallScore,
            createdAt = scan.createdAt,
            reviewedAt = scan.reviewedAt
        )
    }
}
