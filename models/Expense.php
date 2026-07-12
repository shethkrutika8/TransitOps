<?php
// TransitOps Expense Model

require_once dirname(__DIR__) . '/models/Model.php';

class Expense extends Model {
    protected $table = 'expenses';

    /**
     * Get expense logs with vehicle details
     * @return array
     */
    public function getExpensesWithDetails() {
        $sql = "SELECT e.*, v.name as vehicle_name, v.registration_number 
                FROM `expenses` e 
                JOIN `vehicles` v ON e.vehicle_id = v.id 
                ORDER BY e.date DESC, e.created_at DESC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Calculate Total Operational Cost dynamically (Expenses + Maintenance + Fuel)
     * @return float
     */
    public function getTotalOperationalCost() {
        $sql = "SELECT (
            (SELECT COALESCE(SUM(`amount`), 0) FROM `expenses`) +
            (SELECT COALESCE(SUM(`cost`), 0) FROM `maintenance`) +
            (SELECT COALESCE(SUM(`cost`), 0) FROM `fuel_logs`)
        ) as total";
        
        $stmt = self::query($sql);
        $res = $stmt->fetch();
        return floatval($res['total'] ?? 0);
    }
}
