package com.ebtikar.skinanalyzer.util

object Constants {
    const val DEVICE_MODEL = "ZMLH02"
    const val DEVICE_BRAND = "Bitmoji"
    const val DEVICE_NAME = "AI Intelligent Skin Analyzer"
    const val DEVICE_EDITION = "Max Edition"

    const val SCREEN_WIDTH = 1920
    const val SCREEN_HEIGHT = 1080

    const val PREF_LANG_KEY = "app_language"
    const val PREF_ANALYSIS_MODE_KEY = "analysis_mode"
    const val PREF_BRIGHTNESS_KEY = "screen_brightness"

    const val LANG_CHINESE = "zh"
    const val LANG_ARABIC = "ar"
    const val LANG_ENGLISH = "en"

    const val ANALYSIS_LOCAL = "local"
    const val ANALYSIS_CLOUD = "cloud"
    const val ANALYSIS_AUTO = "auto"

    const val DIAGNOSIS_WHITE = "white"
    const val DIAGNOSIS_UV = "uv"
    const val DIAGNOSIS_CROSS_POL = "cross_pol"
    const val DIAGNOSIS_PARALLEL_POL = "parallel_pol"
    const val DIAGNOSIS_WOODS = "woods"
    const val DIAGNOSIS_ALL = "all"

    const val DB_NAME = "skin_analyzer_db"
    const val DB_VERSION = 1

    const val SPLASH_DELAY_MS = 2500L

    const val MAX_IMAGE_SIZE_BYTES = 10 * 1024 * 1024

    const val SCAN_OVERLAY_PROFESSIONAL = "professional"
    const val SCAN_OVERLAY_MINIMAL = "minimal"
    const val SCAN_OVERLAY_CUSTOM = "custom"
}
