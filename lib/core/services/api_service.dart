import 'package:dio/dio.dart';
import 'package:transitops/core/models/user.dart';

class ApiService {
  late final Dio _dio;

  ApiService() {
    _dio = Dio(BaseOptions(
      baseUrl: 'http://localhost/transitops', // Update with your actual API URL
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        // Add auth token if available
        // final token = storage.getToken();
        // if (token != null) {
        //   options.headers['Authorization'] = 'Bearer $token';
        // }
        return handler.next(options);
      },
      onError: (error, handler) {
        // Handle errors
        return handler.next(error);
      },
    ));
  }

  // Authentication
  Future<User> login(String email, String password) async {
    try {
      final response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
      });

      if (response.statusCode == 200) {
        return User.fromJson(response.data['user']);
      } else {
        throw Exception('Login failed');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  Future<void> logout() async {
    try {
      await _dio.get('/logout');
    } catch (e) {
      // Continue with logout even if API call fails
    }
  }

  // Vehicles
  Future<List<dynamic>> getVehicles() async {
    final response = await _dio.get('/vehicles/get');
    return response.data as List<dynamic>;
  }

  Future<void> createVehicle(Map<String, dynamic> data) async {
    await _dio.post('/vehicles/create', data: data);
  }

  Future<void> updateVehicle(int id, Map<String, dynamic> data) async {
    await _dio.post('/vehicles/update', data: {...data, 'id': id});
  }

  Future<void> deleteVehicle(int id) async {
    await _dio.post('/vehicles/delete', data: {'id': id});
  }

  // Drivers
  Future<List<dynamic>> getDrivers() async {
    final response = await _dio.get('/drivers/get');
    return response.data as List<dynamic>;
  }

  Future<void> createDriver(Map<String, dynamic> data) async {
    await _dio.post('/drivers/create', data: data);
  }

  Future<void> updateDriver(int id, Map<String, dynamic> data) async {
    await _dio.post('/drivers/update', data: {...data, 'id': id});
  }

  Future<void> deleteDriver(int id) async {
    await _dio.post('/drivers/delete', data: {'id': id});
  }

  // Trips
  Future<List<dynamic>> getTrips() async {
    final response = await _dio.get('/trips/get');
    return response.data as List<dynamic>;
  }

  Future<void> createTrip(Map<String, dynamic> data) async {
    await _dio.post('/trips/create', data: data);
  }

  Future<void> updateTrip(int id, Map<String, dynamic> data) async {
    await _dio.post('/trips/update', data: {...data, 'id': id});
  }

  Future<void> deleteTrip(int id) async {
    await _dio.post('/trips/delete', data: {'id': id});
  }

  Future<void> dispatchTrip(int id) async {
    await _dio.post('/trips/dispatch', data: {'id': id});
  }

  Future<void> completeTrip(int id) async {
    await _dio.post('/trips/complete', data: {'id': id});
  }

  Future<void> cancelTrip(int id) async {
    await _dio.post('/trips/cancel', data: {'id': id});
  }

  // Maintenance
  Future<List<dynamic>> getMaintenance() async {
    final response = await _dio.get('/maintenance/get');
    return response.data as List<dynamic>;
  }

  Future<void> createMaintenance(Map<String, dynamic> data) async {
    await _dio.post('/maintenance/create', data: data);
  }

  Future<void> updateMaintenance(int id, Map<String, dynamic> data) async {
    await _dio.post('/maintenance/update', data: {...data, 'id': id});
  }

  Future<void> deleteMaintenance(int id) async {
    await _dio.post('/maintenance/delete', data: {'id': id});
  }

  Future<void> completeMaintenance(int id) async {
    await _dio.post('/maintenance/complete', data: {'id': id});
  }

  // Fuel
  Future<List<dynamic>> getFuelLogs() async {
    final response = await _dio.get('/fuel/get');
    return response.data as List<dynamic>;
  }

  Future<void> createFuelLog(Map<String, dynamic> data) async {
    await _dio.post('/fuel/create', data: data);
  }

  Future<void> updateFuelLog(int id, Map<String, dynamic> data) async {
    await _dio.post('/fuel/update', data: {...data, 'id': id});
  }

  Future<void> deleteFuelLog(int id) async {
    await _dio.post('/fuel/delete', data: {'id': id});
  }

  // Expenses
  Future<List<dynamic>> getExpenses() async {
    final response = await _dio.get('/expenses/get');
    return response.data as List<dynamic>;
  }

  Future<void> createExpense(Map<String, dynamic> data) async {
    await _dio.post('/expenses/create', data: data);
  }

  Future<void> updateExpense(int id, Map<String, dynamic> data) async {
    await _dio.post('/expenses/update', data: {...data, 'id': id});
  }

  Future<void> deleteExpense(int id) async {
    await _dio.post('/expenses/delete', data: {'id': id});
  }

  // Dashboard
  Future<Map<String, dynamic>> getDashboardData() async {
    final response = await _dio.get('/dashboard');
    return response.data as Map<String, dynamic>;
  }
}
