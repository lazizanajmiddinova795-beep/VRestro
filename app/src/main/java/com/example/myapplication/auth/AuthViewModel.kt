package com.example.myapplication.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.myapplication.data.api.RetrofitClient
import com.example.myapplication.data.models.LoginRequest
import com.example.myapplication.data.models.OtpRequest
import com.example.myapplication.data.models.UserModel
import com.example.myapplication.utils.TokenManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class AuthState {
    object Idle    : AuthState()
    object Loading : AuthState()
    data class Authenticated(val user: UserModel, val token: String) : AuthState()
    data class OtpRequired(val userId: Int, val name: String) : AuthState()
    data class Error(val message: String) : AuthState()
}

class AuthViewModel : ViewModel() {
    private val api = RetrofitClient.api

    private val _state = MutableStateFlow<AuthState>(AuthState.Idle)
    val state: StateFlow<AuthState> = _state

    fun checkAuthStatus() {
        if (TokenManager.isLoggedIn()) {
            val user = TokenManager.getUser()
            val token = TokenManager.getToken() ?: ""
            if (user != null) _state.value = AuthState.Authenticated(user, token)
        }
    }

    fun login(login: String, password: String) {
        viewModelScope.launch {
            _state.value = AuthState.Loading
            try {
                val resp = api.login(LoginRequest(login.trim(), password.trim()))
                if (resp.isSuccessful) {
                    val body = resp.body()!!
                    if (body.requiresOtp) {
                        _state.value = AuthState.OtpRequired(
                            userId = body.user?.id ?: 0,
                            name   = body.user?.name ?: ""
                        )
                    } else {
                        val token = body.token ?: ""
                        val user  = body.user ?: return@launch
                        TokenManager.saveToken(token)
                        TokenManager.saveUser(user)
                        _state.value = AuthState.Authenticated(user, token)
                    }
                } else {
                    val msg = resp.errorBody()?.string()?.extractMessage() ?: "Login muvaffaqiyatsiz (${resp.code()})"
                    _state.value = AuthState.Error(msg)
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
                val resp = api.verifyOtp(OtpRequest(userId, otp.trim()))
                if (resp.isSuccessful) {
                    val body  = resp.body()!!
                    val token = body.token ?: ""
                    val user  = body.user ?: return@launch
                    TokenManager.saveToken(token)
                    TokenManager.saveUser(user)
                    _state.value = AuthState.Authenticated(user, token)
                } else {
                    _state.value = AuthState.Error("Kod noto'g'ri yoki muddati o'tgan.")
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

    private fun String.extractMessage(): String? = try {
        org.json.JSONObject(this).optString("message").takeIf { it.isNotEmpty() }
    } catch (_: Exception) { null }
}
