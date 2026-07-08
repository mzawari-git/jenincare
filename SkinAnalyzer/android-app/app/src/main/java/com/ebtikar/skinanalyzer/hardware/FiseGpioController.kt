package com.ebtikar.skinanalyzer.hardware

import android.content.Context
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.withContext
import timber.log.Timber
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FiseGpioController @Inject constructor(
    @ApplicationContext private val context: Context
) {

    private val gpioFiles = (0..4).map { File("/sys/class/fise_gpio$it/level") }
    private val ledFile = File("/sys/class/fise_led/level")
    private val rawGpioPins = intArrayOf(34, 149, 45, 54, 56)
    private val rawGpioFiles = rawGpioPins.map { File("/sys/class/gpio/gpio$it/value") }
    @Volatile private var _available = false
    @Volatile private var _useRawGpio = false
    @Volatile private var _setupComplete = false
    var selinuxEnforcing: Boolean? = null
        private set

    val isAvailable: Boolean get() = _available

    private var _statusMessage = ""
    val statusMessage: String get() = _statusMessage

    val hasRoot: Boolean = false
    val rootManagerDetected: Boolean = false
    val detectedRootManagerPackage: String? = null

    init {
        checkSelinux()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        if (gpioOk || ledOk) {
            _available = true
            _useRawGpio = false
            _statusMessage = "أضواء التشخيص جاهزة ✓ (FISE driver)"
            Timber.i("FISE GPIO controller available: ${gpioFiles.size} channels, fise_gpio_exists=${gpioFiles.map { it.exists() }}, fise_led=$ledOk, SELinux=$selinuxEnforcing")
            turnAllOff()
        } else {
            val rawOk = tryRawGpioSetup()
            if (rawOk) {
                _available = true
                _useRawGpio = true
                _statusMessage = "أضواء التشخيص جاهزة ✓ (raw GPIO fallback)"
                Timber.i("Raw GPIO fallback available: pins=${rawGpioPins.joinToString()}")
            } else {
                _available = false
                _useRawGpio = false
                _statusMessage = "⚠️ أضواء التشخيص غير متصلة — FISE driver لا يوجد"
                Timber.w("FISE GPIO controller not available (SELinux=$selinuxEnforcing)")
                gpioFiles.forEach {
                    Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${try { it.canWrite() } catch (_: Exception) { false }}")
                }
                rawGpioFiles.forEachIndexed { i, f ->
                    Timber.w("  ${f.absolutePath}: exists=${f.exists()}, canWrite=${try { f.canWrite() } catch (_: Exception) { false }}")
                }
                Timber.w("  ${ledFile.absolutePath}: exists=${ledFile.exists()}")
            }
        }
    }

    suspend fun setupGpioViaShell(): Boolean {
        if (_setupComplete) return _available
        if (_available) return true
        return withContext(Dispatchers.IO) {
            Timber.i("Attempting GPIO setup via shell commands...")
            try {
                val unbindCmd = "echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind"
                val bindCmd = "echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind"
                val exportPins = rawGpioPins.joinToString("; ") { "echo $it > /sys/class/gpio/export" }
                val setDir = rawGpioPins.joinToString("; ") { "echo out > /sys/class/gpio/gpio$it/direction" }
                val setOff = rawGpioPins.joinToString("; ") { "echo 1 > /sys/class/gpio/gpio$it/value" }
                val chmodFise = (0..4).joinToString("; ") { "chmod 666 /sys/class/fise_gpio$it/level" }
                val chmodRaw = rawGpioPins.joinToString("; ") { "chmod 666 /sys/class/gpio/gpio$it/value" }
                val chmodLed = "chmod 666 /sys/class/fise_led/level"

                val fullScript = """
                    $unbindCmd
                    sleep 1
                    $bindCmd
                    sleep 1
                    $exportPins
                    sleep 1
                    $setDir
                    $setOff
                    $chmodFise
                    $chmodRaw
                    $chmodLed
                """.trimIndent()

                Timber.d("Running GPIO setup script: $fullScript")
                val process = Runtime.getRuntime().exec(arrayOf("sh", "-c", fullScript))
                val exitCode = process.waitFor()
                val stderr = process.errorStream.bufferedReader().readText()
                val stdout = process.inputStream.bufferedReader().readText()

                if (exitCode != 0) {
                    Timber.w("GPIO setup script exit code: $exitCode, stderr: $stderr")
                } else {
                    Timber.i("GPIO setup script completed successfully")
                }
                if (stdout.isNotBlank()) Timber.d("GPIO setup stdout: $stdout")
                if (stderr.isNotBlank()) Timber.d("GPIO setup stderr: $stderr")

                delay(500)

                _setupComplete = true
                val gpioNow = gpioFiles.all { it.exists() } && verifyWriteAccess()
                val ledNow = ledFile.exists()
                if (gpioNow || ledNow) {
                    _available = true
                    _useRawGpio = false
                    _statusMessage = "أضواء التشخيص جاهزة ✓ (FISE driver)"
                    Timber.i("GPIO setup via shell: SUCCESS! FISE available")
                    turnAllOff()
                    return@withContext true
                }

                val rawNow = rawGpioFiles.all { it.exists() }
                if (rawNow) {
                    _available = true
                    _useRawGpio = true
                    _statusMessage = "أضواء التشخيص جاهزة ✓ (raw GPIO)"
                    Timber.i("GPIO setup via shell: SUCCESS! Raw GPIO available")
                    turnAllOff()
                    return@withContext true
                }

                Timber.w("GPIO setup via shell: files still not available after setup")
                _available = false
                _statusMessage = "⚠️ أضواء التشخيص غير متصلة — محاولة الإعداد فشلت"
                false
            } catch (e: Exception) {
                Timber.e(e, "GPIO setup via shell FAILED")
                _setupComplete = true
                _available = false
                _statusMessage = "⚠️ أضواء التشخيص غير متصلة — ${e.message}"
                false
            }
        }
    }

    private fun tryRawGpioSetup(): Boolean {
        Timber.i("Trying raw GPIO setup for pins: ${rawGpioPins.joinToString()}")
        var allOk = true
        for (pin in rawGpioPins) {
            try {
                val exportFile = File("/sys/class/gpio/export")
                if (exportFile.exists()) {
                    exportFile.writeText("$pin")
                    Thread.sleep(50)
                }
                val dirFile = File("/sys/class/gpio/gpio$pin/direction")
                if (dirFile.exists()) {
                    dirFile.writeText("out")
                    dirFile.writeText("1") // OFF (active LOW)
                }
            } catch (e: Exception) {
                Timber.w("Raw GPIO export failed for pin $pin: ${e.message}")
            }
        }
        Thread.sleep(100)
        for (file in rawGpioFiles) {
            try {
                if (!file.exists()) {
                    allOk = false
                    continue
                }
                file.writeText("1") // OFF
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                Timber.d("Raw GPIO write test ${file.absolutePath}: wrote=1(OFF), readback=$readback")
            } catch (e: Exception) {
                Timber.w("Raw GPIO write test FAILED for ${file.absolutePath}: ${e.message}")
                allOk = false
            }
        }
        return allOk && rawGpioFiles.all { it.exists() }
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
                file.writeText("1")  // Write OFF (active LOW: 1=OFF) to avoid turning LEDs on during init
                val rb = file.readText().trim()
                Timber.d("GPIO write test ${file.absolutePath}: wrote=1(OFF), readback=$rb")
            } catch (e: Exception) {
                Timber.e(e, "GPIO write test FAILED for ${file.absolutePath} (SELinux=$selinuxEnforcing)")
                return false
            }
        }
        Timber.i("All FISE GPIO write tests passed")
        return true
    }

    suspend fun recheckAvailability(): Boolean {
        if (_available) return true
        if (!_setupComplete) {
            val shellOk = setupGpioViaShell()
            if (shellOk) return true
        }
        Timber.i("Rechecking GPIO availability at runtime...")
        checkSelinux()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        if (gpioOk || ledOk) {
            _available = true
            _useRawGpio = false
            _statusMessage = "أضواء التشخيص جاهزة ✓ (FISE driver)"
            Timber.i("FISE GPIO re-check: NOW available! gpio_ok=$gpioOk, led_ok=$ledOk")
            turnAllOff()
            return true
        }
        val rawOk = tryRawGpioSetup()
        if (rawOk) {
            _available = true
            _useRawGpio = true
            _statusMessage = "أضواء التشخيص جاهزة ✓ (raw GPIO fallback)"
            Timber.i("Raw GPIO re-check: NOW available!")
            turnAllOff()
            return true
        }
        _available = false
        _useRawGpio = false
        _statusMessage = "⚠️ أضواء التشخيص غير متصلة — FISE driver لا يوجد"
        Timber.w("GPIO re-check: still unavailable. SELinux=$selinuxEnforcing")
        gpioFiles.forEach {
            Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${try { it.canWrite() } catch (_: Exception) { false }}")
        }
        rawGpioFiles.forEachIndexed { i, f ->
            Timber.w("  ${f.absolutePath}: exists=${f.exists()}, canWrite=${try { f.canWrite() } catch (_: Exception) { false }}")
        }
        return false
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val value = if (on) "0" else "1"  // Active LOW: 0=ON, 1=OFF
        if (_useRawGpio) {
            if (index >= rawGpioFiles.size) return false
            val file = rawGpioFiles[index]
            if (!file.exists()) {
                Timber.w("Raw GPIO pin ${rawGpioPins[index]} file does not exist: ${file.absolutePath}")
                return false
            }
            return try {
                file.writeText(value)
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                Timber.i("Raw GPIO pin ${rawGpioPins[index]} -> ${if (on) "ON" else "OFF"} (wrote=$value, readback=$readback)")
                true
            } catch (e: Exception) {
                Timber.e(e, "Failed to write raw GPIO pin ${rawGpioPins[index]}")
                false
            }
        } else {
            val file = gpioFiles[index]
            if (!file.exists()) {
                Timber.w("FISE GPIO $index file does not exist: ${file.absolutePath}")
                return false
            }
            return try {
                file.writeText(value)
                val readback = try { file.readText().trim() } catch (_: Exception) { "?" }
                Timber.i("FISE GPIO $index -> ${if (on) "ON" else "OFF"} (wrote=$value, readback=$readback)")
                true
            } catch (e: Exception) {
                Timber.e(e, "Failed to write FISE GPIO $index (SELinux=$selinuxEnforcing)")
                false
            }
        }
    }

    fun setMasterLed(on: Boolean): Boolean {
        val value = if (on) "0" else "1"
        if (_useRawGpio) {
            val rawLedFile = File("/sys/class/fise_led/level")
            return try {
                if (rawLedFile.exists()) {
                    rawLedFile.writeText(value)
                    Timber.i("Raw LED master -> ${if (on) "ON" else "OFF"} (wrote=$value)")
                    true
                } else {
                    Timber.w("Raw LED master file does not exist: ${rawLedFile.absolutePath}")
                    false
                }
            } catch (e: Exception) {
                Timber.e(e, "Failed to write raw LED master")
                false
            }
        } else {
            if (!ledFile.exists()) {
                Timber.w("FISE LED master file does not exist: ${ledFile.absolutePath}")
                return false
            }
            return try {
                ledFile.writeText(value)
                Timber.i("FISE LED master -> ${if (on) "ON" else "OFF"} (wrote=$value)")
                true
            } catch (e: Exception) {
                Timber.e(e, "Failed to write FISE LED master")
                false
            }
        }
    }

    fun turnAllOff() {
        setMasterLed(false)
        for (i in gpioFiles.indices) {
            setGpio(i, false)
        }
    }

    fun supportsSpectrum(spectrum: LightSpectrum): Boolean =
        spectrumToGpioIndex(spectrum) >= 0

    fun activateSpectrum(spectrum: LightSpectrum): Boolean {
        val gpioIndex = spectrumToGpioIndex(spectrum)
        if (gpioIndex < 0) {
            Timber.w("No GPIO channel for ${spectrum.name}")
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
            LightSpectrum.BLUE, LightSpectrum.RED, LightSpectrum.BROWN -> -1
            LightSpectrum.ALL -> -1
            LightSpectrum.OFF -> -2
            else -> -1
        }
    }
}
