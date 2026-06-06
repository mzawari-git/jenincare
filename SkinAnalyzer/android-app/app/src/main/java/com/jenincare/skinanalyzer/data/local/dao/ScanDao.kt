package com.jenincare.skinanalyzer.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.jenincare.skinanalyzer.data.local.entity.ScanEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface ScanDao {
    @Query("SELECT * FROM scans ORDER BY createdAt DESC")
    fun getAllScans(): Flow<List<ScanEntity>>

    @Query("SELECT * FROM scans ORDER BY createdAt DESC")
    suspend fun getAllScansList(): List<ScanEntity>

    @Query("SELECT * FROM scans WHERE id = :id")
    suspend fun getScanById(id: String): ScanEntity?

    @Query("SELECT * FROM scans WHERE id = :id")
    fun getScanByIdFlow(id: String): Flow<ScanEntity?>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertAll(scans: List<ScanEntity>)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(scan: ScanEntity)

    @Query("DELETE FROM scans WHERE id = :id")
    suspend fun deleteById(id: String)

    @Query("DELETE FROM scans")
    suspend fun deleteAll()
}
