<?php
/**
 * Wander Wise - Book Place Endpoint (api/book_place.php)
 * Method: POST
 *
 * Accepts JSON or POST form data:
 * - user_id or email, customerName, customerPhone
 * - place_id
 * - travel_date
 * - number_of_guests / guests / peopleCount
 * - payment_method (optional, default UPI)
 *
 * Inserts records into `bookings` and `payments` tables.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST.'
    ]);
    exit;
}

// Read raw JSON or form POST
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

// Extract & normalize inputs
$userId       = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$name         = isset($data['name']) ? trim($data['name']) : (isset($data['customerName']) ? trim($data['customerName']) : '');
$email        = isset($data['email']) ? trim($data['email']) : (isset($data['customerEmail']) ? trim($data['customerEmail']) : '');
$phone        = isset($data['phone']) ? trim($data['phone']) : (isset($data['customerPhone']) ? trim($data['customerPhone']) : '');
$placeId      = isset($data['place_id']) ? (int)$data['place_id'] : (isset($data['packageId']) ? (int)$data['packageId'] : 0);
$travelDate   = isset($data['travel_date']) ? trim($data['travel_date']) : (isset($data['travelDate']) ? trim($data['travelDate']) : '');
$guests       = isset($data['number_of_guests']) ? (int)$data['number_of_guests'] : (isset($data['guests']) ? (int)$data['guests'] : (isset($data['peopleCount']) ? (int)$data['peopleCount'] : 1));
$paymentMethod = isset($data['payment_method']) ? trim($data['payment_method']) : 'UPI';

// Also check nested package object if passed from dashboard
if ($placeId === 0 && isset($data['package']) && is_array($data['package'])) {
    if (!empty($data['package']['id'])) {
        $placeId = (int)$data['package']['id'];
    }
}

if ($guests <= 0) {
    $guests = 1;
}

// Validation
if (empty($email) && $userId <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'User ID or valid email address is required.'
    ]);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ]);
    exit;
}

if ($placeId <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid destination/place to book.'
    ]);
    exit;
}

if (empty($travelDate)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Travel date is required.'
    ]);
    exit;
}

try {
    $pdo = getPDOConnection();
    $pdo->beginTransaction();

    // 1. Resolve or create user
    $resolvedUserId = 0;
    if ($userId > 0) {
        $userCheck = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $userCheck->execute([':id' => $userId]);
        $row = $userCheck->fetch();
        if ($row) {
            $resolvedUserId = (int)$row['id'];
        }
    }

    if ($resolvedUserId === 0 && !empty($email)) {
        $userCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $userCheck->execute([':email' => $email]);
        $row = $userCheck->fetch();
        if ($row) {
            $resolvedUserId = (int)$row['id'];
        } else {
            // Register new customer account automatically
            $userName = !empty($name) ? $name : explode('@', $email)[0];
            $defaultHash = password_hash('WanderWise@2026', PASSWORD_DEFAULT);
            $insertUser = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (:name, :email, :pass, :phone, 'customer')");
            $insertUser->execute([
                ':name'  => $userName,
                ':email' => $email,
                ':pass'  => $defaultHash,
                ':phone' => $phone
            ]);
            $resolvedUserId = (int)$pdo->lastInsertId();
        }
    }

    if ($resolvedUserId <= 0) {
        throw new Exception('Could not associate booking with a valid user.');
    }

    // 2. Fetch destination / place details
    $placeStmt = $pdo->prepare("SELECT id, title, state_region, price_per_person, duration_days FROM places WHERE id = :id");
    $placeStmt->execute([':id' => $placeId]);
    $place = $placeStmt->fetch();

    if (!$place) {
        throw new Exception("Destination (ID: {$placeId}) not found in database.");
    }

    // 3. Calculate total amount
    $pricePerPerson = (float)$place['price_per_person'];
    $totalAmount = $pricePerPerson * $guests;

    // 4. Insert into `bookings` table
    $bookingSql = "INSERT INTO bookings (
        user_id,
        place_id,
        travel_date,
        number_of_guests,
        total_amount,
        booking_status
    ) VALUES (
        :user_id,
        :place_id,
        :travel_date,
        :guests,
        :total_amount,
        'Confirmed'
    )";

    $bookingStmt = $pdo->prepare($bookingSql);
    $bookingStmt->execute([
        ':user_id'      => $resolvedUserId,
        ':place_id'     => $placeId,
        ':travel_date'  => $travelDate,
        ':guests'       => $guests,
        ':total_amount' => $totalAmount
    ]);

    $bookingId = (int)$pdo->lastInsertId();

    // 5. Insert payment record into `payments` table
    $allowedMethods = ['Card', 'UPI', 'NetBanking', 'Cash'];
    if (!in_array($paymentMethod, $allowedMethods, true)) {
        $paymentMethod = 'UPI';
    }

    $txnId = 'TXN_WW_' . date('Ymd') . '_' . strtoupper(substr(uniqid(), -6));
    $paymentSql = "INSERT INTO payments (
        booking_id,
        transaction_id,
        payment_method,
        amount_paid,
        payment_status
    ) VALUES (
        :booking_id,
        :txn_id,
        :method,
        :amount,
        'Success'
    )";

    $paymentStmt = $pdo->prepare($paymentSql);
    $paymentStmt->execute([
        ':booking_id' => $bookingId,
        ':txn_id'     => $txnId,
        ':method'     => $paymentMethod,
        ':amount'     => $totalAmount
    ]);

    $pdo->commit();

    echo json_encode([
        'success'           => true,
        'message'           => 'Booking confirmed successfully!',
        'booking_id'        => $bookingId,
        'booking_reference' => 'WW' . $bookingId,
        'transaction_id'    => $txnId,
        'total_amount'      => $totalAmount,
        'details' => [
            'place_id'         => $placeId,
            'title'            => $place['title'],
            'state_region'     => $place['state_region'],
            'travel_date'      => $travelDate,
            'number_of_guests' => $guests,
            'payment_status'   => 'Success'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error completing booking: ' . $e->getMessage()
    ]);
}
