package com.ebtikar.skinanalyzer.hardware

import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import timber.log.Timber
import java.util.concurrent.CopyOnWriteArrayList
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SpectrumController @Inject constructor(
    private val serialBus: SerialBusManager,
    private val fiseGpio: FiseGpioController
) {

    @Volatile private var currentSpectrum: LightSpectrum = LightSpectrum.OFF
    private val stateListeners = CopyOnWriteArrayList<(LightSpectrum) -> Unit>()
    private val activateMutex = Mutex()

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

    suspend fun ensureHardwareReady(): Boolean {
        var anyReady = false
        if (!fiseGpio.isAvailable) {
            Timber.i("GPIO unavailable, attempting recheck...")
            val gpioOk = fiseGpio.recheckAvailability()
            if (gpioOk) anyReady = true
        } else {
            anyReady = true
        }
        if (!serialBus.isConnected) {
            Timber.i("Serial bus not connected, attempting auto-connect...")
            val serialResult = serialBus.autoConnect()
            if (serialResult.isSuccess) anyReady = true
        } else {
            anyReady = true
        }
        Timber.i("ensureHardwareReady: gpio=${fiseGpio.isAvailable}, serial=${serialBus.isConnected}, anyReady=$anyReady")
        return anyReady
    }

    suspend fun activate(spectrum: LightSpectrum): Result<Unit> = activateMutex.withLock {
        if (spectrum == LightSpectrum.OFF) {
            fiseGpio.turnAllOff()
            if (serialBus.isConnected) serialBus.sendCommand(LightSpectrum.OFF)
            currentSpectrum = spectrum
            _currentSpectrumFlow.value = spectrum
            notifyListeners(spectrum)
            return@withLock Result.success(Unit)
        }
        if (spectrum == LightSpectrum.ALL) {
            val gpioOk = fiseGpio.activateAll()
            if (!gpioOk && serialBus.isConnected) {
                return@withLock serialBus.sendAllLightsCommand()
            }
            currentSpectrum = spectrum
            _currentSpectrumFlow.value = spectrum
            notifyListeners(spectrum)
            return@withLock Result.success(Unit)
        }

        if (fiseGpio.supportsSpectrum(spectrum)) {
            Timber.d("Activating spectrum via FISE GPIO: ${spectrum.name}")
            val ok = fiseGpio.activateSpectrum(spectrum)
            if (ok) {
                currentSpectrum = spectrum
                _currentSpectrumFlow.value = spectrum
                notifyListeners(spectrum)
                return@withLock Result.success(Unit)
            }
            Timber.e("FISE GPIO activation FAILED for ${spectrum.name}")
        }

        if (serialBus.isConnected) {
            Timber.d("Activating spectrum via serial bus: ${spectrum.name}")
            val result = serialBus.sendCommand(spectrum)
            if (result.isSuccess) {
                currentSpectrum = spectrum
                _currentSpectrumFlow.value = spectrum
                notifyListeners(spectrum)
                Timber.d("Spectrum activated via serial: ${spectrum.name}")
                return@withLock Result.success(Unit)
            }
            Timber.w("Serial activation failed for ${spectrum.name}: ${result.exceptionOrNull()?.message}")
        }

        Timber.e("No working GPIO or serial for ${spectrum.name}. LED will NOT turn on.")
        return@withLock Result.failure(IllegalStateException("No LED hardware for ${spectrum.name}: GPIO=${fiseGpio.isAvailable}, serial=${serialBus.isConnected}"))
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

    data class LedTestResult(
        val spectrum: LightSpectrum,
        val gpioOk: Boolean,
        val serialOk: Boolean,
        val anyOk: Boolean
    )

    suspend fun testAllLedsForScan(
        spectra: List<LightSpectrum>,
        onLedTest: suspend (LightSpectrum, Int, Int) -> Unit = { _, _, _ -> }
    ): List<LedTestResult> {
        val results = mutableListOf<LedTestResult>()
        val total = spectra.size

        for ((index, spectrum) in spectra.withIndex()) {
            onLedTest(spectrum, index + 1, total)

            val gpioOk = if (fiseGpio.supportsSpectrum(spectrum)) {
                val result = fiseGpio.activateSpectrum(spectrum)
                delay(150)
                fiseGpio.turnAllOff()
                delay(50)
                result
            } else false

            val serialOk = if (serialBus.isConnected && !gpioOk) {
                val result = serialBus.sendCommand(spectrum)
                delay(150)
                serialBus.sendCommand(LightSpectrum.OFF)
                delay(50)
                result.isSuccess
            } else false

            results.add(LedTestResult(spectrum, gpioOk, serialOk, gpioOk || serialOk))
            Timber.i("LED test ${spectrum.name}: gpio=$gpioOk, serial=$serialOk")
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
        try {
            fiseGpio.turnAllOff()
        } catch (_: Exception) {}
        try {
            if (serialBus.isConnected) {
                kotlinx.coroutines.runBlocking {
                    kotlinx.coroutines.withTimeoutOrNull(3000L) {
                        serialBus.sendCommand(LightSpectrum.OFF)
                    }
                }
            }
        } catch (_: Exception) {}
        currentSpectrum = LightSpectrum.OFF
        _currentSpectrumFlow.value = LightSpectrum.OFF
        stateListeners.clear()
    }
}
