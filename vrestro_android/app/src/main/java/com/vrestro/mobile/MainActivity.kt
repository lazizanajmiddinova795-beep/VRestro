package com.vrestro.mobile

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.vrestro.mobile.ui.auth.AuthState
import com.vrestro.mobile.ui.auth.AuthViewModel
import com.vrestro.mobile.ui.auth.LoginScreen
import com.vrestro.mobile.ui.cashier.CashierScreen
import com.vrestro.mobile.ui.kitchen.KitchenScreen
import com.vrestro.mobile.ui.theme.Background
import com.vrestro.mobile.ui.theme.Primary
import com.vrestro.mobile.ui.theme.VRestroTheme
import com.vrestro.mobile.ui.waiter.WaiterScreen
import com.vrestro.mobile.utils.TokenManager

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        TokenManager.init(applicationContext)
        enableEdgeToEdge()
        setContent {
            VRestroTheme {
                VRestroApp()
            }
        }
    }
}

@Composable
fun VRestroApp() {
    val authViewModel: AuthViewModel = viewModel()
    val state by authViewModel.state.collectAsStateWithLifecycle()

    LaunchedEffect(Unit) {
        authViewModel.checkAuthStatus()
    }

    when (val s = state) {
        is AuthState.Idle, is AuthState.Error -> {
            LoginScreen(
                onAuthenticated = { /* State will auto-update */ },
                viewModel = authViewModel
            )
        }
        is AuthState.Loading -> {
            Box(
                Modifier
                    .fillMaxSize()
                    .background(Background),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(color = Primary)
            }
        }
        is AuthState.OtpRequired -> {
            LoginScreen(
                onAuthenticated = { /* State will auto-update */ },
                viewModel = authViewModel
            )
        }
        is AuthState.Authenticated -> {
            val role = s.user.role.lowercase()
            when {
                role.contains("cashier") || role.contains("kassir") -> {
                    CashierScreen(onLogout = { authViewModel.logout() })
                }
                role.contains("chef") || role.contains("oshpaz") || role.contains("kitchen") -> {
                    KitchenScreen(onLogout = { authViewModel.logout() })
                }
                else -> {
                    // Default: Waiter
                    WaiterScreen(onLogout = { authViewModel.logout() })
                }
            }
        }
    }
}
