import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:transitops/core/providers/auth_provider.dart';
import 'package:transitops/features/auth/screens/login_screen.dart';
import 'package:transitops/features/dashboard/screens/dashboard_screen.dart';
import 'package:transitops/features/dashboard/screens/main_layout.dart';
import 'package:transitops/features/vehicles/screens/vehicles_screen.dart';
import 'package:transitops/features/drivers/screens/drivers_screen.dart';
import 'package:transitops/features/trips/screens/trips_screen.dart';
import 'package:transitops/features/maintenance/screens/maintenance_screen.dart';
import 'package:transitops/features/fuel/screens/fuel_screen.dart';
import 'package:transitops/features/expenses/screens/expenses_screen.dart';
import 'package:transitops/features/reports/screens/reports_screen.dart';
import 'package:transitops/features/notifications/screens/notifications_screen.dart';
import 'package:transitops/features/settings/screens/settings_screen.dart';

class AppRouter {
  static GoRouter router = GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final authProvider = context.read<AuthProvider>();
      final isLoggingIn = state.matchedLocation == '/login';

      if (!authProvider.isAuthenticated && !isLoggingIn) {
        return '/login';
      }

      if (authProvider.isAuthenticated && isLoggingIn) {
        return '/';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/',
        name: 'home',
        builder: (context, state) => const MainLayout(
          child: DashboardScreen(),
        ),
      ),
      GoRoute(
        path: '/dashboard',
        name: 'dashboard',
        builder: (context, state) => const MainLayout(
          child: DashboardScreen(),
        ),
      ),
      GoRoute(
        path: '/vehicles',
        name: 'vehicles',
        builder: (context, state) => const MainLayout(
          child: VehiclesScreen(),
        ),
      ),
      GoRoute(
        path: '/drivers',
        name: 'drivers',
        builder: (context, state) => const MainLayout(
          child: DriversScreen(),
        ),
      ),
      GoRoute(
        path: '/trips',
        name: 'trips',
        builder: (context, state) => const MainLayout(
          child: TripsScreen(),
        ),
      ),
      GoRoute(
        path: '/maintenance',
        name: 'maintenance',
        builder: (context, state) => const MainLayout(
          child: MaintenanceScreen(),
        ),
      ),
      GoRoute(
        path: '/fuel',
        name: 'fuel',
        builder: (context, state) => const MainLayout(
          child: FuelScreen(),
        ),
      ),
      GoRoute(
        path: '/expenses',
        name: 'expenses',
        builder: (context, state) => const MainLayout(
          child: ExpensesScreen(),
        ),
      ),
      GoRoute(
        path: '/reports',
        name: 'reports',
        builder: (context, state) => const MainLayout(
          child: ReportsScreen(),
        ),
      ),
      GoRoute(
        path: '/notifications',
        name: 'notifications',
        builder: (context, state) => const MainLayout(
          child: NotificationsScreen(),
        ),
      ),
      GoRoute(
        path: '/settings',
        name: 'settings',
        builder: (context, state) => const MainLayout(
          child: SettingsScreen(),
        ),
      ),
    ],
  );
}
