<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

$package_id = (int) $_POST['package_id'];
$day_of_week = mysqli_real_escape_string($conn, $_POST['day_of_week']);
$start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
$end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
$max_booking_per_slot = (int) $_POST['max_booking_per_slot'];
$status = mysqli_real_escape_string($conn, $_POST['status']);

$sql = "
    INSERT INTO booking_rules (
        package_id,
        day_of_week,
        start_time,
        end_time,
        max_booking_per_slot,
        status
    ) VALUES (
        $package_id,
        '$day_of_week',
        '$start_time',
        '$end_time',
        $max_booking_per_slot,
        '$status'
    )
";

mysqli_query($conn, $sql);

header("Location: peraturan.php?success=rule_added");
exit;