import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/booking.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class AdminBookingsScreen extends ConsumerStatefulWidget {
  const AdminBookingsScreen({super.key});

  @override
  ConsumerState<AdminBookingsScreen> createState() =>
      _AdminBookingsScreenState();
}

class _AdminBookingsScreenState extends ConsumerState<AdminBookingsScreen> {
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
      final list =
          await ref.read(catalogRepositoryProvider).booking.fetchBookings();
      if (mounted) setState(() => _items = list);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _cancel(Booking b) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Batalkan booking?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Tidak')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Ya')),
        ],
      ),
    );
    if (ok != true) return;

    try {
      await ref.read(catalogRepositoryProvider).admin.adminCancelBooking(b.id);
      await _load();
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final df = DateFormat('d MMM yyyy HH:mm');

    return Scaffold(
      appBar: AppBar(title: const Text('Semua booking')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _items.length,
                itemBuilder: (context, i) {
                  final b = _items[i];
                  final canCancel = b.status != 'cancelled' && b.status != 'completed';
                  return Card(
                    child: ListTile(
                      title: Text(b.customerName ?? 'Pelanggan'),
                      subtitle: Text(
                        '${df.format(b.startAt.toLocal())}\n'
                        '${b.serviceName ?? '-'} • ${b.status}',
                      ),
                      isThreeLine: true,
                      trailing: canCancel
                          ? IconButton(
                              icon: const Icon(Icons.cancel_outlined),
                              onPressed: () => _cancel(b),
                            )
                          : null,
                    ),
                  );
                },
              ),
            ),
    );
  }
}
