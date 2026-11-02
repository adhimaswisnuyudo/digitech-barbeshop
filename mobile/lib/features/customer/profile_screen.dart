import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _name = TextEditingController();
  final _whatsapp = TextEditingController();

  @override
  void initState() {
    super.initState();
    final user = ref.read(authControllerProvider).value?.user;
    _name.text = user?.name ?? '';
    _whatsapp.text = user?.whatsappNumber ?? '';
  }

  @override
  void dispose() {
    _name.dispose();
    _whatsapp.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    try {
      await ref.read(authControllerProvider.notifier).updateProfile(
            name: _name.text.trim(),
            whatsapp: _whatsapp.text.trim(),
          );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profil disimpan')),
        );
      }
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  Future<void> _logout() async {
    await ref.read(authControllerProvider.notifier).logout();
    if (mounted) context.go('/login');
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authControllerProvider).value?.user;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          Text('Email: ${user?.email ?? '-'}'),
          const SizedBox(height: 16),
          TextField(
            controller: _name,
            decoration: const InputDecoration(labelText: 'Nama'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _whatsapp,
            decoration: const InputDecoration(labelText: 'WhatsApp'),
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 24),
          FilledButton(onPressed: _save, child: const Text('Simpan')),
          const SizedBox(height: 12),
          OutlinedButton(onPressed: _logout, child: const Text('Keluar')),
        ],
      ),
    );
  }
}
