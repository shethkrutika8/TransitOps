import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:transitops/core/providers/auth_provider.dart';
import 'package:transitops/core/theme/app_theme.dart';

class MainLayout extends StatefulWidget {
  final Widget child;

  const MainLayout({super.key, required this.child});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  bool _isSidebarCollapsed = false;
  int _selectedIndex = 0;

  final List<MenuItem> _menuItems = [
    MenuItem(
      icon: FontAwesomeIcons.thLarge,
      label: 'Dashboard',
      route: '/',
      permission: null,
    ),
    MenuItem(
      icon: FontAwesomeIcons.truck,
      label: 'Vehicles',
      route: '/vehicles',
      permission: 'vehicles_view',
    ),
    MenuItem(
      icon: FontAwesomeIcons.idCard,
      label: 'Drivers',
      route: '/drivers',
      permission: 'drivers_view',
    ),
    MenuItem(
      icon: FontAwesomeIcons.route,
      label: 'Trips',
      route: '/trips',
      permission: 'trips_manage',
    ),
    MenuItem(
      icon: FontAwesomeIcons.tools,
      label: 'Maintenance',
      route: '/maintenance',
      permission: 'maintenance_manage',
    ),
    MenuItem(
      icon: FontAwesomeIcons.gasPump,
      label: 'Fuel Logs',
      route: '/fuel',
      permission: 'fuel_manage',
    ),
    MenuItem(
      icon: FontAwesomeIcons.fileInvoiceDollar,
      label: 'Expenses',
      route: '/expenses',
      permission: 'expenses_manage',
    ),
    MenuItem(
      icon: FontAwesomeIcons.chartBar,
      label: 'Reports',
      route: '/reports',
      permission: 'reports_view',
    ),
    MenuItem(
      icon: FontAwesomeIcons.bell,
      label: 'System Alerts',
      route: '/notifications',
      permission: null,
    ),
    MenuItem(
      icon: FontAwesomeIcons.sliders,
      label: 'Settings',
      route: '/settings',
      permission: 'all',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.user;

    // Filter menu items based on permissions
    final visibleMenuItems = _menuItems.where((item) {
      if (item.permission == null) return true;
      return user?.hasPermission(item.permission!) ?? false;
    }).toList();

    return Scaffold(
      body: Row(
        children: [
          // Sidebar
          Container(
            width: _isSidebarCollapsed ? 80 : 260,
            decoration: BoxDecoration(
              color: AppTheme.lightBgSidebar,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(2, 0),
                ),
              ],
            ),
            child: Column(
              children: [
                // Logo
                Container(
                  height: 70,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  decoration: BoxDecoration(
                    border: Border(
                      bottom: BorderSide(
                        color: Colors.white.withOpacity(0.05),
                      ),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        FontAwesomeIcons.truckMoving,
                        color: AppTheme.primaryColor,
                        size: 28,
                      ),
                      if (!_isSidebarCollapsed) ...[
                        const SizedBox(width: 12),
                        Text(
                          'TransitOps',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Menu Items
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    itemCount: visibleMenuItems.length,
                    itemBuilder: (context, index) {
                      final item = visibleMenuItems[index];
                      final isSelected = _selectedIndex == index;

                      return _buildMenuItem(
                        item,
                        isSelected,
                        index,
                      );
                    },
                  ),
                ),

                // Footer
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    border: Border(
                      top: BorderSide(
                        color: Colors.white.withOpacity(0.05),
                      ),
                    ),
                  ),
                  child: Column(
                    children: [
                      if (!_isSidebarCollapsed) ...[
                        const Text(
                          'TransitOps System',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.white54,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'v1.0.0',
                          style: TextStyle(
                            fontSize: 10,
                            color: Colors.white38,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Main Content
          Expanded(
            child: Column(
              children: [
                // Top Navbar
                Container(
                  height: 70,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surface,
                    border: Border(
                      bottom: BorderSide(
                        color: Theme.of(context).dividerColor,
                      ),
                    ),
                  ),
                  child: Row(
                    children: [
                      // Sidebar Toggle
                      IconButton(
                        icon: const Icon(FontAwesomeIcons.bars),
                        onPressed: () {
                          setState(() {
                            _isSidebarCollapsed = !_isSidebarCollapsed;
                          });
                        },
                      ),
                      const SizedBox(width: 16),

                      // Breadcrumb/Title (optional)
                      Expanded(
                        child: Text(
                          _getSelectedMenuItemName(visibleMenuItems),
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),

                      // Right Actions
                      Row(
                        children: [
                          // Theme Toggle
                          IconButton(
                            icon: const Icon(FontAwesomeIcons.moon),
                            onPressed: () {
                              // TODO: Implement theme toggle
                            },
                          ),

                          // Notifications
                          Stack(
                            children: [
                              IconButton(
                                icon: const Icon(FontAwesomeIcons.bell),
                                onPressed: () {
                                  context.push('/notifications');
                                },
                              ),
                              Positioned(
                                right: 8,
                                top: 8,
                                child: Container(
                                  width: 8,
                                  height: 8,
                                  decoration: const BoxDecoration(
                                    color: AppTheme.dangerColor,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ),
                            ],
                          ),

                          // User Menu
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [AppTheme.primaryColor, AppTheme.infoColor],
                              ),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              children: [
                                const Icon(
                                  FontAwesomeIcons.user,
                                  color: Colors.white,
                                  size: 16,
                                ),
                                const SizedBox(width: 8),
                                if (!_isSidebarCollapsed)
                                  Text(
                                    user?.name ?? 'User',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w600,
                                      fontSize: 14,
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            icon: const Icon(FontAwesomeIcons.rightFromBracket),
                            onPressed: () {
                              authProvider.logout();
                              context.go('/login');
                            },
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                // Page Content
                Expanded(
                  child: widget.child,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(MenuItem item, bool isSelected, int index) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedIndex = index;
          });
          context.push(item.route);
        },
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 12,
          ),
          decoration: BoxDecoration(
            color: isSelected
                ? AppTheme.primaryColor.withOpacity(0.15)
                : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              Icon(
                item.icon,
                color: isSelected ? Colors.white : Colors.white54,
                size: 20,
              ),
              if (!_isSidebarCollapsed) ...[
                const SizedBox(width: 12),
                Text(
                  item.label,
                  style: TextStyle(
                    color: isSelected ? Colors.white : Colors.white70,
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                    fontSize: 14,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  String _getSelectedMenuItemName(List<MenuItem> items) {
    if (_selectedIndex < items.length) {
      return items[_selectedIndex].label;
    }
    return 'Dashboard';
  }
}

class MenuItem {
  final IconData icon;
  final String label;
  final String route;
  final String? permission;

  MenuItem({
    required this.icon,
    required this.label,
    required this.route,
    this.permission,
  });
}
