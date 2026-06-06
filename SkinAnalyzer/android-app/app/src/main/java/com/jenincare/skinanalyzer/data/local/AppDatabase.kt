package com.jenincare.skinanalyzer.data.local

import androidx.room.Database
import androidx.room.RoomDatabase
import com.jenincare.skinanalyzer.data.local.dao.ReportDao
import com.jenincare.skinanalyzer.data.local.dao.ScanDao
import com.jenincare.skinanalyzer.data.local.entity.DefectEntity
import com.jenincare.skinanalyzer.data.local.entity.HeatmapPointEntity
import com.jenincare.skinanalyzer.data.local.entity.ReportEntity
import com.jenincare.skinanalyzer.data.local.entity.ScanEntity
import com.jenincare.skinanalyzer.data.local.entity.TipEntity

@Database(
    entities = [
        ScanEntity::class,
        ReportEntity::class,
        DefectEntity::class,
        HeatmapPointEntity::class,
        TipEntity::class
    ],
    version = 1,
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {
    abstract fun scanDao(): ScanDao
    abstract fun reportDao(): ReportDao
}
