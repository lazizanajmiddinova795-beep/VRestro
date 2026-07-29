package com.vrestro.mobile.ui.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.google.gson.Gson
import com.vrestro.mobile.data.api.RetrofitClient
import com.vrestro.mobile.data.models.LoginRequest
import com.vrestro.mobile.data.models.OtpRequest
import com.vrestro.mobile.data.models.UserModel
import com.vrestro.mobile.utils.TokenManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class AuthState {
    object Idle : AuthState()
    object Loading : AuthState()
    data class Authenticated(val user: UserModel, val token: String) : AuthState()
    data class OtpRequired(val userId: Int, val name: String) : AuthState()
    data class Error(val message: String) : AuthState()
}

class AuthViewModel : ViewModel() {
    private val api = RetrofitClient.api
    private val gson = Gson()

    private val _state = MutableStateFlow<AuthState>(AuthState.Idle)
    val state: StateFlow<AuthState> = _state

    fun checkAuthStatus() {
        viewModelScope.launch {
            if (TokenManager.isLoggedIn()) {
                val userJson = TokenManager.getUserJson()
                val token = TokenManager.getToken() ?: ""
                if (!userJson.isNullOrEmpty()) {
                    val user = gson.fromJson(userJson, UserModel::class.java)
                    _state.value = AuthState.Authenticated(user, token)
                } else {
                    _state.value = AuthState.Idle
                }
            } else {
                _state.value = AuthState.Idle
            }
        }
    }

    fun login(login: String, password: String) {
        viewModelScope.launch {
            _state.value = AuthState.Loading
            try {
                val response = api.login(LoginRequest(login, password))
                if (response.isSuccessful) {
                    val body = response.body()
                    if (body?.requiresOtp == true) {
                        val userId = body.user?.id ?: 0
                        val name = body.user?.name ?: ""
                        _state.value = AuthState.OtpRequired(userId, name)
                    } else {
                        val token = body?.token ?: ""
                        val user = body?.user
                        if (user != null && token.isNotEmpty()) {
                            TokenManager.saveToken(token)
                            TokenManager.saveUser(gson.toJson(user), user.role)
                            _state.value = AuthState.Authenticated(user, token)
                        } else {
                            _state.value = AuthState.Error("Login muvaffaqiyatsiz.")
                        }
                    }
                } else {
                    val errorBody = response.errorBody()?.string()
                    _state.value = AuthState.Error(
                        extractMessage(errorBody) ?: "Login muvaffaqiyatsiz. (${response.code()})"
                    )
                }
            } catch (e: Exception) {
                _state.value = AuthState.Error("Tarmoq xatosi: ${e.localizedMessage}")
            }
        }
    }

    fun submitOtp(userId: Int, otp: String) {
        viewModelScope.launch {
            _state.value = AuthState.Loading
            try {
                val response = api.verifyOtp(OtpRequest(userId, otp))
                if (response.isSuccessful) {
                    val body = response.body()
                    val token = body?.token ?: ""
                    val user = body?.user
                    if (user != null && token.isNotEmpty()) {
                        TokenManager.saveToken(token)
                        TokenManager.saveUser(gson.toJson(user), user.role)
                        _state.value = AuthState.Authenticated(user, token)
                    } else {
                        _state.value = AuthState.Error("OTP tasdiqlash muvaffaqiyatsiz.")
                    }
                } else {
                    val errorBody = response.errorBody()?.string()
                    _state.value = AuthState.Error(
                        extractMessage(errorBody) ?: "Kod noto'g'ri yoki muddati o'tgan."
                    )
                }
            } catch (e: Exception) {
                _state.value = AuthState.Error("Tarmoq xatosi: ${e.localizedMessage}")
            }
        }
    }

    fun logout() {
        TokenManager.clear()
        _state.value = AuthState.Idle
    }

    private fun extractMessage(json: String?): String? {
        return try {
            val map = gson.fromJson(json, Map::class.java)
            map["message"] as? String
        } catch (e: Exception) { null }
    }
}
