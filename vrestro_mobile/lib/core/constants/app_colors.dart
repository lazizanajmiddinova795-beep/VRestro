import 'package:flutter/material.dart';

class AppColors {
  // Base Neumorphic Light background & shadows
  static const Color background = Color(0xFFF0F3F8);
  static const Color cardBg = Color(0xFFF0F3F8);
  static const Color lightShadow = Color(0xFFFFFFFF);
  static const Color darkShadow = Color(0xFFD1D9E6);
  static const Color insetDarkShadow = Color(0xFFC3CBD9);

  // Corporate Primary Accent Colors
  static const Color primary = Color(0xFF4F46E5); // Indigo corporate
  static const Color primaryLight = Color(0xFF6366F1);
  static const Color secondary = Color(0xFF0EA5E9); // Sky blue
  static const Color accent = Color(0xFFF59E0B); // Warm amber

  // Status Badge Colors
  static const Color statusNew = Color(0xFFEF4444); // Red pulse
  static const Color statusCooking = Color(0xFFF59E0B); // Amber
  static const Color statusReady = Color(0xFF10B981); // Emerald
  static const Color statusDelivered = Color(0xFF64748B); // Slate
  static const Color statusPaid = Color(0xFF059669); // Dark Emerald

  // Text colors
  static const Color textPrimary = Color(0xFF1E293B); // Dark Slate
  static const Color textSecondary = Color(0xFF64748B); // Medium Slate
  static const Color textLight = Color(0xFF94A3B8); // Light Slate

  // Table status colors
  static const Color tableAvailable = Color(0xFF10B981);
  static const Color tableOccupied = Color(0xFFEF4444);
  static const Color tableReserved = Color(0xFFF59E0B);
}
