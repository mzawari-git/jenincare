package com.ebtikar.skinanalyzer.core.di

import android.content.Context
import com.ebtikar.skinanalyzer.ai.CloudAnalysisProvider
import com.ebtikar.skinanalyzer.ai.LocalTFLiteProvider
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepository
import com.ebtikar.skinanalyzer.data.repository.SkinAnalysisRepositoryImpl
import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.ai.TFLiteEngine
import com.ebtikar.skinanalyzer.ai.FaceLandmarkDetector
import com.ebtikar.skinanalyzer.ai.FeatureExtractor
import com.ebtikar.skinanalyzer.util.NetworkMonitor
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.ebtikar.skinanalyzer.util.UpdateChecker
import dagger.Binds
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object AppModule {

    @Provides
    @Singleton
    fun provideSerialBusManager(@ApplicationContext context: Context): SerialBusManager {
        return SerialBusManager(context)
    }

    @Provides
    @Singleton
    fun provideFiseGpioController(@ApplicationContext context: Context): FiseGpioController {
        return FiseGpioController(context)
    }

    @Provides
    @Singleton
    fun provideSpectrumController(serialBus: SerialBusManager, fiseGpio: FiseGpioController): SpectrumController {
        return SpectrumController(serialBus, fiseGpio)
    }

    @Provides
    @Singleton
    fun provideUSBCameraManager(@ApplicationContext context: Context): USBCameraManager {
        return USBCameraManager(context)
    }

    @Provides
    @Singleton
    fun provideFrameCapturePipeline(
        spectrumController: SpectrumController,
        cameraManager: USBCameraManager,
        serialBusManager: SerialBusManager,
        faceDetector: FaceLandmarkDetector,
        fiseGpioController: FiseGpioController,
        preferencesManager: PreferencesManager
    ): FrameCapturePipeline {
        return FrameCapturePipeline(spectrumController, cameraManager, serialBusManager, faceDetector, fiseGpioController, preferencesManager)
    }

    @Provides
    @Singleton
    fun provideTFLiteEngine(@ApplicationContext context: Context): TFLiteEngine {
        return TFLiteEngine(context)
    }

    @Provides
    @Singleton
    fun provideFaceLandmarkDetector(): FaceLandmarkDetector {
        return FaceLandmarkDetector()
    }

    @Provides
    @Singleton
    fun provideFeatureExtractor(tfliteEngine: TFLiteEngine): FeatureExtractor {
        return FeatureExtractor(tfliteEngine)
    }

    @Provides
    @Singleton
    fun provideNetworkMonitor(@ApplicationContext context: Context): NetworkMonitor {
        return NetworkMonitor(context)
    }

    @Provides
    @Singleton
    fun provideUpdateChecker(@ApplicationContext context: Context): UpdateChecker {
        return UpdateChecker(context)
    }


    @Provides
    @Singleton
    fun provideAnalysisProviderManager(
        localProvider: LocalTFLiteProvider,
        cloudProvider: CloudAnalysisProvider
    ): AnalysisProviderManager {
        val manager = AnalysisProviderManager()
        manager.registerProvider(localProvider)
        manager.registerProvider(cloudProvider)
        return manager
    }
}

@Module
@InstallIn(SingletonComponent::class)
abstract class RepositoryModule {

    @Binds
    @Singleton
    abstract fun bindSkinAnalysisRepository(
        impl: SkinAnalysisRepositoryImpl
    ): SkinAnalysisRepository
}
