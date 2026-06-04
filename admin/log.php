<?php

function addAdminLog($conn, $adminId, $actionType, $description, $relatedTable = null, $relatedId = null)
{
    $query = "
        INSERT INTO admin_logs (
            admin_id,
            action_type,
            description,
            related_table,
            related_id
        ) VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Prepare log failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isssi",
        $adminId,
        $actionType,
        $description,
        $relatedTable,
        $relatedId
    );

    if (!mysqli_stmt_execute($stmt)) {
        die("Execute log failed: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
    return true;
}

?>