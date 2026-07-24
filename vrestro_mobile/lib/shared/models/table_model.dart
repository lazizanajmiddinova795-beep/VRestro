class TableModel {
  final int id;
  final String number;
  final int capacity;
  final String status; // free, occupied, reserved
  final int? activeOrderId;
  final double? totalAmount;

  TableModel({
    required this.id,
    required this.number,
    required this.capacity,
    required this.status,
    this.activeOrderId,
    this.totalAmount,
  });

  factory TableModel.fromJson(Map<String, dynamic> json) {
    return TableModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      number: json['number']?.toString() ?? json['table_number']?.toString() ?? '',
      capacity: json['capacity'] is int ? json['capacity'] : int.tryParse(json['capacity'].toString()) ?? 4,
      status: json['status'] ?? 'free',
      activeOrderId: json['active_order_id'] != null ? int.tryParse(json['active_order_id'].toString()) : null,
      totalAmount: json['total_amount'] != null ? double.tryParse(json['total_amount'].toString()) : null,
    );
  }
}
