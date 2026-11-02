import 'package:booking_barbershop/features/customer/booking_flow_screen.dart';
import 'package:booking_barbershop/features/customer/bookings_list_screen.dart';
import 'package:booking_barbershop/features/customer/branches_screen.dart';
import 'package:booking_barbershop/features/customer/profile_screen.dart';
import 'package:flutter/material.dart';

class CustomerShell extends StatefulWidget {
  const CustomerShell({super.key});

  @override
  State<CustomerShell> createState() => _CustomerShellState();
}

class _CustomerShellState extends State<CustomerShell> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final pages = [
      const BranchesScreen(),
      const BookingFlowScreen(),
      const BookingsListScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      body: pages[_index],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.store), label: 'Cabang'),
          NavigationDestination(icon: Icon(Icons.event), label: 'Booking'),
          NavigationDestination(icon: Icon(Icons.history), label: 'Riwayat'),
          NavigationDestination(icon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}
