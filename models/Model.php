<?php
// TransitOps Base Model

require_once dirname(__DIR__) . '/config/database.php';

abstract class Model {
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Get Database Instance
     * @return PDO
     */
    protected static function db() {
        return Database::connect();
    }

    /**
     * Get all records from the table
     * @param string $orderBy
     * @return array
     */
    public function all($orderBy = '') {
        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $stmt = self::db()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Find a record by its primary key
     * @param mixed $id
     * @return array|false
     */
    public function find($id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find records matching custom conditions
     * @param string $whereClause (e.g. "status = :status AND type = :type")
     * @param array $params (e.g. ['status' => 'Active', 'type' => 'Truck'])
     * @param string $orderBy
     * @return array
     */
    public function where($whereClause, $params = [], $orderBy = '') {
        $sql = "SELECT * FROM `{$this->table}` WHERE {$whereClause}";
        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single record matching custom conditions
     * @param string $whereClause
     * @param array $params
     * @return array|false
     */
    public function findOne($whereClause, $params = []) {
        $sql = "SELECT * FROM `{$this->table}` WHERE {$whereClause} LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Create a new record
     * @param array $data
     * @return string|false Inserted ID or false
     */
    public function create($data) {
        $columns = implode(', ', array_map(function($col) { return "`{$col}`"; }, array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = self::db()->prepare($sql);
        if ($stmt->execute($data)) {
            return self::db()->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing record
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "`{$key}` = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');
        
        $sql = "UPDATE `{$this->table}` SET {$fields} WHERE `{$this->primaryKey}` = :_id";
        
        // Prepare execution data merging primary key parameter
        $execData = array_merge($data, ['_id' => $id]);
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($execData);
    }

    /**
     * Delete a record by primary key
     * @param mixed $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Run raw query with parameters
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function query($sql, $params = []) {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
