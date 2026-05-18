<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: peraturan.php');
    exit;
}

$rule_id = isset($_POST['rule_id']) ? (int) $_POST['rule_id'] : 0;
$package_id = isset($_POST['package_id']) ? (int) $_POST['package_id'] : 0;
$day_of_week = mysqli_real_escape_string($conn, $_POST['day_of_week'] ?? '');
$start_time = mysqli_real_escape_string($conn, $_POST['start_time'] ?? '');
$end_time = mysqli_real_escape_string($conn, $_POST['end_time'] ?? '');
$max_booking_per_slot = isset($_POST['max_booking_per_slot']) ? (int) $_POST['max_booking_per_slot'] : 1;
$status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Aktif');

if ($rule_id <= 0) {
    header('Location: peraturan.php?error=invalid_id');
    exit;
}

$update = "UPDATE booking_rules SET package_id = $package_id, day_of_week = '$day_of_week', start_time = '$start_time', end_time = '$end_time', max_booking_per_slot = $max_booking_per_slot, status = '$status' WHERE rule_id = $rule_id";

mysqli_query($conn, $update);

header('Location: peraturan.php?success=rule_updated');
exit;
