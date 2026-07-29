package com.example.myapplication.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val VRestroColorScheme = lightColorScheme(
    primary          = Primary,
    onPrimary        = SurfaceCard,
    primaryContainer = PrimaryLight,
    secondary        = Secondary,
    background       = Background,
    surface          = Surface,
    onBackground     = TextPrimary,
    onSurface        = TextPrimary,
    error            = StatusNew,
)

@Composable
fun VRestroTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = VRestroColorScheme,
        typography  = Typography,
        content     = content
    )
}
