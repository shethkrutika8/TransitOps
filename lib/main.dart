import 'package:flutter/material.dart';
import 'package:flutter_web_plugins/flutter_web_plugins.dart';
import 'package:provider/provider.dart';
import 'package:transitops/core/providers/auth_provider.dart';
import 'package:transitops/core/router/app_router.dart';
import 'package:transitops/core/theme/app_theme.dart';

void main() {
  setUrlStrategy(PathUrlStrategy());
  runApp(const TransitOpsApp());
}

class TransitOpsApp extends StatelessWidget {
  const TransitOpsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MaterialApp.router(
        title: 'TransitOps',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        darkTheme: AppTheme.darkTheme,
        themeMode: ThemeMode.system,
        routerConfig: AppRouter.router,
      ),
    );
  }
}
