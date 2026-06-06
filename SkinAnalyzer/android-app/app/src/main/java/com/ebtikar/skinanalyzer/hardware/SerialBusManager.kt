package com.ebtikar.skinanalyzer.hardware

import android.content.Context
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import com.hoho.android.usbserial.driver.UsbSerialDriver
import com.hoho.android.usbserial.driver.UsbSerialPort
import com.hoho.android.usbserial.driver.UsbSerialProber
import com.hoho.android.usbserial.util.SerialInputOutputManager
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
    }

    private var serialPort: UsbSerialPort? = null
    private var ioManager: SerialInputOutputManager? = null
    private var onDataReceived: ((ByteArray) -> Unit)? = null

    val isConnected: Boolean get() = serialPort?.isOpen == true

    fun findDriver(): UsbSerialDriver? {
        val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager
        val drivers = UsbSerialProber.getDefaultProber().findAllDrivers(usbManager)
        return drivers.firstOrNull().also { driver ->
            if (driver != null) {
                Timber.i("Found USB serial driver: ${driver.device.deviceName}")
            } else {
                Timber.w("No USB serial driver found")
            }
        }
    }

    fun connect(driver: UsbSerialDriver): Result<Unit> {
        return try {
            val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager
            val connection = usbManager.openDevice(driver.device)
                ?: return Result.failure(IllegalStateException("Cannot open USB device"))

            serialPort = driver.ports.first().also { port ->
                port.open(connection)
                port.setParameters(BAUD_RATE, DATA_BITS, STOP_BITS, PARITY)
            }

            ioManager = SerialInputOutputManager(serialPort!!, this).also { it.start() }
            Timber.i("Serial bus connected at $BAUD_RATE baud")
            Result.success(Unit)
        } catch (e: Exception) {
            Timber.e(e, "Failed to connect serial bus")
            Result.failure(e)
        }
    }

    fun sendCommand(spectrum: LightSpectrum): Result<Unit> {
        val port = serialPort ?: return Result.failure(IllegalStateException("Serial port not open"))
        return try {
            val payload = buildCommandPayload(spectrum.commandByte)
            port.write(payload, 1000)
            Timber.d("Sent command: ${spectrum.name} -> ${payload.joinToString(" ") { "0x%02X".format(it) }}")
            Result.success(Unit)
        } catch (e: Exception) {
            Timber.e(e, "Failed to send command for ${spectrum.name}")
            Result.failure(e)
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
        Timber.i("Serial bus disconnected")
    }

    private fun buildCommandPayload(commandByte: Byte): ByteArray {
        return byteArrayOf(HEADER_BYTE, 0x01, commandByte, FOOTER_BYTE)
    }

    override fun onNewData(data: ByteArray) {
        Timber.v("Serial RX: ${data.joinToString(" ") { "0x%02X".format(it) }}")
        onDataReceived?.invoke(data)
    }

    override fun onRunError(e: Exception) {
        Timber.e(e, "Serial I/O error")
    }
}
