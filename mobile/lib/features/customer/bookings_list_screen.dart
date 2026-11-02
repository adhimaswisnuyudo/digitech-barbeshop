import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/booking.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class BookingsListScreen extends ConsumerStatefulWidget {
  const BookingsListScreen({super.key});

  @override
  ConsumerState<BookingsListScreen> createState() => _BookingsListScreenState();
}

class _BookingsListScreenState extends ConsumerState<BookingsListScreen> {
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
      final list = await ref.read(catalogRepositoryProvider).booking.fetchBookings();
      if (mounted) setState(() => _items = list);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _cancel(Booking b) async {
    try {
      await ref.read(catalogRepositoryProvider).booking.cancelBooking(b.id);
      await _load();
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final df = DateFormat('d MMM yyyy HH:mm');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Riwayat booking'),
        actions: [
          IconButton(onPressed: _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _items.isEmpty
              ? const Center(child: Text('Belum ada booking'))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _items.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, i) {
                    final b = _items[i];
                    return Card(
                      child: ListTile(
                        title: Text(b.serviceName ?? 'Layanan'),
                        subtitle: Text(
                          '${df.format(b.startAt.toLocal())}\nStatus: ${b.status}',
                        ),
                        isThreeLine: true,
                        trailing: b.status == 'confirmed'
                            ? IconButton(
                                icon: const Icon(Icons.cancel),
                                onPressed: () => _cancel(b),
                              )
                            : null,
                      ),
                    );
                  },
                ),
    );
  }
}
