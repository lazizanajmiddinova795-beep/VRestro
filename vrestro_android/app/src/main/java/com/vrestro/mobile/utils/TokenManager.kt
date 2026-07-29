package com.vrestro.mobile.utils

import android.content.Context
import android.content.SharedPreferences
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import com.google.gson.Gson

object TokenManager {
    private const val PREFS_FILE = "vrestro_secure_prefs"
    private const val KEY_TOKEN = "auth_token"
    private const val KEY_USER = "user_data"
    private const val KEY_ROLE = "user_role"

    private var prefs: SharedPreferences? = null
    private val gson = Gson()

    fun init(context: Context) {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()
        prefs = EncryptedSharedPreferences.create(
            context,
            PREFS_FILE,
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        )
    }

    fun saveToken(token: String) {
        prefs?.edit()?.putString(KEY_TOKEN, token)?.apply()
    }

    fun getToken(): String? = prefs?.getString(KEY_TOKEN, null)

    fun saveUser(userJson: String, role: String) {
        prefs?.edit()
            ?.putString(KEY_USER, userJson)
            ?.putString(KEY_ROLE, role)
            ?.apply()
    }

    fun getUserJson(): String? = prefs?.getString(KEY_USER, null)
    fun getRole(): String? = prefs?.getString(KEY_ROLE, null)

    fun clear() {
        prefs?.edit()?.clear()?.apply()
    }

    fun isLoggedIn(): Boolean = !getToken().isNullOrEmpty()
}
