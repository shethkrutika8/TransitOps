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
        }

        if ($this->notifModel->markRead($id)) {
            $this->json(['success' => true, 'message' => 'Notification marked as read.']);
        } else {
            $this->json(['success' => false, 'error' => 'Notification not found.']);
        }
    }
}
