class BookingSlot {
  BookingSlot({
    required this.startAt,
    required this.endAt,
    required this.barberId,
  });

  final DateTime startAt;
  final DateTime endAt;
  final String barberId;

  factory BookingSlot.fromJson(Map<String, dynamic> json) {
    return BookingSlot(
      startAt: DateTime.parse(json['start_at'] as String),
      endAt: DateTime.parse(json['end_at'] as String),
      barberId: json['barber_id'] as String,
    );
  }
}

class Booking {
  Booking({
    required this.id,
    required this.status,
    required this.startAt,
    required this.endAt,
    this.notes,
    this.paymentMethod = 'pay_at_shop',
    this.branchName,
    this.serviceName,
    this.customerName,
    this.customerWhatsapp,
    this.barberName,
  });

  final String id;
  final String status;
  final DateTime startAt;
  final DateTime endAt;
  final String? notes;
  final String paymentMethod;
  final String? branchName;
  final String? serviceName;
  final String? customerName;
  final String? customerWhatsapp;
  final String? barberName;

  factory Booking.fromJson(Map<String, dynamic> json) {
    final branch = json['branch'] as Map<String, dynamic>?;
    final service = json['service'] as Map<String, dynamic>?;
    final customer = json['customer'] as Map<String, dynamic>?;
    final barber = json['barber'] as Map<String, dynamic>?;

    return Booking(
      id: json['id'] as String,
      status: json['status'] as String,
      startAt: DateTime.parse(json['start_at'] as String),
      endAt: DateTime.parse(json['end_at'] as String),
      notes: json['notes'] as String?,
      paymentMethod: json['payment_method'] as String? ?? 'pay_at_shop',
      branchName: branch?['name'] as String?,
      serviceName: service?['name'] as String?,
      customerName: customer?['name'] as String?,
      customerWhatsapp: customer?['whatsapp_number'] as String?,
      barberName: barber?['name'] as String?,
    );
  }
}
