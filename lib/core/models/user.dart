class User {
  final int id;
  final String email;
  final String name;
  final int roleId;
  final String status;
  final String? roleName;
  final List<String>? permissions;

  User({
    required this.id,
    required this.email,
    required this.name,
    required this.roleId,
    required this.status,
    this.roleName,
    this.permissions,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      email: json['email'] as String,
      name: json['name'] as String,
      roleId: json['role_id'] as int,
      status: json['status'] as String,
      roleName: json['role_name'] as String?,
      permissions: json['permissions'] != null
          ? List<String>.from(json['permissions'] as List)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'email': email,
      'name': name,
      'role_id': roleId,
      'status': status,
      'role_name': roleName,
      'permissions': permissions,
    };
  }

  bool hasPermission(String permission) {
    if (permissions == null) return false;
    return permissions!.contains(permission) || permissions!.contains('all');
  }
}
