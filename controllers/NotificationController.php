<?php
// TransitOps Notification Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';

class NotificationController extends Controller {

    public function index() {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM notifications ORDER BY created_at DESC");
        $stmt->execute();

        $this->view('notifications/index', [
            'title' => 'Alerts & Notifications | TransitOps',
            'headerTitle' => 'System Alerts',
            'notifications' => $stmt->fetchAll()
        ]);
    }

    public function latest() {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 8");
        $stmt->execute();
        $notifications = $stmt->fetchAll();

        $this->json([
            'success' => true,
            'unread_count' => count($notifications),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead() {
        $data = $this->getPostData();
        $id = intval($data['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Notification ID is required.']);
        }

        $db = Database::connect();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        AuditHelper::log('Mark Notification Read', 'Notification', $id, 'Notification marked as read.');
        $this->json(['success' => true, 'message' => 'Notification marked as read.']);
    }
}
