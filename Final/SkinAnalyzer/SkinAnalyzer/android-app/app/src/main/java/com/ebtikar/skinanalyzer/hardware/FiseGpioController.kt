package com.ebtikar.skinanalyzer.hardware

import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FiseGpioController @Inject constructor() {

    private val gpioMap = mapOf(
        0 to 34,
        1 to 149,
        2 to 45,
        3 to 54,
        4 to 56,
        5 to 155
    )

    private val gpioFiles = gpioMap.map { (index, gpioNum) ->
        index to File("/sys/class/gpio/gpio$gpioNum/value")
    }.toMap()

    private var _available = false
    var selinuxEnforcing: Boolean? = null
        private set

    val isAvailable: Boolean get() = _available

    init {
        checkSelinux()
        exportAll()
        val allExist = gpioFiles.values.all { it.exists() }
        val writable = allExist && verifyWriteAccess()
        _available = writable
        if (_available) {
            Timber.i("Standard GPIO controller available: ${gpioMap.size} channels (gpios=${gpioMap.values}), SELinux=$selinuxEnforcing")
            turnAllOff()
        } else {
            Timber.w("Standard GPIO controller NOT available (SELinux=$selinuxEnforcing)")
            gpioFiles.forEach { (idx, file) ->
                Timber.w("  GPIO $idx (${gpioMap[idx]}): exists=${file.exists()}, canWrite=${file.canWrite()}")
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

    private fun exportAll() {
        for ((index, gpioNum) in gpioMap) {
            val dir = File("/sys/class/gpio/gpio$gpioNum")
            if (!dir.exists()) {
                try {
                    shellExec("echo $gpioNum > /sys/class/gpio/export")
                    Timber.d("Exported GPIO $gpioNum for FISE index $index")
                } catch (e: Exception) {
                    Timber.d("Cannot export GPIO $gpioNum (index $index): ${e.message}. Assume pre-exported.")
                }
            }
            try {
                shellExec("echo out > /sys/class/gpio/gpio$gpioNum/direction")
            } catch (e: Exception) {
                Timber.d("Cannot set direction for GPIO $gpioNum (index $index): ${e.message}")
            }
        }
    }

    private fun verifyWriteAccess(): Boolean {
        for ((index, file) in gpioFiles) {
            if (!writeSysfs(file, "1")) {
                Timber.e("GPIO write test FAILED for index $index (${gpioMap[index]})")
                return false
            }
            val rb = try { file.readText().trim() } catch (e: Exception) { "?" }
            Timber.d("GPIO $index write test: wrote=1, readback=$rb")
        }
        Timber.i("All GPIO write tests passed")
        return true
    }

    fun setGpioViaShell(index: Int, on: Boolean): Boolean {
        val file = gpioFiles[index] ?: return false
        return writeSysfs(file, if (on) "0" else "1")
    }

    fun setGpioViaSu(index: Int, on: Boolean): Boolean {
        val gpioNum = gpioMap[index] ?: return false
        val value = if (on) "0" else "1"
        return try {
            val proc = Runtime.getRuntime().exec(arrayOf("su", "-c", "echo $value > /sys/class/gpio/gpio$gpioNum/value"))
            val exitCode = proc.waitFor()
            Timber.d("GPIO $index via su -> ${if (on) "ON" else "OFF"} (exit=$exitCode)")
            exitCode == 0
        } catch (e: Exception) {
            Timber.e(e, "su write failed for GPIO $index")
            false
        }
    }

    private fun shellExec(cmd: String): Boolean {
        return try {
            val proc = Runtime.getRuntime().exec(arrayOf("sh", "-c", cmd))
            val exit = proc.waitFor()
            exit == 0
        } catch (e: Exception) {
            false
        }
    }

    private fun writeSysfs(file: File, value: String): Boolean {
        val path = file.absolutePath
        return if (shellExec("echo $value > $path")) {
            true
        } else {
            Timber.e("Shell write to $path = $value failed, trying direct")
            try {
                file.writeText(value)
                true
            } catch (e: Exception) {
                Timber.e(e, "Direct write to $path = $value also failed")
                false
            }
        }
    }

    fun setMasterLed(on: Boolean): Boolean {
        Timber.d("Master LED control is N/A (managed by lcdparamservice); returning true")
        return true
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        val file = gpioFiles[index] ?: return false
        val value = if (on) "0" else "1"
        val ok = writeSysfs(file, value)
        val readback = try { file.readText().trim() } catch (e: Exception) { "?" }
        Timber.i("GPIO $index (gpio-${gpioMap[index]}) -> ${if (on) "ON" else "OFF"} (ok=$ok, readback=$readback)")
        return ok
    }

    fun turnAllOff() {
        for (i in gpioFiles.keys.sorted()) {
            setGpio(i, false)
        }
    }

    fun supportsSpectrum(spectrum: LightSpectrum): Boolean =
        spectrumToGpioIndex(spectrum) >= 0

    fun activateSpectrum(spectrum: LightSpectrum): Boolean {
        val gpioIndex = spectrumToGpioIndex(spectrum)
        if (gpioIndex < 0) {
            Timber.w("No GPIO channel for ${spectrum.name}; caller should fall back")
            return false
        }
        turnAllOff()
        return setGpio(gpioIndex, true)
    }

    fun activateAll(): Boolean {
        var allOk = true
        for (i in gpioFiles.keys.sorted()) {
            if (!setGpio(i, true)) allOk = false
        }
        return allOk
    }

    private fun spectrumToGpioIndex(spectrum: LightSpectrum): Int {
        return when (spectrum) {
            LightSpectrum.WHITE -> 0
            LightSpectrum.UV365 -> 1
            LightSpectrum.WOODS -> 2
            LightSpectrum.POL_P -> 3
            LightSpectrum.POL_N -> 4
            LightSpectrum.BLUE, LightSpectrum.RED, LightSpectrum.BROWN -> -1
            LightSpectrum.ALL -> -1
            LightSpectrum.OFF -> -2
            else -> -1
        }
    }
}
