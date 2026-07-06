package com.ebtikar.skinanalyzer.hardware

import com.ebtikar.skinanalyzer.hardware.FiseGpioController
import com.ebtikar.skinanalyzer.hardware.LightSpectrum
import com.ebtikar.skinanalyzer.hardware.SerialBusManager
import com.ebtikar.skinanalyzer.hardware.SpectrumController
import kotlinx.coroutines.test.runTest
import org.junit.Assert.assertEquals
import org.junit.Assert.assertTrue
import org.junit.Before
import org.junit.Test
import org.mockito.kotlin.mock
import org.mockito.kotlin.whenever

class SpectrumControllerTest {

    private lateinit var controller: SpectrumController
    private lateinit var mockSerialBus: SerialBusManager
    private lateinit var mockFiseGpio: FiseGpioController

    @Before
    fun setup() {
        mockSerialBus = mock()
        mockFiseGpio = mock()
        whenever(mockFiseGpio.isAvailable).thenReturn(false)
        controller = SpectrumController(mockSerialBus, mockFiseGpio)
    }

    @Test
    fun `initial spectrum is OFF`() {
        assertEquals(LightSpectrum.OFF, controller.currentLight)
    }

    @Test
    fun `activate succeeds when serial connected`() = runTest {
        whenever(mockSerialBus.isConnected).thenReturn(true)
        whenever(mockSerialBus.sendCommand(LightSpectrum.WHITE)).thenReturn(Result.success(Unit))

        val result = controller.activate(LightSpectrum.WHITE)

        assertTrue(result.isSuccess)
        assertEquals(LightSpectrum.WHITE, controller.currentLight)
    }

    @Test
    fun `activate simulates when nothing connected`() = runTest {
        whenever(mockSerialBus.isConnected).thenReturn(false)

        val result = controller.activate(LightSpectrum.BLUE)

        assertTrue(result.isSuccess)
        assertEquals(LightSpectrum.BLUE, controller.currentLight)
    }

    @Test
    fun `BLUE falls back to serial when GPIO has no dedicated channel for it`() = runTest {
        // GPIO is present and active, but it has no physical channel for the
        // colored RGB-ring spectra (BLUE/RED/BROWN) - those must go over serial
        // instead of silently re-activating another GPIO channel.
        whenever(mockFiseGpio.isAvailable).thenReturn(true)
        whenever(mockFiseGpio.supportsSpectrum(LightSpectrum.BLUE)).thenReturn(false)
        whenever(mockSerialBus.isConnected).thenReturn(true)
        whenever(mockSerialBus.sendCommand(LightSpectrum.BLUE)).thenReturn(Result.success(Unit))

        val result = controller.activate(LightSpectrum.BLUE)

        assertTrue(result.isSuccess)
        assertEquals(LightSpectrum.BLUE, controller.currentLight)
    }

    @Test
    fun `WHITE uses GPIO directly when GPIO supports it`() = runTest {
        whenever(mockFiseGpio.isAvailable).thenReturn(true)
        whenever(mockFiseGpio.supportsSpectrum(LightSpectrum.WHITE)).thenReturn(true)
        whenever(mockFiseGpio.activateSpectrum(LightSpectrum.WHITE)).thenReturn(true)

        val result = controller.activate(LightSpectrum.WHITE)

        assertTrue(result.isSuccess)
        assertEquals(LightSpectrum.WHITE, controller.currentLight)
    }

    @Test
    fun `listener receives spectrum changes`() = runTest {
        whenever(mockSerialBus.isConnected).thenReturn(false)

        var receivedSpectrum: LightSpectrum? = null
        controller.addStateListener { receivedSpectrum = it }

        controller.activate(LightSpectrum.RED)

        assertEquals(LightSpectrum.RED, receivedSpectrum)
    }

    @Test
    fun `shutdown clears listeners and resets to OFF`() {
        controller.shutdown()
        assertEquals(LightSpectrum.OFF, controller.currentLight)
    }
}
