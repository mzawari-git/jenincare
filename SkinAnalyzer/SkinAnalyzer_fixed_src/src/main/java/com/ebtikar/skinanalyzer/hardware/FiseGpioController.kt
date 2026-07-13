package com.ebtikar.skinanalyzer.hardware

import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FiseGpioController @Inject constructor() {

    private val gpioFiles = (0..5).map { File("/sys/class/fise_gpio$it/level") }
    private val ledFile = File("/sys/class/fise_led/level")
    private var _available = false
    var selinuxEnforcing: Boolean? = null
        private set

    val isAvailable: Boolean get() = _available

    init {
        checkSelinux()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        _available = gpioOk || ledOk
        if (_available) {
            Timber.i("FISE GPIO controller available: ${gpioFiles.size} channels, fise_led=${ledOk}, SELinux=${selinuxEnforcing}")
            gpioFiles.forEach { Timber.d("  ${it.absolutePath}: exists=${it.exists()}") }
            turnAllOff()
        } else {
            Timber.w("FISE GPIO controller not available (SELinux=${selinuxEnforcing})")
            gpioFiles.forEach {
                Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${it.canWrite()}")
            }
        }
    }

    private fun checkSelinux() {
        selinuxEnforcing = try {
            val enforceFile = File("/sys/fs/selinux/enforce")
            if (enforceFile.exists()) enforceFile.readText().trim() == "1"
            else null
        } catch (e: Exception) {
            null
        }
    }

    private fun verifyWriteAccess(): Boolean {
        for (file in gpioFiles) {
            try {
                file.writeText("0")
                val rb = file.readText().trim()
                Timber.d("GPIO write test ${file.absolutePath}: wrote=0, readback=$rb")
            } catch (e: Exception) {
                Timber.e(e, "GPIO write test FAILED for ${file.absolutePath} (SELinux=$selinuxEnforcing)")
                return false
            }
        }
        Timber.i("All GPIO write tests passed (write syscall succeeded)")
        return true
    }

    /** Try to execute a command via shell (may help if SELinux blocks direct writes in app context) */
    fun setGpioViaShell(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val value = if (on) "1" else "0"
        val path = gpioFiles[index].absolutePath
        return try {
            val cmd = "sh -c 'echo $value > $path'"
            val proc = Runtime.getRuntime().exec(cmd)
            val exitCode = proc.waitFor()
            Timber.d("FISE GPIO $index via shell -> ${if (on) "ON" else "OFF"} (exit=$exitCode)")
            exitCode == 0
        } catch (e: Exception) {
            Timber.e(e, "Shell write failed for FISE GPIO $index")
            false
        }
    }

    /** Try to execute via su (requires root) */
    fun setGpioViaSu(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val value = if (on) "1" else "0"
        val path = gpioFiles[index].absolutePath
        return try {
            val proc = Runtime.getRuntime().exec(arrayOf("su", "-c", "echo $value > $path"))
            val exitCode = proc.waitFor()
            Timber.d("FISE GPIO $index via su -> ${if (on) "ON" else "OFF"} (exit=$exitCode)")
            exitCode == 0
        } catch (e: Exception) {
            Timber.e(e, "su write failed for FISE GPIO $index")
            false
        }
    }

    fun setMasterLed(on: Boolean): Boolean {
        return try {
            val value = if (on) "1" else "0"
            ledFile.writeText(value)
            Timber.i("FISE LED master -> ${if (on) "ON" else "OFF"}")
            true
        } catch (e: Exception) {
            Timber.e(e, "Failed to write FISE LED master")
            false
        }
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val file = gpioFiles[index]
        return try {
            val value = if (on) "1" else "0"
            file.writeText(value)
            val readback = try { file.readText().trim() } catch (e: Exception) { "?" }
            Timber.i("FISE GPIO $index -> ${if (on) "ON" else "OFF"} (readback=$readback)")
            true
        } catch (e: Exception) {
            Timber.e(e, "Failed to write FISE GPIO $index (SELinux=$selinuxEnforcing)")
            false
        }
    }

    fun turnAllOff() {
        setMasterLed(false)
        for (i in gpioFiles.indices) {
            setGpio(i, false)
        }
    }

    /**
     * Returns true if [spectrum] is driven by a dedicated physical FISE GPIO channel.
     *
     * The board only exposes 5 usable diagnostic channels (fise_gpio0..4) for the
     * White / UV365 / Wood's / Cross-pol / Parallel-pol LEDs. The colored analysis
     * lights (Blue 465nm, Red 630nm, Brown 590nm) are driven by the addressable RGB
     * LED ring over the serial bus protocol instead - there is no GPIO line for them.
     * Previously these three spectra were (incorrectly) mapped to GPIO channel 0,
     * which meant requesting "Blue/Red/Brown" silently re-activated the White LED
     * instead of the requested color. They are now correctly reported as
     * unsupported here so the caller (SpectrumController) can fall back to the
     * serial bus, which natively supports their command bytes.
     */
    fun supportsSpectrum(spectrum: LightSpectrum): Boolean =
        spectrumToGpioIndex(spectrum) >= 0

    fun activateSpectrum(spectrum: LightSpectrum): Boolean {
        val gpioIndex = spectrumToGpioIndex(spectrum)
        if (gpioIndex < 0) {
            Timber.w("FISE GPIO has no dedicated channel for ${spectrum.name}; caller should fall back")
            return false
        }
        turnAllOff()
        val gpioOk = setGpio(gpioIndex, true)
        val ledOk = setMasterLed(true)
        return gpioOk && ledOk
    }

    fun activateAll(): Boolean {
        var allOk = true
        for (i in gpioFiles.indices) {
            if (!setGpio(i, true)) allOk = false
        }
        val ledOk = setMasterLed(true)
        return allOk && ledOk
    }

    private fun spectrumToGpioIndex(spectrum: LightSpectrum): Int {
        return when (spectrum) {
            LightSpectrum.WHITE -> 0
            LightSpectrum.UV365 -> 1
            LightSpectrum.WOODS -> 2
            LightSpectrum.POL_P -> 3
            LightSpectrum.POL_N -> 4
            // No dedicated GPIO channel - these go through the serial RGB ring instead.
            LightSpectrum.BLUE, LightSpectrum.RED, LightSpectrum.BROWN -> -1
            LightSpectrum.ALL -> -1
            LightSpectrum.OFF -> -2
            else -> -1
        }
    }
}
