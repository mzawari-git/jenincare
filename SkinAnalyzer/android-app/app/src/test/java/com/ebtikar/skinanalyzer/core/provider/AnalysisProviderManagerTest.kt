package com.ebtikar.skinanalyzer.core.provider

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.BeforeEach
import org.junit.jupiter.api.Test
import java.io.File

class AnalysisProviderManagerTest {

    private lateinit var manager: AnalysisProviderManager
    private lateinit var mockLocalProvider: AnalysisProvider
    private lateinit var mockCloudProvider: AnalysisProvider

    @BeforeEach
    fun setUp() {
        manager = AnalysisProviderManager()

        mockLocalProvider = object : AnalysisProvider {
            override fun getName() = "Local_TFLite_Engine"
            override fun isAvailable() = true
            override fun getPriority() = 10
            override fun initialize() = Result.success(Unit)
            override suspend fun analyze(images: Map<String, File>) = AnalysisResult(
                providerName = getName(),
                executionTimeMs = 120L,
                metrics = emptyMap()
            )
            override fun shutdown() {}
        }

        mockCloudProvider = object : AnalysisProvider {
            override fun getName() = "Cloud_Analysis_Engine"
            override fun isAvailable() = true
            override fun getPriority() = 50
            override fun initialize() = Result.success(Unit)
            override suspend fun analyze(images: Map<String, File>) = AnalysisResult(
                providerName = getName(),
                executionTimeMs = 1450L,
                metrics = emptyMap()
            )
            override fun shutdown() {}
        }
    }

    @Test
    fun `test fallback mechanism when cloud provider drops offline`() {
        manager.registerProvider(mockLocalProvider)
        manager.registerProvider(mockCloudProvider)

        val dynamicCloud = object : AnalysisProvider {
            override fun getName() = "Cloud_Analysis_Engine"
            override fun isAvailable() = false
            override fun getPriority() = 50
            override fun initialize() = Result.success(Unit)
            override suspend fun analyze(images: Map<String, File>) = AnalysisResult(
                providerName = getName(),
                executionTimeMs = 0L,
                metrics = emptyMap()
            )
            override fun shutdown() {}
        }
        manager.unregisterProvider("Cloud_Analysis_Engine")
        manager.registerProvider(dynamicCloud)

        val activeProvider = manager.resolveActiveProvider()
        assertNotNull(activeProvider)
        assertEquals("Local_TFLite_Engine", activeProvider!!.getName())
    }

    @Test
    fun `test priority resolution selection`() {
        manager.registerProvider(mockLocalProvider)
        manager.registerProvider(mockCloudProvider)

        val activeProvider = manager.resolveActiveProvider()
        assertNotNull(activeProvider)
        assertEquals("Cloud_Analysis_Engine", activeProvider!!.getName())
    }

    @Test
    fun `test no providers returns null`() {
        val activeProvider = manager.resolveActiveProvider()
        assertNull(activeProvider)
    }

    @Test
    fun `test register and unregister provider`() {
        manager.registerProvider(mockLocalProvider)
        assertEquals(1, manager.getRegisteredProviders().size)

        manager.unregisterProvider("Local_TFLite_Engine")
        assertEquals(0, manager.getRegisteredProviders().size)
    }

    @Test
    fun `test shutdown all clears providers`() {
        manager.registerProvider(mockLocalProvider)
        manager.registerProvider(mockCloudProvider)
        assertEquals(2, manager.getRegisteredProviders().size)

        manager.shutdownAll()
        assertEquals(0, manager.getRegisteredProviders().size)
    }
}
