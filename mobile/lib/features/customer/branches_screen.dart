import 'package:booking_barbershop/data/models/branch.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:booking_barbershop/features/customer/branch_detail_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class BranchesScreen extends ConsumerStatefulWidget {
  const BranchesScreen({super.key});

  @override
  ConsumerState<BranchesScreen> createState() => _BranchesScreenState();
}

class _BranchesScreenState extends ConsumerState<BranchesScreen> {
  List<Branch> _branches = [];
  var _loading = true;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      final repo = ref.read(catalogRepositoryProvider).catalog;
      final branches = await repo.fetchBranches();
      if (mounted) setState(() => _branches = branches);
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
          : _branches.isEmpty
              ? const Center(child: Text('Belum ada cabang aktif'))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _branches.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, i) {
                    final b = _branches[i];
                    return Card(
                      child: ListTile(
                        title: Text(b.name),
                        subtitle: Text(b.address, maxLines: 2),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => BranchDetailScreen(branchId: b.id),
                            ),
                          );
                        },
                      ),
                    );
                  },
                ),
    );
  }
}
