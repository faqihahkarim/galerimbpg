<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../../login.php");
    exit();
}

include '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pakej.php");
    exit();
}

$action = $_POST['action'] ?? '';
$adminId = $_SESSION['admin_id'];


// =====================================================
// ADD PACKAGE
// =====================================================
if ($action === 'add') {

    $packageName = trim($_POST['package_name'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $description = trim($_POST['package_description'] ?? '');
    $requiresActivity = intval($_POST['requires_activity_selection'] ?? 0);
    $activityIds = $_POST['activity_id'] ?? [];

    if (!is_array($activityIds)) {
        $activityIds = [$activityIds];
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!isset($_FILES['package_image']) || $_FILES['package_image']['error'] !== UPLOAD_ERR_OK) {
        header("Location: pakej.php?error=need_image");
        exit();
    }

    $imageName = $_FILES['package_image']['name'];
    $tmpName = $_FILES['package_image']['tmp_name'];
    $imageSize = $_FILES['package_image']['size'];
    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (!in_array($imageExt, $allowedTypes)) {
        header("Location: pakej.php?error=invalid_image_type");
        exit();
    }

    if ($imageSize > $maxSize) {
        header("Location: pakej.php?error=image_too_large");
        exit();
    }

    $uploadDir = "../../../assets/images/packages/";
    $dbImagePath = "assets/images/packages/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newImageName = "pakej_" . uniqid() . "_" . time() . "." . $imageExt;
    $targetPath = $uploadDir . $newImageName;
    $imageUrl = $dbImagePath . $newImageName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        header("Location: pakej.php?error=upload_failed");
        exit();
    }

    $insertQuery = "
        INSERT INTO packages (
            package_name,
            capacity,
            description,
            image_url,
            requires_activity_selection,
            status,
            created_by
        ) VALUES (?, ?, ?, ?, ?, 'active', ?)
    ";

    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param(
        $stmt,
        "sissii",
        $packageName,
        $capacity,
        $description,
        $imageUrl,
        $requiresActivity,
        $adminId
    );

    if (!mysqli_stmt_execute($stmt)) {
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }

        header("Location: pakej.php?error=insert_failed");
        exit();
    }

    $packageId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($requiresActivity === 1 && !empty($activityIds)) {
        $relationQuery = "
            INSERT INTO package_activities (package_id, activity_id)
            VALUES (?, ?)
        ";

        $relStmt = mysqli_prepare($conn, $relationQuery);

        foreach ($activityIds as $actId) {
            $cleanActId = intval($actId);

            if ($cleanActId > 0) {
                mysqli_stmt_bind_param($relStmt, "ii", $packageId, $cleanActId);
                mysqli_stmt_execute($relStmt);
            }
        }

        mysqli_stmt_close($relStmt);
    }

    mysqli_close($conn);
    header("Location: pakej.php?success=package_added");
    exit();
}


