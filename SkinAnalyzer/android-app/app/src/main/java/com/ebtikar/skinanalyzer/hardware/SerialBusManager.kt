package com.ebtikar.skinanalyzer.hardware

import android.content.Context
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import com.hoho.android.usbserial.driver.UsbSerialDriver
import com.hoho.android.usbserial.driver.UsbSerialPort
import com.hoho.android.usbserial.driver.UsbSerialProber
import com.hoho.android.usbserial.util.SerialInputOutputManager
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withTimeout
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SerialBusManager @Inject constructor(
    private val context: Context
) : SerialInputOutputManager.Listener {

    companion object {
        private const val BAUD_RATE = 115200
        private const val DATA_BITS = UsbSerialPort.DATABITS_8
        private const val STOP_BITS = UsbSerialPort.STOPBITS_1
        private const val PARITY = UsbSerialPort.PARITY_NONE
        private const val HEADER_BYTE: Byte = 0xAA.toByte()
        private const val FOOTER_BYTE: Byte = 0x55.toByte()
        private const val ACK_BYTE: Byte = 0x06.toByte()
        private const val NACK_BYTE: Byte = 0x15.toByte()
        private const val COMMAND_TIMEOUT_MS = 2000L
        private const val MAX_RETRIES = 3
    }

    @Volatile private var serialPort: UsbSerialPort? = null
    @Volatile private var ioManager: SerialInputOutputManager? = null
    @Volatile private var onDataReceived: ((ByteArray) -> Unit)? = null
    private val commandMutex = Mutex()
    private val lastResponse = MutableStateFlow<ByteArray?>(null)

    private val _connectionState = MutableStateFlow(ConnectionState.DISCONNECTED)
    val connectionState: StateFlow<ConnectionState> = _connectionState.asStateFlow()

    private val _lastError = MutableStateFlow<String?>(null)
    val lastError: StateFlow<String?> = _lastError.asStateFlow()

    private val _commandStats = MutableStateFlow(CommandStats())
    val commandStats: StateFlow<CommandStats> = _commandStats.asStateFlow()

    enum class ConnectionState {
        DISCONNECTED, CONNECTING, CONNECTED, ERROR
    }

    data class CommandStats(
        val totalSent: Int = 0,
        val totalAcks: Int = 0,
        val totalNacks: Int = 0,
        val totalTimeouts: Int = 0,
        val avgResponseTimeMs: Long = 0L,
        val lastCommandTimeMs: Long = 0L
    )

    val isConnected: Boolean get() = serialPort?.isOpen == true

    suspend fun autoConnect(): Result<Unit> {
        if (isConnected) return Result.success(Unit)
        Timber.i("Auto-connecting serial bus...")
        val driver = findDriver() ?: run {
            Timber.w("No USB serial device found for auto-connect")
            return Result.failure(IllegalStateException("No USB serial device found"))
        }
        return connect(driver).also { result ->
            if (result.isSuccess) {
                Timber.i("Serial bus auto-connected successfully to ${driver.device.deviceName}")
            } else {
                Timber.e("Serial bus auto-connect failed: ${result.exceptionOrNull()?.message}")
            }
        }
    }

    fun findDriver(): UsbSerialDriver? {
        val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager

        val drivers = UsbSerialProber.getDefaultProber().findAllDrivers(usbManager)
        if (drivers.isNotEmpty()) {
            val driver = drivers.first()
            Timber.i("Found USB serial driver (default prober): ${driver.device.deviceName} (${driver.device.vendorId}/${driver.device.productId})")
            return driver
        }

        Timber.w("Default prober found nothing. Scanning ALL USB devices...")
        val allDevices = usbManager.deviceList
        Timber.i("Total USB devices: ${allDevices.size}")
        for ((name, device) in allDevices) {
            Timber.i("USB device: $name, VID=${device.vendorId}, PID=${device.productId}, class=${device.deviceClass}, name=${device.deviceName}")
            val result = tryAllDriverTypes(usbManager, device)
            if (result != null) return result
        }

        Timber.e("No USB serial device found after scanning ${allDevices.size} devices")
        return null
    }

    private fun tryAllDriverTypes(usbManager: android.hardware.usb.UsbManager, device: UsbDevice): UsbSerialDriver? {
        val driverClasses = listOf(
            com.hoho.android.usbserial.driver.CdcAcmSerialDriver::class.java,
            com.hoho.android.usbserial.driver.Ch34xSerialDriver::class.java,
            com.hoho.android.usbserial.driver.Cp21xxSerialDriver::class.java,
            com.hoho.android.usbserial.driver.FtdiSerialDriver::class.java,
            com.hoho.android.usbserial.driver.ProlificSerialDriver::class.java
        )
        for (clazz in driverClasses) {
            try {
                val constructor = clazz.getConstructor(UsbDevice::class.java)
                val driver = constructor.newInstance(device) as UsbSerialDriver
                if (driver.ports.isNotEmpty()) {
                    Timber.i("Successfully created driver ${clazz.simpleName} for ${device.deviceName}")
                    return driver
                }
            } catch (e: Exception) {
                Timber.d("Driver ${clazz.simpleName} failed for ${device.deviceName}: ${e.message}")
            }
        }
        return null
    }

    fun listAllUsbDevices(): List<String> {
        val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager
        val result = mutableListOf<String>()
        for ((name, device) in usbManager.deviceList) {
            val hasPermission = usbManager.hasPermission(device)
            result.add("$name: VID=${device.vendorId} PID=${device.productId} class=${device.deviceClass} perm=$hasPermission name=${device.productName ?: device.deviceName}")
        }
        return result
    }

    fun connect(driver: UsbSerialDriver): Result<Unit> {
        return try {
            _connectionState.value = ConnectionState.CONNECTING
            val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager
            if (!usbManager.hasPermission(driver.device)) {
                _connectionState.value = ConnectionState.ERROR
                return Result.failure(IllegalStateException("USB permission not granted"))
            }
            val connection = usbManager.openDevice(driver.device)
                ?: run {
                    _connectionState.value = ConnectionState.ERROR
                    return Result.failure(IllegalStateException("Cannot open USB device"))
                }

            serialPort = driver.ports.first().also { port ->
                port.open(connection)
                port.setParameters(BAUD_RATE, DATA_BITS, STOP_BITS, PARITY)
            }

            ioManager = SerialInputOutputManager(serialPort!!, this).also { it.start() }
            _connectionState.value = ConnectionState.CONNECTED
            _lastError.value = null
            Timber.i("Serial bus connected at $BAUD_RATE baud")
            Result.success(Unit)
        } catch (e: Exception) {
            _connectionState.value = ConnectionState.ERROR
            _lastError.value = e.message
            Timber.e(e, "Failed to connect serial bus")
            Result.failure(e)
        }
    }

    suspend fun sendCommand(spectrum: LightSpectrum): Result<Unit> {
        if (spectrum == LightSpectrum.ALL) {
            return sendAllLightsCommand()
        }
        return commandMutex.withLock {
            sendCommandWithRetry(spectrum)
        }
    }

    suspend fun sendAllLightsCommand(): Result<Unit> {
        return commandMutex.withLock {
            try {
                val port = serialPort ?: return@withLock Result.failure(IllegalStateException("Serial port not open"))

                // Drain any stale response before starting
                lastResponse.value = null
                delay(50)
                lastResponse.value = null

                // Send individual light commands sequentially (more reliable than multi-byte packet)
                for (spectrum in LightSpectrum.CAPTURE_SEQUENCE) {
                    val payload = buildCommandPayload(spectrum.commandByte)
                    port.write(payload, 1000)
                    Timber.d("TX: ALL -> ${spectrum.name} ${payload.joinToString(" ") { "0x%02X".format(it) }}")
                    delay(50)
                }

                // Wait for all pending ACKs to arrive, then drain them
                delay(100)
                lastResponse.value = null

                Timber.i("ALL lights turned on via individual commands")
                Result.success(Unit)
            } catch (e: Exception) {
                _lastError.value = e.message
                Timber.e(e, "Failed to send ALL lights command")
                Result.failure(e)
            }
        }
    }

    private suspend fun sendCommandWithRetry(spectrum: LightSpectrum): Result<Unit> {
        var lastException: Exception? = null

        for (attempt in 1..MAX_RETRIES) {
            try {
                val port = serialPort
                    ?: run {
                        _lastError.value = "Serial port not open"
                        return Result.failure(IllegalStateException("Serial port not open"))
                    }

                val payload = buildCommandPayload(spectrum.commandByte)
                val startTime = System.currentTimeMillis()

                port.write(payload, 1000)
                Timber.d("TX [Attempt $attempt]: ${spectrum.name} -> ${payload.joinToString(" ") { "0x%02X".format(it) }}")

                delay(50)

                val elapsed = System.currentTimeMillis() - startTime
                val stats = _commandStats.value
                _commandStats.value = stats.copy(
                    totalSent = stats.totalSent + 1,
                    lastCommandTimeMs = elapsed
                )

                return Result.success(Unit)
            } catch (e: Exception) {
                lastException = e
                _lastError.value = e.message
                Timber.e(e, "Command failed for ${spectrum.name} (attempt $attempt/$MAX_RETRIES)")
                if (attempt < MAX_RETRIES) {
                    delay(100L * attempt)
                }
            }
        }

        return Result.failure(lastException ?: IllegalStateException("Command failed after $MAX_RETRIES retries"))
    }

    private suspend fun waitForAck(startTime: Long): Boolean {
        return try {
            withTimeout(COMMAND_TIMEOUT_MS) {
                while (System.currentTimeMillis() - startTime < COMMAND_TIMEOUT_MS) {
                    val response = lastResponse.value
                    if (response != null && response.isNotEmpty()) {
                        lastResponse.value = null
                        return@withTimeout response.first() == ACK_BYTE
                    }
                    delay(10)
                }
                false
            }
        } catch (e: Exception) {
            false
        }
    }

    suspend fun sendRawCommand(commandBytes: ByteArray): Result<ByteArray> {
        return commandMutex.withLock {
            try {
                val port = serialPort
                    ?: return@withLock Result.failure(IllegalStateException("Serial port not open"))

                port.write(commandBytes, 1000)
                delay(100)
                val response = lastResponse.value ?: byteArrayOf()
                lastResponse.value = null
                Result.success(response)
            } catch (e: Exception) {
                _lastError.value = e.message
                Result.failure(e)
            }
        }
    }

    suspend fun ping(): Boolean {
        return try {
            val port = serialPort ?: return false
            val pingBytes = byteArrayOf(HEADER_BYTE, 0xFF.toByte(), FOOTER_BYTE)
            port.write(pingBytes, 500)
            delay(200)
            val response = lastResponse.value
            lastResponse.value = null
            response != null && response.isNotEmpty()
        } catch (e: Exception) {
            false
        }
    }

    fun setOnDataReceived(listener: (ByteArray) -> Unit) {
        onDataReceived = listener
    }

    fun disconnect() {
        ioManager?.stop()
        ioManager = null
        try {
            serialPort?.close()
        } catch (e: Exception) {
            Timber.w(e, "Error closing serial port")
        }
        serialPort = null
        _connectionState.value = ConnectionState.DISCONNECTED
        _commandStats.value = CommandStats()
        Timber.i("Serial bus disconnected")
    }

    private fun buildCommandPayload(commandByte: Byte): ByteArray {
        return byteArrayOf(HEADER_BYTE, 0x01, commandByte, FOOTER_BYTE)
    }

    override fun onNewData(data: ByteArray) {
        Timber.v("Serial RX: ${data.joinToString(" ") { "0x%02X".format(it) }}")
        lastResponse.value = data
        onDataReceived?.invoke(data)
    }

    override fun onRunError(e: Exception) {
        Timber.e(e, "Serial I/O error")
        _connectionState.value = ConnectionState.ERROR
        _lastError.value = e.message
    }
}
