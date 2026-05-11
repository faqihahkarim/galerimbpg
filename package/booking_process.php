<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../index.php");
  exit;
}

$slot_id = intval($_POST['slot_id']);
$package_id = intval($_POST['package_id']);

$organization_name = mysqli_real_escape_string($conn, $_POST['organization_name']);
$contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
$phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$total_participants = intval($_POST['total_participants']);
$admin_remark = mysqli_real_escape_string($conn, $_POST['admin_remark'] ?? '');

/* Insert booking */
$bookingQuery = "
  INSERT INTO bookings (
    slot_id,
    package_id,
    organization_name,
    contact_person,
    phone_number,
    email,
    total_participants,
    booking_status,
    admin_remark,
    created_at
  ) VALUES (
    '$slot_id',
    '$package_id',
    '$organization_name',
    '$contact_person',
    '$phone_number',
    '$email',
    '$total_participants',
    'booked',
    '$admin_remark',
    NOW()
  )
";

if (mysqli_query($conn, $bookingQuery)) {

  $booking_id = mysqli_insert_id($conn);

  /* Insert activity allocation if exists */
  if (!empty($_POST['activity_participants'])) {
    foreach ($_POST['activity_participants'] as $activity_id => $participant_count) {
      $activity_id = intval($activity_id);
      $participant_count = intval($participant_count);

      if ($participant_count > 0) {
        $activityQuery = "
          INSERT INTO booking_activities (
            booking_id,
            activity_id,
            participant_count
          ) VALUES (
            '$booking_id',
            '$activity_id',
            '$participant_count'
          )
        ";

        mysqli_query($conn, $activityQuery);
      }
    }
  }

  /* Update selected slot status */
  $updateSlotQuery = "
    UPDATE booking_slots
    SET slot_status = 'booked'
    WHERE slot_id = '$slot_id'
    AND package_id = '$package_id'
  ";

  mysqli_query($conn, $updateSlotQuery);

  header("Location: booking_success.php?booking_id=" . $booking_id);
  exit;

} else {
  echo "Booking failed: " . mysqli_error($conn);
}
?>