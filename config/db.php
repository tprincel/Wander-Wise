<?php
/**
 * Wander Wise - Database Configuration (config/db.php)
 *
 * Configured for MySQL / MariaDB via PDO
 * Database: wanderwise_db
 */

// Enable CORS for frontend API calls
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Respond to preflight OPTIONS requests immediately
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'wanderwise_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a configured PDO database connection instance.
 *
 * @return PDO
 */
function getPDOConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    return $pdo;
}

// Global PDO instance helper
$pdo = getPDOConnection();
