class ShopService {
  ShopService({
    required this.id,
    required this.name,
    required this.durationMinutes,
    required this.price,
    this.description,
    this.isActive = true,
  });

  final String id;
  final String name;
  final int durationMinutes;
  final int price;
  final String? description;
  final bool isActive;

  factory ShopService.fromJson(Map<String, dynamic> json) {
    return ShopService(
      id: json['id'] as String,
      name: json['name'] as String,
      durationMinutes: json['duration_minutes'] as int,
      price: json['price'] as int,
      description: json['description'] as String?,
      isActive: json['is_active'] as bool? ?? true,
    );
  }
}
