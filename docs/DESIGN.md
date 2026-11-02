# Design — Booking Barbershop

Acuan: [PRD.md](./PRD.md)

Palet warna dan tipografi resmi aplikasi Flutter (customer, staff, admin — satu theme).

---

## Tipografi

| Token | Font | Catatan |
|-------|------|---------|
| **Font family** | **Plus Jakarta Sans** | Sering disebut “Jakarta Sans”; satu family untuk seluruh UI |

### Bobot yang dipakai (MVP)

| Penggunaan | Weight |
|------------|--------|
| Headline / AppBar title | **700** Bold |
| Subtitle / label tombol | **600** SemiBold |
| Body | **400** Regular |
| Caption / hint | **400** Regular (ukuran lebih kecil) |

### Flutter

- **Rekomendasi:** package [`google_fonts`](https://pub.dev/packages/google_fonts) → `GoogleFonts.plusJakartaSansTextTheme()` + override di `ThemeData`.
- Alternatif: bundel file `.ttf` (Regular, Medium, SemiBold, Bold) di `assets/fonts/` dan daftar di `pubspec.yaml` dengan `family: Plus Jakarta Sans`.

```dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

TextTheme buildAppTextTheme(TextTheme base) {
  return GoogleFonts.plusJakartaSansTextTheme(base).copyWith(
    headlineMedium: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700),
    titleMedium: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w600),
    bodyMedium: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w400),
  );
}
```

Lisensi: [SIL Open Font License](https://fonts.google.com/specimen/Plus+Jakarta+Sans) (cocok untuk app komersial).

---

## Warna brand

| Token | Hex | Flutter `Color` | Penggunaan |
|-------|-----|-----------------|------------|
| **Primary** | `#404040` | `Color(0xFF404040)` | AppBar, tombol utama (FilledButton), FAB, link/teks emphasis |
| **Secondary** | `#5E5E5E` | `Color(0xFF5E5E5E)` | Tombol sekunder (Outlined/Text), chip, ikon sekunder, divider accent |

### Rekomendasi turunan (implementasi)

| Token | Hex | Catatan |
|-------|-----|---------|
| `onPrimary` | `#FFFFFF` | Teks/ikon di atas primary |
| `onSecondary` | `#FFFFFF` | Teks di tombol secondary filled (jika dipakai) |
| `surface` | `#FFFFFF` | Background halaman |
| `background` | `#F5F5F5` | Background alternatif / scaffold |
| `onSurface` | `#212121` | Body text utama |
| `onSurfaceVariant` | `#5E5E5E` | Subtitle — selaras secondary |
| `outline` | `#BDBDBD` | Border input, divider netral |

Primary dan secondary sengaja **netral abu-abu** — cocok untuk brand barbershop tanpa warna aksen terpisah di MVP. Warna status (success/error) tetap Material standar atau ditambahkan later.

---

## Flutter `ThemeData` (referensi)

```dart
import 'package:flutter/material.dart';

abstract final class AppColors {
  static const primary = Color(0xFF404040);
  static const secondary = Color(0xFF5E5E5E);
  static const onPrimary = Color(0xFFFFFFFF);
  static const surface = Color(0xFFFFFFFF);
  static const background = Color(0xFFF5F5F5);
}

ThemeData buildAppTheme() {
  const primary = AppColors.primary;
  const secondary = AppColors.secondary;

  return ThemeData(
    useMaterial3: true,
    textTheme: buildAppTextTheme(ThemeData.light().textTheme),
    colorScheme: ColorScheme.light(
      primary: primary,
      onPrimary: AppColors.onPrimary,
      secondary: secondary,
      onSecondary: AppColors.onPrimary,
      surface: AppColors.surface,
      onSurface: const Color(0xFF212121),
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: primary,
      foregroundColor: AppColors.onPrimary,
      elevation: 0,
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: primary,
        foregroundColor: AppColors.onPrimary,
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: secondary,
        side: const BorderSide(color: secondary),
      ),
    ),
  );
}
```

---

## Checklist implementasi UI

- [ ] `AppColors` / `theme.dart` di `mobile/`
- [ ] **Plus Jakarta Sans** via `google_fonts` atau assets font
- [ ] Semua layar memakai `Theme.of(context)` — hindari hardcode hex di widget
- [ ] Kontras teks tombol primary ≥ WCAG AA (putih on `#404040` ✓)

---

## Riwayat

| Tanggal | Perubahan |
|---------|-----------|
| 2025-11-02 | Primary `#404040`, Secondary `#5E5E5E` |
| 2025-11-02 | Font **Plus Jakarta Sans** |
