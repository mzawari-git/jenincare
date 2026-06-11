package com.ebtikar.skinanalyzer.hardware

enum class LightSpectrum(
    val displayName: String,
    val displayNameAr: String,
    val commandByte: Byte,
    val settlingWindowMs: Long,
    val wavelengthNm: Int = 0,
    val colorHex: String = "#FFFFFF",
    val capturePurpose: String = ""
) {
    WHITE(
        "White RGB Daylight",
        "ضوء أبيض نهاري",
        0x01, 40, 5500,
        "#FFFFFF",
        "Baseline skin tone & texture"
    ),
    POL_P(
        "Cross-Polarized (+)",
        "مستقطب متقاطع (+)",
        0x07, 45, 0,
        "#E0E0FF",
        "Subsurface pigmentation & vascular"
    ),
    POL_N(
        "Parallel-Polarized (-)",
        "مستقطب موازٍ (-)",
        0x08, 45, 0,
        "#FFE0E0",
        "Surface texture & pores"
    ),
    UV365(
        "UV Spectrum 365nm",
        "أشعة فوق بنفسجية 365nm",
        0x02, 60, 365,
        "#9B59B6",
        "UV damage & porphyrins"
    ),
    WOODS(
        "Wood's Light",
        "ضوء وودز",
        0x03, 60, 365,
        "#8E44AD",
        "Melanin distribution & fungi"
    ),
    BLUE(
        "Blue Light 465nm",
        "ضوء أزرق 465nm",
        0x04, 45, 465,
        "#3498DB",
        "Acne bacteria & sebum"
    ),
    RED(
        "Red Light 630nm",
        "ضوء أحمر 630nm",
        0x05, 45, 630,
        "#E74C3C",
        "Vascular & collagen"
    ),
    BROWN(
        "Brown Light 590nm",
        "ضوء بني 590nm",
        0x06, 45, 590,
        "#D35400",
        "Deep pigmentation & spots"
    ),
    ALL(
        "All Lights On",
        "جميع الأضواء",
        0x0F, 100, 0,
        "#FFFFFF",
        "All spectra simultaneously"
    ),
    OFF(
        "Off",
        "إيقاف",
        0x00, 0, 0,
        "#000000",
        ""
    );

    companion object {
        val CAPTURE_SEQUENCE = listOf(WHITE, POL_P, POL_N, UV365, WOODS, BLUE, RED, BROWN)

        val UV_SPECTRA = listOf(UV365, WOODS)
        val RGB_SPECTRA = listOf(WHITE, BLUE, RED, BROWN)
        val CROSS_SPECTRA = listOf(POL_P, POL_N)
        val ALL_SPECTRA = CAPTURE_SEQUENCE

        val DIAGNOSTIC_SPECTRA = entries.filter { it != OFF && it != ALL }

        val ALL_COMMAND: ByteArray by lazy {
            val cmds = byteArrayOf(
                WHITE.commandByte, UV365.commandByte, WOODS.commandByte,
                BLUE.commandByte, RED.commandByte, BROWN.commandByte,
                POL_P.commandByte, POL_N.commandByte
            )
            val length = cmds.size.toByte()
            val checksum = (0xAA.toByte().toInt() + length.toInt() + cmds.sumOf { it.toInt() and 0xFF } and 0xFF).toByte()
            byteArrayOf(0xAA.toByte(), length) + cmds + checksum + 0x55.toByte()
        }

        fun fromCommandByte(byte: Byte): LightSpectrum? =
            entries.find { it.commandByte == byte }

        fun getSpectraForMetric(metricType: String): List<LightSpectrum> = when (metricType) {
            "PIGMENTATION", "UV_SPOTS" -> listOf(UV365, WOODS, BROWN)
            "VASCULAR", "DARK_CIRCLES" -> listOf(RED, POL_P)
            "ACNE", "SEBUM", "BLACKHEADS" -> listOf(BLUE, WHITE)
            "WRINKLES", "COLLAGEN" -> listOf(RED, POL_N, WHITE)
            "MOISTURE", "TEXTURE" -> listOf(POL_N, WHITE)
            "PORES" -> listOf(POL_N, POL_P)
            else -> listOf(WHITE)
        }

        fun getSpectraForDiagnosisMode(mode: String): List<LightSpectrum> = when (mode) {
            "uv" -> UV_SPECTRA
            "rgb" -> RGB_SPECTRA
            "cross" -> CROSS_SPECTRA
            else -> ALL_SPECTRA
        }
    }
}
