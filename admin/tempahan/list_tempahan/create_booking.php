<?php
// list_tempahan/create_booking.php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_login'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include '../../../db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['organization_name', 'package_id', 'slot_id', 'total_participants'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Sanitize inputs
$organization_name = mysqli_real_escape_string($conn, $data['organization_name']);
$package_id = (int)$data['package_id'];
$slot_id = (int)$data['slot_id'];
$total_participants = (int)$data['total_participants'];
$status = isset($data['status']) ? mysqli_real_escape_string($conn, $data['status']) : 'Belum Lulus';
$admin_comment = isset($data['admin_comment']) ? mysqli_real_escape_string($conn, $data['admin_comment']) : '';

// Get package fee
$fee_query = "SELECT fee FROM packages WHERE package_id = ?";
$stmt = mysqli_prepare($conn, $fee_query);
mysqli_stmt_bind_param($stmt, 'i', $package_id);
mysqli_stmt_execute($stmt);
$fee_result = mysqli_stmt_get_result($stmt);
$package = mysqli_fetch_assoc($fee_result);

if (!$package) {
    echo json_encode(['success' => false, 'message' => 'Package not found']);
    exit;
}

$fee_per_pax = (float)$package['fee'];
$total_fee = $fee_per_pax * $total_participants;

// Generate display ID
$count_query = "SELECT COUNT(*) as total FROM bookings";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$next_id = $count_row['total'] + 1;
$display_id = 'BK' . $next_id;

// Insert booking
$insert_query = "INSERT INTO bookings (
    display_id,
    organization_name,
    package_id,
    slot_id,
    total_participants,
    total_fee,
    status,
    admin_comment,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param(
    $stmt,
    'ssiidsss',
    $display_id,
    $organization_name,
    $package_id,
    $slot_id,
    $total_participants,
    $total_fee,
    $status,
    $admin_comment
);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    
    // Optional: Update slot status if fully booked
    $update_slot_query = "
        UPDATE booking_slots 
        SET slot_status = 'booked' 
        WHERE slot_id = ? AND capacity <= (
            SELECT COALESCE(SUM(total_participants), 0) 
            FROM bookings 
            WHERE slot_id = ? AND status != 'Batal'
        )
    ";
    $stmt = mysqli_prepare($conn, $update_slot_query);
    mysqli_stmt_bind_param($stmt, 'ii', $slot_id, $slot_id);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'display_id' => $display_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create booking: ' . mysqli_error($conn)
    ]);
}

exit;
?>