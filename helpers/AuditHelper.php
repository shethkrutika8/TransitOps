<?php
// TransitOps Audit Trail Logger Helper

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class AuditHelper {
    
    /**
     * Log user activity
     * @param string $action General action (e.g. 'Create Vehicle', 'Dispatch Trip')
     * @param string|null $entityType The entity class (e.g. 'Vehicle', 'Trip')
     * @param int|null $entityId The specific entity ID
     * @param string|null $details Context details (e.g. 'Vehicle TX-1029-A registered.')
     * @return bool
     */
    public static function log($action, $entityType = null, $entityId = null, $details = null) {
        try {
            $db = Database::connect();
            $userId = AuthHelper::userId();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`) 
                    VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_address)";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'details' => $details,
                'ip_address' => $ipAddress
            ]);
        } catch (PDOException $e) {
            // Silently fail logging in production to avoid crashing critical functions
            if (defined('DEV_MODE') && DEV_MODE) {
                error_log("Audit Logging Failed: " . $e->getMessage());
            }
            return false;
        }
    }
}
