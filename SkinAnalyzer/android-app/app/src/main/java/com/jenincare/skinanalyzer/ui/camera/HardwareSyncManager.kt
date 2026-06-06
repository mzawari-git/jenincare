package com.jenincare.skinanalyzer.ui.camera

import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothDevice
import android.bluetooth.BluetoothManager
import android.bluetooth.BluetoothSocket
import android.content.Context
import android.hardware.usb.UsbConstants
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbDeviceConnection
import android.hardware.usb.UsbEndpoint
import android.hardware.usb.UsbManager
import android.util.Log
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.io.File
import java.io.FileOutputStream
import java.io.IOException
import java.util.UUID

enum class SpectralMode {
    RGB,
    UV,
    CROSS_POLARIZED
}

sealed class HardwareConnection {
    data object Disconnected : HardwareConnection()
    data class Connected(val deviceName: String, val transport: String) : HardwareConnection()
}

class HardwareSyncManager(private val context: Context) {

    companion object {
        private const val TAG = "HardwareSync"
        private val SPP_UUID = UUID.fromString("00001101-0000-1000-8000-00805F9B34FB")
        private const val SYNC_TIMEOUT_MS = 5000L
        private val SERIAL_PORTS = arrayOf("/dev/ttyS4", "/dev/ttyS0", "/dev/ttyFIQ0")
        private const val SERIAL_BAUD = 9600

        // Binary protocol: AA 66 <cmd> <val> 23
        private const val PROTOCOL_HEADER_1: Byte = 0xAA.toByte()
        private const val PROTOCOL_HEADER_2: Byte = 0x66.toByte()
        private const val PROTOCOL_FOOTER: Byte = 0x23.toByte()

        // LED group command bytes (base 0x10 + group index)
        private const val CMD_GROUP_1: Byte = 0x10
        private const val CMD_GROUP_2: Byte = 0x11
        private const val CMD_GROUP_3: Byte = 0x12
        private const val CMD_GROUP_4: Byte = 0x13
        private const val CMD_GROUP_5: Byte = 0x14

        // Brightness values
        private const val BRIGHTNESS_OFF: Byte = 0x00
        private const val BRIGHTNESS_FULL: Byte = 0xFF.toByte()
        private const val BRIGHTNESS_40_PERCENT: Byte = 0x66

        // Mode to LED group mapping
        private val MODE_GROUPS = mapOf(
            SpectralMode.RGB to CMD_GROUP_1,
            SpectralMode.UV to CMD_GROUP_2,
            SpectralMode.CROSS_POLARIZED to CMD_GROUP_3
        )

        private val ALL_GROUPS = byteArrayOf(
            CMD_GROUP_1, CMD_GROUP_2, CMD_GROUP_3, CMD_GROUP_4, CMD_GROUP_5
        )
    }

    private var bluetoothSocket: BluetoothSocket? = null
    private var usbDevice: UsbDevice? = null
    private var usbConnection: UsbDeviceConnection? = null
    private var usbOutEndpoint: UsbEndpoint? = null
    private var activeSerialPort: String? = null
    private var serialOutputStream: FileOutputStream? = null

    suspend fun connectToDevice(): HardwareConnection = withContext(Dispatchers.IO) {
        if (connectSerialPort()) {
            Log.d(TAG, "Connected via Serial port: $activeSerialPort")
            return@withContext HardwareConnection.Connected(
                deviceName = "ZMLH02 ($activeSerialPort)",
                transport = "Serial"
            )
        }

        val usbManager = context.getSystemService(Context.USB_SERVICE) as UsbManager
        val deviceList = usbManager.deviceList
        if (deviceList.isNotEmpty()) {
            val device = deviceList.entries.first().value
            if (device.deviceClass == UsbConstants.USB_CLASS_COMM ||
                device.deviceClass == UsbConstants.USB_CLASS_VENDOR_SPEC
            ) {
                if (openUsbConnection(usbManager, device)) {
                    usbDevice = device
                    Log.d(TAG, "Connected via USB")
                    return@withContext HardwareConnection.Connected(
                        deviceName = device.productName ?: "USB Device",
                        transport = "USB"
                    )
                }
            }
        }

        val bluetoothManager = context.getSystemService(Context.BLUETOOTH_SERVICE) as BluetoothManager
        val bluetoothAdapter = bluetoothManager.adapter
        if (bluetoothAdapter != null && bluetoothAdapter.isEnabled) {
            val bondedDevices = bluetoothAdapter.bondedDevices
            val targetDevice = bondedDevices?.firstOrNull { device ->
                device.name?.contains("SkinAnalyzer", ignoreCase = true) == true ||
                        device.name?.contains("Dermal", ignoreCase = true) == true ||
                        device.name?.contains("LED", ignoreCase = true) == true
            }
            if (targetDevice != null) {
                try {
                    val socket = targetDevice.createRfcommSocketToServiceRecord(SPP_UUID)
                    bluetoothAdapter.cancelDiscovery()
                    socket.connect()
                    bluetoothSocket = socket
                    Log.d(TAG, "Connected via Bluetooth: ${targetDevice.name}")
                    return@withContext HardwareConnection.Connected(
                        deviceName = targetDevice.name ?: "BT Device",
                        transport = "Bluetooth"
                    )
                } catch (e: IOException) {
                    Log.w(TAG, "BT connect failed: ${e.message}")
                }
            }
        }

        HardwareConnection.Disconnected
    }

