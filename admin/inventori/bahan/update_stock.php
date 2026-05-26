<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit();
}

if (!isset($_POST['material_id']) || !isset($_POST['material_stock'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing material data'
    ]);
    exit();
}

$materialId = intval($_POST['material_id']);
$materialStock = intval($_POST['material_stock']);

if ($materialId <= 0 || $materialStock < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid material data'
    ]);
    exit();
}

/*
    Tentukan stock_status secara automatik
    Boleh ubah threshold ikut kehendak awak.
    Contoh:
    0 = Tiada Stok
    1-5 = Stok Rendah
    6 ke atas = Ada Stok
*/
if ($materialStock == 0) {
    $stockStatus = 'Tiada Stok';
} elseif ($materialStock <= 10) {
    $stockStatus = 'Stok Rendah';
} else {
    $stockStatus = 'Stok Tersedia';
}

$updateQuery = "
    UPDATE materials
    SET 
        material_stock = ?,
        stock_status = ?
    WHERE material_id = ?
    AND status = 'active'
";

$stmt = mysqli_prepare($conn, $updateQuery);

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare statement failed'
    ]);
    exit();
}

mysqli_stmt_bind_param($stmt, "isi", $materialStock, $stockStatus, $materialId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Stock updated successfully',
        'new_stock' => $materialStock,
        'stock_status' => $stockStatus
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update stock'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>