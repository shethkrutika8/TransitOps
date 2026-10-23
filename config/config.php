<?php
// TransitOps Configuration File

$environment = getenv('TRANSITOPS_ENV');
$environment = $environment !== false ? strtolower(trim($environment)) : 'production';

if (!in_array($environment, ['development', 'production', 'test'], true)) {
    throw new RuntimeException('TRANSITOPS_ENV must be development, production, or test.');
}

define('APP_ENV', $environment);
define('DEV_MODE', APP_ENV === 'development');

if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

date_default_timezone_set(getenv('TRANSITOPS_TIMEZONE') ?: 'UTC');

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('APP_PATH', dirname(__DIR__));
define('UPLOAD_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'uploads');

$upload_dirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'vehicles',
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'drivers',
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'documents',
];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create the uploads directory. Check hosting file permissions.');
        }
    }

    if (!is_writable($dir)) {
        throw new RuntimeException('The uploads directory is not writable. Check hosting file permissions.');
    }
}

$configuredBaseUrl = getenv('TRANSITOPS_BASE_URL');
if ($configuredBaseUrl !== false && trim($configuredBaseUrl) !== '') {
    $baseUrl = rtrim(trim($configuredBaseUrl), '/');
} else {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
    );
    $protocol = $isHttps ? 'https://' : 'http://';
    $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }
    $baseUrl = $protocol . $domainName . $dir;
}
define('BASE_URL', $baseUrl);

define('APP_NAME', 'TransitOps');
define('CURRENCY_SYMBOL', '$');
define('VERSION', '1.0.0');
