import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/shop_service.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class AdminServicesScreen extends ConsumerStatefulWidget {
  const AdminServicesScreen({super.key});

  @override
  ConsumerState<AdminServicesScreen> createState() =>
      _AdminServicesScreenState();
}

class _AdminServicesScreenState extends ConsumerState<AdminServicesScreen> {
  List<ShopService> _items = [];
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
          await ref.read(catalogRepositoryProvider).admin.fetchServices();
      if (mounted) setState(() => _items = list);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _toggleActive(ShopService service) async {
    try {
      await ref.read(catalogRepositoryProvider).admin.updateService(
            service.id,
            isActive: !service.isActive,
          );
      await _load();
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final currency = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Scaffold(
      appBar: AppBar(title: const Text('Layanan')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _items.length,
                itemBuilder: (context, i) {
                  final s = _items[i];
                  return Card(
                    child: SwitchListTile(
                      title: Text(s.name),
                      subtitle: Text(
                        '${s.durationMinutes} menit • ${currency.format(s.price)}',
                      ),
                      value: s.isActive,
                      onChanged: (_) => _toggleActive(s),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
