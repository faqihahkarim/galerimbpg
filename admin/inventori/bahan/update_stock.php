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

if (!isset($_POST['material_id']) || !isset($_POST['material_stock'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing material data'
    ]);
    exit();
}

$materialId = intval($_POST['material_id']);
$materialStock = intval($_POST['material_stock']);
$adminId = $_SESSION['admin_id'];

if ($materialId <= 0 || $materialStock < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid material data'
    ]);
    exit();
}

// Tentukan stock_status
if ($materialStock == 0) {
    $stockStatus = 'Tiada Stok';
} elseif ($materialStock <= 10) {
    $stockStatus = 'Stok Rendah';
} else {
    $stockStatus = 'Stok Tersedia';
}

// Get material name
$materialName = 'Bahan';

$nameQuery = "
    SELECT material_name
    FROM materials
    WHERE material_id = ?
    LIMIT 1
";

$nameStmt = mysqli_prepare($conn, $nameQuery);

if ($nameStmt) {
    mysqli_stmt_bind_param($nameStmt, "i", $materialId);
    mysqli_stmt_execute($nameStmt);

    $nameResult = mysqli_stmt_get_result($nameStmt);
    $nameData = mysqli_fetch_assoc($nameResult);

    if ($nameData) {
        $materialName = $nameData['material_name'];
    }

    mysqli_stmt_close($nameStmt);
}

$updateQuery = "
    UPDATE materials
    SET 
        material_stock = ?,
        stock_status = ?,
        updated_by = ?
    WHERE material_id = ?
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
    $materialStock,
    $stockStatus,
    $adminId,
    $materialId
);

if (mysqli_stmt_execute($stmt)) {

    addAdminLog(
        $conn,
        $adminId,
        'material_updated',
        'Stok bahan dikemaskini: ' . $materialName . ' (' . $materialStock . ')',
        'materials',
        $materialId
    );

    if ($stockStatus === 'Stok Rendah' || $stockStatus === 'Tiada Stok') {
        addAdminLog(
            $conn,
            $adminId,
            'stock_low',
            'Amaran stok bahan: ' . $materialName . ' kini ' . $stockStatus . ' (' . $materialStock . ')',
            'materials',
            $materialId
        );
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Stock updated successfully',
        'new_stock' => $materialStock,
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