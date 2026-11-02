class AppUser {
  AppUser({
    required this.id,
    required this.role,
    required this.name,
    required this.email,
    this.whatsappNumber,
    this.branchId,
  });

  final String id;
  final String role;
  final String name;
  final String email;
  final String? whatsappNumber;
  final String? branchId;

  bool get isCustomer => role == 'customer';
  bool get isStaff => role == 'staff';
  bool get isAdmin => role == 'admin';
  bool get hasWhatsApp =>
      whatsappNumber != null && whatsappNumber!.trim().isNotEmpty;

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: json['id'] as String,
      role: json['role'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      whatsappNumber: json['whatsapp_number'] as String?,
      branchId: json['branch_id'] as String?,
    );
  }

  AppUser copyWith({String? name, String? whatsappNumber}) {
    return AppUser(
      id: id,
      role: role,
      name: name ?? this.name,
      email: email,
      whatsappNumber: whatsappNumber ?? this.whatsappNumber,
      branchId: branchId,
    );
  }
}
