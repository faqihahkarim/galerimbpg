<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produk.php");
    exit();
}

$action = $_POST['action'] ?? '';


// =====================================================
// FUNCTION: Tentukan status stok
// =====================================================
function getStockStatus($stock) {
    if ($stock == 0) {
        return 'Tiada Stok';
    } elseif ($stock <= 10) {
        return 'Stok Rendah';
    } else {
        return 'Stok Tersedia';
    }
}


// =====================================================
// ADD PRODUCT
// =====================================================
if ($action === 'add') {

    $productName = trim($_POST['product_name']);
    $productType = trim($_POST['product_type']);
    $productMotif = trim($_POST['product_motif']);
    $productWeight = $_POST['product_weight'] !== '' ? $_POST['product_weight'] : null;
    $productHeight = $_POST['product_height'] !== '' ? $_POST['product_height'] : null;
    $productDiameter = $_POST['product_diameter'] !== '' ? $_POST['product_diameter'] : null;
    $productPrice = $_POST['product_price'];
    $productStock = intval($_POST['product_stock']);
    $stockStatus = getStockStatus($productStock);

    if (
        !isset($_FILES['product_images']) ||
        count($_FILES['product_images']['name']) !== 3
    ) {
        header("Location: produk.php?error=need_3_images");
        exit();
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    foreach ($_FILES['product_images']['name'] as $index => $imageName) {
        $imageError = $_FILES['product_images']['error'][$index];
        $imageSize = $_FILES['product_images']['size'][$index];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if ($imageError !== UPLOAD_ERR_OK) {
            header("Location: produk.php?error=need_3_images");
            exit();
        }

        if (!in_array($imageExt, $allowedTypes)) {
            header("Location: produk.php?error=invalid_image_type");
            exit();
        }

        if ($imageSize > $maxSize) {
            header("Location: produk.php?error=image_too_large");
            exit();
        }
    }

    $insertProductQuery = "
        INSERT INTO products (
            product_name,
            product_type,
            product_motif,
            product_weight,
            product_height,
            product_diameter,
            product_price,
            product_stock,
            stock_status,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ";

    $stmt = mysqli_prepare($conn, $insertProductQuery);

    mysqli_stmt_bind_param(
        $stmt,
        "sssddddis",
        $productName,
        $productType,
        $productMotif,
        $productWeight,
        $productHeight,
        $productDiameter,
        $productPrice,
        $productStock,
        $stockStatus
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: produk.php?error=insert_failed");
        exit();
    }

    $productId = mysqli_insert_id($conn);

    $uploadDir = "../../../assets/images/products/";
    $dbImagePath = "assets/images/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['product_images']['tmp_name'] as $index => $tmpName) {
        $originalName = $_FILES['product_images']['name'][$index];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $newImageName = "product_" . $productId . "_" . time() . "_" . ($index + 1) . "." . $imageExt;

        $targetPath = $uploadDir . $newImageName;
        $imageUrl = $dbImagePath . $newImageName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            header("Location: produk.php?error=upload_failed");
            exit();
        }

        $isMain = ($index === 0) ? 1 : 0;
        $sortOrder = $index + 1;

        $insertImageQuery = "
            INSERT INTO product_images (
                product_id,
                image_url,
                is_main,
                sort_order
            ) VALUES (?, ?, ?, ?)
        ";

        $imageStmt = mysqli_prepare($conn, $insertImageQuery);
        mysqli_stmt_bind_param($imageStmt, "isii", $productId, $imageUrl, $isMain, $sortOrder);

        if (!mysqli_stmt_execute($imageStmt)) {
            header("Location: produk.php?error=image_insert_failed");
            exit();
        }

        mysqli_stmt_close($imageStmt);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: produk.php?success=product_added");
    exit();
}


// =====================================================
// EDIT PRODUCT
// =====================================================
if ($action === 'edit') {

    $productId = intval($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        header("Location: produk.php?error=invalid_product");
        exit();
    }

    $productName = trim($_POST['product_name']);
    $productType = trim($_POST['product_type']);
    $productMotif = trim($_POST['product_motif']);
    $productWeight = $_POST['product_weight'] !== '' ? $_POST['product_weight'] : null;
    $productHeight = $_POST['product_height'] !== '' ? $_POST['product_height'] : null;
    $productDiameter = $_POST['product_diameter'] !== '' ? $_POST['product_diameter'] : null;
    $productPrice = $_POST['product_price'];
    $productStock = intval($_POST['product_stock']);
    $stockStatus = getStockStatus($productStock);

    $existingImages = json_decode($_POST['existing_images'] ?? '[]', true);
    $deletedImages = json_decode($_POST['deleted_images'] ?? '[]', true);

    if (!is_array($existingImages)) {
        $existingImages = [];
    }

    if (!is_array($deletedImages)) {
        $deletedImages = [];
    }

    $newImageCount = 0;

    if (isset($_FILES['product_images'])) {
        foreach ($_FILES['product_images']['name'] as $name) {
            if (!empty($name)) {
                $newImageCount++;
            }
        }
    }

    $totalImages = count($existingImages) + $newImageCount;

    if ($totalImages !== 3) {
        header("Location: produk.php?error=need_3_images");
        exit();
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    if (isset($_FILES['product_images'])) {
        foreach ($_FILES['product_images']['name'] as $index => $imageName) {
            if (empty($imageName)) {
                continue;
            }

            $imageError = $_FILES['product_images']['error'][$index];
            $imageSize = $_FILES['product_images']['size'][$index];
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if ($imageError !== UPLOAD_ERR_OK) {
                header("Location: produk.php?error=upload_failed");
                exit();
            }

            if (!in_array($imageExt, $allowedTypes)) {
                header("Location: produk.php?error=invalid_image_type");
                exit();
            }

            if ($imageSize > $maxSize) {
                header("Location: produk.php?error=image_too_large");
                exit();
            }
        }
    }

    $updateProductQuery = "
        UPDATE products
        SET
            product_name = ?,
            product_type = ?,
            product_motif = ?,
            product_weight = ?,
            product_height = ?,
            product_diameter = ?,
            product_price = ?,
            product_stock = ?,
            stock_status = ?
        WHERE product_id = ?
        AND status = 'active'
    ";

    $stmt = mysqli_prepare($conn, $updateProductQuery);

    mysqli_stmt_bind_param(
        $stmt,
        "sssddddisi",
        $productName,
        $productType,
        $productMotif,
        $productWeight,
        $productHeight,
        $productDiameter,
        $productPrice,
        $productStock,
        $stockStatus,
        $productId
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: produk.php?error=update_failed");
        exit();
    }

    // Delete gambar lama yang admin tekan X
    if (!empty($deletedImages)) {
        foreach ($deletedImages as $imageId) {
            $imageId = intval($imageId);

            $getImageQuery = "
                SELECT image_url 
                FROM product_images 
                WHERE image_id = ?
                AND product_id = ?
                LIMIT 1
            ";

            $getStmt = mysqli_prepare($conn, $getImageQuery);
            mysqli_stmt_bind_param($getStmt, "ii", $imageId, $productId);
            mysqli_stmt_execute($getStmt);

            $imageResult = mysqli_stmt_get_result($getStmt);
            $imageData = mysqli_fetch_assoc($imageResult);

            if ($imageData) {
                $filePath = "../../" . $imageData['image_url'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $deleteImageQuery = "
                    DELETE FROM product_images
                    WHERE image_id = ?
                    AND product_id = ?
                ";

                $deleteStmt = mysqli_prepare($conn, $deleteImageQuery);
                mysqli_stmt_bind_param($deleteStmt, "ii", $imageId, $productId);
                mysqli_stmt_execute($deleteStmt);
                mysqli_stmt_close($deleteStmt);
            }

            mysqli_stmt_close($getStmt);
        }
    }

    // Upload gambar baru
    $uploadDir = "../../../assets/images/products/";
    $dbImagePath = "assets/images/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['product_images'])) {
        foreach ($_FILES['product_images']['tmp_name'] as $index => $tmpName) {
            $originalName = $_FILES['product_images']['name'][$index];

            if (empty($originalName)) {
                continue;
            }

            $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newImageName = "product_" . $productId . "_" . time() . "_edit_" . ($index + 1) . "." . $imageExt;

            $targetPath = $uploadDir . $newImageName;
            $imageUrl = $dbImagePath . $newImageName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                header("Location: produk.php?error=upload_failed");
                exit();
            }

            $insertImageQuery = "
                INSERT INTO product_images (
                    product_id,
                    image_url,
                    is_main,
                    sort_order
                ) VALUES (?, ?, 0, 0)
            ";

            $imageStmt = mysqli_prepare($conn, $insertImageQuery);
            mysqli_stmt_bind_param($imageStmt, "is", $productId, $imageUrl);

            if (!mysqli_stmt_execute($imageStmt)) {
                header("Location: produk.php?error=image_insert_failed");
                exit();
            }

            mysqli_stmt_close($imageStmt);
        }
    }

    // Susun semula sort_order dan is_main ikut image_id ASC
    $reorderQuery = "
        SELECT image_id
        FROM product_images
        WHERE product_id = ?
        ORDER BY image_id ASC
    ";

    $reorderStmt = mysqli_prepare($conn, $reorderQuery);
    mysqli_stmt_bind_param($reorderStmt, "i", $productId);
    mysqli_stmt_execute($reorderStmt);

    $reorderResult = mysqli_stmt_get_result($reorderStmt);

    $sortOrder = 1;

    while ($image = mysqli_fetch_assoc($reorderResult)) {
        $imageId = $image['image_id'];
        $isMain = ($sortOrder === 1) ? 1 : 0;

        $updateImageQuery = "
            UPDATE product_images
            SET 
                sort_order = ?,
                is_main = ?
            WHERE image_id = ?
            AND product_id = ?
        ";

        $updateImageStmt = mysqli_prepare($conn, $updateImageQuery);
        mysqli_stmt_bind_param($updateImageStmt, "iiii", $sortOrder, $isMain, $imageId, $productId);
        mysqli_stmt_execute($updateImageStmt);
        mysqli_stmt_close($updateImageStmt);

        $sortOrder++;
    }

    mysqli_stmt_close($reorderStmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: produk.php?success=product_updated");
    exit();
}


// =====================================================
// DELETE PRODUCT
// =====================================================
if ($action === 'delete') {

    $productId = intval($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        header("Location: produk.php?error=invalid_product");
        exit();
    }

    $deleteQuery = "
        UPDATE products
        SET status = 'deleted'
        WHERE product_id = ?
    ";

    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $productId);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: produk.php?success=product_deleted");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: produk.php?error=delete_failed");
        exit();
    }
}


// =====================================================
// FALLBACK
// =====================================================
header("Location: produk.php?error=invalid_action");
exit();
?>