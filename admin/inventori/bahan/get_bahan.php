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
        'message' => 'Product ID not found'
    ]);
    exit();
}

$productId = intval($_GET['id']);

$productQuery = "
    SELECT *
    FROM products
    WHERE product_id = ?
    AND status = 'active'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $productQuery);
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$productResult = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($productResult);

if (!$product) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found'
    ]);
    exit();
}

$imageQuery = "
    SELECT image_id, image_url, is_main
    FROM product_images
    WHERE product_id = ?
    ORDER BY image_id ASC
";

$imageStmt = mysqli_prepare($conn, $imageQuery);
mysqli_stmt_bind_param($imageStmt, "i", $productId);
mysqli_stmt_execute($imageStmt);

$imageResult = mysqli_stmt_get_result($imageStmt);

$images = [];

while ($image = mysqli_fetch_assoc($imageResult)) {
    $images[] = $image;
}

echo json_encode([
    'status' => 'success',
    'product' => $product,
    'images' => $images
]);

mysqli_stmt_close($stmt);
mysqli_stmt_close($imageStmt);
mysqli_close($conn);
?>