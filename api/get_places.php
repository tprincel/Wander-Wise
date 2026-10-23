<?php
/**
 * Wander Wise - Get Places Endpoint (api/get_places.php)
 * Method: GET
 *
 * Fetches all destinations from the `places` table with optional
 * category, state, search keyword, and duration filters.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getPDOConnection();

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $state    = isset($_GET['state']) ? trim($_GET['state']) : (isset($_GET['region']) ? trim($_GET['region']) : '');
    $search   = isset($_GET['search']) ? trim($_GET['search']) : (isset($_GET['q']) ? trim($_GET['q']) : '');
    $duration = isset($_GET['duration']) ? trim($_GET['duration']) : '';
    $featured = isset($_GET['featured']) ? trim($_GET['featured']) : '';

    $query = "SELECT id, title, state_region, category, price_per_person, duration_days, image_url, description, is_featured, created_at FROM places WHERE 1=1";
    $params = [];

    // Category filter
    if ($category !== '' && strtolower($category) !== 'all') {
        $query .= " AND LOWER(category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    // State / Region filter
    if ($state !== '' && strtolower($state) !== 'all') {
        $query .= " AND (LOWER(state_region) LIKE LOWER(:state) OR LOWER(title) LIKE LOWER(:state))";
        $params[':state'] = '%' . $state . '%';
    }

    // Keyword Search filter
    if ($search !== '') {
        $query .= " AND (LOWER(title) LIKE LOWER(:search) OR LOWER(state_region) LIKE LOWER(:search) OR LOWER(description) LIKE LOWER(:search))";
        $params[':search'] = '%' . $search . '%';
    }

    // Duration filter
    if ($duration !== '' && strtolower($duration) !== 'all') {
        if ($duration === '10' || $duration === '10+') {
            $query .= " AND duration_days >= 10";
        } else {
            $query .= " AND duration_days = :duration";
            $params[':duration'] = (int)$duration;
        }
    }

    // Featured filter
    if ($featured !== '') {
        $query .= " AND is_featured = 1";
    }

    $query .= " ORDER BY is_featured DESC, id ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $places = $stmt->fetchAll();

    // Format fields and provide aliases for backward-compatible frontend templates
    foreach ($places as &$p) {
        $p['id']               = (int)$p['id'];
        $p['price_per_person'] = (float)$p['price_per_person'];
        $p['duration_days']    = (int)$p['duration_days'];
        $p['is_featured']      = (bool)$p['is_featured'];

        // Aliases for compatibility
        $p['name']        = $p['title'];
        $p['destination'] = $p['state_region'];
        $p['state']       = $p['state_region'];
        $p['price']       = $p['price_per_person'];
        $p['duration']    = $p['duration_days'];
    }
    unset($p);

    echo json_encode($places, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch places',
        'details' => $e->getMessage()
    ]);
}
