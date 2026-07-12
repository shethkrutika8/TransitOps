<?php
// TransitOps Maintenance Model

require_once dirname(__DIR__) . '/models/Model.php';

class Maintenance extends Model {
    protected $table = 'maintenance';

    /**
     * Get maintenance history records with vehicle names
     * @return array
     */
    public function getHistoryWithDetails() {
        $sql = "SELECT m.*, v.name as vehicle_name, v.registration_number
                FROM `maintenance` m
                JOIN `vehicles` v ON m.vehicle_id = v.id
                ORDER BY m.date DESC, m.created_at DESC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }
}
