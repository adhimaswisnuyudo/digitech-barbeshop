import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:booking_barbershop/features/admin/admin_home_screen.dart';
import 'package:booking_barbershop/features/auth/complete_whatsapp_screen.dart';
import 'package:booking_barbershop/features/auth/login_screen.dart';
import 'package:booking_barbershop/features/auth/register_screen.dart';
import 'package:booking_barbershop/features/customer/customer_shell.dart';
import 'package:booking_barbershop/features/staff/staff_home_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

final _rootKey = GlobalKey<NavigatorState>();

/// Memicu ulang [GoRouter.redirect] saat auth berubah tanpa membuang navigator.
final routerRefreshListenableProvider = Provider<Listenable>((ref) {
  final notifier = ValueNotifier<int>(0);
  ref.listen(authControllerProvider, (_, _) {
    notifier.value++;
  });
  ref.onDispose(notifier.dispose);
  return notifier;
});

final routerProvider = Provider<GoRouter>((ref) {
  final auth = ref.watch(authControllerProvider);
  final refresh = ref.watch(routerRefreshListenableProvider);

  return GoRouter(
    navigatorKey: _rootKey,
    refreshListenable: refresh,
    initialLocation: '/login',
    redirect: (context, state) {
      final path = state.matchedLocation;
      final user = auth.valueOrNull?.user;
      final loggingIn = path == '/login' || path == '/register';

      if (auth.isLoading) return null;

      if (user == null) {
        return loggingIn ? null : '/login';
      }

      if (user.isCustomer && !user.hasWhatsApp && path != '/complete-whatsapp') {
        return '/complete-whatsapp';
      }

      if (loggingIn) {
        return _homeForRole(user.role);
      }

      return null;
    },
    routes: [
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
      GoRoute(
        path: '/complete-whatsapp',
        builder: (_, __) => const CompleteWhatsAppScreen(),
      ),
      GoRoute(path: '/customer', builder: (_, __) => const CustomerShell()),
      GoRoute(path: '/staff', builder: (_, __) => const StaffHomeScreen()),
      GoRoute(path: '/admin', builder: (_, __) => const AdminHomeScreen()),
    ],
  );
});

String _homeForRole(String role) {
  return switch (role) {
    'admin' => '/admin',
    'staff' => '/staff',
    _ => '/customer',
  };
}
