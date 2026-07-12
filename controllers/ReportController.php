<?php
// TransitOps Report Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';

class ReportController extends Controller {

    /**
     * Show reports interface
     */
    public function index() {
        $type = $_GET['type'] ?? 'roi';
        
        $data = $this->fetchReportData($type);

        $this->view('reports/index', [
            'title' => 'System Reports | TransitOps',
            'headerTitle' => 'Operational Reports',
            'reportType' => $type,
            'reportData' => $data
        ]);
    }

    /**
     * Generate & Export reports (CSV / Excel / Print)
     */
    public function generate() {
        $type = $_GET['type'] ?? 'roi';
        $format = $_GET['format'] ?? 'print';

        $data = $this->fetchReportData($type);
        
        AuditHelper::log('Generate Report', 'Report', null, "Exported report '{$type}' in format '{$format}'.");

        if ($format === 'csv') {
            $filename = "transitops_report_{$type}_" . date('Ymd_His') . ".csv";
            $headers = [];
            $rows = [];

            if ($type === 'roi') {
                $headers = ['Reg Number', 'Vehicle Name', 'Purchase Cost ($)', 'Total Trips Revenue ($)', 'Total Operational Cost ($)', 'Net Profit ($)', 'ROI (%)'];
                foreach ($data as $r) {
                    $rows[] = [
                        $r['registration_number'],
                        $r['name'],
                        $r['purchase_cost'],
                        $r['total_revenue'],
                        $r['total_cost'],
                        $r['net_profit'],
                        $r['roi_pct']
                    ];
                }
            } elseif ($type === 'fuel') {
                $headers = ['Reg Number', 'Vehicle Name', 'Total Fuel (L)', 'Total Fuel Cost ($)', 'Avg Mileage (km/L)'];
                foreach ($data as $r) {
                    $rows[] = [
                        $r['registration_number'],
                        $r['name'],
                        $r['total_fuel'],
                        $r['total_cost'],
                        $r['avg_mileage']
                    ];
                }
            } elseif ($type === 'driver') {
                $headers = ['Employee ID', 'Driver Name', 'Safety Score (%)', 'Trips Completed', 'Total Revenue Generated ($)'];
                foreach ($data as $r) {
                    $rows[] = [
                        $r['employee_id'],
                        $r['name'],
                        $r['safety_score'],
                        $r['trips_count'],
                        $r['total_revenue']
                    ];
                }
            } elseif ($type === 'maintenance') {
                $headers = ['Reg Number', 'Vehicle Name', 'Total Tickets', 'Total Maintenance Cost ($)'];
                foreach ($data as $r) {
                    $rows[] = [
                        $r['registration_number'],
                        $r['name'],
                        $r['tickets_count'],
                        $r['total_maintenance']
                    ];
                }
            } elseif ($type === 'expense') {
                $headers = ['Reg Number', 'Vehicle Name', 'General Expenses ($)', 'Maintenance ($)', 'Fuel ($)', 'Total Operational Spend ($)'];
                foreach ($data as $r) {
                    $rows[] = [
                        $r['registration_number'],
                        $r['name'],
                        $r['expenses_cost'],
                        $r['maintenance_cost'],
                        $r['fuel_cost'],
                        $r['total_cost']
                    ];
                }
            }

            $this->exportCSV($filename, $headers, $rows);
        } else {
            // Render print-friendly version
            $this->view('reports/print', [
                'reportType' => $type,
                'reportData' => $data,
                'noLayout' => true // Don't wrap in main layout shell
            ]);
        }
    }

