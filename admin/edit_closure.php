<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: closure_date.php');
    exit;
}

$closure_id = isset($_POST['closure_id']) ? (int) $_POST['closure_id'] : 0;
$closure_date = mysqli_real_escape_string($conn, $_POST['closure_date'] ?? '');
$closure_name = mysqli_real_escape_string($conn, $_POST['closure_name'] ?? '');
$status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Aktif');

if ($closure_id <= 0) {
    header('Location: closure_date.php?error=invalid_id');
    exit;
}

$update = "UPDATE closure_dates SET closure_date = '$closure_date', closure_name = '$closure_name', status = '$status' WHERE closure_id = $closure_id";

mysqli_query($conn, $update);

header('Location: closure_date.php?success=closure_updated');
exit;
