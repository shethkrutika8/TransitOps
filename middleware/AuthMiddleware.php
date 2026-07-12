<?php
// TransitOps Login Guard Middleware

require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class AuthMiddleware {
    
    /**
     * Validate user login session
     */
    public static function handle() {
        if (!AuthHelper::isLoggedIn()) {
            // Check if it is an AJAX request
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                      || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
            
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.']);
                exit;
            } else {
                // Save requested URL for redirect post-login (optional)
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                header("Location: " . BASE_URL . "/login");
                exit;
            }
        }
    }
}
