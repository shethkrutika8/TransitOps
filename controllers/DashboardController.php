<?php
// TransitOps Dashboard Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class DashboardController extends Controller {

    public function index() {
        $db = Database::connect();

        // ── Vehicle Stats ───────────────────────────────────────────────
        $vStmt = $db->query("SELECT status, COUNT(*) as cnt FROM vehicles GROUP BY status");
        $vRows = $vStmt->fetchAll();
        $vCounts = ['Available' => 0, 'On Trip' => 0, 'In Shop' => 0, 'Retired' => 0];
        foreach ($vRows as $r) {
            $vCounts[$r['status']] = (int)$r['cnt'];
        }
        $totalVehicles = array_sum($vCounts);
        $activeVehicles = $totalVehicles - $vCounts['Retired'];

        // ── Driver Stats ────────────────────────────────────────────────
        $dStmt = $db->query("SELECT status, COUNT(*) as cnt FROM drivers GROUP BY status");
        $dRows = $dStmt->fetchAll();
        $dCounts = ['Available' => 0, 'On Trip' => 0, 'Off Duty' => 0, 'Suspended' => 0];
        foreach ($dRows as $r) {
            $dCounts[$r['status']] = (int)$r['cnt'];
        }
        $totalDrivers = array_sum($dCounts);

        // ── Trip Stats ──────────────────────────────────────────────────
        $tStmt = $db->query("SELECT status, COUNT(*) as cnt FROM trips GROUP BY status");
        $tRows = $tStmt->fetchAll();
        $tCounts = ['Draft' => 0, 'Dispatched' => 0, 'Completed' => 0, 'Cancelled' => 0];
        foreach ($tRows as $r) {
            $tCounts[$r['status']] = (int)$r['cnt'];
        }

        // Trips completed TODAY
        $todayTrips = $db->query("SELECT COUNT(*) as cnt FROM trips WHERE status = 'Completed' AND DATE(updated_at) = CURDATE()")->fetch();
        $completedToday = (int)($todayTrips['cnt'] ?? 0);

        // ── Financial Metrics ────────────────────────────────────────────
        $fuelTotal = $db->query("SELECT COALESCE(SUM(cost),0) as total FROM fuel_logs")->fetch()['total'];
        $maintTotal = $db->query("SELECT COALESCE(SUM(cost),0) as total FROM maintenance")->fetch()['total'];
        $expTotal   = $db->query("SELECT COALESCE(SUM(amount),0) as total FROM expenses")->fetch()['total'];
        $totalOpCost = floatval($fuelTotal) + floatval($maintTotal) + floatval($expTotal);

        // Fleet utilization = (On Trip / Active vehicles) * 100
        $fleetUtil = ($activeVehicles > 0) ? round(($vCounts['On Trip'] / $activeVehicles) * 100, 1) : 0;

        // ── Monthly Financial Trends (Last 6 Months) ───────────────────
        $months = [];
        $monthFuel = [];
        $monthMaint = [];
        for ($i = 5; $i >= 0; $i--) {
            $y = date('Y', strtotime("-{$i} months"));
            $m = date('m', strtotime("-{$i} months"));
            $label = date('M Y', strtotime("-{$i} months"));
            $months[] = $label;

            $fRow = $db->prepare("SELECT COALESCE(SUM(cost),0) as t FROM fuel_logs WHERE YEAR(date)=? AND MONTH(date)=?");
            $fRow->execute([$y, $m]);
            $monthFuel[] = floatval($fRow->fetch()['t']);

            $mRow = $db->prepare("SELECT COALESCE(SUM(cost),0) as t FROM maintenance WHERE YEAR(date)=? AND MONTH(date)=?");
            $mRow->execute([$y, $m]);
            $monthMaint[] = floatval($mRow->fetch()['t']);
        }

        // ── Monthly Fleet Utilization % (last 6 months) ────────────────
        $utilRates = [];
        for ($i = 5; $i >= 0; $i--) {
            // Approximation: (completed trips / active vehicles * 10) capped at 100
            $y = date('Y', strtotime("-{$i} months"));
            $m = date('m', strtotime("-{$i} months"));
            $tRow = $db->prepare("SELECT COUNT(*) as cnt FROM trips WHERE status='Completed' AND YEAR(start_date)=? AND MONTH(start_date)=?");
            $tRow->execute([$y, $m]);
            $cnt = (int)$tRow->fetch()['cnt'];
            $rate = min(100, ($activeVehicles > 0) ? round(($cnt / max($activeVehicles, 1)) * 20, 1) : 0);
            $utilRates[] = $rate;
        }

        // ── Recent Trips (last 5) ───────────────────────────────────────
        $recentTrips = $db->query("SELECT t.trip_number, t.source, t.destination, t.status, t.revenue, t.start_date,
                                          v.registration_number, d.name as driver_name
                                   FROM trips t
                                   JOIN vehicles v ON t.vehicle_id = v.id
                                   JOIN drivers d ON t.driver_id = d.id
                                   ORDER BY t.created_at DESC LIMIT 5")->fetchAll();

        // ── Vehicles In Maintenance ─────────────────────────────────────
        $inShop = $db->query("SELECT v.registration_number, v.name, m.type, m.vendor, m.date, m.cost
                              FROM maintenance m
                              JOIN vehicles v ON m.vehicle_id = v.id
                              WHERE m.status = 'In Progress'
                              ORDER BY m.date ASC LIMIT 5")->fetchAll();

        // ── Upcoming Expiries (next 30 days) ───────────────────────────
        $upcoming = $db->query("SELECT registration_number, name, insurance_expiry, rc_expiry
                                FROM vehicles
                                WHERE status != 'Retired'
                                AND (insurance_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                  OR rc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                                ORDER BY insurance_expiry ASC LIMIT 5")->fetchAll();

        // ── Recent Expenses ─────────────────────────────────────────────
        $recentExpenses = $db->query("SELECT e.type, e.amount, e.date, e.remarks, v.registration_number
                                      FROM expenses e JOIN vehicles v ON e.vehicle_id = v.id
                                      ORDER BY e.date DESC, e.created_at DESC LIMIT 5")->fetchAll();

        // ── Unread Notifications count ──────────────────────────────────
        $notifModel = new Notification();
        $unreadCount = $notifModel->getUnreadCount();

        // ── Chart Data Bundle ───────────────────────────────────────────
        $chartData = [
            'vehicles'   => $vCounts,
            'drivers'    => $dCounts,
            'trips'      => $tCounts,
            'financials' => ['months' => $months, 'fuel' => $monthFuel, 'maintenance' => $monthMaint],
            'utilization'=> ['months' => $months, 'rates' => $utilRates],
        ];

        $this->view('dashboard/index', [
            'title'          => 'Dashboard | TransitOps',
            'headerTitle'    => 'Executive Dashboard',
            'totalVehicles'  => $totalVehicles,
            'activeVehicles' => $activeVehicles,
            'vCounts'        => $vCounts,
            'totalDrivers'   => $totalDrivers,
            'dCounts'        => $dCounts,
            'tCounts'        => $tCounts,
            'completedToday' => $completedToday,
            'fuelTotal'      => $fuelTotal,
            'maintTotal'     => $maintTotal,
            'totalOpCost'    => $totalOpCost,
            'fleetUtil'      => $fleetUtil,
            'recentTrips'    => $recentTrips,
            'inShop'         => $inShop,
            'upcoming'       => $upcoming,
            'recentExpenses' => $recentExpenses,
            'unreadCount'    => $unreadCount,
            'chartData'      => json_encode($chartData),
        ]);
    }
}
