<?php
// get_available_slots.php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_login'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

include '../../../db.php';

$package_id = isset($_GET['package_id']) ? (int) $_GET['package_id'] : 0;

if ($package_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid package ID.',
        'debug' => ['package_id_received' => $_GET['package_id'] ?? 'none']
    ]);
    exit;
}

// Get ALL slots (available + booked) for debugging
$query = "
    SELECT 
        slot_id,
        slot_date,
        start_time,
        end_time,
        slot_status
    FROM booking_slots
    WHERE package_id = $package_id
        AND slot_date >= CURDATE()
        AND slot_status = 'available'
    ORDER BY slot_date ASC, start_time ASC
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $slots = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $slots[] = [
            'slot_id' => $row['slot_id'],
            'slot_date' => $row['slot_date'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'slot_status' => $row['slot_status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'slots' => $slots,
        'debug' => [
            'package_id' => $package_id,
            'total_slots' => count($slots),
            'available_slots' => count(array_filter($slots, function($s) { return $s['slot_status'] === 'available'; })),
            'booked_slots' => count(array_filter($slots, function($s) { return $s['slot_status'] === 'booked'; }))
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No slots found for this package.',
        'debug' => [
            'package_id' => $package_id,
            'query' => $query
        ]
    ]);
}

exit;
?>