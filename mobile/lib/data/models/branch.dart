class Branch {
  Branch({
    required this.id,
    required this.name,
    required this.address,
    required this.latitude,
    required this.longitude,
    required this.openingHours,
    this.timezone = 'Asia/Jakarta',
  });

  final String id;
  final String name;
  final String address;
  final double latitude;
  final double longitude;
  final Map<String, dynamic> openingHours;
  final String timezone;

  factory Branch.fromJson(Map<String, dynamic> json) {
    return Branch(
      id: json['id'] as String,
      name: json['name'] as String,
      address: json['address'] as String,
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      openingHours: Map<String, dynamic>.from(json['opening_hours'] as Map),
      timezone: json['timezone'] as String? ?? 'Asia/Jakarta',
    );
  }
}