// =====================================================
// EDIT PACKAGE
// =====================================================
if ($action === 'edit') {

    $packageId = intval($_POST['package_id'] ?? 0);

    if ($packageId <= 0) {
        header("Location: pakej.php?error=invalid_package");
        exit();
    }

    $packageName = trim($_POST['package_name'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $description = trim($_POST['package_description'] ?? '');
    $requiresActivity = intval($_POST['requires_activity_selection'] ?? 0);
    $activityIds = $_POST['activity_id'] ?? [];

    if (!is_array($activityIds)) {
        $activityIds = [$activityIds];
    }

    // Optional image update
    if (isset($_FILES['package_image']) && !empty($_FILES['package_image']['name'])) {

        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        $imageName = $_FILES['package_image']['name'];
        $tmpName = $_FILES['package_image']['tmp_name'];
        $imageError = $_FILES['package_image']['error'];
        $imageSize = $_FILES['package_image']['size'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if ($imageError !== UPLOAD_ERR_OK) {
            header("Location: pakej.php?error=image_upload_error");
            exit();
        }

        if (!in_array($imageExt, $allowedTypes)) {
            header("Location: pakej.php?error=invalid_image_type");
            exit();
        }

        if ($imageSize > $maxSize) {
            header("Location: pakej.php?error=image_too_large");
            exit();
        }

        $uploadDir = "../../../assets/images/packages/";
        $dbImagePath = "assets/images/packages/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $oldQuery = "SELECT image_url FROM packages WHERE package_id = ? LIMIT 1";
        $oldStmt = mysqli_prepare($conn, $oldQuery);
        mysqli_stmt_bind_param($oldStmt, "i", $packageId);
        mysqli_stmt_execute($oldStmt);

        $oldResult = mysqli_stmt_get_result($oldStmt);
        $oldData = mysqli_fetch_assoc($oldResult);
        mysqli_stmt_close($oldStmt);

        if (!empty($oldData['image_url'])) {
            $oldFilePath = "../../../" . $oldData['image_url'];

            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $newImageName = "pakej_" . $packageId . "_" . time() . "_edit." . $imageExt;
        $targetPath = $uploadDir . $newImageName;
        $imageUrl = $dbImagePath . $newImageName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            header("Location: pakej.php?error=upload_failed");
            exit();
        }

        $updateImageQuery = "
            UPDATE packages
            SET image_url = ?
            WHERE package_id = ?
        ";

        $imgStmt = mysqli_prepare($conn, $updateImageQuery);
        mysqli_stmt_bind_param($imgStmt, "si", $imageUrl, $packageId);

        if (!mysqli_stmt_execute($imgStmt)) {
            header("Location: pakej.php?error=image_update_failed");
            exit();
        }

        mysqli_stmt_close($imgStmt);
    }

    $updateQuery = "
        UPDATE packages
        SET
            package_name = ?,
            capacity = ?,
            description = ?,
            requires_activity_selection = ?,
            updated_by = ?
        WHERE package_id = ?
        AND status = 'active'
    ";

    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param(
        $stmt,
        "sisiii",
        $packageName,
        $capacity,
        $description,
        $requiresActivity,
        $adminId,
        $packageId
    );

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: pakej.php?error=update_failed");
        exit();
    }

    mysqli_stmt_close($stmt);

    $clearRelationQuery = "DELETE FROM package_activities WHERE package_id = ?";
    $clearStmt = mysqli_prepare($conn, $clearRelationQuery);
    mysqli_stmt_bind_param($clearStmt, "i", $packageId);
    mysqli_stmt_execute($clearStmt);
    mysqli_stmt_close($clearStmt);

    if ($requiresActivity === 1 && !empty($activityIds)) {
        $relationQuery = "
            INSERT INTO package_activities (package_id, activity_id)
            VALUES (?, ?)
        ";

        $relStmt = mysqli_prepare($conn, $relationQuery);

        foreach ($activityIds as $actId) {
            $cleanActId = intval($actId);

            if ($cleanActId > 0) {
                mysqli_stmt_bind_param($relStmt, "ii", $packageId, $cleanActId);
                mysqli_stmt_execute($relStmt);
            }
        }

        mysqli_stmt_close($relStmt);
    }

    mysqli_close($conn);
    header("Location: pakej.php?success=package_updated");
    exit();
}


// =====================================================
// DELETE PACKAGE
// =====================================================
if ($action === 'delete') {

    $packageId = intval($_POST['package_id'] ?? 0);

    if ($packageId <= 0) {
        header("Location: pakej.php?error=invalid_package");
        exit();
    }

    $deleteQuery = "
        UPDATE packages
        SET status = 'deleted'
        WHERE package_id = ?
    ";

    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $packageId);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: pakej.php?error=delete_failed");
        exit();
    }

    mysqli_stmt_close($stmt);

    $clearRelQuery = "DELETE FROM package_activities WHERE package_id = ?";
    $clearStmt = mysqli_prepare($conn, $clearRelQuery);
    mysqli_stmt_bind_param($clearStmt, "i", $packageId);
    mysqli_stmt_execute($clearStmt);
    mysqli_stmt_close($clearStmt);

    mysqli_close($conn);
    header("Location: pakej.php?success=package_deleted");
    exit();
}


// =====================================================
// FALLBACK
// =====================================================
mysqli_close($conn);
header("Location: pakej.php?error=invalid_action");
exit();
?>