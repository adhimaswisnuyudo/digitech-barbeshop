import 'dart:io';

import 'package:booking_barbershop/core/config/app_config.dart';
import 'package:booking_barbershop/core/network/api_client.dart';
import 'package:dio/dio.dart';

/// Registrasi FCM token ke backend.
///
/// Setelah Firebase (`firebase_core` / `firebase_messaging`) dan file
/// `google-services.json` / `GoogleService-Info.plist` siap, panggil
/// [syncFcmToken] dari listener `FirebaseMessaging.onTokenRefresh` dan
/// setelah login.
class PushRegistration {
  PushRegistration(this._api);

  final ApiClient _api;

  Future<void> syncFcmToken(String token) async {
    if (token.isEmpty) return;

    final platform = Platform.isIOS
        ? 'ios'
        : Platform.isAndroid
            ? 'android'
            : null;
    if (platform == null) return;

    try {
      await _api.post('/devices', data: {
        'fcm_token': token,
        'platform': platform,
      });
    } on DioException {
      // Push opsional; gagal registrasi tidak memblokir app.
    }
  }

  static bool get isConfigured => AppConfig.firebaseProjectId.isNotEmpty;
}
