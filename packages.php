<?php
/**
 * Wander Wise - Packages API Endpoint
 * Method: GET
 * Returns all available tour packages or filters by state/category.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

try {
    $pdo = getPDOConnection();

    $state = isset($_GET['state']) ? trim($_GET['state']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    $query = "SELECT id, name, destination, duration, price, category, state, description, image_url, created_at FROM packages WHERE 1=1";
    $params = [];

    if ($state !== '' && strtolower($state) !== 'all') {
        $query .= " AND LOWER(state) = LOWER(:state)";
        $params[':state'] = $state;
    }

    if ($category !== '' && strtolower($category) !== 'all') {
        $query .= " AND LOWER(category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    $query .= " ORDER BY id ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();

    // Cast numerical fields for proper JSON formatting
    foreach ($packages as &$pkg) {
        $pkg['id'] = (int)$pkg['id'];
        $pkg['duration'] = (int)$pkg['duration'];
        $pkg['price'] = (float)$pkg['price'];
    }
    unset($pkg);

    echo json_encode($packages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch packages',
        'details' => $e->getMessage()
    ]);
}