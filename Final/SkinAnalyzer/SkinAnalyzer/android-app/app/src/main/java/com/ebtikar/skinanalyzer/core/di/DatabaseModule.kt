package com.ebtikar.skinanalyzer.core.di

import android.content.Context
import androidx.room.Room
import com.ebtikar.skinanalyzer.data.local.SkinReportDao
import com.ebtikar.skinanalyzer.data.local.SkinReportDatabase
import com.ebtikar.skinanalyzer.util.Constants
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object DatabaseModule {

    @Provides
    @Singleton
    fun provideDatabase(@ApplicationContext context: Context): SkinReportDatabase {
        return Room.databaseBuilder(
            context,
            SkinReportDatabase::class.java,
            Constants.DB_NAME
        ).fallbackToDestructiveMigration().build()
    }

    @Provides
    @Singleton
    fun provideSkinReportDao(database: SkinReportDatabase): SkinReportDao {
        return database.skinReportDao()
    }
}
