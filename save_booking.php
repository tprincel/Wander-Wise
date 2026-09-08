<?php
/**
 * Wander Wise - Save Booking Endpoint
 * Method: POST
 * Accepts JSON payload from dashboard booking modal and inserts into bookings table.
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

// Read raw JSON body
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON payload received.'
    ]);
    exit;
}

// Validate required customer fields
$customerName  = isset($data['customerName'])  ? trim($data['customerName'])  : '';
$customerEmail = isset($data['customerEmail']) ? trim($data['customerEmail']) : '';
$customerPhone = isset($data['customerPhone']) ? trim($data['customerPhone']) : '';
$travelDate    = isset($data['travelDate'])    ? trim($data['travelDate'])    : '';
$peopleCount   = isset($data['peopleCount'])   ? trim($data['peopleCount'])   : '';
$package       = isset($data['package']) && is_array($data['package']) ? $data['package'] : null;

if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($travelDate) || empty($peopleCount) || !$package) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required booking fields.'
    ]);
    exit;
}

// Email format validation
if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address provided.'
    ]);
    exit;
}

// Extract package details
$packageName = isset($package['name']) ? trim($package['name']) : 'Custom Package';
$destination = isset($package['destination']) ? trim($package['destination']) : 'Custom Destination';
$duration    = isset($package['duration']) ? trim((string)$package['duration']) : 'Flexible';
$totalAmount = isset($package['price']) ? (float)$package['price'] : 0.00;
$packageType = isset($package['type']) ? trim($package['type']) : 'fixed';

try {
    $pdo = getPDOConnection();

    // Generate unique booking reference
    $bookingReference = 'BW-' . strtoupper(substr(uniqid(), -6));

    $sql = "INSERT INTO bookings (
                booking_reference,
                customer_name,
                customer_email,
                customer_phone,
                travel_date,
                people_count,
                package_name,
                destination,
                duration,
                total_amount,
                package_type,
                booking_status
            ) VALUES (
                :ref,
                :name,
                :email,
                :phone,
                :travel_date,
                :people,
                :pkg_name,
                :dest,
                :duration,
                :total,
                :pkg_type,
                'Confirmed'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ref'         => $bookingReference,
        ':name'        => $customerName,
        ':email'       => $customerEmail,
        ':phone'       => $customerPhone,
        ':travel_date' => $travelDate,
        ':people'      => $peopleCount,
        ':pkg_name'    => $packageName,
        ':dest'        => $destination,
        ':duration'    => $duration,
        ':total'       => $totalAmount,
        ':pkg_type'    => $packageType
    ]);

    $insertedId = $pdo->lastInsertId();
    $displayBookingId = 'BW' . $insertedId;

    echo json_encode([
        'success'           => true,
        'message'           => 'Booking saved successfully!',
        'booking_id'        => $displayBookingId,
        'booking_reference' => $bookingReference
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saving booking to database',
        'details' => $e->getMessage()
    ]);
}