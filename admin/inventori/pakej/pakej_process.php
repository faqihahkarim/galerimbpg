<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pakej.php");
    exit();
}

$action = $_POST['action'] ?? '';

// =====================================================
// ADD PACKAGE (FIXED: IMAGE LOGIC ORDER)
// =====================================================
if ($action === 'add') {
    $packageName = trim($_POST['package_name']);
    $capacity = intval($_POST['capacity']);
    $description = trim($_POST['description']);
    $requiresActivity = intval($_POST['requires_activity_selection'] ?? 0);
    $activityIds = isset($_POST['activity_ids']) ? $_POST['activity_ids'] : []; 

    
    if ($requiresActivity === 1 && !empty($activityIds)) {
        $relationQuery = "INSERT INTO package_activities (package_id, activity_id) VALUES (?, ?)";
        $relStmt = mysqli_prepare($conn, $relationQuery);
        
        foreach ($activityIds as $actId) {
            $cleanActId = intval($actId);
            mysqli_stmt_bind_param($relStmt, "ii", $packageId, $cleanActId);
            mysqli_stmt_execute($relStmt);
        }
        mysqli_stmt_close($relStmt);
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    $imageName = $_FILES['package_image']['name'];
    $tmpName = $_FILES['package_image']['tmp_name'];
    $imageError = $_FILES['package_image']['error'];
    $imageSize = $_FILES['package_image']['size'];
    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if ($imageError !== UPLOAD_ERR_OK) {
        header("Location: pakej.php?error=need_image");
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

    // 2. NEW: Generate unique image name BEFORE inserting into database
    $uploadDir = "../../../assets/images/packages/";
    $dbImagePath = "assets/images/packages/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Using microtime() or uniqid() since we don't have the auto-increment packageId yet
    $newImageName = "pakej_" . uniqid() . "_" . time() . "." . $imageExt;
    $targetPath = $uploadDir . $newImageName;
    $imageUrl = $dbImagePath . $newImageName; // Now $imageUrl is defined and NOT null!

    // Move file to server directory first
    if (!move_uploaded_file($tmpName, $targetPath)) {
        header("Location: pakej.php?error=upload_failed");
        exit();
    }

    // 3. Insert text data and image_url together safely
    $insertQuery = "
        INSERT INTO packages (
            package_name, 
            capacity, 
            description, 
            image_url,
            requires_activity_selection, 
            status
        ) VALUES (?, ?, ?, ?, ?, 'active')
    ";
    
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "sissi", $packageName, $capacity, $description, $imageUrl, $requiresActivity);
    
    if (!mysqli_stmt_execute($stmt)) {
        // If DB insert fails, clean up the uploaded image from directory
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
        header("Location: pakej.php?error=insert_failed");
        exit();
    }

    $packageId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // 4. Handle Relational mapping injection if user chose "Ya"
    if ($requiresActivity === 1 && !empty($activityId)) {
        $relationQuery = "INSERT INTO package_activities (package_id, activity_id) VALUES (?, ?)";
        $relStmt = mysqli_prepare($conn, $relationQuery);
        mysqli_stmt_bind_param($relStmt, "ii", $packageId, $activityId);
        mysqli_stmt_execute($relStmt);
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

    $packageName = trim($_POST['package_name']);
    $capacity = intval($_POST['capacity']);
    $description = trim($_POST['description']);
    $requiresActivity = intval($_POST['requires_activity_selection'] ?? 0);
    $activityId = !empty($_POST['activity_id']) ? intval($_POST['activity_id']) : null;

    // 1. Process image replacement optionally if uploaded
    if (isset($_FILES['package_image']) && !empty($_FILES['package_image']['name'])) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        $imageName = $_FILES['package_image']['name'];
        $tmpName = $_FILES['package_image']['tmp_name'];
        $imageError = $_FILES['package_image']['error'];
        $imageSize = $_FILES['package_image']['size'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if ($imageError === UPLOAD_ERR_OK && in_array($imageExt, $allowedTypes) && $imageSize <= $maxSize) {
            $uploadDir = "../../../assets/images/packages/";
            $dbImagePath = "assets/images/packages/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Remove legacy file asset from system stack storage
            $oldQuery = "SELECT image_url FROM packages WHERE package_id = ? LIMIT 1";
            $oldStmt = mysqli_prepare($conn, $oldQuery);
            mysqli_stmt_bind_param($oldStmt, "i", $packageId);
            mysqli_stmt_execute($oldStmt);
            $res = mysqli_stmt_get_result($oldStmt);
            $oldData = mysqli_fetch_assoc($res);
            mysqli_stmt_close($oldStmt);

            if (!empty($oldData['image_url'])) {
                $oldFilePath = "../../../" . $oldData['image_url'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Write new file down
            $newImageName = "pakej_" . $packageId . "_" . time() . "_edit." . $imageExt;
            if (move_uploaded_file($tmpName, $uploadDir . $newImageName)) {
                $imageUrl = $dbImagePath . $newImageName;
                
                $updateImgDb = "UPDATE packages SET image_url = ? WHERE package_id = ?";
                $imgDbStmt = mysqli_prepare($conn, $updateImgDb);
                mysqli_stmt_bind_param($imgDbStmt, "si", $imageUrl, $packageId);
                mysqli_stmt_execute($imgDbStmt);
                mysqli_stmt_close($imgDbStmt);
            }
        }
    }

    // 2. Base content update query execution
    $updateQuery = "
        UPDATE packages 
        SET package_name = ?, capacity = ?, description = ?, requires_activity_selection = ? 
        WHERE package_id = ? AND status = 'active'
    ";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sisii", $packageName, $capacity, $description, $requiresActivity, $packageId);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: pakej.php?error=update_failed");
        exit();
    }
    mysqli_stmt_close($stmt);

    // 3. Clear existing relational table mapping dependencies out first
    $clearRelationQuery = "DELETE FROM package_activities WHERE package_id = ?";
    $clearStmt = mysqli_prepare($conn, $clearRelationQuery);
    mysqli_stmt_bind_param($clearStmt, "i", $packageId);
    mysqli_stmt_execute($clearStmt);
    mysqli_stmt_close($clearStmt);

    // 4. Re-inject data cleanly if status still demands activity link
    if ($requiresActivity === 1 && !empty($activityId)) {
        $relinkQuery = "INSERT INTO package_activities (package_id, activity_id) VALUES (?, ?)";
        $relinkStmt = mysqli_prepare($conn, $relinkQuery);
        mysqli_stmt_bind_param($relinkStmt, "ii", $packageId, $activityId);
        mysqli_stmt_execute($relinkStmt);
        mysqli_stmt_close($relinkStmt);
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

    // Soft delete mapping standard
    $deleteQuery = "UPDATE packages SET status = 'deleted' WHERE package_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $packageId);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Optional cascade: Clear associated relation assignments
        $clearRel = "DELETE FROM package_activities WHERE package_id = ?";
        $clearStmt = mysqli_prepare($conn, $clearRel);
        mysqli_stmt_bind_param($clearStmt, "i", $packageId);
        mysqli_stmt_execute($clearStmt);
        mysqli_stmt_close($clearStmt);

        mysqli_close($conn);
        header("Location: pakej.php?success=package_deleted");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: pakej.php?error=delete_failed");
        exit();
    }
}

// Fallback handling redirect
header("Location: pakej.php?error=invalid_action");
exit();
?>