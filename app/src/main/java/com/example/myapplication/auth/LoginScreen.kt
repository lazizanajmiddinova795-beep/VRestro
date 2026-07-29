package com.example.myapplication.auth

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.slideInVertically
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.rounded.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.*
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.myapplication.ui.theme.*

@Composable
fun LoginScreen(viewModel: AuthViewModel) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(Background),
        contentAlignment = Alignment.Center
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            AnimatedVisibility(visible = true, enter = fadeIn() + slideInVertically { it / 2 }) {
                Column(
                    modifier = Modifier
                        .padding(24.dp)
                        .widthIn(max = 420.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    LoginCard(state = state, viewModel = viewModel)
                }
            }
        }
    }
}

@Composable
private fun LoginCard(state: AuthState, viewModel: AuthViewModel) {
    // Neumorphic card
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(12.dp, RoundedCornerShape(28.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(28.dp))
    ) {
        Column(
            modifier = Modifier.padding(32.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            // Logo
            Box(
                modifier = Modifier
                    .size(84.dp)
                    .shadow(8.dp, RoundedCornerShape(22.dp), spotColor = Primary.copy(0.4f))
                    .background(
                        Brush.linearGradient(listOf(Primary, PrimaryLight)),
                        RoundedCornerShape(22.dp)
                    ),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.Rounded.RestaurantMenu, null, tint = Color.White, modifier = Modifier.size(46.dp))
            }

            Spacer(Modifier.height(20.dp))
            Text("VRestro Mobile", fontSize = 26.sp, fontWeight = FontWeight.ExtraBold, color = TextPrimary, letterSpacing = 0.3.sp)
            Text("Restoran Boshqaruv Tizimi", fontSize = 14.sp, color = TextSecondary, modifier = Modifier.padding(top = 4.dp))
            Spacer(Modifier.height(32.dp))

            when (val s = state) {
                is AuthState.OtpRequired -> OtpFields(s.userId, s.name, state, viewModel)
                else -> LoginFields(state, viewModel)
            }

            if (state is AuthState.Error) {
                Spacer(Modifier.height(16.dp))
                Surface(color = StatusNew.copy(0.1f), shape = RoundedCornerShape(12.dp)) {
                    Row(Modifier.padding(12.dp), verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Rounded.ErrorOutline, null, tint = StatusNew, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(8.dp))
                        Text((state as AuthState.Error).message, color = StatusNew, fontSize = 13.sp)
                    }
                }
            }
        }
    }
}

@Composable
private fun LoginFields(state: AuthState, viewModel: AuthViewModel) {
    var login    by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var pwVisible by remember { mutableStateOf(false) }
    val loading = state is AuthState.Loading

    VRestroTextField(value = login, onChange = { login = it }, label = "Login",
        hint = "masalan: waiter1", icon = Icons.Rounded.Person)
    Spacer(Modifier.height(16.dp))
    VRestroTextField(
        value = password, onChange = { password = it }, label = "Parol",
        hint = "••••••••", icon = Icons.Rounded.Lock,
        visual = if (pwVisible) VisualTransformation.None else PasswordVisualTransformation(),
        trailing = {
            IconButton(onClick = { pwVisible = !pwVisible }) {
                Icon(if (pwVisible) Icons.Rounded.VisibilityOff else Icons.Rounded.Visibility, null, tint = TextSecondary)
            }
        }
    )
    Spacer(Modifier.height(28.dp))
    VRestroButton(text = "Tizimga Kirish", icon = Icons.Rounded.Login, loading = loading) {
        if (!loading) viewModel.login(login, password)
    }
}

@Composable
private fun OtpFields(userId: Int, name: String, state: AuthState, viewModel: AuthViewModel) {
    var otp by remember { mutableStateOf("") }
    val loading = state is AuthState.Loading

    Surface(color = Secondary.copy(0.1f), shape = RoundedCornerShape(12.dp)) {
        Text(
            "$name, Telegram orqali kelgan 8 xonali kodni kiriting.",
            fontSize = 13.sp, color = TextPrimary, textAlign = TextAlign.Center,
            modifier = Modifier.padding(12.dp)
        )
    }
    Spacer(Modifier.height(20.dp))
    VRestroTextField(
        value = otp, onChange = { otp = it }, label = "Tasdiqlash kodi",
        hint = "8 xonali kod", icon = Icons.Rounded.Password,
        keyboardType = KeyboardType.NumberPassword
    )
    Spacer(Modifier.height(28.dp))
    VRestroButton(text = "Tasdiqlash", icon = Icons.Rounded.CheckCircleOutline, loading = loading) {
        if (!loading) viewModel.submitOtp(userId, otp)
    }
}

// ─── Shared UI Components ──────────────────────────────────────────────────

@Composable
fun VRestroTextField(
    value: String,
    onChange: (String) -> Unit,
    label: String,
    hint: String,
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    visual: VisualTransformation = VisualTransformation.None,
    keyboardType: KeyboardType = KeyboardType.Text,
    trailing: (@Composable () -> Unit)? = null
) {
    Column(Modifier.fillMaxWidth()) {
        Text(label, fontSize = 12.sp, fontWeight = FontWeight.SemiBold,
            color = TextSecondary, modifier = Modifier.padding(start = 4.dp, bottom = 6.dp))
        OutlinedTextField(
            value = value, onValueChange = onChange,
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text(hint, color = TextLight, fontSize = 14.sp) },
            leadingIcon = { Icon(icon, null, tint = Primary) },
            trailingIcon = trailing,
            visualTransformation = visual,
            keyboardOptions = KeyboardOptions(keyboardType = keyboardType),
            shape = RoundedCornerShape(16.dp),
            colors = OutlinedTextFieldDefaults.colors(
                focusedBorderColor = Primary,
                unfocusedBorderColor = ShadowDark,
                focusedContainerColor = SurfaceCard,
                unfocusedContainerColor = SurfaceCard,
                cursorColor = Primary
            ),
            singleLine = true
        )
    }
}

@Composable
fun VRestroButton(
    text: String,
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    loading: Boolean,
    onClick: () -> Unit
) {
    Button(
        onClick = onClick,
        modifier = Modifier.fillMaxWidth().height(54.dp),
        enabled = !loading,
        shape = RoundedCornerShape(16.dp),
        colors = ButtonDefaults.buttonColors(containerColor = Primary, contentColor = Color.White),
        elevation = ButtonDefaults.buttonElevation(defaultElevation = 6.dp)
    ) {
        if (loading) {
            CircularProgressIndicator(Modifier.size(22.dp), color = Color.White, strokeWidth = 2.dp)
        } else {
            Icon(icon, null, modifier = Modifier.size(20.dp))
            Spacer(Modifier.width(8.dp))
            Text(text, fontWeight = FontWeight.SemiBold, fontSize = 15.sp, letterSpacing = 0.3.sp)
        }
    }
}
