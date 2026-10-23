<?php
// TransitOps Database Helper Class

class Database {
    private static ?PDO $conn = null;

    /**
     * Get Database Connection
     * @return PDO
     */
    public static function connect(): PDO {
        if (self::$conn === null) {
            $host = getenv('TRANSITOPS_DB_HOST');
            $dbName = getenv('TRANSITOPS_DB_NAME');
            $username = getenv('TRANSITOPS_DB_USER');
            $password = getenv('TRANSITOPS_DB_PASSWORD');

            if (APP_ENV === 'production' && (
                $host === false || $host === ''
                || $dbName === false || $dbName === ''
                || $username === false || $username === ''
                || $password === false
            )) {
                error_log('TransitOps database configuration is incomplete.');
                throw new RuntimeException('Database configuration is incomplete. Set the TransitOps database environment variables.');
            }

            $host = $host !== false && $host !== '' ? $host : 'localhost';
            $dbName = $dbName !== false && $dbName !== '' ? $dbName : 'transitops';
            $username = $username !== false && $username !== '' ? $username : 'root';
            $password = $password !== false ? $password : '';

            try {
                $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$conn = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                error_log('TransitOps database connection failed: ' . $e->getMessage());
                if (DEV_MODE) {
                    throw new RuntimeException('Database Connection Error: ' . $e->getMessage(), 0, $e);
                } else {
                    throw new RuntimeException('Database connection failed. Check the hosting database settings and application logs.', 0, $e);
                }
            }
        }
        return self::$conn;
    }
}
