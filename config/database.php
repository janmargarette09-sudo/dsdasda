<?php
// config/database.php — PDO connection singleton

class Database {
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host = 'localhost';
            $db   = 'teacher_load_system';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';
            
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
            ];
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                error_log("DB Connection failed: " . $e->getMessage());
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$instance;
    }
    
    /**
     * Alias for getInstance() for backward compatibility
     */
    public function getConnection(): PDO {
        return self::getInstance();
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
