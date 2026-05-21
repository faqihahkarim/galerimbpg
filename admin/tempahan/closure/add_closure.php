<?php
session_start();
include '../../../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit;
}

$closure_date = mysqli_real_escape_string($conn, $_POST['closure_date']);
$closure_name = mysqli_real_escape_string($conn, $_POST['closure_name']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

$sql = "
    INSERT INTO closure_dates (
        closure_date,
        closure_name,
        status
    ) VALUES (
        '$closure_date',
        '$closure_name',
        '$status'
    )
";

mysqli_query($conn, $sql);

header("Location: closure_date.php?success=closure_added");
exit;