<?php
// get_booking_activities.php
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

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'booking'; // 'booking' or 'all'

// If mode is 'all', return all active activities
if ($mode === 'all') {
    $query = "
        SELECT 
            a.activity_id,
            a.activity_name,
            a.description,
            a.default_capacity,
            ai.image_url
        FROM activities a
        LEFT JOIN activity_images ai ON a.activity_id = ai.activity_id AND ai.is_main = 1
        WHERE a.status = 'active'
        ORDER BY a.activity_id ASC
    ";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = [
                'activity_id' => $row['activity_id'],
                'activity_name' => $row['activity_name'],
                'description' => $row['description'],
                'participant_count' => 0, // No count for all activities mode
                'default_capacity' => (int) $row['default_capacity'],
                'image_url' => $row['image_url'] ? '../' . $row['image_url'] : ''
            ];
        }
        
        echo json_encode([
            'success' => true,
            'activities' => $activities
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No activities found.'
        ]);
    }
    exit;
}

// Original mode: Get booking-specific activities
if ($booking_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid booking ID.'
    ]);
    exit;
}

$query = "
    SELECT 
        ba.activity_id,
        ba.participant_count,
        a.activity_name,
        a.description,
        a.default_capacity,
        ai.image_url
    FROM booking_activities ba
    LEFT JOIN activities a ON ba.activity_id = a.activity_id
    LEFT JOIN activity_images ai ON a.activity_id = ai.activity_id AND ai.is_main = 1
    WHERE ba.booking_id = $booking_id
    ORDER BY a.activity_id ASC
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = [
            'activity_id' => $row['activity_id'],
            'activity_name' => $row['activity_name'],
            'description' => $row['description'],
            'participant_count' => (int) $row['participant_count'],
            'default_capacity' => (int) $row['default_capacity'],
            'image_url' => $row['image_url'] ? '../' . $row['image_url'] : ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
} else {
    // No activities found for this booking, return empty array
    echo json_encode([
        'success' => true,
        'activities' => [],
        'message' => 'No activities found for this booking.'
    ]);
}

exit;
?>