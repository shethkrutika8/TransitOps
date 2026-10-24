import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:transitops/core/models/user.dart';
import 'package:transitops/core/services/api_service.dart';

class AuthProvider with ChangeNotifier {
  User? _user;
  bool _isLoading = false;
  String? _errorMessage;

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _user != null;

  AuthProvider() {
    _loadUserFromStorage();
  }

  Future<void> _loadUserFromStorage() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userJson = prefs.getString('user');
      if (userJson != null) {
        _user = User.fromJson(
          Map<String, dynamic>.from(
            // This would be a proper JSON decode in production
            <String, dynamic>{},
          ),
        );
        notifyListeners();
      }
    } catch (e) {
      if (kDebugMode) {
        print('Error loading user from storage: $e');
      }
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // In production, this would call the actual API
      // For now, we'll simulate a successful login
      await Future.delayed(const Duration(seconds: 1));

      // Mock user data - replace with actual API call
      _user = User(
        id: 1,
        email: email,
        name: 'Admin User',
        roleId: 1,
        status: 'Active',
        roleName: 'Super Admin',
        permissions: ['all'],
      );

      // Save to storage
      final prefs = await SharedPreferences.getInstance();
      // In production, save actual user JSON
      await prefs.setBool('isLoggedIn', true);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
