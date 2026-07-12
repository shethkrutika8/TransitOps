<?php
// TransitOps Vehicle Model

require_once dirname(__DIR__) . '/models/Model.php';

class Vehicle extends Model {
    protected $table = 'vehicles';

    /**
     * Check if registration number exists (excluding optional current vehicle ID)
     * @param string $regNo
     * @param int|null $excludeId
     * @return bool
     */
    public function isRegNumberUnique($regNo, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `registration_number` = :reg_no";
        $params = ['reg_no' => $regNo];
        
        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return ($result['count'] == 0);
    }

    /**
     * Check if vehicle is available for assignments
     * @param int $id
     * @return bool
     */
    public function isAvailable($id) {
        $vehicle = $this->find($id);
        return ($vehicle && $vehicle['status'] === 'Available');
    }

    /**
     * Check if vehicle is under maintenance
     * @param int $id
     * @return bool
     */
    public function isInShop($id) {
        $vehicle = $this->find($id);
        return ($vehicle && $vehicle['status'] === 'In Shop');
    }
}
