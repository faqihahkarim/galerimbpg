<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_POST['generate_month'])) {
    header("Location: peraturan.php?error=no_month");
    exit;
}

$generateMonth = $_POST['generate_month']; // format: 2026-06

$year = date('Y', strtotime($generateMonth . '-01'));
$month = date('m', strtotime($generateMonth . '-01'));
$daysInMonth = date('t', strtotime($generateMonth . '-01'));



$generatedCount = 0;

for ($day = 1; $day <= $daysInMonth; $day++) {

    $slotDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $dayOfWeek = date('l', strtotime($slotDate));

    $closureQuery = "
        SELECT closure_id
        FROM closure_dates
        WHERE closure_date = '$slotDate'
        AND status = 'active'
        LIMIT 1
    ";

    $closureResult = mysqli_query($conn, $closureQuery);
    $closure = mysqli_fetch_assoc($closureResult);

    $rulesQuery = "
        SELECT *
        FROM booking_rules
        WHERE day_of_week = '$dayOfWeek'
        AND status = 'active'
    ";

    $rulesResult = mysqli_query($conn, $rulesQuery);

    while ($rule = mysqli_fetch_assoc($rulesResult)) {

        $package_id = (int) $rule['package_id'];
        $start_time = $rule['start_time'];
        $end_time = $rule['end_time'];

        $slotStatus = $closure ? 'closed' : 'available';
        $closureId = $closure ? (int)$closure['closure_id'] : "NULL";

        $checkDuplicate = "
            SELECT slot_id
            FROM booking_slots
            WHERE package_id = $package_id
            AND slot_date = '$slotDate'
            AND start_time = '$start_time'
            AND end_time = '$end_time'
            LIMIT 1
        ";

        $duplicateResult = mysqli_query($conn, $checkDuplicate);

        if (mysqli_num_rows($duplicateResult) === 0) {
            $insertSlot = "
                INSERT INTO booking_slots (
                    package_id,
                    slot_date,
                    start_time,
                    end_time,
                    slot_status,
                    closure_id
                ) VALUES (
                    $package_id,
                    '$slotDate',
                    '$start_time',
                    '$end_time',
                    '$slotStatus',
                    $closureId
                )
            ";

            if (mysqli_query($conn, $insertSlot)) {
                $generatedCount++;
            }
        }
    }
}

header("Location: slot.php?success=slots_generated&count=$generatedCount");
exit;