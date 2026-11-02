import 'package:booking_barbershop/app.dart';
import 'package:booking_barbershop/data/models/branch.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AdminBranchesScreen extends ConsumerStatefulWidget {
  const AdminBranchesScreen({super.key});

  @override
  ConsumerState<AdminBranchesScreen> createState() =>
      _AdminBranchesScreenState();
}

class _AdminBranchesScreenState extends ConsumerState<AdminBranchesScreen> {
  List<Branch> _items = [];
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
          await ref.read(catalogRepositoryProvider).admin.fetchBranches();
      if (mounted) setState(() => _items = list);
    } catch (e) {
      if (mounted) showErrorSnackBar(context, e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Cabang')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _items.length,
                itemBuilder: (context, i) {
                  final b = _items[i];
                  return Card(
                    child: ListTile(
                      title: Text(b.name),
                      subtitle: Text(
                        '${b.address}\n${b.latitude.toStringAsFixed(5)}, ${b.longitude.toStringAsFixed(5)}',
                      ),
                      isThreeLine: true,
                    ),
                  );
                },
              ),
            ),
    );
  }
}
