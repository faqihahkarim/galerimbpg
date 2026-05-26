<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_login'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

include '../../../db.php';

if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Activity ID not found'
    ]);
    exit();
}

$activityId = intval($_GET['id']);

$activityQuery = "
    SELECT *
    FROM activities
    WHERE activity_id = ?
    AND status = 'active'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $activityQuery);
mysqli_stmt_bind_param($stmt, "i", $activityId);
mysqli_stmt_execute($stmt);

$activityResult = mysqli_stmt_get_result($stmt);
$activity = mysqli_fetch_assoc($activityResult);

if (!$activity) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Activity not found'
    ]);
    exit();
}

$imageQuery = "
    SELECT image_id, image_url, is_main
    FROM activity_images
    WHERE activity_id = ?
    ORDER BY image_id ASC
";

$imageStmt = mysqli_prepare($conn, $imageQuery);
mysqli_stmt_bind_param($imageStmt, "i", $activityId);
mysqli_stmt_execute($imageStmt);

$imageResult = mysqli_stmt_get_result($imageStmt);

$images = [];

while ($image = mysqli_fetch_assoc($imageResult)) {
    $images[] = $image;
}

echo json_encode([
    'status' => 'success',
    'activity' => $activity,
    'images' => $images
]);

mysqli_stmt_close($stmt);
mysqli_stmt_close($imageStmt);
mysqli_close($conn);
?>