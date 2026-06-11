package com.ebtikar.skinanalyzer.core.di

import com.ebtikar.skinanalyzer.BuildConfig
import com.ebtikar.skinanalyzer.data.local.TokenManager
import com.ebtikar.skinanalyzer.data.remote.CloudApiService
import com.ebtikar.skinanalyzer.util.PreferencesManager
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.runBlocking
import kotlinx.serialization.json.Json
import okhttp3.HttpUrl.Companion.toHttpUrlOrNull
import okhttp3.Interceptor
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.kotlinx.serialization.asConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @Provides
    @Singleton
    fun provideAuthInterceptor(tokenManager: TokenManager): Interceptor {
        return Interceptor { chain ->
            val token = runBlocking { tokenManager.getToken() }
            val requestBuilder = chain.request().newBuilder()
                .addHeader("Accept", "application/json")

            if (!token.isNullOrEmpty()) {
                requestBuilder.addHeader("Authorization", "Bearer $token")
            } else if (BuildConfig.API_KEY != "mock_key_for_dev") {
                requestBuilder.addHeader("Authorization", "Bearer ${BuildConfig.API_KEY}")
            }

            chain.proceed(requestBuilder.build())
        }
    }

    @Provides
    @Singleton
    fun provideLoggingInterceptor(): HttpLoggingInterceptor {
        return HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.BODY
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
        }
    }

    @Provides
    @Singleton
    fun provideOkHttpClient(
        authInterceptor: Interceptor,
        loggingInterceptor: HttpLoggingInterceptor,
        preferencesManager: PreferencesManager
    ): OkHttpClient {
        return OkHttpClient.Builder()
            .addInterceptor { chain ->
                val originalRequest = chain.request()
                val customUrl = runBlocking { preferencesManager.apiUrlFlow.first() }
                
                if (customUrl.isNotBlank()) {
                    try {
                        val customHttpUrl = customUrl.toHttpUrlOrNull()
                        if (customHttpUrl != null) {
                            val newUrl = originalRequest.url.newBuilder()
                                .scheme(customHttpUrl.scheme)
                                .host(customHttpUrl.host)
                                .port(customHttpUrl.port)
                                .build()
                            chain.proceed(originalRequest.newBuilder().url(newUrl).build())
                        } else {
                            chain.proceed(originalRequest)
                        }
                    } catch (e: Exception) {
                        chain.proceed(originalRequest)
                    }
                } else {
                    chain.proceed(originalRequest)
                }
            }
            .addInterceptor(authInterceptor)
            .addInterceptor(loggingInterceptor)
            .connectTimeout(60, TimeUnit.SECONDS)
            .readTimeout(60, TimeUnit.SECONDS)
            .writeTimeout(120, TimeUnit.SECONDS)
            .build()
    }

    @Provides
    @Singleton
    fun provideJson(): Json {
        return Json {
            ignoreUnknownKeys = true
            encodeDefaults = true
            coerceInputValues = true
        }
    }

    @Provides
    @Singleton
    fun provideRetrofit(okHttpClient: OkHttpClient, json: Json): Retrofit {
        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL + "/")
            .client(okHttpClient)
            .addConverterFactory(json.asConverterFactory("application/json".toMediaType()))
            .build()
    }

    @Provides
    @Singleton
    fun provideCloudApiService(retrofit: Retrofit): CloudApiService {
        return retrofit.create(CloudApiService::class.java)
    }
}
