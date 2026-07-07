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
        "White Light (RGB Daylight)",
        "الضوء المرئي (RGB)",
        0x01, 150, 5500,
        "#FFFFFF",
        "Surface analysis: pores, wrinkles, skin tone"
    ),
    POL_P(
        "Cross-Polarized Light",
        "ضوء مستقطب متقاطع",
        0x07, 150, 0,
        "#E0E0FF",
        "Blood vessels, redness, rosacea, deep pigmentation"
    ),
    POL_N(
        "Parallel-Polarized Light",
        "ضوء مستقطب موازٍ",
        0x08, 150, 0,
        "#FFE0E0",
        "Fine lines, texture, surface condition"
    ),
    UV365(
        "UV Light 365nm",
        "الأشعة فوق البنفسجية 365nm",
        0x02, 300, 365,
        "#9B59B6",
        "Porphyrins, sun damage, sebum"
    ),
    WOODS(
        "Wood's Light",
        "ضوء وودز السريري",
        0x03, 300, 365,
        "#8E44AD",
        "Hydration levels, deep melasma"
    ),
    BLUE(
        "Blue Light 465nm",
        "ضوء أزرق 465nm",
        0x04, 150, 465,
        "#3498DB",
        "Acne bacteria & sebum"
    ),
    RED(
        "Red Light 630nm",
        "ضوء أحمر 630nm",
        0x05, 150, 630,
        "#E74C3C",
        "Vascular & collagen"
    ),
    BROWN(
        "Brown Light 590nm",
        "ضوء بني 590nm",
        0x06, 150, 590,
        "#D35400",
        "Deep pigmentation & spots"
    ),
    ALL(
        "All Lights On",
        "جميع الأضواء",
        0x0F, 200, 0,
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

        const val DIAGNOSIS_WHITE = "white"
        const val DIAGNOSIS_UV = "uv"
        const val DIAGNOSIS_CROSS_POL = "cross_pol"
        const val DIAGNOSIS_PARALLEL_POL = "parallel_pol"
        const val DIAGNOSIS_WOODS = "woods"
        const val DIAGNOSIS_ALL = "all"

        val DIAGNOSIS_MODE_SPECTRA: Map<String, List<LightSpectrum>> = mapOf(
            DIAGNOSIS_ALL to ALL_SPECTRA,
            DIAGNOSIS_WHITE to listOf(WHITE),
            DIAGNOSIS_UV to listOf(UV365, WOODS, WHITE),
            DIAGNOSIS_CROSS_POL to listOf(POL_P, POL_N, WHITE),
            DIAGNOSIS_PARALLEL_POL to listOf(POL_P, WHITE),
            DIAGNOSIS_WOODS to listOf(WOODS, WHITE)
        )

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
            "PORPHYRINS" -> listOf(UV365)
            "ROSACEA" -> listOf(POL_P)
            "MELASMA" -> listOf(WOODS)
            else -> listOf(WHITE)
        }

        fun getSpectraForDiagnosisMode(mode: String): List<LightSpectrum> =
            DIAGNOSIS_MODE_SPECTRA[mode] ?: ALL_SPECTRA
    }
}
