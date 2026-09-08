<?php
/**
 * Wander Wise - Save Inquiry Endpoint
 * Method: POST
 * Accepts inquiry details from form.html or AJAX and records into inquiries table.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);

$name = '';
$email = '';
$destination = '';

if (is_array($json)) {
    $name        = isset($json['name'])        ? trim($json['name'])        : '';
    $email       = isset($json['email'])       ? trim($json['email'])       : '';
    $destination = isset($json['destination']) ? trim($json['destination']) : '';
} else {
    $name        = isset($_POST['name'])        ? trim($_POST['name'])        : '';
    $email       = isset($_POST['email'])       ? trim($_POST['email'])       : '';
    $destination = isset($_POST['destination']) ? trim($_POST['destination']) : '';
}

if (empty($name) || empty($email) || empty($destination)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Name, email, and destination state are required.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address.'
    ]);
    exit;
}

try {
    $pdo = getPDOConnection();

    $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, destination_state) VALUES (:name, :email, :dest)");
    $stmt->execute([
        ':name'  => $name,
        ':email' => $email,
        ':dest'  => $destination
    ]);

    $inquiryId = $pdo->lastInsertId();

    echo json_encode([
        'success'    => true,
        'message'    => 'Inquiry saved successfully!',
        'inquiry_id' => (int)$inquiryId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error saving inquiry',
        'details' => $e->getMessage()
    ]);
}
