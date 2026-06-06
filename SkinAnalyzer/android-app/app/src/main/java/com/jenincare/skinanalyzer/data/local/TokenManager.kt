package com.jenincare.skinanalyzer.data.local

import android.content.SharedPreferences
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TokenManager @Inject constructor(
    private val encryptedPreferences: SharedPreferences
) {
    companion object {
        private const val KEY_AUTH_TOKEN = "auth_token"
        private const val KEY_REFRESH_TOKEN = "refresh_token"
        private const val KEY_USER_ID = "user_id"
    }

    fun saveToken(token: String) {
        encryptedPreferences.edit()
            .putString(KEY_AUTH_TOKEN, token)
            .apply()
    }

    fun getToken(): String? {
        val t = encryptedPreferences.getString(KEY_AUTH_TOKEN, null)
        android.util.Log.e("TokenMgr", "getToken: ${if (t.isNullOrBlank()) "EMPTY" else "OK[${t.length}]"}")
        return t
    }

    fun saveRefreshToken(token: String) {
        encryptedPreferences.edit()
            .putString(KEY_REFRESH_TOKEN, token)
            .apply()
    }

    fun getRefreshToken(): String? {
        return encryptedPreferences.getString(KEY_REFRESH_TOKEN, null)
    }

    fun saveUserId(userId: String) {
        encryptedPreferences.edit()
            .putString(KEY_USER_ID, userId)
            .apply()
    }

    fun getUserId(): String? {
        return encryptedPreferences.getString(KEY_USER_ID, null)
    }

    fun clearToken() {
        encryptedPreferences.edit()
            .remove(KEY_AUTH_TOKEN)
            .remove(KEY_REFRESH_TOKEN)
            .remove(KEY_USER_ID)
            .apply()
    }

    fun hasToken(): Boolean {
        return !getToken().isNullOrBlank()
    }
}
