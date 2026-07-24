class PaymentModel {
  final int id;
  final int orderId;
  final double amount;
  final String paymentType; // cash, card, click, payme
  final String status;
  final DateTime createdAt;

  PaymentModel({
    required this.id,
    required this.orderId,
    required this.amount,
    required this.paymentType,
    required this.status,
    required this.createdAt,
  });

  factory PaymentModel.fromJson(Map<String, dynamic> json) {
    return PaymentModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderId: json['order_id'] is int ? json['order_id'] : int.parse(json['order_id'].toString()),
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      paymentType: json['payment_type'] ?? 'cash',
      status: json['status'] ?? 'completed',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at']) ?? DateTime.now()
          : DateTime.now(),
    );
  }
}
