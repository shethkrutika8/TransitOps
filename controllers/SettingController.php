<?php
// TransitOps Settings Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';

class SettingController extends Controller {

    public function index() {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings ORDER BY setting_key ASC");
        $stmt->execute();

        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('settings/index', [
            'title' => 'System Settings | TransitOps',
            'headerTitle' => 'System Settings',
            'settings' => $settings
        ]);
    }

    public function updateSettings() {
        $data = $this->getPostData();
        $allowedKeys = ['app_name', 'company_name', 'currency', 'timezone', 'license_warning_days', 'insurance_warning_days'];

        $db = Database::connect();
        $db->beginTransaction();

        try {
            foreach ($allowedKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    continue;
                }

                $value = trim((string) $data[$key]);
                $existsStmt = $db->prepare("SELECT id FROM settings WHERE setting_key = :key LIMIT 1");
                $existsStmt->execute(['key' => $key]);

                if ($existsStmt->fetch()) {
                    $updateStmt = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
                    $updateStmt->execute(['value' => $value, 'key' => $key]);
                } else {
                    $insertStmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
                    $insertStmt->execute(['key' => $key, 'value' => $value]);
                }
            }

            $db->commit();
            AuditHelper::log('Update Settings', 'System', null, 'System settings updated.');
            $this->json(['success' => true, 'message' => 'Settings updated successfully.']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to update settings.']);
        }
    }
}
