-keepattributes *Annotation*
-keepattributes Signature
-keepattributes InnerClasses

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
