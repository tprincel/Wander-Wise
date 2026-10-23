<?php
/**
 * Wander Wise Travel Web Application
 * Root Database Forwarder & Configuration
 */

require_once __DIR__ . '/config/db.php';

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
