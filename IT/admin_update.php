<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['role'] !== 'it_officer') {
    die("Access denied");
}

$id = $_POST['admin_id'];
$email = $_POST['email'];
$role = $_POST['role'];
$name= $_POST['admin_name'];

$stmt = mysqli_prepare($conn,
    "UPDATE admins SET email=?, role=?, admin_name=? WHERE admin_id=?"
);

mysqli_stmt_bind_param($stmt, "sssi", $email, $role, $name, $id);
mysqli_stmt_execute($stmt);

header("Location: it_dashboard.php");
exit;