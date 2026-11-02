import 'package:booking_barbershop/core/network/api_client.dart';
import 'package:booking_barbershop/data/models/app_user.dart';
import 'package:dio/dio.dart';

class AuthRepository {
  AuthRepository(this._api);

  final ApiClient _api;

  Future<({AppUser user, String token})> login(String email, String password) async {
    try {
      final res = await _api.post<Map<String, dynamic>>('/auth/login', data: {
        'email': email,
        'password': password,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      return (
        user: AppUser.fromJson(data['user'] as Map<String, dynamic>),
        token: data['token'] as String,
      );
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<({AppUser user, String token})> register({
    required String name,
    required String email,
    required String password,
    required String whatsappNumber,
  }) async {
    try {
      final res = await _api.post<Map<String, dynamic>>('/auth/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'whatsapp_number': whatsappNumber,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      return (
        user: AppUser.fromJson(data['user'] as Map<String, dynamic>),
        token: data['token'] as String,
      );
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<({AppUser user, String token})> googleSignIn(String idToken) async {
    try {
      final res = await _api.post<Map<String, dynamic>>('/auth/google', data: {
        'id_token': idToken,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      return (
        user: AppUser.fromJson(data['user'] as Map<String, dynamic>),
        token: data['token'] as String,
      );
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } on DioException {
      // ignore
    }
  }

  Future<AppUser> fetchProfile() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/profile');
      final data = res.data!['data'] as Map<String, dynamic>;
      return AppUser.fromJson(data);
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<AppUser> updateProfile({String? name, String? whatsappNumber}) async {
    try {
      final res = await _api.put<Map<String, dynamic>>('/profile', data: {
        if (name != null) 'name': name,
        if (whatsappNumber != null) 'whatsapp_number': whatsappNumber,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      return AppUser.fromJson(data);
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }
}
