package com.jenincare.skinanalyzer.util

import android.content.Context
import android.content.SharedPreferences
import dagger.hilt.android.qualifiers.ApplicationContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class BaseUrlProvider @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private val prefs: SharedPreferences
        get() = context.getSharedPreferences("base_url_prefs", Context.MODE_PRIVATE)

    fun getBaseUrl(): String {
        return prefs.getString(KEY_BASE_URL, DEFAULT_BASE_URL) ?: DEFAULT_BASE_URL
    }

    fun setBaseUrl(url: String) {
        val normalized = url.trimEnd('/')
        prefs.edit().putString(KEY_BASE_URL, normalized).apply()
    }

    companion object {
        private const val KEY_BASE_URL = "base_url"
        const val DEFAULT_BASE_URL = "https://www.jenincare.shop/api/"
    }
}
