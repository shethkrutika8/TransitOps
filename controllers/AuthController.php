<?php
// TransitOps Authentication Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';

class AuthController extends Controller {

    /**
     * Show the Login Screen
     */
    public function loginView() {
        if (AuthHelper::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', ['title' => 'Login | TransitOps']);
    }

    /**
     * Process User Authentication
     */
    public function loginProcess() {
        if (AuthHelper::isLoggedIn()) {
            $this->json(['success' => true, 'redirect' => BASE_URL . '/dashboard']);
        }

        $data = $this->getPostData();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->json(['success' => false, 'error' => 'Please fill in all credentials.']);
        }

        try {
            $db = Database::connect();
            $sql = "SELECT u.*, r.name as role_name, r.permissions 
                    FROM `users` u 
                    JOIN `roles` r ON u.role_id = r.id 
                    WHERE u.email = :email 
                    LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $this->json(['success' => false, 'error' => 'Invalid email or password.']);
            }

            if ($user['status'] !== 'Active') {
                $this->json(['success' => false, 'error' => 'Your account has been suspended. Please contact admin.']);
            }

            // Set session variables
            AuthHelper::login($user);

            // Write into audit logs
            AuditHelper::log('User Login', 'User', $user['id'], "User {$user['name']} ({$user['email']}) logged in successfully.");

            // Return success with redirect URL
            $redirectUrl = $_SESSION['redirect_url'] ?? (BASE_URL . '/dashboard');
            unset($_SESSION['redirect_url']); // Clear redirect URL path
            
            $this->json([
                'success' => true, 
                'message' => 'Login successful. Redirecting...', 
                'redirect' => $redirectUrl
            ]);

        } catch (PDOException $e) {
            $this->json(['success' => false, 'error' => 'A system error occurred. Please try again.']);
        }
    }

    /**
     * Process User Logout
     */
    public function logout() {
        if (AuthHelper::isLoggedIn()) {
            AuditHelper::log('User Logout', 'User', AuthHelper::userId(), "User " . AuthHelper::userName() . " logged out.");
        }
        
        AuthHelper::logout();
        $this->redirect('/login');
    }
}
