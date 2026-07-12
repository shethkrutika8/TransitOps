<?php
// TransitOps Authentication Helper

class AuthHelper {
    
    /**
     * Check if a user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the logged-in user ID
     * @return int|null
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get the logged-in user name
     * @return string|null
     */
    public static function userName() {
        return $_SESSION['user_name'] ?? null;
    }

    /**
     * Get the logged-in user email
     * @return string|null
     */
    public static function userEmail() {
        return $_SESSION['user_email'] ?? null;
    }

    /**
     * Get the logged-in user role ID
     * @return int|null
     */
    public static function userRoleId() {
        return $_SESSION['user_role_id'] ?? null;
    }

    /**
     * Get the logged-in user role name
     * @return string|null
     */
    public static function userRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Get user permissions
     * @return array
     */
    public static function userPermissions() {
        return $_SESSION['user_permissions'] ?? [];
    }

    /**
     * Check if user has specific permission
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($permission) {
        if (!self::isLoggedIn()) {
            return false;
        }

        $permissions = self::userPermissions();

        // Super Admin has all permissions (denoted by "all")
        if (in_array('all', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Login user session
     * @param array $user User data array from database (joined with role table)
     */
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role_id'] = $user['role_id'];
        $_SESSION['user_role'] = $user['role_name'];
        
        // Parse permissions JSON
        $perms = json_decode($user['permissions'] ?? '[]', true);
        $_SESSION['user_permissions'] = is_array($perms) ? $perms : [];
    }

    /**
     * Logout and destroy session
     */
    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}
