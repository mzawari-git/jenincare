package com.jenincare.skinanalyzer.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp

// Arabic text font — use NotoSansArabic when bundled
val NotoSansArabic = FontFamily.Default

// Numbers & indicators — use SpaceGrotesk when bundled (high legibility)
val SpaceGrotesk = FontFamily.Default

val SkinAnalyzerTypography = Typography(
    displayLarge = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Light,
        fontSize = 32.sp,
        lineHeight = 40.sp,
        letterSpacing = 0.sp
    ),
    displayMedium = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Light,
        fontSize = 28.sp,
        lineHeight = 36.sp,
        letterSpacing = 0.sp
    ),
    displaySmall = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Normal,
        fontSize = 24.sp,
        lineHeight = 32.sp,
        letterSpacing = 0.sp
    ),
    headlineLarge = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Bold,
        fontSize = 22.sp,
        lineHeight = 28.sp,
        letterSpacing = 0.sp
    ),
    headlineMedium = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Normal,
        fontSize = 20.sp,
        lineHeight = 28.sp,
        letterSpacing = 0.sp
    ),
    headlineSmall = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.SemiBold,
        fontSize = 18.sp,
        lineHeight = 24.sp,
        letterSpacing = 0.sp
    ),
    titleLarge = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Bold,
        fontSize = 18.sp,
        lineHeight = 24.sp,
        letterSpacing = 0.sp
    ),
    titleMedium = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Medium,
        fontSize = 16.sp,
        lineHeight = 22.sp,
        letterSpacing = 0.sp
    ),
    titleSmall = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Medium,
        fontSize = 14.sp,
        lineHeight = 20.sp,
        letterSpacing = 0.sp
    ),
    bodyLarge = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Normal,
        fontSize = 15.sp,
        lineHeight = 24.sp,
        letterSpacing = 0.5.sp
    ),
    bodyMedium = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Normal,
        fontSize = 13.sp,
        lineHeight = 20.sp,
        letterSpacing = 0.25.sp
    ),
    bodySmall = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Light,
        fontSize = 12.sp,
        lineHeight = 16.sp,
        letterSpacing = 0.4.sp
    ),
    labelLarge = TextStyle(
        fontFamily = SpaceGrotesk,
        fontWeight = FontWeight.Medium,
        fontSize = 14.sp,
        lineHeight = 20.sp,
        letterSpacing = 0.1.sp
    ),
    labelMedium = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Medium,
        fontSize = 12.sp,
        lineHeight = 16.sp,
        letterSpacing = 0.5.sp
    ),
    labelSmall = TextStyle(
        fontFamily = NotoSansArabic,
        fontWeight = FontWeight.Medium,
        fontSize = 11.sp,
        lineHeight = 16.sp,
        letterSpacing = 0.5.sp
    )
)

// ── Extended Arabic-optimized text styles ──

val ArabicDisplayLarge = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Light,
    fontSize = 32.sp,
    lineHeight = 40.sp,
    letterSpacing = 0.sp
)

val ArabicHeadlineMedium = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Normal,
    fontSize = 20.sp,
    lineHeight = 28.sp,
    letterSpacing = 0.sp
)

val ArabicTitleLarge = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Bold,
    fontSize = 22.sp,
    lineHeight = 30.sp,
    letterSpacing = 0.sp
)

val ArabicTitleMedium = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Bold,
    fontSize = 18.sp,
    lineHeight = 26.sp,
    letterSpacing = 0.sp
)

val ArabicTitleSmall = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.SemiBold,
    fontSize = 16.sp,
    lineHeight = 22.sp,
    letterSpacing = 0.sp
)

val ArabicBodyLarge = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Normal,
    fontSize = 15.sp,
    lineHeight = 24.sp,
    letterSpacing = 0.5.sp
)

val ArabicBodyMedium = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Normal,
    fontSize = 13.sp,
    lineHeight = 20.sp,
    letterSpacing = 0.25.sp
)

val ArabicBodySmall = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Light,
    fontSize = 12.sp,
    lineHeight = 16.sp,
    letterSpacing = 0.4.sp
)

val ArabicLabelLarge = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.SemiBold,
    fontSize = 14.sp,
    lineHeight = 20.sp,
    letterSpacing = 0.1.sp
)

// Number-optimized styles (SpaceGrotesk for high legibility)
val ScoreNumberStyle = TextStyle(
    fontFamily = SpaceGrotesk,
    fontWeight = FontWeight.Medium,
    fontSize = 48.sp,
    lineHeight = 56.sp,
    letterSpacing = (-1).sp
)

val ScoreSubtitleStyle = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Light,
    fontSize = 14.sp,
    lineHeight = 20.sp,
    letterSpacing = 0.5.sp
)

val RadarAxisLabelStyle = TextStyle(
    fontFamily = NotoSansArabic,
    fontWeight = FontWeight.Medium,
    fontSize = 11.sp,
    lineHeight = 14.sp,
    letterSpacing = 0.sp
)

val MetricValueStyle = TextStyle(
    fontFamily = SpaceGrotesk,
    fontWeight = FontWeight.Medium,
    fontSize = 16.sp,
    lineHeight = 22.sp,
    letterSpacing = 0.sp
)
