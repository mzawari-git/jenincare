package com.jenincare.skinanalyzer.di

import com.jenincare.skinanalyzer.data.repository.AuthRepository
import com.jenincare.skinanalyzer.data.repository.AuthRepositoryImpl
import com.jenincare.skinanalyzer.data.repository.ProductRepository
import com.jenincare.skinanalyzer.data.repository.ProductRepositoryImpl
import com.jenincare.skinanalyzer.data.repository.ScanRepository
import com.jenincare.skinanalyzer.data.repository.ScanRepositoryImpl
import com.jenincare.skinanalyzer.data.repository.SettingsRepository
import com.jenincare.skinanalyzer.data.repository.SettingsRepositoryImpl
import dagger.Binds
import dagger.Module
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
abstract class RepositoryModule {

    @Binds
    @Singleton
    abstract fun bindScanRepository(
        impl: ScanRepositoryImpl
    ): ScanRepository

    @Binds
    @Singleton
    abstract fun bindAuthRepository(
        impl: AuthRepositoryImpl
    ): AuthRepository

    @Binds
    @Singleton
    abstract fun bindProductRepository(
        impl: ProductRepositoryImpl
    ): ProductRepository

    @Binds
    @Singleton
    abstract fun bindSettingsRepository(
        impl: SettingsRepositoryImpl
    ): SettingsRepository
}
