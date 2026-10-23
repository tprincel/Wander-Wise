<?php
/**
 * Wander Wise - Contact & Inquiries Endpoint (api/contact.php)
 * Method: POST
 *
 * Inserts messages directly into the `inquiries` table:
 * - name
 * - email
 * - subject
 * - message
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Please use POST.']);
    exit;
}

// Read raw JSON or form POST
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$name    = isset($data['name']) ? trim($data['name']) : '';
$email   = isset($data['email']) ? trim($data['email']) : '';
$subject = isset($data['subject']) ? trim($data['subject']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';

// Also accommodate trip discovery form fields (destination)
if (empty($subject) && !empty($data['destination'])) {
    $subject = 'Trip Inquiry for ' . trim($data['destination']);
}
if (empty($message) && !empty($data['destination'])) {
    $message = 'Inquiry for tour packages in ' . trim($data['destination']);
}

// Validation
if (empty($name) || empty($email)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Name and email are required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

if (empty($message)) {
    $message = 'General inquiry from Wander Wise website.';
}

try {
    $pdo = getPDOConnection();

    $sql = "INSERT INTO inquiries (name, email, subject, message, status) VALUES (:name, :email, :subject, :message, 'Unread')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    $inquiryId = (int)$pdo->lastInsertId();

    echo json_encode([
        'success'    => true,
        'message'    => 'Thank you! Your message has been sent successfully.',
        'inquiry_id' => $inquiryId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saving inquiry: ' . $e->getMessage()
    ]);
}
