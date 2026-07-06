package com.ebtikar.skinanalyzer.hardware

import android.content.Context
import dagger.hilt.android.qualifiers.ApplicationContext
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FiseGpioController @Inject constructor(
    @ApplicationContext private val context: Context
) {

    private val gpioMap = mapOf(
        0 to 34,
        1 to 149,
        2 to 45,
        3 to 54,
        4 to 56
    )

    private val gpioFiles = gpioMap.map { (index, gpioNum) ->
        index to File("/sys/class/gpio/gpio$gpioNum/value")
    }.toMap()

    private var _available = false
    var selinuxEnforcing: Boolean? = null
        private set

    val isAvailable: Boolean get() = _available

    private var _statusMessage = ""
    val statusMessage: String get() = _statusMessage

    init {
        checkSelinux()
        if (!setupGpio()) {
            Timber.w("Initial GPIO setup failed. Trying FISE driver rebind...")
            if (rebindFiseDriver()) {
                Timber.i("FISE driver rebound. Retrying GPIO setup...")
                setupGpio()
            }
            if (!_available) {
                Timber.w("FISE driver rebind failed or didn't help. Trying boot script install...")
                if (installBootScript()) {
                    Timber.i("Boot script installed & run. Retrying GPIO setup...")
                    setupGpio()
                }
            }
            if (!_available) {
                Timber.w("Trying one-shot export via sh -c for each pin...")
                var anyOk = false
                for ((_, gpioNum) in gpioMap) {
                    val dir = File("/sys/class/gpio/gpio$gpioNum")
                    if (!dir.exists()) {
                        if (shellExec("echo $gpioNum > /sys/class/gpio/export")) {
                            anyOk = true
                            kotlinx.coroutines.runBlocking { kotlinx.coroutines.delay(80) }
                            shellExec("echo out > /sys/class/gpio/gpio$gpioNum/direction")
                            shellExec("echo 1 > /sys/class/gpio/gpio$gpioNum/value")
                            shellExec("chmod 666 /sys/class/gpio/gpio$gpioNum/value")
                        }
                    } else {
                        anyOk = true
                    }
                }
                if (anyOk) setupGpio()
            }
        }
        if (_available) {
            _statusMessage = "أضواء التشخيص جاهزة ✓"
            Timber.i("Standard GPIO controller available: 5 channels (gpios=${gpioMap.values}), SELinux=$selinuxEnforcing")
            turnAllOff()
        } else {
            _statusMessage = "⚠️ أضواء التشخيص غير متصلة. قم بتشغيل: setup_gpio.ps1 عبر ADB"
            Timber.e("GPIO NOT available after all attempts (SELinux=$selinuxEnforcing)")
            gpioFiles.forEach { (idx, file) ->
                Timber.e("  GPIO $idx (gpio${gpioMap[idx]}): dir=${File("/sys/class/gpio/gpio${gpioMap[idx]}").exists()}, exists=${file.exists()}, canWrite=${file.canWrite()}, readback=${try { file.readText().trim() } catch (_: Exception) { "?" }}")
            }
        }
    }

    private fun setupGpio(): Boolean {
        exportAll()
        chmodAllValues()
        val allExist = gpioFiles.values.all { it.exists() }
        val writable = allExist && verifyWriteAccess()
        _available = writable
        return _available
    }

    suspend fun recheckAvailability(): Boolean {
        if (_available) return true
        Timber.i("Rechecking GPIO availability at runtime...")
        checkSelinux()

        if (setupGpio()) {
            Timber.i("GPIO re-check: now available!")
            turnAllOff()
            return true
        }

        Timber.i("GPIO re-check: still unavailable. Trying FISE rebind with delay...")
        if (rebindFiseDriver()) {
            kotlinx.coroutines.delay(200)
            if (setupGpio()) {
                Timber.i("GPIO re-check: NOW available after FISE rebind!")
                turnAllOff()
                return true
            }
        }

        Timber.i("GPIO re-check: FISE rebind failed. Trying direct export for each pin...")
        var anyExported = false
        for ((_, gpioNum) in gpioMap) {
            val dir = File("/sys/class/gpio/gpio$gpioNum")
            if (dir.exists()) {
                anyExported = true
                continue
            }
            if (directWrite("/sys/class/gpio/export", gpioNum.toString())) {
                kotlinx.coroutines.delay(50)
                directWrite("/sys/class/gpio/gpio$gpioNum/direction", "out")
                directWrite("/sys/class/gpio/gpio$gpioNum/value", "1")
                anyExported = true
            } else {
                Timber.e("GPIO $gpioNum: all export methods failed")
            }
        }

        val allExist = gpioFiles.values.all { it.exists() }
        val writable = allExist && verifyWriteAccess()
        _available = writable
        if (_available) {
            _statusMessage = "أضواء التشخيص جاهزة ✓"
            Timber.i("GPIO re-check: finally available after direct export!")
            turnAllOff()
        } else {
            _statusMessage = "⚠️ أضواء التشخيص غير متصلة. قم بتشغيل: .\\setup_gpio.ps1 في PowerShell"
            Timber.w("GPIO re-check: still unavailable after all attempts. SELinux=$selinuxEnforcing")
            gpioFiles.forEach { (idx, file) ->
                Timber.w("  GPIO $idx (${gpioMap[idx]}): exists=${file.exists()}, canWrite=${file.canWrite()}, readback=${try { file.readText().trim() } catch (_: Exception) { "?" }}")
            }
        }
        return _available
    }

    private fun directWrite(path: String, value: String): Boolean {
        if (suExec("echo $value > $path")) return true
        if (shellExec("echo $value > $path")) return true
        return try {
            File(path).writeText(value)
            true
        } catch (e: Exception) {
            Timber.e(e, "Direct write to $path failed")
            false
        }
    }

    private fun exportAll() {
        for ((index, gpioNum) in gpioMap) {
            val dir = File("/sys/class/gpio/gpio$gpioNum")
            if (!dir.exists()) {
                val exported = suExec("echo $gpioNum > /sys/class/gpio/export")
                if (exported) {
                    Timber.d("Exported GPIO $gpioNum via su")
                } else {
                    val shellOk = shellExec("echo $gpioNum > /sys/class/gpio/export")
                    if (shellOk) {
                        Timber.d("Exported GPIO $gpioNum via shell")
                    } else {
                        Timber.d("Cannot export GPIO $gpioNum (index $index). Assume pre-exported.")
                    }
                }
            }
            val dirOk = suExec("echo out > /sys/class/gpio/gpio$gpioNum/direction")
            if (!dirOk) {
                shellExec("echo out > /sys/class/gpio/gpio$gpioNum/direction")
            }
        }
    }

    private fun chmodAllValues() {
        for ((_, gpioNum) in gpioMap) {
            suExec("chmod 666 /sys/class/gpio/gpio$gpioNum/value") ||
            shellExec("chmod 666 /sys/class/gpio/gpio$gpioNum/value")
        }
    }

    private fun rebindFiseDriver(): Boolean {
        try {
            Timber.i("Attempting FISE driver unbind/rebind...")
            val unbindOk = suExec("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind") ||
                           shellExec("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind")
            if (!unbindOk) {
                Timber.d("FISE unbind failed or driver not bound — may already be unbound")
            }
            val bindOk = suExec("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind") ||
                         shellExec("echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind")
            if (!bindOk) {
                Timber.e("FISE bind failed — driver may not exist or already bound")
                return false
            }
            Timber.i("FISE driver rebound successfully")
            return true
        } catch (e: Exception) {
            Timber.e(e, "FISE driver rebind exception")
            return false
        }
    }

    fun installBootScript(): Boolean {
        try {
            val scriptName = "99gpio_setup.sh"
            val scriptPath = "/data/local/tmp/$scriptName"

            val destFile = File(scriptPath)
            if (destFile.exists() && destFile.canExecute()) {
                Timber.i("GPIO boot script already installed at $scriptPath")
                return true
            }

            val inputStream = context.assets.open("scripts/$scriptName")
            val bytes = inputStream.readBytes()
            inputStream.close()

            destFile.writeBytes(bytes)
            destFile.setExecutable(true)

            Timber.i("GPIO boot script copied to $scriptPath (${bytes.size} bytes)")

            val chmodOk = suExec("chmod 755 $scriptPath") || shellExec("chmod 755 $scriptPath")
            Timber.i("GPIO boot script chmod result: $chmodOk")

            val runOk = suExec("sh $scriptPath") || shellExec("sh $scriptPath")
            if (runOk) {
                Timber.i("GPIO boot script executed successfully via su")
                return true
            } else {
                Timber.w("GPIO boot script execution failed via su, trying shell")
                val shellRunOk = shellExec("sh $scriptPath")
                if (shellRunOk) {
                    Timber.i("GPIO boot script executed via shell")
                    return true
                }
                Timber.w("GPIO boot script execution failed via both methods")
                return false
            }
        } catch (e: Exception) {
            Timber.e(e, "Failed to install GPIO boot script")
            return false
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

    private fun writeSysfs(file: File, value: String): Boolean {
        val path = file.absolutePath
        if (suExec("echo $value > $path")) return true
        if (shellExec("echo $value > $path")) return true
        return try {
            file.writeText(value)
            true
        } catch (e: Exception) {
            Timber.e(e, "Direct write to $path = $value also failed")
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

    private fun suExec(cmd: String): Boolean {
        return try {
            val proc = Runtime.getRuntime().exec(arrayOf("su", "-c", cmd))
            val exit = proc.waitFor()
            if (exit == 0) true else {
                Timber.d("suExec: cmd='$cmd' exit=$exit")
                false
            }
        } catch (e: Exception) {
            Timber.d("suExec failed: ${e.message}")
            false
        }
    }

    private fun ensurePinReady(index: Int): Boolean {
        val gpioNum = gpioMap[index] ?: return false
        val dir = File("/sys/class/gpio/gpio$gpioNum")
        if (dir.exists()) return dir.isDirectory

        Timber.d("GPIO $index (gpio-$gpioNum): not exported, trying to export...")
        if (directWrite("/sys/class/gpio/export", gpioNum.toString())) {
            try { Thread.sleep(50) } catch (_: InterruptedException) {}
            directWrite("/sys/class/gpio/gpio$gpioNum/direction", "out")
            directWrite("/sys/class/gpio/gpio$gpioNum/value", "1")
            suExec("chmod 666 /sys/class/gpio/gpio$gpioNum/value") ||
            shellExec("chmod 666 /sys/class/gpio/gpio$gpioNum/value")
            return dir.exists()
        }
        return false
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        val gpioNum = gpioMap[index] ?: return false
        val file = gpioFiles[index] ?: return false
        if (!file.exists()) {
            val ready = ensurePinReady(index)
            if (!ready) {
                Timber.w("GPIO $index (gpio-$gpioNum): cannot export, write will likely fail")
            }
        }
        val value = if (on) "0" else "1"
        var ok = writeSysfs(file, value)
        var rb = try { file.readText().trim() } catch (e: Exception) { "?" }
        if (ok && rb != value) {
            Timber.w("GPIO $index (gpio-$gpioNum): write ok but readback=$rb != expected=$value — retrying with direct write")
            try { Thread.sleep(20) } catch (_: InterruptedException) {}
            try { file.writeText(value); ok = true } catch (e: Exception) { ok = false }
            rb = try { file.readText().trim() } catch (e: Exception) { "?" }
        }
        val verified = ok && rb == value
        Timber.i("GPIO $index (gpio-$gpioNum) -> ${if (on) "ON" else "OFF"} (ok=$ok, readback=$rb, verified=$verified)")
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
