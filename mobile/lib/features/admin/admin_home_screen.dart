import 'package:booking_barbershop/features/admin/admin_bookings_screen.dart';
import 'package:booking_barbershop/features/admin/admin_branches_screen.dart';
import 'package:booking_barbershop/features/admin/admin_services_screen.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AdminHomeScreen extends ConsumerStatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  ConsumerState<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends ConsumerState<AdminHomeScreen> {
  Map<String, dynamic>? _stats;
  var _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final data = await ref.read(catalogRepositoryProvider).admin.dashboard();
      if (mounted) setState(() => _stats = data);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final week = _stats?['week'] as Map<String, dynamic>?;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin'),
        actions: [
          IconButton(
            onPressed: () async {
              await ref.read(authControllerProvider.notifier).logout();
              if (context.mounted) context.go('/login');
            },
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _StatCard(
                    title: 'Booking hari ini',
                    value: '${_stats?['bookings_today'] ?? 0}',
                  ),
                  const SizedBox(height: 12),
                  _StatCard(
                    title: 'Booking minggu ini',
                    value: '${week?['bookings_count'] ?? 0}',
                  ),
                  const SizedBox(height: 12),
                  _StatCard(
                    title: 'No-show rate (minggu)',
                    value: '${((week?['no_show_rate'] as num? ?? 0) * 100).toStringAsFixed(1)}%',
                  ),
                  const SizedBox(height: 24),
                  ListTile(
                    leading: const Icon(Icons.content_cut),
                    title: const Text('Kelola layanan'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => const AdminServicesScreen(),
                      ),
                    ),
                  ),
                  ListTile(
                    leading: const Icon(Icons.store),
                    title: const Text('Kelola cabang'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => const AdminBranchesScreen(),
                      ),
                    ),
                  ),
                  ListTile(
                    leading: const Icon(Icons.event_note),
                    title: const Text('Semua booking'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => const AdminBookingsScreen(),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Tambah cabang/barber baru: gunakan API admin atau Filament (post-MVP).',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ],
              ),
            ),
    );
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({required this.title, required this.value});

  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.bodyMedium),
            const SizedBox(height: 8),
            Text(value, style: Theme.of(context).textTheme.headlineMedium),
          ],
        ),
      ),
    );
  }
}
