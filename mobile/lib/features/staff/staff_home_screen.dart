import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/booking.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class StaffHomeScreen extends ConsumerStatefulWidget {
  const StaffHomeScreen({super.key});

  @override
  ConsumerState<StaffHomeScreen> createState() => _StaffHomeScreenState();
}

class _StaffHomeScreenState extends ConsumerState<StaffHomeScreen> {
  List<Booking> _items = [];
  var _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final today = DateFormat('yyyy-MM-dd').format(DateTime.now());
      final list = await ref
          .read(catalogRepositoryProvider)
          .booking
          .fetchBookings(date: today);
      if (mounted) setState(() => _items = list);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _status(Booking b, String status) async {
    try {
      await ref.read(catalogRepositoryProvider).booking.updateStatus(b.id, status);
      await _load();
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tf = DateFormat('HH:mm');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Jadwal hari ini'),
        actions: [
          IconButton(onPressed: _load, icon: const Icon(Icons.refresh)),
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
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _items.length,
              itemBuilder: (context, i) {
                final b = _items[i];
                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${b.customerName ?? 'Pelanggan'} • ${tf.format(b.startAt.toLocal())}',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        Text('WA: ${b.customerWhatsapp ?? '-'}'),
                        Text('${b.serviceName ?? ''} • ${b.status}'),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          children: [
                            OutlinedButton(
                              onPressed: () => _status(b, 'in_progress'),
                              child: const Text('Mulai'),
                            ),
                            OutlinedButton(
                              onPressed: () => _status(b, 'completed'),
                              child: const Text('Selesai'),
                            ),
                            OutlinedButton(
                              onPressed: () => _status(b, 'no_show'),
                              child: const Text('No-show'),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
