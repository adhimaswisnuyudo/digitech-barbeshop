import 'package:booking_barbershop/core/config/app_config.dart';
import 'package:dio/dio.dart';

class ApiClient {
  ApiClient({Dio? dio, String? Function()? tokenProvider})
      : _tokenProvider = tokenProvider,
        _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 15),
                receiveTimeout: const Duration(seconds: 15),
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = _tokenProvider?.call();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
      ),
    );
  }

  final Dio _dio;
  final String? Function()? _tokenProvider;

  Dio get dio => _dio;

  Future<Response<T>> get<T>(String path, {Map<String, dynamic>? query}) {
    return _dio.get<T>(path, queryParameters: query);
  }

  Future<Response<T>> post<T>(String path, {Object? data}) {
    return _dio.post<T>(path, data: data);
  }

  Future<Response<T>> put<T>(String path, {Object? data}) {
    return _dio.put<T>(path, data: data);
  }

  Future<Response<T>> patch<T>(String path, {Object? data}) {
    return _dio.patch<T>(path, data: data);
  }

  Future<Response<T>> delete<T>(String path) {
    return _dio.delete<T>(path);
  }
}

class ApiException implements Exception {
  ApiException(this.message, {this.code, this.statusCode, this.errors});

  final String message;
  final String? code;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  static ApiException fromDio(DioException e) {
    final data = e.response?.data;
    if (data is Map<String, dynamic>) {
      return ApiException(
        data['message']?.toString() ?? 'Terjadi kesalahan',
        code: data['code']?.toString(),
        statusCode: e.response?.statusCode,
        errors: data['errors'] is Map<String, dynamic>
            ? data['errors'] as Map<String, dynamic>
            : null,
      );
    }
    return ApiException(e.message ?? 'Koneksi gagal', statusCode: e.response?.statusCode);
  }
}
