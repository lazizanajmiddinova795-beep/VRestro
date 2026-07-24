import 'dart:async';
import 'package:dio/dio.dart';
import '../network/dio_client.dart';
import '../constants/api_constants.dart';

class SystemNotification {
  final String id;
  final String title;
  final String message;
  final bool isRead;
  final DateTime createdAt;

  SystemNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.isRead,
    required this.createdAt,
  });

  factory SystemNotification.fromJson(Map<String, dynamic> json) {
    return SystemNotification(
      id: json['id']?.toString() ?? '',
      title: json['data']?['title'] ?? json['title'] ?? 'Bildirishnoma',
      message: json['data']?['message'] ?? json['message'] ?? '',
      isRead: json['read_at'] != null,
      createdAt: json['created_at'] != null 
          ? DateTime.tryParse(json['created_at']) ?? DateTime.now() 
          : DateTime.now(),
    );
  }
}

class NotificationService {
  final Dio _dio = DioClient().dio;
  final StreamController<List<SystemNotification>> _notificationsController =
      StreamController<List<SystemNotification>>.broadcast();

  Timer? _pollingTimer;

  Stream<List<SystemNotification>> get notificationsStream =>
      _notificationsController.stream;

  void startPolling({Duration interval = const Duration(seconds: 10)}) {
    _pollingTimer?.cancel();
    _fetchNotifications();
    _pollingTimer = Timer.periodic(interval, (_) => _fetchNotifications());
  }

  void stopPolling() {
    _pollingTimer?.cancel();
  }

  Future<List<SystemNotification>> fetchNotifications() async {
    return await _fetchNotifications();
  }

  Future<List<SystemNotification>> _fetchNotifications() async {
    try {
      final response = await _dio.get(ApiConstants.notifications);
      if (response.statusCode == 200) {
        final List data = response.data['notifications'] ?? response.data ?? [];
        final notifications = data
            .map((item) => SystemNotification.fromJson(item))
            .toList();
        _notificationsController.add(notifications);
        return notifications;
      }
    } catch (_) {
      // Handle silently during background polling
    }
    return [];
  }

  Future<bool> markAsRead(String id) async {
    try {
      final response = await _dio.patch('${ApiConstants.notifications}/$id/read');
      if (response.statusCode == 200) {
        _fetchNotifications();
        return true;
      }
    } catch (_) {}
    return false;
  }

  Future<bool> markAllAsRead() async {
    try {
      final response = await _dio.post('${ApiConstants.notifications}/read-all');
      if (response.statusCode == 200) {
        _fetchNotifications();
        return true;
      }
    } catch (_) {}
    return false;
  }

  void dispose() {
    _pollingTimer?.cancel();
    _notificationsController.close();
  }
}
