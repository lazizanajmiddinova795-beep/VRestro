import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';

class NeumorphicTheme {
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: AppColors.background,
      primaryColor: AppColors.primary,
      colorScheme: const ColorScheme.light(
        primary: AppColors.primary,
        secondary: AppColors.secondary,
        surface: AppColors.cardBg,
        background: AppColors.background,
        error: AppColors.statusNew,
      ),
      textTheme: GoogleFonts.interTextTheme().copyWith(
        headlineLarge: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontWeight: FontWeight.bold,
          fontSize: 24,
        ),
        titleLarge: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontWeight: FontWeight.w700,
          fontSize: 18,
        ),
        titleMedium: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontWeight: FontWeight.w600,
          fontSize: 15,
        ),
        bodyLarge: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontSize: 14,
        ),
        bodyMedium: GoogleFonts.inter(
          color: AppColors.textSecondary,
          fontSize: 13,
        ),
      ),
    );
  }

  // Soft Neumorphism Container Decoration (Extruded state)
  static BoxDecoration neumorphicBox({
    double borderRadius = 16,
    Color color = AppColors.cardBg,
    bool isSelected = false,
  }) {
    return BoxDecoration(
      color: isSelected ? AppColors.primary.withOpacity(0.08) : color,
      borderRadius: BorderRadius.circular(borderRadius),
      border: isSelected ? Border.all(color: AppColors.primary, width: 1.5) : null,
      boxShadow: isSelected
          ? [
              BoxShadow(
                color: AppColors.primary.withOpacity(0.2),
                blurRadius: 10,
                offset: const Offset(2, 4),
              )
            ]
          : const [
              BoxShadow(
                color: AppColors.lightShadow,
                offset: Offset(-5, -5),
                blurRadius: 10,
                spreadRadius: 1,
              ),
              BoxShadow(
                color: AppColors.darkShadow,
                offset: Offset(5, 5),
                blurRadius: 10,
                spreadRadius: 1,
              ),
            ],
    );
  }

  // Soft Neumorphism Sunken / Pressed Decoration (Pressed state)
  static BoxDecoration neumorphicPressedBox({
    double borderRadius = 16,
    Color color = AppColors.cardBg,
  }) {
    return BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(borderRadius),
      boxShadow: const [
        BoxShadow(
          color: AppColors.darkShadow,
          offset: Offset(-3, -3),
          blurRadius: 6,
          spreadRadius: 0,
        ),
        BoxShadow(
          color: AppColors.lightShadow,
          offset: Offset(3, 3),
          blurRadius: 6,
          spreadRadius: 0,
        ),
      ],
    );
  }
}
