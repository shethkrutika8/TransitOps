<?php
// TransitOps Notification Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class NotificationController extends Controller {
    private $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    /**
     * Full notification list page
     */
    public function index() {
        // Auto-generate system alerts on each page load
        Notification::generateSystemAlerts();

        $notifications = $this->notifModel->getAll();

        $this->view('notifications/index', [
            'title' => 'System Alerts | TransitOps',
            'headerTitle' => 'System Notifications',
            'notifications' => $notifications
        ]);
    }

    /**
     * Get latest unread notifications (AJAX polling)
     */
    public function latest() {
        // Generate alerts if needed
        Notification::generateSystemAlerts();

        $notifications = $this->notifModel->getLatest(8);
        $unreadCount   = $this->notifModel->getUnreadCount();

        $this->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notification as read (AJAX POST)
     */
    public function markAsRead() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            // Mark all read if no specific ID
            $this->notifModel->markAllRead();
            $this->json(['success' => true, 'message' => 'All notifications marked as read.']);
            return;
        }

        if ($this->notifModel->markRead($id)) {
            $this->json(['success' => true, 'message' => 'Notification marked as read.']);
        } else {
            $this->json(['success' => false, 'error' => 'Notification not found.']);
        }
    }

    /**
     * Update notification (AJAX POST)
     */
    public function update() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Notification ID is required.']);
            return;
        }

        $updateData = [
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'type' => $data['type'] ?? 'info',
            'is_read' => $data['is_read'] ?? 0
        ];

        if ($this->notifModel->update($id, $updateData)) {
            $this->json(['success' => true, 'message' => 'Notification updated successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update notification.']);
        }
    }

    /**
     * Delete notification (AJAX POST)
     */
    public function delete() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Notification ID is required.']);
            return;
        }

        if ($this->notifModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Notification deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete notification.']);
        }
    }
}
