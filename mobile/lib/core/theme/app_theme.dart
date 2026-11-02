import 'package:booking_barbershop/core/theme/app_colors.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

ThemeData buildAppTheme() {
  const primary = AppColors.primary;
  const secondary = AppColors.secondary;
  final base = ThemeData.light(useMaterial3: true);
  final textTheme = GoogleFonts.plusJakartaSansTextTheme(base.textTheme);

  return base.copyWith(
    scaffoldBackgroundColor: AppColors.background,
    textTheme: textTheme,
    colorScheme: const ColorScheme.light(
      primary: primary,
      onPrimary: AppColors.onPrimary,
      secondary: secondary,
      onSecondary: AppColors.onPrimary,
      surface: AppColors.surface,
      onSurface: AppColors.onSurface,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: primary,
      foregroundColor: AppColors.onPrimary,
      elevation: 0,
      centerTitle: true,
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: primary,
        foregroundColor: AppColors.onPrimary,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: secondary,
        side: const BorderSide(color: secondary),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.surface,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    ),
  );
}
