plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
    id("com.google.dagger.hilt.android")
    id("com.google.devtools.ksp")
}

android {
    namespace = "com.jenincare.skinanalyzer"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.jenincare.skinanalyzer"
        minSdk = 24
        targetSdk = 34
        ndk {
            abiFilters += listOf("arm64-v8a", "armeabi-v7a")
        }
        versionCode = 7
        versionName = "1.4.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"

        vectorDrawables {
            useSupportLibrary = true
        }
        buildConfigField("String", "API_BASE_URL", "\"https://www.jenincare.shop/api/\"")
        buildConfigField("String", "API_VERSION", "\"v1\"")
        buildConfigField("String", "DEFAULT_AUTH_TOKEN", "\"\"")
    }

    signingConfigs {
        create("release") {
            storeFile = file("release.keystore")
            storePassword = "android"
            keyAlias = "jenincare"
            keyPassword = "android"
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            isShrinkResources = false
            signingConfig = signingConfigs.getByName("release")
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
        debug {
            isMinifyEnabled = false
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    composeOptions {
        kotlinCompilerExtensionVersion = "1.5.10"
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        compose = true
        viewBinding = true
        buildConfig = true
    }

    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
            excludes += "/META-INF/INDEX.LIST"
            excludes += "/META-INF/DEPENDENCIES"
        }
        jniLibs {
            useLegacyPackaging = true
        }
    }
}

val composeBom = "androidx.compose:compose-bom:2024.02.00"

dependencies {
    // Kotlin Standard Library (Explicitly added to resolve fundamental IDE errors)
    implementation("org.jetbrains.kotlin:kotlin-stdlib:1.9.22")

    // Compose BOM
    implementation(platform(composeBom))
    androidTestImplementation(platform(composeBom))

    // Compose UI
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.ui:ui-graphics")
    implementation("androidx.compose.ui:ui-tooling-preview")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.material:material-icons-extended")
    implementation("androidx.compose.foundation:foundation")
    implementation("androidx.compose.runtime:runtime-livedata")
    implementation("androidx.compose.runtime:runtime")
    debugImplementation("androidx.compose.ui:ui-tooling")
    debugImplementation("androidx.compose.ui:ui-test-manifest")

    // Activity & Lifecycle
    implementation("androidx.activity:activity-compose:1.8.2")
    implementation("androidx.lifecycle:lifecycle-runtime-compose:2.7.0")
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.7.0")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.7.0")

    // Navigation Compose
    implementation("androidx.navigation:navigation-compose:2.7.6")

    // Hilt - Dependency Injection
    implementation("com.google.dagger:hilt-android:2.50")
    ksp("com.google.dagger:hilt-android-compiler:2.50")
    implementation("androidx.hilt:hilt-navigation-compose:1.1.0")
    implementation("androidx.hilt:hilt-work:1.1.0")
    ksp("androidx.hilt:hilt-compiler:1.1.0")

    // UVC Camera (USB Video Class - bypasses Android Camera HAL for Bitmoji ZMLH02)
    // alexey-pelykh fork of saki4510t/UVCCamera, available on Maven Central
    implementation("org.uvccamera:lib:0.0.13")

    // Retrofit2 + OkHttp
    val retrofitVersion = "2.9.0"
    implementation("com.squareup.retrofit2:retrofit:$retrofitVersion")
    implementation("com.squareup.retrofit2:converter-moshi:$retrofitVersion")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // Moshi (reflective mode — no KSP codegen to avoid build crashes)
    implementation("com.squareup.moshi:moshi-kotlin:1.15.0")

    // Kotlinx Serialization
    implementation("org.jetbrains.kotlinx:kotlinx-serialization-json:1.6.2")

    // Coil - Image Loading
    implementation("io.coil-kt:coil-compose:2.5.0")

    // DataStore Preferences
    implementation("androidx.datastore:datastore-preferences:1.0.0")

    // Security Crypto
    implementation("androidx.security:security-crypto:1.1.0-alpha06")

    // QR Code Generation
    implementation("com.google.zxing:core:3.5.3")

    // Room Database
    val roomVersion = "2.6.1"
    implementation("androidx.room:room-runtime:$roomVersion")
    implementation("androidx.room:room-ktx:$roomVersion")
    ksp("androidx.room:room-compiler:$roomVersion")

    // WorkManager
    implementation("androidx.work:work-runtime-ktx:2.9.0")

    // Firebase
    implementation(platform("com.google.firebase:firebase-bom:32.7.0"))
    implementation("com.google.firebase:firebase-crashlytics-ktx")
    implementation("com.google.firebase:firebase-analytics-ktx")
    implementation("com.google.firebase:firebase-messaging-ktx")

    // TensorFlow Lite
    implementation("org.tensorflow:tensorflow-lite:2.14.0")
    implementation("org.tensorflow:tensorflow-lite-support:0.4.4")
    implementation("org.tensorflow:tensorflow-lite-gpu:2.14.0")

    // Accompanist
    implementation("com.google.accompanist:accompanist-permissions:0.32.0")
    implementation("com.google.accompanist:accompanist-systemuicontroller:0.32.0")
    implementation("com.google.accompanist:accompanist-navigation-animation:0.32.0")

    // Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3")
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-play-services:1.7.3")

    // Material Components (for XML themes)
    implementation("com.google.android.material:material:1.11.0")

    // Biometric Authentication
    implementation("androidx.biometric:biometric:1.1.0")

    // Core KTX
    implementation("androidx.core:core-ktx:1.12.0")
    implementation("androidx.core:core-splashscreen:1.0.1")
    implementation("androidx.exifinterface:exifinterface:1.3.7")

    // Testing
    testImplementation("junit:junit:4.13.2")
    testImplementation("org.jetbrains.kotlinx:kotlinx-coroutines-test:1.7.3")
    testImplementation("io.mockk:mockk:1.13.8")
    androidTestImplementation("androidx.test.ext:junit:1.1.5")
    androidTestImplementation("androidx.test.espresso:espresso-core:3.5.1")
    androidTestImplementation("androidx.compose.ui:ui-test-junit4")
}
