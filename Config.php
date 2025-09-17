<?php
/**
 * Database configuration for microservices
 */
class DatabaseConfig {
    // Database connection parameters
    private static $dbConfigs = [
        'merchflow' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'merchflow',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ],
        'docbd' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'docbd',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ],
        'legaldb' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'legaldb',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ],
        'facilitiesdb' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'facilitiesdb',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ],
        'visitordb' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'visitordb',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ]
    ];
    
    /**
     * Get PDO connection for a specific service
     * 
     * @param string $service The service name (userdb, posdb, etc.)
     * @return PDO Database connection
     */
    public static function getConnection($service) {
    if (!isset(self::$dbConfigs[$service])) {
        throw new Exception("Unknown service: $service");
    }
    
    $config = self::$dbConfigs[$service];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        return new PDO($dsn, $config['username'], $config['password'], $options);
    } catch (PDOException $e) {
        throw new PDOException("Connection failed for $service: " . $e->getMessage(), (int)$e->getCode());
    }


    }
}