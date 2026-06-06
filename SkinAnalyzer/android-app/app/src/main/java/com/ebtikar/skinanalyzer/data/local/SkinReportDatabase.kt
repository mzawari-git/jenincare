package com.ebtikar.skinanalyzer.data.local

import androidx.room.Database
import androidx.room.RoomDatabase

@Database(entities = [SkinReportEntity::class], version = 1, exportSchema = false)
abstract class SkinReportDatabase : RoomDatabase() {
    abstract fun skinReportDao(): SkinReportDao
}
