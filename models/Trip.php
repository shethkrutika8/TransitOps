<?php
// TransitOps Trip Model

require_once dirname(__DIR__) . '/models/Model.php';

class Trip extends Model {
    protected $table = 'trips';

    /**
     * Generate a unique auto-incrementing-like trip number
     * Format: TRP-YYYYMMDD-XXXX
     * @return string
     */
    public function generateTripNumber() {
        $prefix = 'TRP-' . date('Ymd') . '-';
        
        // Find how many trips exist for today to generate a sequential number, fallback to random if fails
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `trip_number` LIKE :prefix";
        $stmt = self::query($sql, ['prefix' => $prefix . '%']);
        $result = $stmt->fetch();
        
        $seq = str_pad(($result['count'] + 1), 4, '0', STR_PAD_LEFT);
        $tripNo = $prefix . $seq;

        // Double check uniqueness
        $checkSql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `trip_number` = :trip_no";
        $checkStmt = self::query($checkSql, ['trip_no' => $tripNo]);
        $checkResult = $checkStmt->fetch();

        if ($checkResult['count'] > 0) {
            // Add a random hash suffix if it somehow collides
            $tripNo = $prefix . $seq . '-' . strtoupper(substr(uniqid(), -3));
        }

        return $tripNo;
    }

    /**
     * Get all trips with vehicle and driver info
     * @return array
     */
    public function getTripsWithDetails() {
        $sql = "SELECT t.*, 
                       v.name as vehicle_name, v.registration_number, v.capacity as vehicle_capacity,
                       d.name as driver_name, d.employee_id, d.license_expiry
                FROM `trips` t
                JOIN `vehicles` v ON t.vehicle_id = v.id
                JOIN `drivers` d ON t.driver_id = d.id
                ORDER BY t.created_at DESC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a single trip with details
     * @param int $id
     * @return array|false
     */
    public function getTripDetails($id) {
        $sql = "SELECT t.*, 
                       v.name as vehicle_name, v.registration_number, v.capacity as vehicle_capacity,
                       d.name as driver_name, d.employee_id, d.license_expiry
                FROM `trips` t
                JOIN `vehicles` v ON t.vehicle_id = v.id
                JOIN `drivers` d ON t.driver_id = d.id
                WHERE t.id = :id
                LIMIT 1";
        $stmt = self::query($sql, ['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Fetch all logs for a trip
     * @param int $tripId
     * @return array
     */
    public function getTripLogs($tripId) {
        $sql = "SELECT tl.*, u.name as user_name
                FROM `trip_logs` tl
                JOIN `users` u ON tl.updated_by = u.id
                WHERE tl.trip_id = :trip_id
                ORDER BY tl.created_at ASC";
        $stmt = self::query($sql, ['trip_id' => $tripId]);
        return $stmt->fetchAll();
    }

    /**
     * Add log record for trip status transition
     * @param int $tripId
     * @param string $status
     * @param string|null $notes
     * @param int $userId
     * @return bool
     */
    public function logStatusTransition($tripId, $status, $notes, $userId) {
        $sql = "INSERT INTO `trip_logs` (`trip_id`, `status`, `notes`, `updated_by`) 
                VALUES (:trip_id, :status, :notes, :updated_by)";
        $stmt = self::query($sql, [
            'trip_id' => $tripId,
            'status' => $status,
            'notes' => $notes,
            'updated_by' => $userId
        ]);
        return (bool)$stmt;
    }
}
