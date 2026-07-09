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

    private val fiseDriverLink = File("/sys/bus/platform/drivers/fise_gpio/fise_gpio")

    val isAvailable: Boolean get() = _available

    private var _statusMessage = ""
    val statusMessage: String get() = _statusMessage

    fun isFiseDriverBound(): Boolean = try {
        fiseDriverLink.exists() && fiseDriverLink.isDirectory
    } catch (_: Exception) { false }

    val hasRoot: Boolean = false
    val rootManagerDetected: Boolean = false
    val detectedRootManagerPackage: String? = null

    init {
        checkSelinux()
        val driverBound = isFiseDriverBound()
        val fiseFilesExist = gpioFiles.all { it.exists() } && ledFile.exists()
        val fiseWriteOk = if (fiseFilesExist) verifyWriteAccess() else false
        val fiseActuallyWorks = if (fiseWriteOk && driverBound) testFiseWriteEffect() else false

        if (fiseActuallyWorks) {
            _available = true
            _useRawGpio = false
            _statusMessage = "أضواء التشخيص جاهزة ✓ (FISE driver)"
            Timber.i("FISE GPIO controller VERIFIED: files exist, writable, driver bound, AND writes take effect")
            turnAllOff()
        } else {
            if (fiseFilesExist && !driverBound) {
                Timber.w("FISE files exist but driver is UNBOUND — raw GPIO should work")
            } else if (fiseFilesExist) {
                Timber.w("FISE files exist but writes do NOT control LEDs — trying raw GPIO fallback")
            }
            val rawOk = tryRawGpioSetup()
            if (rawOk) {
                _available = true
                _useRawGpio = true
                _statusMessage = "أضواء التشخيص جاهزة ✓ (raw GPIO fallback)"
                Timber.i("Raw GPIO fallback available: pins=${rawGpioPins.joinToString()}")
            } else {
                _available = false
                _useRawGpio = false
                _statusMessage = "⚠️ أضواء التشخيص غير متصلة"
                Timber.w("GPIO not available (FISE broken=$fiseFilesExist, driverBound=$driverBound, raw failed)")
                gpioFiles.forEach {
                    Timber.w("  ${it.absolutePath}: exists=${it.exists()}")
                }
                rawGpioFiles.forEach { f ->
                    Timber.w("  ${f.absolutePath}: exists=${f.exists()}")
                }
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
                val exportPins = rawGpioPins.joinToString("; ") { "echo $it > /sys/class/gpio/export" }
                val setDir = rawGpioPins.joinToString("; ") { "echo out > /sys/class/gpio/gpio$it/direction" }
                val setOff = rawGpioPins.joinToString("; ") { "echo 1 > /sys/class/gpio/gpio$it/value" }
                val chmodRaw = rawGpioPins.joinToString("; ") { "chmod 666 /sys/class/gpio/gpio$it/value" }
                val chmodDir = rawGpioPins.joinToString("; ") { "chmod 666 /sys/class/gpio/gpio$it/direction" }

                val fullScript = """
                    $unbindCmd
                    sleep 1
                    $exportPins
                    sleep 1
                    $setDir
                    sleep 1
                    $setOff
                    $chmodRaw
                    $chmodDir
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
                val driverBound = isFiseDriverBound()
                if (driverBound) {
                    Timber.w("FISE driver still bound after unbind attempt — raw GPIO may not work")
                }
                val rawNow = rawGpioFiles.all { it.exists() }
                if (rawNow) {
                    val verifyOk = tryRawGpioSetup()
                    if (verifyOk) {
                        _available = true
                        _useRawGpio = true
                        _statusMessage = "أضواء التشخيص جاهزة ✓ (raw GPIO)"
                        Timber.i("GPIO setup via shell: SUCCESS! Raw GPIO available, driverBound=$driverBound")
                        turnAllOff()
                        return@withContext true
                    }
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

        val exportCmd = rawGpioPins.joinToString("; ") { "echo $it > /sys/class/gpio/export 2>/dev/null" }
        val setDirCmd = rawGpioPins.joinToString("; ") { "echo out > /sys/class/gpio/gpio$it/direction" }
        val setOffCmd = rawGpioPins.joinToString("; ") { "echo 1 > /sys/class/gpio/gpio$it/value" }
        val chmodCmd = rawGpioPins.joinToString("; ") { "chmod 666 /sys/class/gpio/gpio$it/value /sys/class/gpio/gpio$it/direction" }

        try {
            val process = Runtime.getRuntime().exec(arrayOf("sh", "-c", "$exportCmd; sleep 1; $setDirCmd; sleep 1; $setOffCmd; $chmodCmd"))
            val exit = process.waitFor()
            val stderr = process.errorStream.bufferedReader().readText()
            Timber.i("Raw GPIO shell setup exit=$exit stderr=$stderr")
        } catch (e: Exception) {
            Timber.w("Raw GPIO shell setup failed: ${e.message}")
            return false
        }

        Thread.sleep(200)

        for ((i, file) in rawGpioFiles.withIndex()) {
            try {
                if (!file.exists()) {
                    allOk = false
                    continue
                }
                val onOk = shellWrite(file.absolutePath, "0")
                Thread.sleep(50)
                val readOn = shellRead(file.absolutePath)
                val offOk = shellWrite(file.absolutePath, "1")
                Thread.sleep(50)
                val readOff = shellRead(file.absolutePath)
                Timber.d("Raw GPIO write-verify pin ${rawGpioPins[i]}: ON→$readOn (ok=$onOk), OFF→$readOff (ok=$offOk)")
                if (readOn != "0" || readOff != "1") {
                    Timber.w("Raw GPIO pin ${rawGpioPins[i]} write-verify FAILED (on=$readOn, off=$readOff)")
                    allOk = false
                }
            } catch (e: Exception) {
                Timber.w("Raw GPIO write test FAILED for ${file.absolutePath}: ${e.message}")
                allOk = false
            }
        }
        return allOk && rawGpioFiles.all { it.exists() }
    }

    private fun testFiseWriteEffect(): Boolean {
        val testFile = gpioFiles[0]
        val rawTestFile = rawGpioFiles[0]
        if (!testFile.exists()) return false
        val fiseOk = try {
            testFile.writeText("0")
            Thread.sleep(100)
            val r1 = testFile.readText().trim()
            testFile.writeText("1")
            Thread.sleep(100)
            val r2 = testFile.readText().trim()
            Timber.i("FISE self-test: 0→$r1, 1→$r2")
            r1 == "0" && r2 == "1"
        } catch (e: Exception) {
            Timber.w("FISE self-test FAILED: ${e.message}")
            false
        }
        val rawOk = if (rawTestFile.exists()) {
            val w1 = shellWrite(rawTestFile.absolutePath, "0")
            Thread.sleep(50)
            val r1 = shellRead(rawTestFile.absolutePath)
            val w2 = shellWrite(rawTestFile.absolutePath, "1")
            Thread.sleep(50)
            val r2 = shellRead(rawTestFile.absolutePath)
            Timber.i("Raw GPIO shell test: write0=$w1 read=$r1, write1=$w2 read=$r2")
            r1 == "0" && r2 == "1"
        } else false

        Timber.i("testFiseWriteEffect: fise=$fiseOk raw=$rawOk driverBound=${isFiseDriverBound()}")
        if (fiseOk && !rawOk) {
            Timber.w("FISE stores values but raw GPIO NOT controlled — driver is orphaned/unbound")
        }
        return fiseOk && rawOk
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
        val driverBound = isFiseDriverBound()
        val gpioOk = gpioFiles.all { it.exists() } && verifyWriteAccess()
        val ledOk = ledFile.exists()
        if (gpioOk && driverBound && ledOk) {
            val fiseWorks = testFiseWriteEffect()
            if (fiseWorks) {
                _available = true
                _useRawGpio = false
                _statusMessage = "أضواء التشخيص جاهزة ✓ (FISE driver)"
                Timber.i("FISE GPIO re-check: NOW available! gpio_ok=$gpioOk, led_ok=$ledOk")
                turnAllOff()
                return true
            }
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
        Timber.w("GPIO re-check: still unavailable. SELinux=$selinuxEnforcing, driverBound=$driverBound")
        gpioFiles.forEach {
            Timber.w("  ${it.absolutePath}: exists=${it.exists()}, canWrite=${try { it.canWrite() } catch (_: Exception) { false }}")
        }
        rawGpioFiles.forEachIndexed { i, f ->
            Timber.w("  ${f.absolutePath}: exists=${f.exists()}, canWrite=${try { f.canWrite() } catch (_: Exception) { false }}")
        }
        return false
    }

    private fun shellWrite(path: String, value: String): Boolean {
        return try {
            val process = Runtime.getRuntime().exec(arrayOf("sh", "-c", "echo $value > $path"))
            val exit = process.waitFor()
            exit == 0
        } catch (e: Exception) {
            false
        }
    }

    private fun shellRead(path: String): String {
        return try {
            val process = Runtime.getRuntime().exec(arrayOf("sh", "-c", "cat $path"))
            val result = process.inputStream.bufferedReader().readText().trim()
            process.waitFor()
            result
        } catch (_: Exception) { "?" }
    }

    fun setGpio(index: Int, on: Boolean): Boolean {
        if (index < 0 || index >= gpioFiles.size) return false
        val value = if (on) "0" else "1"  // Active LOW: 0=ON, 1=OFF

        if (index < rawGpioFiles.size && rawGpioFiles[index].exists()) {
            val path = rawGpioFiles[index].absolutePath
            try {
                val ok = shellWrite(path, value)
                if (ok) {
                    val readback = shellRead(path)
                    if (readback == value) {
                        Timber.i("Raw GPIO pin ${rawGpioPins[index]} -> ${if (on) "ON" else "OFF"} (wrote=$value, readback=$readback) OK")
                        return true
                    }
                    Timber.w("Raw GPIO pin ${rawGpioPins[index]} wrote=$value but readback=$readback, trying FISE")
                } else {
                    Timber.w("Raw GPIO pin ${rawGpioPins[index]} shell write failed, trying FISE")
                }
            } catch (e: Exception) {
                Timber.w("Raw GPIO pin ${rawGpioPins[index]} write failed: ${e.message}, trying FISE")
            }
        }

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

    fun setMasterLed(on: Boolean): Boolean {
        val value = if (on) "0" else "1"
        val rawLedFile = File("/sys/class/fise_led/level")
        if (rawLedFile.exists()) {
            return try {
                rawLedFile.writeText(value)
                val readback = try { rawLedFile.readText().trim() } catch (_: Exception) { "?" }
                Timber.i("LED master -> ${if (on) "ON" else "OFF"} (wrote=$value, readback=$readback)")
                true
            } catch (e: Exception) {
                Timber.e(e, "Failed to write LED master")
                false
            }
        }
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

    /**
     * Read the current value of a GPIO pin for verification.
     * Returns "0" if ON, "1" if OFF, or null if unreadable.
     */
    fun readGpioValue(index: Int): String? {
        if (index < 0 || index >= rawGpioFiles.size) return null
        val file = rawGpioFiles[index]
        if (!file.exists()) return null
        return try {
            val value = file.readText().trim()
            Timber.d("GPIO $index readback: $value")
            value
        } catch (e: Exception) {
            // Try FISE file
            if (index < gpioFiles.size && gpioFiles[index].exists()) {
                try {
                    gpioFiles[index].readText().trim()
                } catch (_: Exception) { null }
            } else {
                null
            }
        }
    }

    fun turnAllOff() {
        for (i in gpioFiles.indices) {
            setGpio(i, false)
        }
        if (isFiseDriverBound()) {
            setMasterLed(false)
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
        if (!gpioOk) return false
        if (isFiseDriverBound()) {
            val ledOk = setMasterLed(true)
            return ledOk
        }
        Timber.d("FISE driver unbound — skipping setMasterLed, raw GPIO direct control")
        return true
    }

    fun activateAll(): Boolean {
        var allOk = true
        for (i in gpioFiles.indices) {
            if (!setGpio(i, true)) allOk = false
        }
        if (!allOk) return false
        if (isFiseDriverBound()) {
            return setMasterLed(true)
        }
        return true
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
