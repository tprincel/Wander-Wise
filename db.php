<?php
/**
 * Wander Wise Travel Web Application
 * Database Connection & Configuration File
 *
 * Configured for local development with XAMPP (Apache + MySQL / MariaDB)
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

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wanderwise_db');
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
                'message' => 'Database connection failed',
                'details' => $e->getMessage()
            ]);
            exit;
        }
    }

    return $pdo;
}

/**
 * Returns a configured MySQLi database connection instance for legacy scripts.
 *
 * @return mysqli
 */
function getMySQLiConnection() {
    static $mysqli = null;

    if ($mysqli === null) {
        $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($mysqli->connect_error) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $mysqli->connect_error
            ]);
            exit;
        }

        $mysqli->set_charset(DB_CHARSET);
    }

    return $mysqli;
}
