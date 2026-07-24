class FoodModel {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final double price;
  final String? imagePath;
  final int categoryId;
  final bool isAvailable;

  FoodModel({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    required this.price,
    this.imagePath,
    required this.categoryId,
    required this.isAvailable,
  });

  factory FoodModel.fromJson(Map<String, dynamic> json) {
    return FoodModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      description: json['description'],
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      imagePath: json['image_path'],
      categoryId: json['category_id'] is int
          ? json['category_id']
          : int.tryParse(json['category_id'].toString()) ?? 0,
      isAvailable: json['is_available'] == true || json['is_available'] == 1,
    );
  }

  FoodModel copyWith({bool? isAvailable}) {
    return FoodModel(
      id: id,
      name: name,
      slug: slug,
      description: description,
      price: price,
      imagePath: imagePath,
      categoryId: categoryId,
      isAvailable: isAvailable ?? this.isAvailable,
    );
  }
}
