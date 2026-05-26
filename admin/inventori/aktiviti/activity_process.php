<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: aktiviti.php");
    exit();
}

$action = $_POST['action'] ?? '';




// =====================================================
// ADD ACTIVITY
// =====================================================
if ($action === 'add') {

    $activityName = trim($_POST['activity_name']);
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $activityPrice = $_POST['activity_price'];
    $target = trim($_POST['target']);
    $duration = trim($_POST['duration']);
    $defaultCapacity = $_POST['default_capacity'] !== '' ? $_POST['default_capacity'] : null;
    $adminId = $_SESSION['admin_id'];
    

    if (
        !isset($_FILES['activity_images']) ||
        count($_FILES['activity_images']['name']) !== 3
    ) {
        header("Location: aktiviti.php?error=need_3_images");
        exit();
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    foreach ($_FILES['activity_images']['name'] as $index => $imageName) {
        $imageError = $_FILES['activity_images']['error'][$index];
        $imageSize = $_FILES['activity_images']['size'][$index];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if ($imageError !== UPLOAD_ERR_OK) {
            header("Location: aktiviti.php?error=need_3_images");
            exit();
        }

        if (!in_array($imageExt, $allowedTypes)) {
            header("Location: aktiviti.php?error=invalid_image_type");
            exit();
        }

        if ($imageSize > $maxSize) {
            header("Location: aktiviti.php?error=image_too_large");
            exit();
        }
    }

    $insertActivityQuery = "
        INSERT INTO activities (
            activity_name,
            description,
            price,
            target,
            duration,
            default_capacity,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $insertActivityQuery);

    mysqli_stmt_bind_param(
        $stmt,
        "ssdssii",
        $activityName,
        $description,
        $activityPrice,
        $target,
        $duration,
        $defaultCapacity,
        $adminId
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: aktiviti.php?error=insert_failed");
        exit();
    }

    $activityId = mysqli_insert_id($conn);

    $uploadDir = "../../../assets/images/activities/";
    $dbImagePath = "assets/images/activities/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['activity_images']['tmp_name'] as $index => $tmpName) {
        $originalName = $_FILES['activity_images']['name'][$index];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $newImageName = "activity_" . $activityId . "_" . time() . "_" . ($index + 1) . "." . $imageExt;

        $targetPath = $uploadDir . $newImageName;
        $imageUrl = $dbImagePath . $newImageName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            header("Location: aktiviti.php?error=upload_failed");
            exit();
        }

        $isMain = ($index === 0) ? 1 : 0;
        $sortOrder = $index + 1;

        $insertImageQuery = "
            INSERT INTO activity_images (
                activity_id,
                image_url,
                is_main,
                sort_order
            ) VALUES (?, ?, ?, ?)
        ";

        $imageStmt = mysqli_prepare($conn, $insertImageQuery);
        mysqli_stmt_bind_param($imageStmt, "isii", $activityId, $imageUrl, $isMain, $sortOrder);

        if (!mysqli_stmt_execute($imageStmt)) {
            header("Location: aktiviti.php?error=image_insert_failed");
            exit();
        }

        mysqli_stmt_close($imageStmt);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: aktiviti.php?success=activity_added");
    exit();
}


