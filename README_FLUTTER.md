# TransitOps Flutter Web Application

TransitOps is a comprehensive fleet operations management system built with Flutter for web deployment.

## Project Structure

```
lib/
├── main.dart                          # App entry point
├── core/
│   ├── models/                        # Data models
│   │   └── user.dart
│   ├── providers/                      # State management
│   │   └── auth_provider.dart
│   ├── services/                       # API and business logic
│   │   └── api_service.dart
│   ├── theme/                          # App theming
│   │   └── app_theme.dart
│   └── router/                         # Navigation
│       └── app_router.dart
└── features/                           # Feature modules
    ├── auth/
    │   └── screens/
    │       └── login_screen.dart
    ├── dashboard/
    │   └── screens/
    │       ├── dashboard_screen.dart
    │       └── main_layout.dart
    ├── vehicles/
    │   └── screens/
    │       └── vehicles_screen.dart
    ├── drivers/
    │   └── screens/
    │       └── drivers_screen.dart
    ├── trips/
    │   └── screens/
    │       └── trips_screen.dart
    ├── maintenance/
    │   └── screens/
    │       └── maintenance_screen.dart
    ├── fuel/
    │   └── screens/
    │       └── fuel_screen.dart
    ├── expenses/
    │   └── screens/
    │       └── expenses_screen.dart
    ├── reports/
    │   └── screens/
    │       └── reports_screen.dart
    ├── notifications/
    │   └── screens/
    │       └── notifications_screen.dart
    └── settings/
        └── screens/
            └── settings_screen.dart
```

## Features

- **Authentication**: Login system with role-based access control
- **Dashboard**: KPI cards, charts, and activity widgets
- **Vehicles**: Fleet management with CRUD operations
- **Drivers**: Driver management with license tracking
- **Trips**: Trip planning, dispatching, and tracking
- **Maintenance**: Service scheduling and tracking
- **Fuel Logs**: Fuel consumption tracking
- **Expenses**: Operational expense management
- **Reports**: Analytics and reporting
- **Notifications**: System alerts and notifications
- **Settings**: System configuration

## Getting Started

### Prerequisites

- Flutter SDK 3.0.0 or higher
- Dart SDK 3.0.0 or higher
- Web browser (Chrome, Firefox, Safari, or Edge)

### Installation

1. Clone the repository
2. Navigate to the project directory
3. Install dependencies:
   ```bash
   flutter pub get
   ```

### Running the Application

To run the Flutter web application:

```bash
flutter run -d chrome
```

Or for other browsers:
```bash
flutter run -d edge
flutter run -d firefox
flutter run -d safari
```

### Building for Production

To build the application for web deployment:

```bash
flutter build web
```

The build output will be in the `build/web` directory.

## Backend Integration

The Flutter app is designed to work with the existing PHP backend. Update the API base URL in `lib/core/services/api_service.dart`:

```dart
_dio = Dio(BaseOptions(
  baseUrl: 'https://your-domain.com', // Update with your actual API URL
  connectTimeout: const Duration(seconds: 30),
  receiveTimeout: const Duration(seconds: 30),
));
```

## Authentication

The app uses a mock authentication system for demonstration. To integrate with the real PHP backend:

1. Update the `login` method in `lib/core/providers/auth_provider.dart` to call the actual API
2. Implement proper token storage using `flutter_secure_storage`
3. Add JWT token validation and refresh logic

## Theme Configuration

The app supports light and dark themes. Colors are defined in `lib/core/theme/app_theme.dart`. Customize the color scheme by modifying the constants:

```dart
static const Color primaryColor = Color(0xFF4F46E5);
static const Color successColor = Color(0xFF10B981);
static const Color warningColor = Color(0xFFF59E0B);
static const Color dangerColor = Color(0xFFEF4444);
static const Color infoColor = Color(0xFF06B6D4);
```

## Deployment

### Deploying to Web Server

1. Build the web application:
   ```bash
   flutter build web --release
   ```

2. Upload the contents of `build/web` to your web server

3. Configure your web server to handle routing:
   - Apache: Use `.htaccess` with mod_rewrite
   - Nginx: Configure try_files directive
   - Firebase Hosting: Configure firebase.json

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/build/web;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

## Development Notes

- The app uses `provider` for state management
- Navigation is handled by `go_router`
- Charts are rendered using `fl_chart`
- Icons from `font_awesome_flutter`
- HTTP requests via `dio`

## Future Enhancements

- Implement full CRUD operations for all modules
- Add real-time notifications
- Implement file upload for documents
- Add export functionality for reports
- Implement offline data sync
- Add mobile app support (iOS/Android)

## License

Proprietary - All rights reserved
