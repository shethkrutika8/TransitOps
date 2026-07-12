<?php
// TransitOps Database Helper Class

class Database {
    private static $host = 'localhost';
    private static $db_name = 'transitops';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    /**
     * Get Database Connection
     * @return PDO
     */
    public static function connect() {
        if (self::$conn === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$conn = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // If in development mode, output details, else a generic message
                if (defined('DEV_MODE') && DEV_MODE) {
                    die("Database Connection Error: " . $e->getMessage());
                } else {
                    die("Database Connection Error. Please try again later.");
                }
            }
        }
        return self::$conn;
    }
}
