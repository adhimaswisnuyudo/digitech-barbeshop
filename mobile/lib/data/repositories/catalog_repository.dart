import 'package:booking_barbershop/core/network/api_client.dart';
import 'package:booking_barbershop/data/models/booking.dart';
import 'package:booking_barbershop/data/models/branch.dart';
import 'package:booking_barbershop/data/models/shop_service.dart';
import 'package:dio/dio.dart';

class CatalogRepository {
  CatalogRepository(this._api);

  final ApiClient _api;

  Future<List<Branch>> fetchBranches() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/branches');
      final list = res.data!['data'] as List<dynamic>;
      return list.map((e) => Branch.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<Branch> fetchBranch(String id) async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/branches/$id');
      return Branch.fromJson(res.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<List<ShopService>> fetchServices() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/services');
      final list = res.data!['data'] as List<dynamic>;
      return list.map((e) => ShopService.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<List<BookingSlot>> fetchAvailability({
    required String branchId,
    required String serviceId,
    required String date,
    String? barberId,
  }) async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/availability', query: {
        'branch_id': branchId,
        'service_id': serviceId,
        'date': date,
        if (barberId != null) 'barber_id': barberId,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      final slots = data['slots'] as List<dynamic>;
      return slots.map((e) => BookingSlot.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }
}

class BookingRepository {
  BookingRepository(this._api);

  final ApiClient _api;

  Future<Booking> createBooking({
    required String branchId,
    required String serviceId,
    required DateTime startAt,
    String? barberId,
    String? notes,
  }) async {
    try {
      final res = await _api.post<Map<String, dynamic>>('/bookings', data: {
        'branch_id': branchId,
        'service_id': serviceId,
        'start_at': startAt.toIso8601String(),
        if (barberId != null) 'barber_id': barberId,
        if (notes != null) 'notes': notes,
      });
      return Booking.fromJson(res.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<List<Booking>> fetchBookings({String? date}) async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/bookings', query: {
        if (date != null) 'date': date,
      });
      final data = res.data!['data'] as Map<String, dynamic>;
      final items = data['items'] as List<dynamic>;
      return items.map((e) => Booking.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<void> cancelBooking(String id) async {
    try {
      await _api.post('/bookings/$id/cancel');
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<void> updateStatus(String id, String status) async {
    try {
      await _api.patch('/bookings/$id/status', data: {'status': status});
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }
}

class AdminRepository {
  AdminRepository(this._api);

  final ApiClient _api;

  Future<Map<String, dynamic>> dashboard() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/admin/dashboard');
      return res.data!['data'] as Map<String, dynamic>;
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<List<ShopService>> fetchServices() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/admin/services');
      final list = res.data!['data'] as List<dynamic>;
      return list
          .map((e) => ShopService.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<ShopService> updateService(
    String id, {
    bool? isActive,
    String? name,
    int? price,
    int? durationMinutes,
  }) async {
    try {
      final res = await _api.put<Map<String, dynamic>>('/admin/services/$id', data: {
        if (isActive != null) 'is_active': isActive,
        if (name != null) 'name': name,
        if (price != null) 'price': price,
        if (durationMinutes != null) 'duration_minutes': durationMinutes,
      });
      return ShopService.fromJson(res.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<List<Branch>> fetchBranches() async {
    try {
      final res = await _api.get<Map<String, dynamic>>('/admin/branches');
      final list = res.data!['data'] as List<dynamic>;
      return list.map((e) => Branch.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }

  Future<void> adminCancelBooking(String id) async {
    try {
      await _api.patch('/bookings/$id/status', data: {'status': 'cancelled'});
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }
}
