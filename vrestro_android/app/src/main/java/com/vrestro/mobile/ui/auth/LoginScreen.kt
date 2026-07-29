package com.vrestro.mobile.ui.auth

import androidx.compose.animation.*
import androidx.compose.foundation.*
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.rounded.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.*
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.vrestro.mobile.ui.theme.*

@Composable
fun LoginScreen(
    onAuthenticated: () -> Unit,
    viewModel: AuthViewModel = viewModel()
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    LaunchedEffect(state) {
        if (state is AuthState.Authenticated) onAuthenticated()
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(Background),
        contentAlignment = Alignment.Center
    ) {
        SingleChildScrollView {
            Column(
                modifier = Modifier
                    .padding(24.dp)
                    .widthIn(max = 420.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                NeumorphicCard {
                    Column(
                        modifier = Modifier.padding(32.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        // Logo
                        Box(
                            modifier = Modifier
                                .size(80.dp)
                                .shadow(8.dp, RoundedCornerShape(20.dp))
                                .background(
                                    Brush.linearGradient(listOf(Primary, PrimaryLight)),
                                    RoundedCornerShape(20.dp)
                                ),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(
                                imageVector = Icons.Rounded.RestaurantMenu,
                                contentDescription = null,
                                tint = Color.White,
                                modifier = Modifier.size(44.dp)
                            )
                        }

                        Spacer(Modifier.height(20.dp))

                        Text(
                            "VRestro Mobile",
                            fontSize = 26.sp,
                            fontWeight = FontWeight.ExtraBold,
                            color = TextPrimary,
                            letterSpacing = 0.5.sp
                        )
                        Text(
                            "Restoran Boshqaruv Tizimi",
                            fontSize = 14.sp,
                            color = TextSecondary,
                            modifier = Modifier.padding(top = 4.dp)
                        )

                        Spacer(Modifier.height(32.dp))

                        when (val s = state) {
                            is AuthState.OtpRequired -> OtpSection(s.userId, s.name, viewModel, state)
                            else -> LoginSection(viewModel, state)
                        }

                        if (state is AuthState.Error) {
                            Spacer(Modifier.height(12.dp))
                            Text(
                                (state as AuthState.Error).message,
                                color = StatusNew,
                                fontSize = 13.sp,
                                textAlign = TextAlign.Center,
                                modifier = Modifier.fillMaxWidth()
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun LoginSection(viewModel: AuthViewModel, state: AuthState) {
    var login by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    val isLoading = state is AuthState.Loading

    NeumorphicTextField(
        value = login,
        onValueChange = { login = it },
        label = "Login",
        hint = "masalan: waiter1",
        icon = Icons.Rounded.Person
    )
    Spacer(Modifier.height(16.dp))
    NeumorphicTextField(
        value = password,
        onValueChange = { password = it },
        label = "Parol",
        hint = "••••••••",
        icon = Icons.Rounded.Lock,
        visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
        trailingIcon = {
            IconButton(onClick = { passwordVisible = !passwordVisible }) {
                Icon(
                    if (passwordVisible) Icons.Rounded.VisibilityOff else Icons.Rounded.Visibility,
                    contentDescription = null, tint = TextSecondary
                )
            }
        }
    )
    Spacer(Modifier.height(28.dp))
    NeumorphicButton(
        text = "Tizimga Kirish",
        icon = Icons.Rounded.Login,
        isLoading = isLoading,
        onClick = { if (!isLoading) viewModel.login(login.trim(), password.trim()) }
    )
}

@Composable
private fun OtpSection(userId: Int, name: String, viewModel: AuthViewModel, state: AuthState) {
    var otp by remember { mutableStateOf("") }
    val isLoading = state is AuthState.Loading

    Text(
        "$name, Telegram orqali yuborilgan 8 xonali kodni kiriting.",
        fontSize = 13.sp, color = TextSecondary, textAlign = TextAlign.Center
    )
    Spacer(Modifier.height(20.dp))
    NeumorphicTextField(
        value = otp,
        onValueChange = { otp = it },
        label = "Tasdiqlash kodi",
        hint = "8 xonali kod",
        icon = Icons.Rounded.Password,
        keyboardType = KeyboardType.Number
    )
    Spacer(Modifier.height(28.dp))
    NeumorphicButton(
        text = "Tasdiqlash",
        icon = Icons.Rounded.CheckCircleOutline,
        isLoading = isLoading,
        onClick = { if (!isLoading) viewModel.submitOtp(userId, otp.trim()) }
    )
}

// ─── Reusable Components ────────────────────────────────

@Composable
fun NeumorphicCard(content: @Composable () -> Unit) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(6.dp, RoundedCornerShape(24.dp), spotColor = ShadowDark)
            .background(Surface, RoundedCornerShape(24.dp))
            .padding(0.dp)
    ) {
        content()
    }
}

@Composable
fun NeumorphicTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    hint: String,
    icon: ImageVector,
    visualTransformation: VisualTransformation = VisualTransformation.None,
    keyboardType: KeyboardType = KeyboardType.Text,
    trailingIcon: (@Composable () -> Unit)? = null
) {
    Column(modifier = Modifier.fillMaxWidth()) {
        Text(label, fontSize = 12.sp, fontWeight = FontWeight.SemiBold,
            color = TextSecondary, modifier = Modifier.padding(start = 4.dp, bottom = 6.dp))
        OutlinedTextField(
            value = value,
            onValueChange = onValueChange,
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text(hint, color = TextSecondary.copy(alpha = 0.5f), fontSize = 14.sp) },
            leadingIcon = { Icon(icon, contentDescription = null, tint = Primary) },
            trailingIcon = trailingIcon,
            visualTransformation = visualTransformation,
            keyboardOptions = KeyboardOptions(keyboardType = keyboardType),
            shape = RoundedCornerShape(14.dp),
            colors = OutlinedTextFieldDefaults.colors(
                focusedBorderColor = Primary,
                unfocusedBorderColor = ShadowDark,
                focusedContainerColor = SurfaceLight,
                unfocusedContainerColor = SurfaceLight,
                cursorColor = Primary
            ),
            singleLine = true
        )
    }
}

@Composable
fun NeumorphicButton(
    text: String,
    icon: ImageVector,
    isLoading: Boolean,
    onClick: () -> Unit
) {
    Button(
        onClick = onClick,
        modifier = Modifier
            .fillMaxWidth()
            .height(52.dp),
        enabled = !isLoading,
        shape = RoundedCornerShape(14.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = Primary,
            contentColor = Color.White
        ),
        elevation = ButtonDefaults.buttonElevation(defaultElevation = 4.dp)
    ) {
        if (isLoading) {
            CircularProgressIndicator(
                modifier = Modifier.size(22.dp),
                color = Color.White,
                strokeWidth = 2.dp
            )
        } else {
            Icon(icon, contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(Modifier.width(8.dp))
            Text(text, fontSize = 15.sp, fontWeight = FontWeight.SemiBold)
        }
    }
}

@Composable
private fun SingleChildScrollView(content: @Composable () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) { content() }
}
