<?php
// TransitOps Configuration File

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (set to true in dev, false in production)
define('DEV_MODE', true);
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('UTC');

// Paths configuration
define('APP_PATH', dirname(__DIR__));
define('UPLOAD_PATH', APP_PATH . '/uploads');

// Create upload subdirectories if they don't exist
$upload_dirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . '/vehicles',
    UPLOAD_PATH . '/drivers',
    UPLOAD_PATH . '/documents'
];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Dynamic Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = str_replace('\\', '/', dirname($scriptName));
if ($dir === '/' || $dir === '\\') {
    $dir = '';
}
define('BASE_URL', $protocol . $domainName . $dir);

// Application Constants
define('APP_NAME', 'TransitOps');
define('CURRENCY_SYMBOL', '$');
define('VERSION', '1.0.0');
