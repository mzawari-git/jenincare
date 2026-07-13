package com.ebtikar.skinanalyzer.data.local

import androidx.room.Database
import androidx.room.RoomDatabase
import androidx.room.migration.Migration
import androidx.sqlite.db.SupportSQLiteDatabase

@Database(entities = [SkinReportEntity::class], version = 2, exportSchema = false)
abstract class SkinReportDatabase : RoomDatabase() {
    abstract fun skinReportDao(): SkinReportDao

    companion object {
        val MIGRATION_1_2 = object : Migration(1, 2) {
            override fun migrate(db: SupportSQLiteDatabase) {
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN aiAnalysisText TEXT NOT NULL DEFAULT ''")
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN expertTipsJson TEXT NOT NULL DEFAULT '[]'")
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN productsJson TEXT NOT NULL DEFAULT '[]'")
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN skinProfileJson TEXT NOT NULL DEFAULT '{}'")
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN confidence REAL NOT NULL DEFAULT 0.85")
                db.execSQL("ALTER TABLE skin_reports ADD COLUMN scanId TEXT NOT NULL DEFAULT ''")
            }
        }
    }
}
