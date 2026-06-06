package com.jenincare.skinanalyzer.di

import com.jenincare.skinanalyzer.BuildConfig
import com.jenincare.skinanalyzer.data.local.TokenManager
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.data.remote.ScanApiService
import com.jenincare.skinanalyzer.util.BaseUrlProvider
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import okhttp3.ConnectionSpec
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.Protocol
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Qualifier
import javax.inject.Singleton

@Qualifier
@Retention(AnnotationRetention.BINARY)
annotation class AuthInterceptorOkHttpClient

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    private const val DEFAULT_BASE_URL = "https://www.jenincare.shop/api/"
    private const val CONNECT_TIMEOUT = 60L
    private const val READ_TIMEOUT = 60L
    private const val WRITE_TIMEOUT = 120L

    @Provides
    @Singleton
    fun provideMoshi(): Moshi = Moshi.Builder()
        .add(KotlinJsonAdapterFactory())
        .build()

    @Provides
    @Singleton
    fun provideLoggingInterceptor(): HttpLoggingInterceptor =
        HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.HEADERS
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
        }

    @Provides
    @Singleton
    @AuthInterceptorOkHttpClient
    fun provideOkHttpClient(
        loggingInterceptor: HttpLoggingInterceptor,
        tokenManager: TokenManager,
        baseUrlProvider: BaseUrlProvider
    ): OkHttpClient {
        val theBaseUrl = baseUrlProvider.getBaseUrl()
        return OkHttpClient.Builder()
            .addInterceptor(Interceptor { chain ->
                val original = chain.request()
                val builder = original.newBuilder()
                    .header("Accept", "application/json")
                    .header("X-Client-Platform", "android")
                    .header("X-Client-Version", BuildConfig.VERSION_NAME)

                val path = original.url.encodedPath
                if (!path.contains("auth/login") && !path.contains("auth/register")) {
                    tokenManager.getToken()?.let { token ->
                        builder.header("Authorization", "Bearer $token")
                    }
                }

                val request = builder
                    .method(original.method, original.body)
                    .build()

                val requestUrl = request.url.toString()
                val defaultBase = "https://www.jenincare.shop"
                if (!requestUrl.startsWith(theBaseUrl) && requestUrl.startsWith(defaultBase)) {
                    val newUrl = requestUrl.replace(defaultBase, theBaseUrl.trimEnd('/'))
                    chain.proceed(request.newBuilder().url(newUrl).build())
                } else {
                    chain.proceed(request)
                }
            })
            .addInterceptor(loggingInterceptor)
            .protocols(listOf(Protocol.HTTP_1_1))
            .connectionSpecs(listOf(ConnectionSpec.COMPATIBLE_TLS, ConnectionSpec.MODERN_TLS, ConnectionSpec.CLEARTEXT))
            .connectTimeout(CONNECT_TIMEOUT, TimeUnit.SECONDS)
            .readTimeout(READ_TIMEOUT, TimeUnit.SECONDS)
            .writeTimeout(WRITE_TIMEOUT, TimeUnit.SECONDS)
            .retryOnConnectionFailure(true)
            .build()
    }

    @Provides
    @Singleton
    fun provideRetrofit(
        @AuthInterceptorOkHttpClient okHttpClient: OkHttpClient,
        moshi: Moshi,
        baseUrlProvider: BaseUrlProvider
    ): Retrofit = Retrofit.Builder()
        .baseUrl((baseUrlProvider.getBaseUrl().let { if (it.isBlank()) DEFAULT_BASE_URL else it }).trimEnd('/') + '/')
        .client(okHttpClient)
        .addConverterFactory(MoshiConverterFactory.create(moshi))
        .build()

    @Provides
    @Singleton
    fun provideApiService(retrofit: Retrofit): ApiService =
        retrofit.create(ApiService::class.java)

    @Provides
    @Singleton
    fun provideScanApiService(retrofit: Retrofit): ScanApiService =
        retrofit.create(ScanApiService::class.java)
}
