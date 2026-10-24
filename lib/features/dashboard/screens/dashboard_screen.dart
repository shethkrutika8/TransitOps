import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:transitops/core/theme/app_theme.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Text(
            'Dashboard',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 24),

          // KPI Cards Row 1
          Row(
            children: [
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Total Vehicles',
                  value: '24',
                  subtitle: '12 active in fleet',
                  icon: FontAwesomeIcons.truck,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Available Vehicles',
                  value: '8',
                  subtitle: 'Ready to dispatch',
                  icon: FontAwesomeIcons.checkCircle,
                  color: AppTheme.successColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Vehicles On Trip',
                  value: '12',
                  subtitle: 'Currently dispatched',
                  icon: FontAwesomeIcons.shippingFast,
                  color: AppTheme.infoColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Under Maintenance',
                  value: '4',
                  subtitle: 'Currently in shop',
                  icon: FontAwesomeIcons.tools,
                  color: AppTheme.warningColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // KPI Cards Row 2
          Row(
            children: [
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Total Drivers',
                  value: '18',
                  subtitle: 'Registered personnel',
                  icon: FontAwesomeIcons.users,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Drivers Available',
                  value: '6',
                  subtitle: 'Ready to assign',
                  icon: FontAwesomeIcons.userCheck,
                  color: AppTheme.successColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Active Trips',
                  value: '12',
                  subtitle: 'Currently on road',
                  icon: FontAwesomeIcons.route,
                  color: AppTheme.infoColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Completed Today',
                  value: '5',
                  subtitle: 'Trips delivered',
                  icon: FontAwesomeIcons.flagCheckered,
                  color: AppTheme.successColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Financial KPI Cards
          Row(
            children: [
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Fuel Cost',
                  value: '\$15,230',
                  subtitle: 'Total fuel spend',
                  icon: FontAwesomeIcons.gasPump,
                  color: AppTheme.dangerColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Maintenance Cost',
                  value: '\$8,450',
                  subtitle: 'Total service spend',
                  icon: FontAwesomeIcons.wrench,
                  color: AppTheme.warningColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Total Operational Cost',
                  value: '\$23,680',
                  subtitle: 'All categories combined',
                  icon: FontAwesomeIcons.wallet,
                  color: AppTheme.dangerColor,
                  largeValue: true,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildKPICard(
                  context,
                  title: 'Fleet Utilization',
                  value: '75%',
                  subtitle: 'Overall efficiency',
                  icon: FontAwesomeIcons.chartPie,
                  color: AppTheme.primaryColor,
                  showProgress: true,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Charts Row
          Row(
            children: [
              Expanded(
                child: _buildChartCard(
                  context,
                  title: 'Vehicle Status Distribution',
                  child: SizedBox(
                    height: 220,
                    child: PieChart(
                      PieChartData(
                        sections: [
                          PieChartSectionData(
                            value: 8,
                            title: 'Available',
                            color: AppTheme.successColor,
                            radius: 50,
                          ),
                          PieChartSectionData(
                            value: 12,
                            title: 'On Trip',
                            color: AppTheme.infoColor,
                            radius: 50,
                          ),
                          PieChartSectionData(
                            value: 4,
                            title: 'In Shop',
                            color: AppTheme.warningColor,
                            radius: 50,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildChartCard(
                  context,
                  title: 'Driver Status Distribution',
                  child: SizedBox(
                    height: 220,
                    child: PieChart(
                      PieChartData(
                        sections: [
                          PieChartSectionData(
                            value: 6,
                            title: 'Available',
                            color: AppTheme.successColor,
                            radius: 50,
                          ),
                          PieChartSectionData(
                            value: 12,
                            title: 'On Trip',
                            color: AppTheme.infoColor,
                            radius: 50,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildChartCard(
                  context,
                  title: 'Trip Status Breakdown',
                  child: SizedBox(
                    height: 220,
                    child: PieChart(
                      PieChartData(
                        sections: [
                          PieChartSectionData(
                            value: 12,
                            title: 'Dispatched',
                            color: AppTheme.primaryColor,
                            radius: 50,
                          ),
                          PieChartSectionData(
                            value: 5,
                            title: 'Completed',
                            color: AppTheme.successColor,
                            radius: 50,
                          ),
                          PieChartSectionData(
                            value: 2,
                            title: 'Cancelled',
                            color: AppTheme.dangerColor,
                            radius: 50,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Recent Activity Widgets
          Row(
            children: [
              Expanded(
                child: _buildActivityCard(
                  context,
                  title: 'Recent Trips',
                  icon: FontAwesomeIcons.route,
                  iconColor: AppTheme.primaryColor,
                  child: _buildRecentTripsList(context),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildActivityCard(
                  context,
                  title: 'Vehicles In Shop',
                  icon: FontAwesomeIcons.tools,
                  iconColor: AppTheme.warningColor,
                  child: _buildVehiclesInShopList(context),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKPICard(
    BuildContext context, {
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    bool largeValue = false,
    bool showProgress = false,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.labelSmall?.copyWith(
                        color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.5,
                      ),
                ),
                const SizedBox(height: 8),
                Text(
                  value,
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: color,
                        fontSize: largeValue ? 28 : null,
                      ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                      ),
                ),
                if (showProgress) ...[
                  const SizedBox(height: 12),
                  LinearProgressIndicator(
                    value: 0.75,
                    backgroundColor: color.withOpacity(0.2),
                    valueColor: AlwaysStoppedAnimation<Color>(color),
                  ),
                ],
              ],
            ),
            Positioned(
              right: 8,
              top: 0,
              bottom: 0,
              child: Icon(
                icon,
                size: 48,
                color: color.withOpacity(0.15),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChartCard(BuildContext context, {required String title, required Widget child}) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ),
    );
  }

  Widget _buildActivityCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color iconColor,
    required Widget child,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(icon, color: iconColor, size: 20),
                    const SizedBox(width: 8),
                    Text(
                      title,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ],
                ),
                TextButton(
                  onPressed: () {},
                  child: const Text('View All'),
                ),
              ],
            ),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ),
    );
  }

  Widget _buildRecentTripsList(BuildContext context) {
    return Column(
      children: [
        _buildTripItem(context, 'New York → Boston', 'TRK-001 | John Doe', 'Dispatched', AppTheme.primaryColor, '\$2,500'),
        const SizedBox(height: 8),
        _buildTripItem(context, 'Chicago → Detroit', 'TRK-002 | Jane Smith', 'Completed', AppTheme.successColor, '\$1,800'),
        const SizedBox(height: 8),
        _buildTripItem(context, 'Miami → Atlanta', 'TRK-003 | Bob Johnson', 'On Trip', AppTheme.infoColor, '\$3,200'),
      ],
    );
  }

  Widget _buildTripItem(BuildContext context, String route, String details, String status, Color statusColor, String revenue) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: statusColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              status,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  route,
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                ),
                Text(
                  details,
                  style: TextStyle(
                    fontSize: 12,
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                  ),
                ),
              ],
            ),
          ),
          Text(
            revenue,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: AppTheme.successColor,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVehiclesInShopList(BuildContext context) {
    return Column(
      children: [
        _buildMaintenanceItem(context, 'TRK-004 – Ford F-150', 'Repair at AutoZone', '\$1,200'),
        const SizedBox(height: 8),
        _buildMaintenanceItem(context, 'TRK-005 – Chevrolet Silverado', 'Inspection at Service Pro', '\$450'),
        const SizedBox(height: 8),
        _buildMaintenanceItem(context, 'TRK-006 – Ram 1500', 'Routine at Quick Lube', '\$200'),
      ],
    );
  }

  Widget _buildMaintenanceItem(BuildContext context, String vehicle, String service, String cost) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppTheme.warningColor,
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              FontAwesomeIcons.tools,
              color: Colors.black,
              size: 16,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  vehicle,
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                ),
                Text(
                  service,
                  style: TextStyle(
                    fontSize: 12,
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                  ),
                ),
              ],
            ),
          ),
          Text(
            cost,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: AppTheme.warningColor,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }
}
