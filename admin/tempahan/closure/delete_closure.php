<?php
session_start();
include '../../../db.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['role'] !=='admin') {
    die("Access denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: closure_date.php?error=invalid_id");
    exit;
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "DELETE FROM closure_dates  WHERE closure_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: closure_date.php?success=rule_deleted");
exit;
?>