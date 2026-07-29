package com.example.myapplication

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.myapplication.auth.AuthState
import com.example.myapplication.auth.AuthViewModel
import com.example.myapplication.auth.LoginScreen
import com.example.myapplication.cashier.CashierScreen
import com.example.myapplication.kitchen.KitchenScreen
import com.example.myapplication.ui.theme.Background
import com.example.myapplication.ui.theme.Primary
import com.example.myapplication.waiter.WaiterScreen

@Composable
fun VRestroApp() {
    val authVm: AuthViewModel = viewModel()
    val state by authVm.state.collectAsStateWithLifecycle()

    LaunchedEffect(Unit) { authVm.checkAuthStatus() }

    when (val s = state) {
        is AuthState.Idle,
        is AuthState.Error,
        is AuthState.OtpRequired -> {
            LoginScreen(viewModel = authVm)
        }

        is AuthState.Loading -> {
            Box(
                Modifier.fillMaxSize().background(Background),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(color = Primary)
            }
        }

        is AuthState.Authenticated -> {
            val role = s.user.role.lowercase()
            when {
                role.contains("cashier") || role.contains("kassir") ->
                    CashierScreen(onLogout = { authVm.logout() })

                role.contains("chef") || role.contains("oshpaz") ||
                role.contains("kitchen") || role.contains("cook") ->
                    KitchenScreen(onLogout = { authVm.logout() })

                else ->
                    // Default: Waiter / ofitsiant
                    WaiterScreen(onLogout = { authVm.logout() })
            }
        }
    }
}
