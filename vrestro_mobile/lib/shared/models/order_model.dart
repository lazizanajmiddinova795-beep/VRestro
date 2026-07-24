import 'food_model.dart';

class OrderItemModel {
  final int id;
  final int foodId;
  final String foodName;
  final int quantity;
  final double price;
  final String status; // pending, cooking, ready, delivered
  final String? notes;

  OrderItemModel({
    required this.id,
    required this.foodId,
    required this.foodName,
    required this.quantity,
    required this.price,
    required this.status,
    this.notes,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      foodId: json['food_id'] is int ? json['food_id'] : int.tryParse(json['food_id'].toString()) ?? 0,
      foodName: json['food']?['name'] ?? json['food_name'] ?? 'Taom',
      quantity: json['quantity'] is int ? json['quantity'] : int.tryParse(json['quantity'].toString()) ?? 1,
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      status: json['status'] ?? 'pending',
      notes: json['notes'],
    );
  }
}

class OrderModel {
  final int id;
  final String orderNumber;
  final int? tableId;
  final String? tableName;
  final int? waiterId;
  final String? waiterName;
  final double totalAmount;
  final String status; // new, cooking, ready, delivered, paid, cancelled
  final List<OrderItemModel> items;
  final DateTime createdAt;

  OrderModel({
    required this.id,
    required this.orderNumber,
    this.tableId,
    this.tableName,
    this.waiterId,
    this.waiterName,
    required this.totalAmount,
    required this.status,
    required this.items,
    required this.createdAt,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    List<OrderItemModel> orderItems = [];
    if (json['items'] is List) {
      orderItems = (json['items'] as List)
          .map((i) => OrderItemModel.fromJson(i))
          .toList();
    }

    return OrderModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderNumber: json['order_number'] ?? '#${json['id']}',
      tableId: json['table_id'] != null ? int.tryParse(json['table_id'].toString()) : null,
      tableName: json['table']?['number']?.toString() ?? json['table_name'],
      waiterId: json['waiter_id'] != null ? int.tryParse(json['waiter_id'].toString()) : null,
      waiterName: json['waiter']?['name'] ?? json['waiter_name'],
      totalAmount: double.tryParse(json['total_amount'].toString()) ?? 0.0,
      status: json['status'] ?? 'new',
      items: orderItems,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at']) ?? DateTime.now()
          : DateTime.now(),
    );
  }
}
