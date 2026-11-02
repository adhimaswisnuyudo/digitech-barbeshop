import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  var _loading = false;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _loading = true);
    try {
      await ref.read(authControllerProvider.notifier).login(
            _email.text.trim(),
            _password.text,
          );
      if (!mounted) return;
      final user = ref.read(authControllerProvider).value?.user;
      if (user != null) {
        context.go(switch (user.role) {
          'admin' => '/admin',
          'staff' => '/staff',
          _ => '/customer',
        });
      }
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _google() async {
    setState(() => _loading = true);
    try {
      await ref.read(authControllerProvider.notifier).googleLogin();
      if (!mounted) return;
      final user = ref.read(authControllerProvider).value?.user;
      if (user == null) return;
      if (user.isCustomer && !user.hasWhatsApp) {
        context.go('/complete-whatsapp');
      } else {
        context.go(switch (user.role) {
          'admin' => '/admin',
          'staff' => '/staff',
          _ => '/customer',
        });
      }
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Masuk')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          TextField(
            controller: _email,
            decoration: const InputDecoration(labelText: 'Email'),
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _password,
            decoration: const InputDecoration(labelText: 'Password'),
            obscureText: true,
          ),
          const SizedBox(height: 24),
          FilledButton(
            onPressed: _loading ? null : _submit,
            child: _loading
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Masuk'),
          ),
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: _loading ? null : _google,
            child: const Text('Masuk dengan Google'),
          ),
          TextButton(
            onPressed: () => context.push('/register'),
            child: const Text('Belum punya akun? Daftar'),
          ),
        ],
      ),
    );
  }
}