    /**
     * Query data helper
     */
    private function fetchReportData($type) {
        $db = Database::connect();
        
        switch ($type) {
            case 'roi':
                // Vehicle Return on Investment Report
                $sql = "SELECT 
                            v.registration_number, 
                            v.name, 
                            v.purchase_cost,
                            COALESCE(SUM(DISTINCT t.revenue), 0) as total_revenue,
                            (
                                COALESCE((SELECT SUM(amount) FROM expenses WHERE vehicle_id = v.id), 0) +
                                COALESCE((SELECT SUM(cost) FROM maintenance WHERE vehicle_id = v.id), 0) +
                                COALESCE((SELECT SUM(cost) FROM fuel_logs WHERE vehicle_id = v.id), 0)
                            ) as total_cost
                        FROM vehicles v
                        LEFT JOIN trips t ON t.vehicle_id = v.id AND t.status = 'Completed'
                        GROUP BY v.id, v.registration_number, v.name, v.purchase_cost
                        ORDER BY total_revenue DESC";
                $stmt = $db->query($sql);
                $results = $stmt->fetchAll();
                
                // Post-calculate ROI calculations
                foreach ($results as &$r) {
                    $r['net_profit'] = $r['total_revenue'] - $r['total_cost'];
                    $r['roi_pct'] = ($r['purchase_cost'] > 0) ? round(($r['net_profit'] / $r['purchase_cost']) * 100, 2) : 0;
                }
                return $results;

            case 'fuel':
                // Fuel consumption and efficiency reports
                $sql = "SELECT 
                            v.registration_number, 
                            v.name, 
                            COALESCE(SUM(f.quantity), 0) as total_fuel,
                            COALESCE(SUM(f.cost), 0) as total_cost,
                            COALESCE(AVG(NULLIF(f.mileage, 0)), 0) as avg_mileage
                        FROM vehicles v
                        LEFT JOIN fuel_logs f ON f.vehicle_id = v.id
                        GROUP BY v.id, v.registration_number, v.name
                        ORDER BY avg_mileage DESC";
                $stmt = $db->query($sql);
                return $stmt->fetchAll();

            case 'driver':
                // Driver performance reports
                $sql = "SELECT 
                            d.employee_id, 
                            d.name, 
                            d.safety_score, 
                            COUNT(t.id) as trips_count,
                            COALESCE(SUM(t.revenue), 0) as total_revenue
                        FROM drivers d
                        LEFT JOIN trips t ON t.driver_id = d.id AND t.status = 'Completed'
                        GROUP BY d.id, d.employee_id, d.name, d.safety_score
                        ORDER BY safety_score DESC";
                $stmt = $db->query($sql);
                return $stmt->fetchAll();

            case 'maintenance':
                // Maintenance cost aggregate report
                $sql = "SELECT 
                            v.registration_number, 
                            v.name, 
                            COUNT(m.id) as tickets_count,
                            COALESCE(SUM(m.cost), 0) as total_maintenance
                        FROM vehicles v
                        LEFT JOIN maintenance m ON m.vehicle_id = v.id
                        GROUP BY v.id, v.registration_number, v.name
                        ORDER BY total_maintenance DESC";
                $stmt = $db->query($sql);
                return $stmt->fetchAll();

            case 'expense':
                // Vehicle Wise operational cost components summary
                $sql = "SELECT 
                            v.registration_number, 
                            v.name, 
                            COALESCE((SELECT SUM(amount) FROM expenses WHERE vehicle_id = v.id), 0) as expenses_cost,
                            COALESCE((SELECT SUM(cost) FROM maintenance WHERE vehicle_id = v.id), 0) as maintenance_cost,
                            COALESCE((SELECT SUM(cost) FROM fuel_logs WHERE vehicle_id = v.id), 0) as fuel_cost
                        FROM vehicles v
                        GROUP BY v.id, v.registration_number, v.name
                        ORDER BY v.registration_number ASC";
                $stmt = $db->query($sql);
                $results = $stmt->fetchAll();
                
                foreach ($results as &$r) {
                    $r['total_cost'] = $r['expenses_cost'] + $r['maintenance_cost'] + $r['fuel_cost'];
                }
                return $results;

            default:
                return [];
        }
    }

    /**
     * CSV Builder Utility
     */
    private function exportCSV($filename, $headers, $data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Write UTF-8 BOM to Excel correctly parses characters
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, $headers);
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
