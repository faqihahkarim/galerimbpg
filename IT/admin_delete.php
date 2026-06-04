<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['role'] !== 'it_officer') {
    die("Access denied");
}

$id = $_GET['id'];

// SAFETY: prevent deleting self
if ($id == $_SESSION['admin_id']) {
    die("You cannot delete yourself");
}

mysqli_query($conn, "DELETE FROM admins WHERE admin_id=$id");

header("Location: it_dashboard.php");
exit;