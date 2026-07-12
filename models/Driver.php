<?php
// TransitOps Driver Model

require_once dirname(__DIR__) . '/models/Model.php';

class Driver extends Model {
    protected $table = 'drivers';

    /**
     * Check if Employee ID is unique
     * @param string $employeeId
     * @param int|null $excludeId
     * @return bool
     */
    public function isEmployeeIdUnique($employeeId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `employee_id` = :emp_id";
        $params = ['emp_id' => $employeeId];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return ($result['count'] == 0);
    }

    /**
     * Check if driver is available for active assignment
     * @param int $id
     * @return bool
     */
    public function isAvailable($id) {
        $driver = $this->find($id);
        if (!$driver) return false;

        // Driver must be Available (not Suspended, Off Duty, or On Trip)
        if ($driver['status'] !== 'Available') {
            return false;
        }

        // License must not be expired
        if (strtotime($driver['license_expiry']) < time()) {
            return false;
        }

        return true;
    }
}
