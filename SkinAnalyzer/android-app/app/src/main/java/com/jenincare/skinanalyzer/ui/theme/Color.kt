package com.jenincare.skinanalyzer.ui.theme

import androidx.compose.ui.graphics.Color

// ─────────────────────────────────────────────
// SkinAnalyzer — Dark Medical Luxury Palette
// ─────────────────────────────────────────────

// Brand Identity: Rose Gold + Dark Navy + Teal
val RoseGold          = Color(0xFFC9956B)   // اللون الذهبي الأساسي
val RoseGoldLight     = Color(0xFFE8B98A)   // Highlight
val RoseGoldDark      = Color(0xFF9E6B43)   // Pressed state
val TealAccent        = Color(0xFF2DD4BF)   // اللون الفيروزي (Active/Live)
val TealDim           = Color(0xFF0D9488)   // Secondary teal

// ── Legacy Brand (remapped to new palette) ──
val JeninBlue         = Color(0xFFC9956B)   // ← remapped to Rose Gold
val JeninBlueDark     = Color(0xFF9E6B43)
val JeninBlueLight    = Color(0xFF3D2A1A)
val JeninGreen        = Color(0xFF2DD4BF)   // ← remapped to Teal
val JeninGreenDark    = Color(0xFF0D9488)
val JeninGreenLight   = Color(0xFF042F2E)

// Medical Professional Colors
val MedicalNavy       = Color(0xFF0D1117)
val MedicalTeal       = Color(0xFF2DD4BF)
val MedicalWhite      = Color(0xFFF0F0F0)

// ── Light Theme Colors (احتياطي) ──
val LightPrimary                = Color(0xFFC9956B)
val LightOnPrimary              = Color(0xFFFFFFFF)
val LightPrimaryContainer       = Color(0xFFFFE8D6)
val LightOnPrimaryContainer     = Color(0xFF3D1F00)
val LightSecondary              = Color(0xFF2DD4BF)
val LightOnSecondary            = Color(0xFFFFFFFF)
val LightSecondaryContainer     = Color(0xFFB2F5EC)
val LightOnSecondaryContainer   = Color(0xFF003330)
val LightTertiary               = Color(0xFF9E6B43)
val LightOnTertiary             = Color(0xFFFFFFFF)
val LightTertiaryContainer      = Color(0xFFFFDCC2)
val LightOnTertiaryContainer    = Color(0xFF341100)
val LightError                  = Color(0xFFBA1A1A)
val LightOnError                = Color(0xFFFFFFFF)
val LightErrorContainer         = Color(0xFFFFDAD6)
val LightOnErrorContainer       = Color(0xFF410002)
val LightBackground             = Color(0xFFFFF8F4)
val LightOnBackground           = Color(0xFF201A16)
val LightSurface                = Color(0xFFFFF8F4)
val LightOnSurface              = Color(0xFF201A16)
val LightSurfaceVariant         = Color(0xFFF4DED1)
val LightOnSurfaceVariant       = Color(0xFF52443B)
val LightOutline                = Color(0xFF857469)
val LightOutlineVariant         = Color(0xFFD7C2B5)
val LightInverseSurface         = Color(0xFF362F2B)
val LightInverseOnSurface       = Color(0xFFFBEEE8)
val LightInversePrimary         = Color(0xFFFFB77C)
val LightSurfaceTint            = Color(0xFFC9956B)

// ── Dark Theme Colors (الافتراضي — Medical Luxury) ──
val DarkPrimary                 = Color(0xFFC9956B)   // Rose Gold
val DarkOnPrimary               = Color(0xFF3D1F00)
val DarkPrimaryContainer        = Color(0xFF5C3317)
val DarkOnPrimaryContainer      = Color(0xFFFFDCC2)
val DarkSecondary               = Color(0xFF2DD4BF)   // Teal
val DarkOnSecondary             = Color(0xFF003330)
val DarkSecondaryContainer      = Color(0xFF004F4A)
val DarkOnSecondaryContainer    = Color(0xFFB2F5EC)
val DarkTertiary                = Color(0xFFE8B98A)   // Rose Gold Light
val DarkOnTertiary              = Color(0xFF3D1F00)
val DarkTertiaryContainer       = Color(0xFF5A3620)
val DarkOnTertiaryContainer     = Color(0xFFFFDCC2)
val DarkError                   = Color(0xFFFFB4AB)
val DarkOnError                 = Color(0xFF690005)
val DarkErrorContainer          = Color(0xFF93000A)
val DarkOnErrorContainer        = Color(0xFFFFDAD6)
val DarkBackground              = Color(0xFF0D1117)   // Deep Navy Black
val DarkOnBackground            = Color(0xFFEDEDED)
val DarkSurface                 = Color(0xFF161B24)   // Surface
val DarkOnSurface               = Color(0xFFEDEDED)
val DarkSurfaceVariant          = Color(0xFF1C2333)   // Card Background
val DarkOnSurfaceVariant        = Color(0xFFB0B8C1)
val DarkOutline                 = Color(0xFF4A5568)
val DarkOutlineVariant          = Color(0xFF2D3748)
val DarkInverseSurface          = Color(0xFFEDEDED)
val DarkInverseOnSurface        = Color(0xFF1A1C1E)
val DarkInversePrimary          = Color(0xFF9E6B43)
val DarkSurfaceTint             = Color(0xFFC9956B)

// ── Heatmap & Medical Severity Colors ──
val SeverityMild                = Color(0xFFFFD54F)
val SeverityModerate            = Color(0xFFFF9800)
val SeveritySevere              = Color(0xFFE53935)
val HeatmapInflammation         = Color(0xFFFF4444)
val HeatmapPigmentation         = Color(0xFFA855F7)
val HeatmapDryness              = Color(0xFFF97316)
val HeatmapHealthy              = Color(0xFF10B981)

// ── Background Depth Palette ──
val BgDeep                     = Color(0xFF080B12)   // Deepest background
val BgBase                     = Color(0xFF0D1117)   // Main background
val BgSurface                  = Color(0xFF161B24)   // Cards & panels
val BgElevated                 = Color(0xFF1C2333)   // Elevated elements

// ── Health Score Colors ──
val ScorePoor                   = Color(0xFFE53935)
val ScoreFair                   = Color(0xFFFF9800)
val ScoreGood                   = Color(0xFF8BC34A)
val ScoreExcellent              = Color(0xFF10B981)
val HealthExcellent             = Color(0xFF10B981)   // Score 80-100
val HealthGood                  = Color(0xFFF59E0B)   // Score 60-79
val HealthFair                  = Color(0xFFEF4444)   // Score 0-59

// ── Upload & Progress ──
val UploadProgressTrack         = Color(0xFF2D3748)
val UploadProgressFill          = Color(0xFFC9956B)

// ── Card & Surface Tokens ──
val CardLight                   = Color(0xFFFFF8F4)
val CardDark                    = Color(0xFF161B24)
val DividerLight                = Color(0xFFF4DED1)
val DividerDark                 = Color(0xFF2D3748)

// ── Glass Effect Border ──
val GlassBorder                 = Color(0x4DC9956B)   // Rose Gold @ 30% opacity
val GlassBorderSubtle           = Color(0x1AC9956B)   // Rose Gold @ 10% opacity
