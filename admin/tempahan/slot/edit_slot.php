<?php
session_start();
include '../../../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: slot.php');
    exit;
}

$slot_id = isset($_POST['slot_id']) ? (int) $_POST['slot_id'] : 0;
$package_id = isset($_POST['package_id']) ? (int) $_POST['package_id'] : 0;
$slot_date = mysqli_real_escape_string($conn, $_POST['slot_date'] ?? '');
$start_time = mysqli_real_escape_string($conn, $_POST['start_time'] ?? '');
$end_time = mysqli_real_escape_string($conn, $_POST['end_time'] ?? '');
$slot_status = mysqli_real_escape_string($conn, $_POST['slot_status'] ?? 'available');

if ($slot_id <= 0) {
    header('Location: slot.php?error=invalid_id');
    exit;
}

$update = "UPDATE booking_slots SET package_id = $package_id, slot_date = '$slot_date', start_time = '$start_time', end_time = '$end_time', slot_status = '$slot_status' WHERE slot_id = $slot_id";

mysqli_query($conn, $update);

header('Location: slot.php?success=slot_updated');
exit;
