import 'package:equatable/equatable.dart';

class UserModel extends Equatable {
  final int id;
  final String name;
  final String email;
  final String role;
  final List<String> permissions;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.permissions,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    List<String> perms = [];
    if (json['permissions'] != null) {
      if (json['permissions'] is List) {
        perms = (json['permissions'] as List).map((e) => e.toString()).toList();
      }
    }
    
    // Determine primary role from roles array or role field
    String userRole = json['role'] ?? 'Waiter';
    if (json['roles'] is List && (json['roles'] as List).isNotEmpty) {
      final firstRole = json['roles'][0];
      userRole = firstRole is Map ? firstRole['name'] : firstRole.toString();
    }

    return UserModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: userRole,
      permissions: perms,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'permissions': permissions,
    };
  }

  @override
  List<Object?> get props => [id, name, email, role, permissions];
}
