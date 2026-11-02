import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CompleteWhatsAppScreen extends ConsumerStatefulWidget {
  const CompleteWhatsAppScreen({super.key});

  @override
  ConsumerState<CompleteWhatsAppScreen> createState() =>
      _CompleteWhatsAppScreenState();
}

class _CompleteWhatsAppScreenState extends ConsumerState<CompleteWhatsAppScreen> {
  final _whatsapp = TextEditingController();
  var _loading = false;

  @override
  void dispose() {
    _whatsapp.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _loading = true);
    try {
      await ref
          .read(authControllerProvider.notifier)
          .updateWhatsApp(_whatsapp.text.trim());
      if (mounted) context.go('/customer');
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Lengkapi profil')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Nomor WhatsApp wajib diisi sebelum melakukan booking.',
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _whatsapp,
              decoration: const InputDecoration(
                labelText: 'WhatsApp',
                hintText: '08xxxxxxxxxx',
              ),
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _loading ? null : _submit,
              child: Text(_loading ? 'Menyimpan...' : 'Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}
