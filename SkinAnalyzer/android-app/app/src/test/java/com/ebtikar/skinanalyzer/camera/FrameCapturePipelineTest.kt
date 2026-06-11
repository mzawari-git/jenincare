package com.ebtikar.skinanalyzer.camera

import com.ebtikar.skinanalyzer.camera.FrameCapturePipeline
import com.ebtikar.skinanalyzer.camera.USBCameraManager
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import org.junit.Assert.assertEquals
import org.junit.Before
import org.junit.Test
import org.mockito.kotlin.mock

class FrameCapturePipelineTest {

    private lateinit var pipeline: FrameCapturePipeline
    private lateinit var mockSpectrumController: SpectrumController
    private lateinit var mockCameraManager: USBCameraManager
    private lateinit var mockSerialBusManager: SerialBusManager

    @Before
    fun setup() {
        mockSpectrumController = mock()
        mockCameraManager = mock()
        mockSerialBusManager = mock()
        pipeline = FrameCapturePipeline(mockSpectrumController, mockCameraManager, mockSerialBusManager)
    }

    @Test
    fun `initial state is IDLE`() {
        assertEquals(FrameCapturePipeline.CaptureState.IDLE, pipeline.captureState.value)
    }

    @Test
    fun `initial captured frames is empty`() {
        assertEquals(emptyMap<Any, Any>(), pipeline.capturedFrames.value)
    }

    @Test
    fun `initial current phase is null`() {
        assertEquals(null, pipeline.currentPhase.value)
    }

    @Test
    fun `reset clears all state`() {
        pipeline.reset()

        assertEquals(FrameCapturePipeline.CaptureState.IDLE, pipeline.captureState.value)
        assertEquals(emptyMap<Any, Any>(), pipeline.capturedFrames.value)
        assertEquals(null, pipeline.currentPhase.value)
    }
}
