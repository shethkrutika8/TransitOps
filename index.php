<?php
// TransitOps Front Controller

define('SECURE_ACCESS', true);

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Autoloader for MVC components
spl_autoload_register(function ($className) {
    $dirs = [
        __DIR__ . '/config/',
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/helpers/',
        __DIR__ . '/middleware/'
    ];
    
    foreach ($dirs as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load configuration
require_once __DIR__ . '/config/config.php';

// Parse Request Path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Strip query string
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Clean the path relative to the subfolder (if app runs in a subdirectory)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptDir !== '/' && $scriptDir !== '\\') {
    // If the path starts with the script directory, strip it
    $scriptDir = str_replace('\\', '/', $scriptDir);
    if (strpos($path, $scriptDir) === 0) {
        $path = substr($path, strlen($scriptDir));
    }
}

// Ensure path starts with / and ends without it (unless it is just /)
$path = '/' . ltrim($path, '/');
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Load Routes
$routes = require_once __DIR__ . '/config/routes.php';

// Match request
$routeKey = $requestMethod . '|' . $path;
$handler = $routes[$routeKey] ?? null;

// Fallback to match if path is registered but with a different method (for 405 error or redirecting)
if (!$handler) {
    // Check if path matches any route ignoring method
    foreach ($routes as $key => $target) {
        list($rMethod, $rPath) = explode('|', $key);
        if ($rPath === $path) {
            // Method not allowed
            http_response_code(405);
            die("Method Not Allowed");
        }
    }
    
    // Page Not Found
    http_response_code(404);
    $errorController = new class extends Controller {
        public function show404() {
            $this->view('errors/404');
        }
    };
    $errorController->show404();
    exit;
}

// Route matched. Extract controller and action
list($controllerName, $action) = explode('@', $handler);

// Autoloading takes care of finding the controller class
if (!class_exists($controllerName)) {
    die("Controller class '{$controllerName}' not found.");
}

// Check Authentication & RBAC using Middleware before loading the controller action
// Skip auth middleware for login views/processes
$isAuthRoute = ($path === '/login' || $path === '/logout');

if (!$isAuthRoute) {
    // Check if user is logged in
    AuthMiddleware::handle();
    
    // Check RBAC permission for this route
    RoleMiddleware::handle($controllerName, $action);
}

// Dispatch to Controller
$controllerInstance = new $controllerName();
if (!method_exists($controllerInstance, $action)) {
    die("Action '{$action}' not found in controller '{$controllerName}'.");
}

// Execute Action
try {
    $controllerInstance->$action();
} catch (Exception $e) {
    if (DEV_MODE) {
        die("An application error occurred: " . $e->getMessage() . "<br><pre>" . $e->getTraceAsString() . "</pre>");
    } else {
        http_response_code(500);
        die("An internal server error occurred. Please contact the administrator.");
    }
}