    private fun connectSerialPort(): Boolean {
        for (port in SERIAL_PORTS) {
            try {
                val file = File(port)
                if (!file.exists() || !file.canWrite()) continue
                configureSerialPort(port)
                serialOutputStream = FileOutputStream(file)
                activeSerialPort = port
                Log.d(TAG, "Serial port $port opened successfully")
                return true
            } catch (e: Exception) {
                Log.v(TAG, "Serial port $port failed: ${e.message}")
            }
        }
        return false
    }

    private fun configureSerialPort(port: String) {
        try {
            // First attempt: use busybox stty
            val process = Runtime.getRuntime().exec(arrayOf(
                "busybox", "stty", "-F", port,
                SERIAL_BAUD.toString(), "cs8", "-cstopb", "-parenb"
            ))
            val result = process.waitFor()
            if (result != 0) {
                // Second attempt: try standard stty
                Runtime.getRuntime().exec(arrayOf(
                    "stty", "-F", port,
                    SERIAL_BAUD.toString(), "cs8", "-cstopb", "-parenb"
                )).waitFor()
            }
            Log.d(TAG, "Serial port $port configured with baud $SERIAL_BAUD")
        } catch (e: Exception) {
            Log.w(TAG, "stty failed, serial communication may be unstable: ${e.message}")
        }
    }

    private fun openUsbConnection(usbManager: UsbManager, device: UsbDevice): Boolean {
        try {
            if (!usbManager.hasPermission(device)) {
                Log.w(TAG, "No permission for USB device: ${device.deviceName}")
                return false
            }
            val connection = usbManager.openDevice(device) ?: return false
            
            // Bitmoji devices often have multiple interfaces; we need to find the CDC/ACM or HID interface
            for (i in 0 until device.interfaceCount) {
                val usbInterface = device.getInterface(i)
                // Claim interface 0 by default, but try others if it fails
                if (connection.claimInterface(usbInterface, true)) {
                    for (j in 0 until usbInterface.endpointCount) {
                        val ep = usbInterface.getEndpoint(j)
                        if (ep.type == UsbConstants.USB_ENDPOINT_XFER_BULK &&
                            ep.direction == UsbConstants.USB_DIR_OUT
                        ) {
                            usbOutEndpoint = ep
                            Log.d(TAG, "Found USB OUT endpoint: ${ep.address}")
                        }
                    }
                    if (usbOutEndpoint != null) {
                        usbConnection = connection
                        return true
                    }
                    connection.releaseInterface(usbInterface)
                }
            }
            connection.close()
            return false
        } catch (e: Exception) {
            Log.e(TAG, "Failed to open USB connection: ${e.message}")
            return false
        }
    }

    suspend fun switchMode(mode: SpectralMode): Boolean = withContext(Dispatchers.IO) {
        val groupCmd = MODE_GROUPS[mode] ?: return@withContext false
        try {
            for (group in ALL_GROUPS) {
                sendBinaryPacket(group, BRIGHTNESS_OFF)
            }
            sendBinaryPacket(groupCmd, BRIGHTNESS_FULL)
            Log.d(TAG, "Switched to $mode mode (group 0x${groupCmd.toString(16)})")
            true
        } catch (e: Exception) {
            Log.e(TAG, "Failed to switch mode: ${e.message}")
            false
        }
    }

    suspend fun triggerCapture(): Boolean = withContext(Dispatchers.IO) {
        try {
            if (isConnected()) {
                sendBinaryPacket(CMD_GROUP_1, BRIGHTNESS_40_PERCENT)
            }
            true
        } catch (e: Exception) {
            Log.e(TAG, "Capture trigger failed: ${e.message}")
            false
        }
    }

    fun isConnected(): Boolean =
        activeSerialPort != null || bluetoothSocket?.isConnected == true || usbConnection != null

    fun release() {
        activeSerialPort = null
        try { serialOutputStream?.close() } catch (_: Exception) { }
        serialOutputStream = null
        try { bluetoothSocket?.close() } catch (_: Exception) { }
        try { usbConnection?.close() } catch (_: Exception) { }
        bluetoothSocket = null
        usbConnection = null
        usbDevice = null
        usbOutEndpoint = null
    }

    private fun buildBinaryPacket(cmd: Byte, value: Byte): ByteArray {
        return byteArrayOf(PROTOCOL_HEADER_1, PROTOCOL_HEADER_2, cmd, value, PROTOCOL_FOOTER)
    }

    private fun sendBinaryPacket(cmd: Byte, value: Byte) {
        val packet = buildBinaryPacket(cmd, value)
        val hex = packet.joinToString(" ") { "%02X".format(it) }
        val out = serialOutputStream
        if (out != null) {
            try {
                out.write(packet)
                out.flush()
                Log.d(TAG, "Binary sent: $hex")
                return
            } catch (e: Exception) {
                Log.w(TAG, "Serial write failed: ${e.message}")
            }
        }

        val usbConn = usbConnection
        val outEp = usbOutEndpoint
        if (usbConn != null && outEp != null) {
            val sent = usbConn.bulkTransfer(outEp, packet, packet.size, SYNC_TIMEOUT_MS.toInt())
            if (sent < 0) Log.w(TAG, "USB bulkTransfer failed")
            return
        }

        bluetoothSocket?.let { socket ->
            if (socket.isConnected) {
                try {
                    socket.outputStream.write(packet)
                    socket.outputStream.flush()
                } catch (e: IOException) {
                    Log.w(TAG, "BT write failed: ${e.message}")
                }
            }
        }
    }
}
