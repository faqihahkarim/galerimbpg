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

if (!isset($_POST['product_id']) || !isset($_POST['product_stock'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing product data'
    ]);
    exit();
}

$productId = intval($_POST['product_id']);
$productStock = intval($_POST['product_stock']);

if ($productId <= 0 || $productStock < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product data'
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
if ($productStock == 0) {
    $stockStatus = 'Tiada Stok';
} elseif ($productStock <= 10) {
    $stockStatus = 'Stok Rendah';
} else {
    $stockStatus = 'Stok Tersedia';
}

$updateQuery = "
    UPDATE products
    SET 
        product_stock = ?,
        stock_status = ?
    WHERE product_id = ?
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

mysqli_stmt_bind_param($stmt, "isi", $productStock, $stockStatus, $productId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Stock updated successfully',
        'new_stock' => $productStock,
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