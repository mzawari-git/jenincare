package com.ebtikar.skinanalyzer.hardware

import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SpectrumController @Inject constructor(
    private val serialBus: SerialBusManager,
    private val fiseGpio: FiseGpioController
) {

    private var currentSpectrum: LightSpectrum = LightSpectrum.OFF
    private val stateListeners = mutableListOf<(LightSpectrum) -> Unit>()

    private val _currentSpectrumFlow = MutableStateFlow(LightSpectrum.OFF)
    val currentSpectrumFlow: StateFlow<LightSpectrum> = _currentSpectrumFlow.asStateFlow()

    private val _sequenceProgress = MutableStateFlow(SpectrumSequenceProgress())
    val sequenceProgress: StateFlow<SpectrumSequenceProgress> = _sequenceProgress.asStateFlow()

    data class SpectrumSequenceProgress(
        val currentPhase: Int = 0,
        val totalPhases: Int = 0,
        val currentSpectrum: LightSpectrum = LightSpectrum.OFF,
        val isRunning: Boolean = false,
        val completedSpectra: List<LightSpectrum> = emptyList(),
        val failedSpectra: List<LightSpectrum> = emptyList()
    ) {
        val percentComplete: Int
            get() = if (totalPhases > 0) (currentPhase * 100 / totalPhases) else 0
    }

    val currentLight: LightSpectrum get() = currentSpectrum

    suspend fun activate(spectrum: LightSpectrum): Result<Unit> {
        val result = when {
            spectrum == LightSpectrum.ALL -> activateAllLights()
            spectrum == LightSpectrum.OFF && fiseGpio.isAvailable -> activateViaGpio(spectrum)
            fiseGpio.isAvailable && fiseGpio.supportsSpectrum(spectrum) -> activateViaGpio(spectrum)
            serialBus.isConnected -> activateViaSerial(spectrum)
            else -> activateViaSimulation(spectrum)
        }

        if (result.isSuccess) {
            currentSpectrum = spectrum
            _currentSpectrumFlow.value = spectrum
            notifyListeners(spectrum)
        }
        delay(spectrum.settlingWindowMs)
        return result
    }

    private fun activateViaGpio(spectrum: LightSpectrum): Result<Unit> {
        Timber.d("Activating spectrum via FISE GPIO: ${spectrum.name}")
        val ok = when (spectrum) {
            LightSpectrum.OFF -> { fiseGpio.turnAllOff(); true }
            else -> fiseGpio.activateSpectrum(spectrum)
        }
        if (!ok) {
            Timber.e("FISE GPIO activation FAILED for ${spectrum.name} (SELinux=${fiseGpio.selinuxEnforcing}). Lights will NOT turn on!")
        }
        return if (ok) Result.success(Unit) else Result.failure(IllegalStateException("FISE GPIO activation failed"))
    }

    private suspend fun activateViaSerial(spectrum: LightSpectrum): Result<Unit> {
        Timber.d("Activating spectrum via serial bus: ${spectrum.name}")
        return serialBus.sendCommand(spectrum).also { result ->
            if (result.isSuccess) {
                Timber.d("Spectrum activated via serial: ${spectrum.name}")
            } else {
                Timber.e("Failed to activate ${spectrum.name} via serial: ${result.exceptionOrNull()?.message}")
            }
        }
    }

    private suspend fun activateViaSimulation(spectrum: LightSpectrum): Result<Unit> {
        Timber.w("No FISE GPIO channel or serial connection available for ${spectrum.name}. Simulating.")
        return Result.success(Unit)
    }

    /**
     * Turns on every real light source the device has: GPIO channels for the
     * White/UV/Wood's/polarized LEDs, plus a serial command for the RGB ring
     * (Blue/Red/Brown) when connected. Falls back to simulation only for
     * sources with no available hardware path at all.
     */
    private suspend fun activateAllLights(): Result<Unit> {
        var anySucceeded = false
        var anyAttempted = false

        if (fiseGpio.isAvailable) {
            anyAttempted = true
            if (fiseGpio.activateAll()) {
                anySucceeded = true
            } else {
                Timber.e("FISE GPIO activateAll() failed (SELinux=${fiseGpio.selinuxEnforcing})")
            }
        }

        // The RGB ring (Blue/Red/Brown) is only reachable over serial, regardless
        // of whether GPIO handled the other channels.
        if (serialBus.isConnected) {
            anyAttempted = true
            val serialResult = serialBus.sendAllLightsCommand()
            if (serialResult.isSuccess) {
                anySucceeded = true
            } else {
                Timber.e("Serial ALL lights command failed: ${serialResult.exceptionOrNull()?.message}")
            }
        }

        if (!anyAttempted) {
            Timber.w("No FISE GPIO or serial connection. Simulating ALL spectra.")
            return Result.success(Unit)
        }

        return if (anySucceeded) Result.success(Unit) else Result.failure(IllegalStateException("ALL lights activation failed"))
    }

    suspend fun executeCaptureSequence(
        onPhaseStart: suspend (LightSpectrum) -> Unit,
        onPhaseComplete: suspend (LightSpectrum, Result<Unit>) -> Unit
    ): Result<List<LightSpectrum>> {
        val results = mutableListOf<LightSpectrum>()
        val failed = mutableListOf<LightSpectrum>()
        val sequence = buildList {
            add(LightSpectrum.ALL)
            addAll(LightSpectrum.CAPTURE_SEQUENCE)
        }

        _sequenceProgress.value = SpectrumSequenceProgress(
            totalPhases = sequence.size,
            isRunning = true
        )

        for ((index, spectrum) in sequence.withIndex()) {
            _sequenceProgress.value = _sequenceProgress.value.copy(
                currentPhase = index,
                currentSpectrum = spectrum
            )

            val activateResult = activate(spectrum)
            if (activateResult.isFailure) {
                Timber.e("Failed to activate ${spectrum.name}, marking as failed")
                failed.add(spectrum)
                onPhaseComplete(spectrum, activateResult)
                continue
            }

            delay(spectrum.settlingWindowMs)
            onPhaseStart(spectrum)

            delay(50)
            onPhaseComplete(spectrum, Result.success(Unit))
            results.add(spectrum)

            _sequenceProgress.value = _sequenceProgress.value.copy(
                completedSpectra = results.toList(),
                failedSpectra = failed.toList()
            )
        }

        activate(LightSpectrum.OFF)

        _sequenceProgress.value = _sequenceProgress.value.copy(
            currentPhase = sequence.size,
            isRunning = false
        )

        Timber.i("Capture sequence complete: ${results.size}/${sequence.size} successful")
        if (failed.isNotEmpty()) {
            Timber.w("Failed spectra: ${failed.joinToString { it.name }}")
        }

        return if (results.size >= sequence.size / 2) {
            Result.success(results)
        } else {
            Result.failure(IllegalStateException("Too many spectra failed: ${failed.size}/${sequence.size}"))
        }
    }

    suspend fun quickTest(): Map<LightSpectrum, Boolean> {
        val results = mutableMapOf<LightSpectrum, Boolean>()
        for (spectrum in LightSpectrum.DIAGNOSTIC_SPECTRA) {
            val result = activate(spectrum)
            delay(200)
            results[spectrum] = result.isSuccess
        }
        activate(LightSpectrum.OFF)
        return results
    }

    fun addStateListener(listener: (LightSpectrum) -> Unit) {
        stateListeners.add(listener)
    }

    fun removeStateListener(listener: (LightSpectrum) -> Unit) {
        stateListeners.remove(listener)
    }

    private fun notifyListeners(spectrum: LightSpectrum) {
        stateListeners.forEach { it(spectrum) }
    }

    fun shutdown() {
        currentSpectrum = LightSpectrum.OFF
        _currentSpectrumFlow.value = LightSpectrum.OFF
        stateListeners.clear()
    }
}
