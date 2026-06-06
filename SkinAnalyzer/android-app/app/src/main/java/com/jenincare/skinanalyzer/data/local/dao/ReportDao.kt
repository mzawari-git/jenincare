package com.jenincare.skinanalyzer.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.jenincare.skinanalyzer.data.local.entity.DefectEntity
import com.jenincare.skinanalyzer.data.local.entity.HeatmapPointEntity
import com.jenincare.skinanalyzer.data.local.entity.ReportEntity
import com.jenincare.skinanalyzer.data.local.entity.TipEntity

@Dao
interface ReportDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertReport(report: ReportEntity)

    @Query("SELECT * FROM reports WHERE scanId = :scanId")
    suspend fun getReport(scanId: String): ReportEntity?

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertDefects(defects: List<DefectEntity>)

    @Query("SELECT * FROM defects WHERE scanId = :scanId")
    suspend fun getDefects(scanId: String): List<DefectEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertHeatmapPoints(points: List<HeatmapPointEntity>)

    @Query("SELECT * FROM heatmap_points WHERE scanId = :scanId")
    suspend fun getHeatmapPoints(scanId: String): List<HeatmapPointEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertTips(tips: List<TipEntity>)

    @Query("SELECT * FROM tips WHERE scanId = :scanId")
    suspend fun getTips(scanId: String): List<TipEntity>

    @Query("DELETE FROM defects WHERE scanId = :scanId")
    suspend fun deleteDefects(scanId: String)

    @Query("DELETE FROM heatmap_points WHERE scanId = :scanId")
    suspend fun deleteHeatmapPoints(scanId: String)

    @Query("DELETE FROM tips WHERE scanId = :scanId")
    suspend fun deleteTips(scanId: String)

    @Query("DELETE FROM reports WHERE scanId = :scanId")
    suspend fun deleteReport(scanId: String)
}
