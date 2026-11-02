import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/booking.dart';
import 'package:booking_barbershop/data/models/branch.dart';
import 'package:booking_barbershop/data/models/shop_service.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class BookingFlowScreen extends ConsumerStatefulWidget {
  const BookingFlowScreen({super.key});

  @override
  ConsumerState<BookingFlowScreen> createState() => _BookingFlowScreenState();
}

class _BookingFlowScreenState extends ConsumerState<BookingFlowScreen> {
  List<Branch> _branches = [];
  List<ShopService> _services = [];
  List<BookingSlot> _slots = [];
  Branch? _branch;
  ShopService? _service;
  BookingSlot? _slot;
  DateTime _date = DateTime.now().add(const Duration(days: 1));
  bool _anyBarber = true;
  var _loadingMeta = true;
  var _loadingSlots = false;

  @override
  void initState() {
    super.initState();
    _loadMeta();
  }

  Future<void> _loadMeta() async {
    final catalog = ref.read(catalogRepositoryProvider).catalog;
    final branches = await catalog.fetchBranches();
    final services = await catalog.fetchServices();
    if (mounted) {
      setState(() {
        _branches = branches;
        _services = services;
        _branch = branches.isNotEmpty ? branches.first : null;
        _service = services.isNotEmpty ? services.first : null;
        _loadingMeta = false;
      });
    }
  }

  Future<void> _loadSlots() async {
    if (_branch == null || _service == null) return;
    setState(() => _loadingSlots = true);
    try {
      final catalog = ref.read(catalogRepositoryProvider).catalog;
      final dateStr = DateFormat('yyyy-MM-dd').format(_date);
      final slots = await catalog.fetchAvailability(
        branchId: _branch!.id,
        serviceId: _service!.id,
        date: dateStr,
      );
      if (mounted) setState(() => _slots = slots);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loadingSlots = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 60)),
    );
    if (picked != null) {
      setState(() {
        _date = picked;
        _slot = null;
      });
      await _loadSlots();
    }
  }

  Future<void> _confirm() async {
    if (_branch == null || _service == null || _slot == null) return;
    try {
      final booking = await ref.read(catalogRepositoryProvider).booking.createBooking(
            branchId: _branch!.id,
            serviceId: _service!.id,
            startAt: _slot!.startAt,
            barberId: _anyBarber ? null : _slot!.barberId,
          );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Booking berhasil: ${booking.id.substring(0, 8)}...')),
      );
      setState(() => _slot = null);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final df = DateFormat('EEE, d MMM yyyy');
    final tf = DateFormat('HH:mm');

    return Scaffold(
      appBar: AppBar(title: const Text('Booking baru')),
      body: _loadingMeta
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                DropdownButtonFormField<Branch>(
                  value: _branch,
                  decoration: const InputDecoration(labelText: 'Cabang'),
                  items: _branches
                      .map((b) => DropdownMenuItem(value: b, child: Text(b.name)))
                      .toList(),
                  onChanged: (v) => setState(() => _branch = v),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<ShopService>(
                  value: _service,
                  decoration: const InputDecoration(labelText: 'Layanan'),
                  items: _services
                      .map((s) => DropdownMenuItem(
                            value: s,
                            child: Text('${s.name} (${s.durationMinutes} mnt)'),
                          ))
                      .toList(),
                  onChanged: (v) => setState(() => _service = v),
                ),
                const SizedBox(height: 12),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(df.format(_date)),
                  trailing: const Icon(Icons.calendar_today),
                  onTap: _pickDate,
                ),
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Barber siapa saja'),
                  value: _anyBarber,
                  onChanged: (v) => setState(() => _anyBarber = v),
                ),
                FilledButton(
                  onPressed: _loadSlots,
                  child: const Text('Lihat slot tersedia'),
                ),
                const SizedBox(height: 16),
                if (_loadingSlots) const LinearProgressIndicator(),
                ..._slots.map(
                  (s) => RadioListTile<BookingSlot>(
                    value: s,
                    groupValue: _slot,
                    onChanged: (v) => setState(() => _slot = v),
                    title: Text(tf.format(s.startAt.toLocal())),
                    subtitle: Text('Barber: ${s.barberId.substring(0, 8)}...'),
                  ),
                ),
                const SizedBox(height: 8),
                const Text('Pembayaran: Bayar di tempat'),
                const SizedBox(height: 16),
                FilledButton(
                  onPressed: _slot == null ? null : _confirm,
                  child: const Text('Konfirmasi booking'),
                ),
              ],
            ),
    );
  }
}
