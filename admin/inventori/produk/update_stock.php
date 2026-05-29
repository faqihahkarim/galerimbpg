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
include '../../log.php';

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
$adminId = $_SESSION['admin_id'];

if ($productId <= 0 || $productStock < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product data'
    ]);
    exit();
}

if ($productStock == 0) {
    $stockStatus = 'Tiada Stok';
} elseif ($productStock <= 10) {
    $stockStatus = 'Stok Rendah';
} else {
    $stockStatus = 'Stok Tersedia';
}

// Get product name first
$productName = 'Produk';

$nameQuery = "
    SELECT product_name
    FROM products
    WHERE product_id = ?
    LIMIT 1
";

$nameStmt = mysqli_prepare($conn, $nameQuery);

if ($nameStmt) {
    mysqli_stmt_bind_param($nameStmt, "i", $productId);
    mysqli_stmt_execute($nameStmt);

    $nameResult = mysqli_stmt_get_result($nameStmt);
    $nameData = mysqli_fetch_assoc($nameResult);

    if ($nameData) {
        $productName = $nameData['product_name'];
    }

    mysqli_stmt_close($nameStmt);
}

$updateQuery = "
    UPDATE products
    SET 
        product_stock = ?,
        stock_status = ?,
        updated_by = ?
    WHERE product_id = ?
    AND status = 'active'
";

$stmt = mysqli_prepare($conn, $updateQuery);

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare statement failed: ' . mysqli_error($conn)
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "isii",
    $productStock,
    $stockStatus,
    $adminId,
    $productId
);

if (mysqli_stmt_execute($stmt)) {

    addAdminLog(
        $conn,
        $adminId,
        'product_updated',
        'Stok produk dikemaskini: ' . $productName . ' (' . $productStock . ')',
        'products',
        $productId
    );

    if ($stockStatus === 'Stok Rendah' || $stockStatus === 'Tiada Stok') {
        addAdminLog(
            $conn,
            $adminId,
            'stock_low',
            'Amaran stok: ' . $productName . ' kini ' . $stockStatus . ' (' . $productStock . ')',
            'products',
            $productId
        );
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Stock updated successfully',
        'new_stock' => $productStock,
        'stock_status' => $stockStatus
    ]);

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update stock: ' . mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>