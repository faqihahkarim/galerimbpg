<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['role'] !== 'it_officer') {
    die("Access denied");
}

$email = $_POST['email'];
$password = $_POST['password'];
$name = $_POST['admin_name'];
$role = $_POST['role'];

// check duplicate email
$check = mysqli_prepare($conn, "SELECT admin_id FROM admins WHERE email=?");
mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);

if (mysqli_fetch_assoc($res)) {
    die("Email already exists");
}

// hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO admins (email, password, admin_name, role) VALUES ( ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $email, $hashed, $name, $role);

mysqli_stmt_execute($stmt);

header("Location: it_dashboard.php");
exit;