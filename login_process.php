<?php
session_start();
include "db.php";

// Validate CSRF (if you added it in form)
//if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
//    die("Invalid request");
//}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare query
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

// SECURE PASSWORD CHECK
if ($admin && password_verify($password, $admin['password'])) {

    // Regenerate session ID (prevent session hijacking)
    session_regenerate_id(true);

    $_SESSION['admin_login'] = true;
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_email'] = $admin['email'];
    //decide on role
    $_SESSION['role'] = $admin['role']; 

 // ROLE-BASED REDIRECTION
    if ($admin['role'] === 'it_officer') {
        header("Location: IT/it_dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;

} else {
    header("Location: login.php?error=1");
    exit;
}