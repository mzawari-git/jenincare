-keepattributes *Annotation*
-keepattributes Signature
-keepattributes InnerClasses
-keepattributes EnclosingMethod

-keep class com.ebtikar.skinanalyzer.model.** { *; }
-keep class com.ebtikar.skinanalyzer.core.provider.** { *; }

-keep class org.tensorflow.lite.** { *; }
-dontwarn org.tensorflow.lite.**

-keep class com.google.mlkit.** { *; }
-dontwarn com.google.mlkit.**

-keep class com.hoho.android.usbserial.** { *; }
-dontwarn com.hoho.android.usbserial.**

-keepclassmembers class * implements android.os.Parcelable {
    public static final ** CREATOR;
}

-keep class * extends androidx.lifecycle.ViewModel { *; }
-keep class * extends androidx.lifecycle.AndroidViewModel { *; }

# kotlinx.serialization
-keepattributes *Annotation*, InnerClasses
-dontnote kotlinx.serialization.AnnotationsKt
-keepclassmembers class kotlinx.serialization.json.** { *** Companion; }
-keepclasseswithmembers class kotlinx.serialization.json.** { kotlinx.serialization.KSerializer serializer(...); }
-keep,includedescriptorclasses class com.ebtikar.skinanalyzer.**$$serializer { *; }
-keepclassmembers class com.ebtikar.skinanalyzer.** { *** Companion; }
-keepclasseswithmembers class com.ebtikar.skinanalyzer.** { kotlinx.serialization.KSerializer serializer(...); }
-keep class kotlinx.serialization.** { *; }
-keepclassmembers class * { *** Companion; }
-keepclasseswithmembers class * { kotlinx.serialization.KSerializer serializer(...); }
-keep,includedescriptorclasses class ** { *; }

# Hilt
-keep class dagger.hilt.** { *; }
-keep class javax.inject.** { *; }
-keep class * extends dagger.hilt.android.internal.managers.ViewComponentManager$FragmentContextWrapper { *; }

# OkHttp
-dontwarn okhttp3.**
-dontwarn okio.**
-keep class okhttp3.** { *; }

# MediaPipe - must not optimize (Graph.<clinit> uses stack inspection)
-keep class com.google.mediapipe.** { *; }
-keepclassmembers class com.google.mediapipe.** { *; }
-dontwarn com.google.mediapipe.**
-optimizations !class/merging/**,!code/simplification/**,!code/removal/**,!method/inlining/**,!code/hoisting/**

# javax.lang.model (referenced by autovalue/JavaPoet in annotation processors)
-dontwarn javax.lang.model.**
-dontwarn autovalue.shaded.**
