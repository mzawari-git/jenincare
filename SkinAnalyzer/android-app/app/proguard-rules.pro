# SkinAnalyzer ProGuard Rules

# Keep all model classes for Gson/Retrofit serialization
-keep class com.jenincare.skinanalyzer.domain.model.** { *; }
-keep class com.jenincare.skinanalyzer.data.remote.dto.** { *; }

# Keep Retrofit interfaces
-keep,allowobfuscation interface com.jenincare.skinanalyzer.data.remote.ScanApiService { *; }

# Gson specific rules
-keepattributes Signature
-keepattributes *Annotation*
-keepattributes EnclosingMethod
-keepattributes InnerClasses

-dontwarn javax.annotation.**
-keep class com.google.gson.** { *; }
-keep class com.google.gson.stream.** { *; }

# Keep Gson @SerializedName annotations
-keepclassmembers,allowobfuscation class * {
    @com.google.gson.annotations.SerializedName <fields>;
}

# Retrofit
-keepattributes Signature, InnerClasses, EnclosingMethod
-keepattributes RuntimeVisibleAnnotations, RuntimeVisibleParameterAnnotations
-keepattributes AnnotationDefault
-keepclassmembers,allowshrinking,allowobfuscation interface * {
    @retrofit2.http.* <methods>;
}
-dontwarn org.codehaus.mojo.animal_sniffer.IgnoreJRERequirement
-dontwarn javax.annotation.**
-dontwarn kotlin.Unit
-dontwarn retrofit2.KotlinExtensions
-dontwarn retrofit2.KotlinExtensions$*

-keep class retrofit2.** { *; }
-keepclasseswithmembers class * {
    @retrofit2.http.* <methods>;
}

# OkHttp
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }
-dontwarn okhttp3.**
-dontwarn okio.**

# TensorFlow Lite
-keep class org.tensorflow.lite.** { *; }
-keep class org.tensorflow.lite.support.** { *; }
-keep class org.tensorflow.lite.gpu.** { *; }
-keep class org.tensorflow.lite.nnapi.** { *; }
-dontwarn org.tensorflow.lite.**

# Keep TFLite model files
-keep class com.jenincare.skinanalyzer.ml.** { *; }

# CameraX
-keep class androidx.camera.** { *; }
-dontwarn androidx.camera.**

# Hilt / Dagger
-keep class dagger.hilt.** { *; }
-keep class javax.inject.** { *; }
-keep class * extends dagger.hilt.android.lifecycle.HiltViewModel

# Compose
-keep class androidx.compose.** { *; }
-dontwarn androidx.compose.**

# Coil
-keep class coil.** { *; }
-dontwarn coil.**

# Keep ViewModels
-keep class * extends androidx.lifecycle.ViewModel { *; }
-keep class com.jenincare.skinanalyzer.ui.** { *; }

# Keep UseCases
-keep class com.jenincare.skinanalyzer.domain.usecase.** { *; }

# Keep R classes
-keep class com.jenincare.skinanalyzer.R$* { *; }

# Remove logging in release
-assumenosideeffects class android.util.Log {
    public static boolean isLoggable(java.lang.String, int);
    public static int v(...);
    public static int d(...);
    public static int i(...);
    public static int w(...);
    public static int e(...);
}
