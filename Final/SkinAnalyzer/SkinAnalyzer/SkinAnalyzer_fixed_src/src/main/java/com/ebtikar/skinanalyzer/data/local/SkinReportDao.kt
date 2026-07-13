package com.ebtikar.skinanalyzer.data.local

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import kotlinx.coroutines.flow.Flow

@Dao
interface SkinReportDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertReport(report: SkinReportEntity)

    @Query("SELECT * FROM skin_reports WHERE id = :reportId")
    suspend fun getReportById(reportId: String): SkinReportEntity?

    @Query("SELECT * FROM skin_reports ORDER BY timestamp DESC")
    fun getAllReports(): Flow<List<SkinReportEntity>>

    @Query("SELECT * FROM skin_reports ORDER BY timestamp DESC LIMIT :limit")
    fun getRecentReports(limit: Int): Flow<List<SkinReportEntity>>

    @Query("DELETE FROM skin_reports WHERE id = :reportId")
    suspend fun deleteReport(reportId: String)

    @Query("SELECT COUNT(*) FROM skin_reports")
    suspend fun getReportCount(): Int
}
