<?php
/**
 * Wander Wise - User Authentication / Login Endpoint
 * Method: POST
 * Accepts JSON or form POST with username, phone, email.
 * Registers user if first time, or authenticates and returns user session profile.
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

// Support both JSON body and standard Form POST
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);

$username = '';
$phone = '';
$email = '';

if (is_array($json)) {
    $username = isset($json['username']) ? trim($json['username']) : '';
    $phone    = isset($json['phone'])    ? trim($json['phone'])    : '';
    $email    = isset($json['email'])    ? trim($json['email'])    : '';
} else {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $phone    = isset($_POST['phone'])    ? trim($_POST['phone'])    : '';
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
}

// Validation
if (empty($username) || empty($phone) || empty($email)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Username, phone number, and email are required.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid 10-digit phone number.'
    ]);
    exit;
}

try {
    $pdo = getPDOConnection();

    // Check if user exists by email
    $stmt = $pdo->prepare("SELECT id, username, email, phone, created_at FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // Optionally update username and phone if changed
        $updateStmt = $pdo->prepare("UPDATE users SET username = :username, phone = :phone WHERE id = :id");
        $updateStmt->execute([
            ':username' => $username,
            ':phone'    => $phone,
            ':id'       => $user['id']
        ]);
        $user['username'] = $username;
        $user['phone'] = $phone;

        echo json_encode([
            'success' => true,
            'message' => 'Welcome back, ' . htmlspecialchars($username) . '!',
            'is_new'  => false,
            'user'    => $user
        ]);
    } else {
        // Register new user
        $insertStmt = $pdo->prepare("INSERT INTO users (username, email, phone) VALUES (:username, :email, :phone)");
        $insertStmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':phone'    => $phone
        ]);

        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Welcome, ' . htmlspecialchars($username) . '!',
            'is_new'  => true,
            'user'    => [
                'id'       => (int)$newId,
                'username' => $username,
                'email'    => $email,
                'phone'    => $phone
            ]
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication service error',
        'details' => $e->getMessage()
    ]);
}
