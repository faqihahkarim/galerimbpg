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
        'message' => 'Package ID not found'
    ]);
    exit();
}

$packageId = intval($_GET['id']);


$packageQuery = "
    SELECT *
    FROM packages
    WHERE package_id = ?
    AND status = 'active'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $packageQuery);
mysqli_stmt_bind_param($stmt, "i", $packageId);
mysqli_stmt_execute($stmt);

$packageResult = mysqli_stmt_get_result($stmt);
$package = mysqli_fetch_assoc($packageResult);

if (!$package) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Pakej tidak ditemui'
    ]);
    exit();
}
mysqli_stmt_close($stmt);

// 2. Cari jika ada satu activity_id berkembar di jadual package_activities
$assignedActivityIds = [];

if ((int)$package['requires_activity_selection'] === 1) {
    $relationQuery = "SELECT activity_id FROM package_activities WHERE package_id = ?";
    $relStmt = mysqli_prepare($conn, $relationQuery);
    mysqli_stmt_bind_param($relStmt, "i", $packageId);
    mysqli_stmt_execute($relStmt);
    $relResult = mysqli_stmt_get_result($relStmt);
    
    while ($row = mysqli_fetch_assoc($relResult)) {
        $assignedActivityIds[] = (int)$row['activity_id'];
    }
    mysqli_stmt_close($relStmt);
}

echo json_encode([
    'status' => 'success',
    'package' => $package,
    'assigned_activity_ids' => $assignedActivityIds // Hantar dalam bentuk array []
]);

mysqli_close($conn);
?>