package com.ebtikar.skinanalyzer.hardware

import kotlinx.coroutines.delay
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SpectrumController @Inject constructor(
    private val serialBus: SerialBusManager
) {

    private var currentSpectrum: LightSpectrum = LightSpectrum.OFF
    private val stateListeners = mutableListOf<(LightSpectrum) -> Unit>()

    val currentLight: LightSpectrum get() = currentSpectrum

    suspend fun activate(spectrum: LightSpectrum): Result<Unit> {
        return if (serialBus.isConnected) {
            serialBus.sendCommand(spectrum).also { result ->
                if (result.isSuccess) {
                    currentSpectrum = spectrum
                    notifyListeners(spectrum)
                    Timber.d("Spectrum activated: ${spectrum.name}")
                }
            }
        } else {
            Timber.w("Serial bus not connected. Simulating spectrum: ${spectrum.name}")
            currentSpectrum = spectrum
            notifyListeners(spectrum)
            Result.success(Unit)
        }
    }

    suspend fun executeCaptureSequence(
        onPhaseStart: suspend (LightSpectrum) -> Unit,
        onPhaseComplete: suspend (LightSpectrum) -> Unit
    ): Result<List<LightSpectrum>> {
        val results = mutableListOf<LightSpectrum>()

        for (spectrum in LightSpectrum.CAPTURE_SEQUENCE) {
            val activateResult = activate(spectrum)
            if (activateResult.isFailure) {
                Timber.e("Failed to activate ${spectrum.name}, aborting sequence")
                return Result.failure(activateResult.exceptionOrNull()!!)
            }

            delay(spectrum.settlingWindowMs)
            onPhaseStart(spectrum)

            delay(50)
            onPhaseComplete(spectrum)
            results.add(spectrum)
        }

        activate(LightSpectrum.OFF)
        Timber.i("Capture sequence complete: ${results.size} phases")
        return Result.success(results)
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
        stateListeners.clear()
    }
}
