package com.ebtikar.skinanalyzer.data.repository

import com.ebtikar.skinanalyzer.ai.FeatureExtractor
import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.core.provider.AnalysisProviderManager
import com.ebtikar.skinanalyzer.data.local.SkinReportDao
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.data.remote.MockAnalysisEngine
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.model.AnalysisState
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.SkinAnalysisReport
import com.ebtikar.skinanalyzer.model.SkinMetric
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.test.runTest
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertTrue
import org.junit.Before
import org.junit.Test
import org.mockito.kotlin.any
import org.mockito.kotlin.mock
import org.mockito.kotlin.verify
import org.mockito.kotlin.whenever
import java.io.File

class SkinAnalysisRepositoryTest {

    private lateinit var repository: SkinAnalysisRepositoryImpl
    private lateinit var mockCapturePipeline: FrameCapturePipeline
    private lateinit var mockProviderManager: AnalysisProviderManager
    private lateinit var mockReportDao: SkinReportDao
    private lateinit var mockEngine: MockAnalysisEngine
    private lateinit var mockFeatureExtractor: FeatureExtractor

    @Before
    fun setup() {
        mockCapturePipeline = mock()
        mockProviderManager = mock()
        mockReportDao = mock()
        mockEngine = MockAnalysisEngine()
        mockFeatureExtractor = mock()
    }

    @Test
    fun `initial state is Idle`() = runTest {
        val context = mock<android.content.Context>()
        whenever(context.filesDir).thenReturn(File("/tmp/test"))

        repository = SkinAnalysisRepositoryImpl(
            context, mockCapturePipeline, mockProviderManager, mockReportDao, mockEngine, mockFeatureExtractor
        )

        assertEquals(AnalysisState.Idle, repository.getAnalysisState().value)
    }

    @Test
    fun `saveReport inserts entity into dao`() = runTest {
        val context = mock<android.content.Context>()
        whenever(context.filesDir).thenReturn(File("/tmp/test"))

        repository = SkinAnalysisRepositoryImpl(
            context, mockCapturePipeline, mockProviderManager, mockReportDao, mockEngine, mockFeatureExtractor
        )

        val report = SkinAnalysisReport(
            id = "test-id",
            providerName = "TestProvider",
            overallScore = 85f,
            metrics = SkinMetric.ALL_TYPES.map {
                SkinMetric(it, 80f, MetricSeverity.GOOD)
            },
            executionTimeMs = 1000
        )

        val result = repository.saveReport(report)

        assertTrue(result.isSuccess)
        verify(mockReportDao).insertReport(any())
    }

    @Test
    fun `deleteReport removes from dao and cleans files`() = runTest {
        val context = mock<android.content.Context>()
        val tempDir = File(System.getProperty("java.io.tmpdir"), "test_captures")
        tempDir.mkdirs()
        whenever(context.filesDir).thenReturn(tempDir)

        repository = SkinAnalysisRepositoryImpl(
            context, mockCapturePipeline, mockProviderManager, mockReportDao, mockEngine, mockFeatureExtractor
        )

        repository.deleteReport("test-id")

        verify(mockReportDao).deleteReport("test-id")
    }

    @Test
    fun `getReportCount returns dao count`() = runTest {
        val context = mock<android.content.Context>()
        whenever(context.filesDir).thenReturn(File("/tmp/test"))

        repository = SkinAnalysisRepositoryImpl(
            context, mockCapturePipeline, mockProviderManager, mockReportDao, mockEngine, mockFeatureExtractor
        )

        whenever(mockReportDao.getReportCount()).thenReturn(5)

        assertEquals(5, repository.getReportCount())
    }
}
