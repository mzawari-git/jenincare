# Consumer ProGuard Rules for SkinAnalyzer library

# Keep all model classes exposed to consumers
-keep class com.jenincare.skinanalyzer.domain.model.** { *; }

# Keep public API interfaces
-keep interface com.jenincare.skinanalyzer.data.remote.ScanApiService { *; }

# Keep use cases that other modules may depend on
-keep class com.jenincare.skinanalyzer.domain.usecase.** { *; }

# Keep DTOs for serialization
-keep class com.jenincare.skinanalyzer.data.remote.dto.** { *; }

# Keep Theme composables for reuse
-keep class com.jenincare.skinanalyzer.ui.theme.** { *; }

# Keep reusable components
-keep class com.jenincare.skinanalyzer.ui.components.** { *; }

# Keep navigation routes
-keep class com.jenincare.skinanalyzer.ui.navigation.Routes { *; }
