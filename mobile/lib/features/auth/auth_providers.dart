import 'package:booking_barbershop/core/config/app_config.dart';
import 'package:booking_barbershop/core/network/api_client.dart';
import 'package:booking_barbershop/core/storage/token_storage.dart';
import 'package:booking_barbershop/data/models/app_user.dart';
import 'package:booking_barbershop/data/repositories/auth_repository.dart';
import 'package:booking_barbershop/data/repositories/catalog_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_sign_in/google_sign_in.dart';

final tokenStorageProvider = Provider((ref) => TokenStorage());

final authTokenProvider = StateProvider<String?>((ref) => null);

final apiClientProvider = Provider((ref) {
  final token = ref.watch(authTokenProvider);
  return ApiClient(tokenProvider: () => token);
});

final authRepositoryProvider = Provider(
  (ref) => AuthRepository(ref.watch(apiClientProvider)),
);

final catalogRepositoryProvider = Provider((ref) {
  final api = ref.watch(apiClientProvider);
  return (
    catalog: CatalogRepository(api),
    booking: BookingRepository(api),
    admin: AdminRepository(api),
  );
});

final googleSignInProvider = Provider<GoogleSignIn>(
  (ref) => GoogleSignIn(
    serverClientId: AppConfig.googleWebClientId.isEmpty
        ? null
        : AppConfig.googleWebClientId,
  ),
);

class AuthState {
  const AuthState({this.user, this.loading = false});

  final AppUser? user;
  final bool loading;

  AuthState copyWith({AppUser? user, bool? loading}) {
    return AuthState(
      user: user ?? this.user,
      loading: loading ?? this.loading,
    );
  }
}

class AuthController extends StateNotifier<AsyncValue<AuthState>> {
  AuthController(this._ref) : super(const AsyncValue.data(AuthState())) {
    bootstrap();
  }

  final Ref _ref;

  AuthRepository get _auth => _ref.read(authRepositoryProvider);
  TokenStorage get _storage => _ref.read(tokenStorageProvider);

  Future<void> bootstrap() async {
    state = const AsyncValue.loading();
    try {
      final token = await _storage.readToken();
      if (token == null) {
        state = const AsyncValue.data(AuthState());
        return;
      }
      _ref.read(authTokenProvider.notifier).state = token;
      final user = await _auth.fetchProfile();
      state = AsyncValue.data(AuthState(user: user));
    } catch (_) {
      await _storage.clear();
      _ref.read(authTokenProvider.notifier).state = null;
      state = const AsyncValue.data(AuthState());
    }
  }

  Future<void> _persistSession(AppUser user, String token) async {
    await _storage.saveToken(token);
    _ref.read(authTokenProvider.notifier).state = token;
    state = AsyncValue.data(AuthState(user: user));
  }

  Future<void> login(String email, String password) async {
    state = AsyncValue.data(AuthState(user: state.value?.user, loading: true));
    try {
      final result = await _auth.login(email, password);
      await _persistSession(result.user, result.token);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
      rethrow;
    } finally {
      if (state.hasError == false) {
        state = AsyncValue.data(AuthState(user: state.value!.user));
      }
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String whatsapp,
  }) async {
    state = AsyncValue.data(AuthState(user: state.value?.user, loading: true));
    try {
      final result = await _auth.register(
        name: name,
        email: email,
        password: password,
        whatsappNumber: whatsapp,
      );
      await _persistSession(result.user, result.token);
    } finally {
      if (!state.hasError) {
        state = AsyncValue.data(AuthState(user: state.value!.user));
      }
    }
  }

  Future<void> googleLogin() async {
    final google = _ref.read(googleSignInProvider);
    final account = await google.signIn();
    if (account == null) return;
    final auth = await account.authentication;
    final idToken = auth.idToken;
    if (idToken == null) {
      throw Exception('Google ID token kosong');
    }
    final result = await _auth.googleSignIn(idToken);
    await _persistSession(result.user, result.token);
  }

  Future<void> updateWhatsApp(String number) async {
    final user = await _auth.updateProfile(whatsappNumber: number);
    state = AsyncValue.data(AuthState(user: user));
  }

  Future<void> updateProfile({String? name, String? whatsapp}) async {
    final user = await _auth.updateProfile(name: name, whatsappNumber: whatsapp);
    state = AsyncValue.data(AuthState(user: user));
  }

  Future<void> logout() async {
    await _auth.logout();
    await _storage.clear();
    _ref.read(authTokenProvider.notifier).state = null;
    state = const AsyncValue.data(AuthState());
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AsyncValue<AuthState>>(
  (ref) => AuthController(ref),
);