// =====================================================
// EDIT ACTIVITY
// =====================================================
if ($action === 'edit') {

    $activityId = intval($_POST['activity_id'] ?? 0);

    if ($activityId <= 0) {
        header("Location: aktiviti.php?error=invalid_activity");
        exit();
    }

    $activityName = trim($_POST['activity_name']);
    $description = trim($_POST['description']);
    $activityPrice = $_POST['activity_price'] !== '' ? $_POST['activity_price'] : null;
    $target = trim($_POST['target']);
    $duration = trim($_POST['duration']);
    $defaultCapacity = (isset($_POST['default_capacity']) && $_POST['default_capacity'] !== '') ? intval($_POST['default_capacity']) : 0;
    $existingImages = json_decode($_POST['existing_images'] ?? '[]', true);
    $deletedImages = json_decode($_POST['deleted_images'] ?? '[]', true);
    $adminId = $_SESSION['admin_id'];

    if (!is_array($existingImages)) {
        $existingImages = [];
    }

    if (!is_array($deletedImages)) {
        $deletedImages = [];
    }

    $newImageCount = 0;

    if (isset($_FILES['activity_images'])) {
        foreach ($_FILES['activity_images']['name'] as $name) {
            if (!empty($name)) {
                $newImageCount++;
            }
        }
    }

    $totalImages = count($existingImages) + $newImageCount;

    if ($totalImages !== 3) {
        header("Location: aktiviti.php?error=need_3_images");
        exit();
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    if (isset($_FILES['activity_images'])) {
        foreach ($_FILES['activity_images']['name'] as $index => $imageName) {
            if (empty($imageName)) {
                continue;
            }

            $imageError = $_FILES['activity_images']['error'][$index];
            $imageSize = $_FILES['activity_images']['size'][$index];
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if ($imageError !== UPLOAD_ERR_OK) {
                header("Location: aktiviti.php?error=upload_failed");
                exit();
            }

            if (!in_array($imageExt, $allowedTypes)) {
                header("Location: aktiviti.php?error=invalid_image_type");
                exit();
            }

            if ($imageSize > $maxSize) {
                header("Location: aktiviti.php?error=image_too_large");
                exit();
            }
        }
    }

    $updateActivityQuery = "
        UPDATE activities
        SET
            activity_name = ?,
            description = ?,
            price = ?,
            target = ?,
            duration = ?,
            default_capacity = ?,   
            updated_by = ?
        WHERE activity_id = ?
        AND status = 'active'
    ";

    $stmt = mysqli_prepare($conn, $updateActivityQuery);

    mysqli_stmt_bind_param(
        $stmt,
        "ssdssiii",
        $activityName,
        $description,
        $activityPrice,
        $target,
        $duration,
        $defaultCapacity,
        $adminId,
        $activityId

    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: aktiviti.php?error=update_failed");
        exit();
    }

    // Delete gambar lama yang admin tekan X
    if (!empty($deletedImages)) {
        foreach ($deletedImages as $imageId) {
            $imageId = intval($imageId);

            $getImageQuery = "
                SELECT image_url 
                FROM activity_images 
                WHERE image_id = ?
                AND activity_id = ?
                LIMIT 1
            ";

            $getStmt = mysqli_prepare($conn, $getImageQuery);
            mysqli_stmt_bind_param($getStmt, "ii", $imageId, $activityId);
            mysqli_stmt_execute($getStmt);

            $imageResult = mysqli_stmt_get_result($getStmt);
            $imageData = mysqli_fetch_assoc($imageResult);

            if ($imageData) {
                $filePath = "../../" . $imageData['image_url'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $deleteImageQuery = "
                    DELETE FROM activity_images
                    WHERE image_id = ?
                    AND activity_id = ?
                ";

                $deleteStmt = mysqli_prepare($conn, $deleteImageQuery);
                mysqli_stmt_bind_param($deleteStmt, "ii", $imageId, $activityId);
                mysqli_stmt_execute($deleteStmt);
                mysqli_stmt_close($deleteStmt);
            }

            mysqli_stmt_close($getStmt);
        }
    }

    // Upload gambar baru
    $uploadDir = "../../../assets/images/activities/";
    $dbImagePath = "assets/images/activities/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['activity_images'])) {
        foreach ($_FILES['activity_images']['tmp_name'] as $index => $tmpName) {
            $originalName = $_FILES['activity_images']['name'][$index];

            if (empty($originalName)) {
                continue;
            }

            $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newImageName = "activity_" . $activityId . "_" . time() . "_edit_" . ($index + 1) . "." . $imageExt;

            $targetPath = $uploadDir . $newImageName;
            $imageUrl = $dbImagePath . $newImageName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                header("Location: aktiviti.php?error=upload_failed");
                exit();
            }

            $insertImageQuery = "
                INSERT INTO activity_images (
                    activity_id,
                    image_url,
                    is_main,
                    sort_order
                ) VALUES (?, ?, 0, 0)
            ";

            $imageStmt = mysqli_prepare($conn, $insertImageQuery);
            $imageBindStatus = mysqli_stmt_bind_param($imageStmt, "is", $activityId, $imageUrl);

            if (!mysqli_stmt_execute($imageStmt)) {
                header("Location: aktiviti.php?error=image_insert_failed");
                exit();
            }

            mysqli_stmt_close($imageStmt);
        }
    }

    // Susun semula sort_order dan is_main ikut image_id ASC
    $reorderQuery = "
        SELECT image_id
        FROM activity_images
        WHERE activity_id = ?
        ORDER BY image_id ASC
    ";

    $reorderStmt = mysqli_prepare($conn, $reorderQuery);
    mysqli_stmt_bind_param($reorderStmt, "i", $activityId);
    mysqli_stmt_execute($reorderStmt);

    $reorderResult = mysqli_stmt_get_result($reorderStmt);

    $sortOrder = 1;

    while ($image = mysqli_fetch_assoc($reorderResult)) {
        $imageId = $image['image_id'];
        $isMain = ($sortOrder === 1) ? 1 : 0;

        $updateImageQuery = "
            UPDATE activity_images
            SET 
                sort_order = ?,
                is_main = ?
            WHERE image_id = ?
            AND activity_id = ?
        ";

        $updateImageStmt = mysqli_prepare($conn, $updateImageQuery);
        mysqli_stmt_bind_param($updateImageStmt, "iiii", $sortOrder, $isMain, $imageId, $activityId);
        mysqli_stmt_execute($updateImageStmt);
        mysqli_stmt_close($updateImageStmt);

        $sortOrder++;
    }

    mysqli_stmt_close($reorderStmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: aktiviti.php?success=activity_updated");
    exit();
}


// =====================================================
// DELETE ACTIVITY
// =====================================================
if ($action === 'delete') {

    $activityId = intval($_POST['activity_id'] ?? 0);

    if ($activityId <= 0) {
        header("Location: aktiviti.php?error=invalid_activity");
        exit();
    }

    $deleteQuery = "
        UPDATE activities
        SET status = 'deleted'
        WHERE activity_id = ?
    ";

    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $activityId);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: aktiviti.php?success=activity_deleted");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: aktiviti.php?error=delete_failed");
        exit();
    }
}


// =====================================================
// FALLBACK
// =====================================================
header("Location: aktiviti.php?error=invalid_action");
exit();
?>