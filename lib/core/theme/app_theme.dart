import 'package:flutter/material.dart';

class AppTheme {
  static const Color primaryColor = Color(0xFFD94368);
  static const Color primaryHover = Color(0xFFBB3156);
  static const Color accentColor = Color(0xFFED8B4B);
  static const Color successColor = Color(0xFF10B981);
  static const Color warningColor = Color(0xFFF59E0B);
  static const Color dangerColor = Color(0xFFEF4444);
  static const Color infoColor = Color(0xFFDF7951);

  static const Color lightBgPrimary = Color(0xFFFFF8F5);
  static const Color lightBgSecondary = Color(0xFFFFFFFF);
  static const Color lightBgSidebar = Color(0xFFFFF0E9);
  static const Color lightTextPrimary = Color(0xFF38232D);
  static const Color lightTextSecondary = Color(0xFF796873);
  static const Color lightBorderColor = Color(0xFFF0E1DF);

  static const Color darkBgPrimary = Color(0xFF0B0F19);
  static const Color darkBgSecondary = Color(0xFF111827);
  static const Color darkBgSidebar = Color(0xFF030712);
  static const Color darkTextPrimary = Color(0xFFF3F4F6);
  static const Color darkTextSecondary = Color(0xFF9CA3AF);
  static const Color darkBorderColor = Color(0xFF1F2937);

  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    scaffoldBackgroundColor: lightBgPrimary,
    colorScheme: const ColorScheme.light(
      primary: primaryColor,
      secondary: accentColor,
      error: dangerColor,
      surface: lightBgSecondary,
    ),
    cardTheme: CardTheme(
      color: lightBgSecondary,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: lightBorderColor),
      ),
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: lightBgSecondary,
      foregroundColor: lightTextPrimary,
      elevation: 0,
      centerTitle: false,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: lightBgPrimary,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: lightBorderColor),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: lightBorderColor),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: primaryColor, width: 2),
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: primaryColor,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        side: const BorderSide(color: primaryColor),
      ),
    ),
  );

  static ThemeData darkTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    scaffoldBackgroundColor: darkBgPrimary,
    colorScheme: const ColorScheme.dark(
      primary: primaryColor,
      secondary: infoColor,
      error: dangerColor,
      surface: darkBgSecondary,
    ),
    cardTheme: CardTheme(
      color: darkBgSecondary,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: darkBorderColor),
      ),
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: darkBgSecondary,
      foregroundColor: darkTextPrimary,
      elevation: 0,
      centerTitle: false,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: darkBgPrimary,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: darkBorderColor),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: darkBorderColor),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: primaryColor, width: 2),
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: primaryColor,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        side: const BorderSide(color: primaryColor),
      ),
    ),
  );
}
