<?php
// TransitOps Dashboard Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/config/database.php';

class DashboardController extends Controller {

    public function index() {
        $db = Database::connect();

        $stats = [
            'vehicles_total' => (int) $db->query("SELECT COUNT(*) as total FROM vehicles")->fetch()['total'],
            'vehicles_available' => (int) $db->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'Available'")->fetch()['total'],
            'vehicles_on_trip' => (int) $db->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'On Trip'")->fetch()['total'],
            'vehicles_maintenance' => (int) $db->query("SELECT COUNT(*) as total FROM vehicles WHERE status = 'In Shop'")->fetch()['total'],
            'drivers_total' => (int) $db->query("SELECT COUNT(*) as total FROM drivers")->fetch()['total'],
            'drivers_available' => (int) $db->query("SELECT COUNT(*) as total FROM drivers WHERE status = 'Available'")->fetch()['total'],
            'drivers_on_trip' => (int) $db->query("SELECT COUNT(*) as total FROM drivers WHERE status = 'On Trip'")->fetch()['total'],
            'active_trips' => (int) $db->query("SELECT COUNT(*) as total FROM trips WHERE status = 'Dispatched'")->fetch()['total'],
            'completed_today' => (int) $db->query("SELECT COUNT(*) as total FROM trips WHERE status = 'Completed' AND DATE(updated_at) = CURDATE()")->fetch()['total'],
            'fuel_cost' => (float) $db->query("SELECT COALESCE(SUM(cost), 0) as total FROM fuel_logs")->fetch()['total'],
            'maintenance_cost' => (float) $db->query("SELECT COALESCE(SUM(cost), 0) as total FROM maintenance")->fetch()['total'],
            'operational_cost' => (float) $db->query("SELECT COALESCE(SUM(amount), 0) as expenses FROM expenses")->fetch()['expenses'] + (float) $db->query("SELECT COALESCE(SUM(cost), 0) as maintenance FROM maintenance")->fetch()['maintenance'] + (float) $db->query("SELECT COALESCE(SUM(cost), 0) as fuel FROM fuel_logs")->fetch()['fuel']
        ];

        $vehiclesTotal = max(1, $stats['vehicles_total']);
        $stats['fleet_utilization_pct'] = round((($stats['vehicles_available'] + $stats['vehicles_on_trip']) / $vehiclesTotal) * 100, 1);

        $recentTripsStmt = $db->prepare("SELECT t.*, v.registration_number, d.name as driver_name
            FROM trips t
            JOIN vehicles v ON t.vehicle_id = v.id
            JOIN drivers d ON t.driver_id = d.id
            ORDER BY t.created_at DESC
            LIMIT 5");
        $recentTripsStmt->execute();
        $recentTrips = $recentTripsStmt->fetchAll();

        $maintenanceStmt = $db->prepare("SELECT v.registration_number, v.name, v.status, m.type, m.description
            FROM vehicles v
            JOIN maintenance m ON m.vehicle_id = v.id
            WHERE v.status = 'In Shop' OR m.status = 'In Progress'
            ORDER BY m.date DESC
            LIMIT 5");
        $maintenanceStmt->execute();
        $maintenanceVehicles = $maintenanceStmt->fetchAll();

        $licenseStmt = $db->prepare("SELECT id, name, employee_id, license_expiry
            FROM drivers
            WHERE license_expiry <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND status != 'Suspended'
            ORDER BY license_expiry ASC
            LIMIT 5");
        $licenseStmt->execute();
        $upcomingLicenses = $licenseStmt->fetchAll();

        $expenseStmt = $db->prepare("SELECT e.*, v.registration_number, v.name as vehicle_name
            FROM expenses e
            JOIN vehicles v ON e.vehicle_id = v.id
            ORDER BY e.date DESC, e.created_at DESC
            LIMIT 5");
        $expenseStmt->execute();
        $recentExpenses = $expenseStmt->fetchAll();

        $notificationStmt = $db->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
        $notificationStmt->execute();
        $notifications = $notificationStmt->fetchAll();

        $this->view('dashboard/index', [
            'title' => 'Dashboard | TransitOps',
            'headerTitle' => 'Executive Dashboard',
            'stats' => $stats,
            'recentTrips' => $recentTrips,
            'maintenanceVehicles' => $maintenanceVehicles,
            'upcomingLicenses' => $upcomingLicenses,
            'recentExpenses' => $recentExpenses,
            'notifications' => $notifications
        ]);
    }
}
