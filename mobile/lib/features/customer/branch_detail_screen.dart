import 'package:booking_barbershop/data/models/branch.dart';
import 'package:booking_barbershop/features/auth/auth_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';

class BranchDetailScreen extends ConsumerStatefulWidget {
  const BranchDetailScreen({super.key, required this.branchId});

  final String branchId;

  @override
  ConsumerState<BranchDetailScreen> createState() =>
      _BranchDetailScreenState();
}

class _BranchDetailScreenState extends ConsumerState<BranchDetailScreen> {
  Branch? _branch;
  var _loading = true;
  var _mapError = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final branch =
          await ref.read(catalogRepositoryProvider).catalog.fetchBranch(widget.branchId);
      if (mounted) setState(() => _branch = branch);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openMaps() async {
    final b = _branch!;
    final uri = Uri.parse(
      'https://www.google.com/maps/search/?api=1&query=${b.latitude},${b.longitude}',
    );
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Detail cabang')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final branch = _branch!;
    final point = LatLng(branch.latitude, branch.longitude);

    return Scaffold(
      appBar: AppBar(title: Text(branch.name)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(branch.address, style: Theme.of(context).textTheme.bodyLarge),
          const SizedBox(height: 12),
          SizedBox(
            height: 220,
            child: _mapError
                ? Center(child: Text('Peta tidak dapat dimuat\n${branch.address}'))
                : FlutterMap(
                    options: MapOptions(
                      initialCenter: point,
                      initialZoom: 15,
                      onMapEvent: (e) {},
                    ),
                    children: [
                      TileLayer(
                        urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.bookingbarbershop.booking_barbershop',
                        errorTileCallback: (_, __, ___) {
                          if (!_mapError) setState(() => _mapError = true);
                        },
                      ),
                      MarkerLayer(
                        markers: [
                          Marker(
                            point: point,
                            width: 40,
                            height: 40,
                            child: const Icon(Icons.location_pin, color: Colors.red, size: 40),
                          ),
                        ],
                      ),
                    ],
                  ),
          ),
          const SizedBox(height: 8),
          const Text(
            '© OpenStreetMap contributors',
            style: TextStyle(fontSize: 11, color: Colors.grey),
          ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: _openMaps,
            icon: const Icon(Icons.navigation),
            label: const Text('Buka navigasi'),
          ),
        ],
      ),
    );
  }
}
