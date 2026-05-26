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
        'message' => 'Material ID not found'
    ]);
    exit();
}

$materialId = intval($_GET['id']);

$materialQuery = "
    SELECT *
    FROM materials
    WHERE material_id = ?
    AND status = 'active'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $materialQuery);
mysqli_stmt_bind_param($stmt, "i", $materialId);
mysqli_stmt_execute($stmt);

$materialResult = mysqli_stmt_get_result($stmt);
$material = mysqli_fetch_assoc($materialResult);

if (!$material) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Material not found'
    ]);
    exit();
}


$images = [];
if (!empty($material['material_image'])) {
    $images[] = [
        'image_id' => $material['material_id'],
        'image_url' => $material['material_image'] 
    ];
}

echo json_encode([
    'status' => 'success',
    'material' => $material,
    'images' => $images
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>