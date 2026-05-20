<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: bahan.php");
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
// ADD MATERIALS
// =====================================================
if ($action === 'add') {

    $materialName = trim($_POST['material_name']);
    $materialBrand = trim($_POST['material_brand']);
    $materialPrice = $_POST['material_price'];
    $materialStock = intval($_POST['material_stock']);
    $stockStatus = getStockStatus($materialStock);

   
    if (!isset($_FILES['material_images']) || empty($_FILES['material_images']['name'][0])) {
        header("Location: bahan.php?error=need_images");
        exit();
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    $imageName = $_FILES['material_images']['name'][0];
    $tmpName = $_FILES['material_images']['tmp_name'][0];
    $imageError = $_FILES['material_images']['error'][0];
    $imageSize = $_FILES['material_images']['size'][0];
    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if ($imageError !== UPLOAD_ERR_OK) {
        header("Location: bahan.php?error=need_images");
        exit();
    }

    if (!in_array($imageExt, $allowedTypes)) {
        header("Location: bahan.php?error=invalid_image_type");
        exit();
    }

    if ($imageSize > $maxSize) {
        header("Location: bahan.php?error=image_too_large");
        exit();
    }


    $insertMaterialQuery = "
        INSERT INTO materials (
            material_name,
            material_brand,
            material_price,
            material_stock,
            stock_status
        ) VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $insertMaterialQuery);
    mysqli_stmt_bind_param(
        $stmt,
        "ssdis", 
        $materialName,
        $materialBrand,
        $materialPrice,
        $materialStock,
        $stockStatus
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: bahan.php?error=insert_failed");
        exit();
    }

    $materialId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Proses Simpan Gambar Fail
    $uploadDir = "../../../assets/images/materials/";
    $dbImagePath = "assets/images/materials/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newImageName = "material_" . $materialId . "_" . time() . "." . $imageExt;
    $targetPath = $uploadDir . $newImageName;
    $imageUrl = $dbImagePath . $newImageName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        header("Location: bahan.php?error=upload_failed");
        exit();
    }

    // UPDATE path gambar terus masuk ke column row material 
    $updateImageQuery = "
        UPDATE materials 
        SET material_image = ? 
        WHERE material_id = ?
    ";
    $imageStmt = mysqli_prepare($conn, $updateImageQuery);
    mysqli_stmt_bind_param($imageStmt, "si", $imageUrl, $materialId);
    mysqli_stmt_execute($imageStmt);
    mysqli_stmt_close($imageStmt);

    mysqli_close($conn);
    header("Location: bahan.php?success=material_added");
    exit();
}


// =====================================================
// EDIT MATERIAL
// =====================================================
if ($action === 'edit') {

    $materialId = intval($_POST['material_id'] ?? 0);

    if ($materialId <= 0) {
        header("Location: bahan.php?error=invalid_material");
        exit();
    }

    $materialName = trim($_POST['material_name']);
    $materialBrand = trim($_POST['material_brand']);
    $materialPrice = $_POST['material_price'];
    $materialStock = intval($_POST['material_stock']);
    $stockStatus = getStockStatus($materialStock);

    // Check jika admin upload gambar baru untuk gantikan gambar lama
    $hasNewImage = isset($_FILES['material_images']) && !empty($_FILES['material_images']['name'][0]);

    if ($hasNewImage) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        $imageName = $_FILES['material_images']['name'][0];
        $tmpName = $_FILES['material_images']['tmp_name'][0];
        $imageError = $_FILES['material_images']['error'][0];
        $imageSize = $_FILES['material_images']['size'][0];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if ($imageError === UPLOAD_ERR_OK && in_array($imageExt, $allowedTypes) && $imageSize <= $maxSize) {
            
            $uploadDir = "../../../assets/images/materials/";
            $dbImagePath = "assets/images/materials/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Dapatkan info fail gambar lama untuk dipadam dari storage server
            $oldImgQuery = "SELECT material_image FROM materials WHERE material_id = ? LIMIT 1";
            $oldImgStmt = mysqli_prepare($conn, $oldImgQuery);
            mysqli_stmt_bind_param($oldImgStmt, "i", $materialId);
            mysqli_stmt_execute($oldImgStmt);
            $res = mysqli_stmt_get_result($oldImgStmt);
            $oldData = mysqli_fetch_assoc($res);
            mysqli_stmt_close($oldImgStmt);

            if (!empty($oldData['material_image'])) {
                $oldFilePath = "../../../" . $oldData['material_image'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Simpan gambar baru ke server folder
            $newImageName = "material_" . $materialId . "_" . time() . "_edit." . $imageExt;
            if (move_uploaded_file($tmpName, $uploadDir . $newImageName)) {
                $imageUrl = $dbImagePath . $newImageName;
                
                // Kemaskini link db image
                $updateImgDb = "UPDATE materials SET material_image = ? WHERE material_id = ?";
                $imgDbStmt = mysqli_prepare($conn, $updateImgDb);
                mysqli_stmt_bind_param($imgDbStmt, "si", $imageUrl, $materialId);
                mysqli_stmt_execute($imgDbStmt);
                mysqli_stmt_close($imgDbStmt);
            }
        }
    }

    // Kemaskini maklumat teks data bahan
    $updateMaterialQuery = "
        UPDATE materials
        SET
            material_name = ?,
            material_brand = ?,
            material_price = ?,
            material_stock = ?,
            stock_status = ?
        WHERE material_id = ?
        AND status = 'active'
    ";

    $stmt = mysqli_prepare($conn, $updateMaterialQuery);
    mysqli_stmt_bind_param(
        $stmt,
        "ssdisi", // s, s, d, i, s, i
        $materialName,
        $materialBrand,
        $materialPrice,
        $materialStock,
        $stockStatus,
        $materialId
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: bahan.php?error=update_failed");
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: bahan.php?success=material_updated");
    exit();
}


// =====================================================
// DELETE MATERIAL
// =====================================================
if ($action === 'delete') {

    $materialId = intval($_POST['material_id'] ?? 0);

    if ($materialId <= 0) {
        header("Location: bahan.php?error=invalid_material");
        exit();
    }

    $deleteQuery = "
        UPDATE materials
        SET status = 'deleted'
        WHERE material_id = ?
    ";

    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $materialId); // dibetulkan (hanya hantar parameter $materialId)

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: bahan.php?success=material_deleted");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: bahan.php?error=delete_failed");
        exit();
    }
}


// =====================================================
// FALLBACK
// =====================================================
header("Location: bahan.php?error=invalid_action");
exit();
?>