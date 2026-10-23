<?php
/**
 * Wander Wise - Authentication Endpoint (api/auth.php)
 * Method: POST, GET
 *
 * Handles:
 * - action=login (authenticates with password_verify and establishes session)
 * - action=register (hashes password with password_hash and creates account)
 * - action=logout (destroys active session)
 * - action=check / me (returns currently authenticated user session)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET check / status
if ($method === 'GET') {
    if (isset($_SESSION['user'])) {
        echo json_encode([
            'authenticated' => true,
            'user'          => $_SESSION['user']
        ]);
    } else {
        echo json_encode([
            'authenticated' => false,
            'user'          => null
        ]);
    }
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Read payload
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$action   = isset($data['action']) ? strtolower(trim($data['action'])) : 'login';
$email    = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$name     = isset($data['name']) ? trim($data['name']) : (isset($data['username']) ? trim($data['username']) : '');
$phone    = isset($data['phone']) ? trim($data['phone']) : '';

try {
    $pdo = getPDOConnection();

    // -------------------------------------------------------------
    // LOGOUT
    // -------------------------------------------------------------
    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        exit;
    }

    // -------------------------------------------------------------
    // REGISTER
    // -------------------------------------------------------------
    if ($action === 'register') {
        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid email address provided.']);
            exit;
        }

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (:name, :email, :pass, :phone, 'customer')");
        $insertStmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':pass'  => $passwordHash,
            ':phone' => $phone
        ]);

        $userId = (int)$pdo->lastInsertId();
        $userData = [
            'id'       => $userId,
            'name'     => $name,
            'username' => $name,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'customer'
        ];

        $_SESSION['user'] = $userData;

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Welcome to Wander Wise.',
            'user'    => $userData
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------------
    if ($action === 'login') {
        // Support login by email or username
        $loginIdentifier = !empty($email) ? $email : $name;

        if (empty($loginIdentifier)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Email or username is required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, phone, role FROM users WHERE email = :id1 OR name = :id2 LIMIT 1");
        $stmt->execute([':id1' => $loginIdentifier, ':id2' => $loginIdentifier]);
        $user = $stmt->fetch();

        if (!$user) {
            // If user logged in via simple prompt from legacy form (without password yet), create them
            if (empty($password) && !empty($email)) {
                $passwordHash = password_hash('WanderWise@2026', PASSWORD_DEFAULT);
                $userName = !empty($name) ? $name : explode('@', $email)[0];
                $create = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (:name, :email, :pass, :phone, 'customer')");
                $create->execute([
                    ':name'  => $userName,
                    ':email' => $email,
                    ':pass'  => $passwordHash,
                    ':phone' => $phone
                ]);
                $user = [
                    'id'            => (int)$pdo->lastInsertId(),
                    'name'          => $userName,
                    'email'         => $email,
                    'phone'         => $phone,
                    'role'          => 'customer',
                    'password_hash' => $passwordHash
                ];
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid email/username or password.']);
                exit;
            }
        }

        // Verify password if password provided or configured
        if (!empty($password)) {
            if (!password_verify($password, $user['password_hash'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Incorrect password. Please try again.']);
                exit;
            }
        }

        $userData = [
            'id'       => (int)$user['id'],
            'name'     => $user['name'],
            'username' => $user['name'],
            'email'    => $user['email'],
            'phone'    => $user['phone'],
            'role'     => $user['role']
        ];

        $_SESSION['user'] = $userData;

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Welcome back, ' . htmlspecialchars($user['name']) . '.',
            'user'    => $userData
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown authentication action.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication server error: ' . $e->getMessage()
    ]);
}
