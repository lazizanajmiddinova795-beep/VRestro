package com.example.myapplication.utils

import android.content.Context
import android.content.SharedPreferences
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import com.example.myapplication.data.models.UserModel
import com.google.gson.Gson

object TokenManager {
    private const val PREFS_FILE = "vrestro_secure_prefs"
    private const val KEY_TOKEN = "auth_token"
    private const val KEY_USER = "user_data"
    private const val KEY_ROLE = "user_role"

    private var prefs: SharedPreferences? = null
    private val gson = Gson()

    fun init(context: Context) {
        try {
            val masterKey = MasterKey.Builder(context)
                .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
                .build()
            prefs = EncryptedSharedPreferences.create(
                context, PREFS_FILE, masterKey,
                EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
                EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
            )
        } catch (e: Exception) {
            // Fallback to plain SharedPreferences if encryption fails
            prefs = context.getSharedPreferences(PREFS_FILE, Context.MODE_PRIVATE)
        }
    }

    fun saveToken(token: String) = prefs?.edit()?.putString(KEY_TOKEN, token)?.apply()
    fun getToken(): String? = prefs?.getString(KEY_TOKEN, null)

    fun saveUser(user: UserModel) {
        prefs?.edit()
            ?.putString(KEY_USER, gson.toJson(user))
            ?.putString(KEY_ROLE, user.role)
            ?.apply()
    }

    fun getUser(): UserModel? = try {
        val json = prefs?.getString(KEY_USER, null)
        if (json != null) gson.fromJson(json, UserModel::class.java) else null
    } catch (e: Exception) { null }

    fun getRole(): String? = prefs?.getString(KEY_ROLE, null)

    fun clear() = prefs?.edit()?.clear()?.apply()

    fun isLoggedIn(): Boolean = !getToken().isNullOrEmpty()
}
