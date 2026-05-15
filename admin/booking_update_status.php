<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tempahan.php");
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
$action = $_POST['action'] ?? '';

$allowedActions = ['approved', 'cancelled'];

if ($booking_id <= 0 || !in_array($action, $allowedActions)) {
    header("Location: tempahan.php?error=invalid_request");
    exit;
}

$checkQuery = "
    SELECT booking_id, booking_status
    FROM bookings
    WHERE booking_id = $booking_id
    LIMIT 1
";

$checkResult = mysqli_query($conn, $checkQuery);

if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
    header("Location: tempahan.php?error=booking_not_found");
    exit;
}

$booking = mysqli_fetch_assoc($checkResult);

if ($booking['booking_status'] !== 'pending') {
    header("Location: tempahan.php?error=already_processed");
    exit;
}

$updateQuery = "
    UPDATE bookings
    SET booking_status = '$action'
    WHERE booking_id = $booking_id
";

$updateResult = mysqli_query($conn, $updateQuery);

if ($updateResult) {
    header("Location: tempahan.php?success=status_updated");
    exit;
} else {
    header("Location: tempahan.php?error=update_failed");
    exit;
}