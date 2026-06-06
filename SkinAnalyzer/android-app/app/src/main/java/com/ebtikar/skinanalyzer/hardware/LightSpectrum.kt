package com.ebtikar.skinanalyzer.hardware

enum class LightSpectrum(
    val displayName: String,
    val commandByte: Byte,
    val settlingWindowMs: Long,
    val wavelengthNm: Int = 0
) {
    WHITE("White RGB Daylight", 0x01, 40, 5500),
    POL_P("Cross-Polarized (+)", 0x07, 45, 0),
    POL_N("Parallel-Polarized (-)", 0x08, 45, 0),
    UV365("UV Spectrum 365nm", 0x02, 60, 365),
    WOODS("Wood's Light", 0x03, 60, 365),
    BLUE("Blue Light", 0x04, 45, 465),
    RED("Red Light", 0x05, 45, 630),
    BROWN("Brown Light", 0x06, 45, 590),
    OFF("Off", 0x00, 0, 0);

    companion object {
        val CAPTURE_SEQUENCE = listOf(WHITE, POL_P, POL_N, UV365, WOODS)

        fun fromCommandByte(byte: Byte): LightSpectrum? =
            entries.find { it.commandByte == byte }
    }
}
