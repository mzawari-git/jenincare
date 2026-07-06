package com.ebtikar.skinanalyzer.ai

import com.ebtikar.skinanalyzer.ai.FeatureExtractor
import com.ebtikar.skinanalyzer.ai.TFLiteEngine
import com.ebtikar.skinanalyzer.model.SkinMetric
import org.junit.Assert.assertEquals
import org.junit.Assert.assertTrue
import org.junit.Before
import org.junit.Test
import org.mockito.kotlin.mock

class FeatureExtractorTest {

    private lateinit var featureExtractor: FeatureExtractor
    private lateinit var mockTfliteEngine: TFLiteEngine

    @Before
    fun setup() {
        mockTfliteEngine = mock()
        featureExtractor = FeatureExtractor(mockTfliteEngine)
    }

    @Test
    fun `SkinMetric ALL_TYPES contains 17 types`() {
        assertEquals(17, SkinMetric.ALL_TYPES.size)
    }

    @Test
    fun `SkinMetric TOTAL_METRICS is 17`() {
        assertEquals(17, SkinMetric.TOTAL_METRICS)
    }

    @Test
    fun `all metric types are present`() {
        val expectedTypes = setOf(
            SkinMetric.Type.MOISTURE,
            SkinMetric.Type.PORES,
            SkinMetric.Type.SEBUM,
            SkinMetric.Type.WRINKLES,
            SkinMetric.Type.TEXTURE,
            SkinMetric.Type.UV_SPOTS,
            SkinMetric.Type.VASCULAR,
            SkinMetric.Type.PIGMENTATION,
            SkinMetric.Type.DARK_CIRCLES,
            SkinMetric.Type.BLACKHEADS,
            SkinMetric.Type.ACNE,
            SkinMetric.Type.COLLAGEN,
            SkinMetric.Type.SKIN_TONE,
            SkinMetric.Type.SENSITIVITY,
            SkinMetric.Type.PORPHYRINS,
            SkinMetric.Type.ROSACEA,
            SkinMetric.Type.MELASMA
        )
        assertEquals(expectedTypes, SkinMetric.Type.entries.toSet())
    }
}
