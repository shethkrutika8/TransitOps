<?php
// TransitOps Fuel Log Model

require_once dirname(__DIR__) . '/models/Model.php';

class FuelLog extends Model {
    protected $table = 'fuel_logs';

    /**
     * Fetch fuel logs joined with vehicle details
     * @return array
     */
    public function getLogsWithDetails() {
        $sql = "SELECT f.*, v.name as vehicle_name, v.registration_number 
                FROM `fuel_logs` f 
                JOIN `vehicles` v ON f.vehicle_id = v.id 
                ORDER BY f.date DESC, f.created_at DESC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get the previous fuel log odometer reading for a vehicle before a certain date
     * @param int $vehicleId
     * @param string $date
     * @param int|null $excludeLogId (to avoid matching itself on updates)
     * @return int|null
     */
    public function getPreviousOdometer($vehicleId, $date, $excludeLogId = null) {
        $sql = "SELECT odometer 
                FROM `fuel_logs` 
                WHERE `vehicle_id` = :vehicle_id 
                AND `date` <= :date";
        
        $params = [
            'vehicle_id' => $vehicleId,
            'date' => $date
        ];

        if ($excludeLogId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeLogId;
        }

        $sql .= " ORDER BY `date` DESC, `odometer` DESC LIMIT 1";

        $stmt = self::query($sql, $params);
        $res = $stmt->fetch();
        return $res ? intval($res['odometer']) : null;
    }
}
