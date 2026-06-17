<?php
session_start();
$base="/web/galeriseramikmbpg/";

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../../../db.php';
include '../../timeout.php';

function getBookingStatusLabel($status) {
  switch ($status) {
    case 'approved':
      return 'Lulus';
    case 'rejected':
      return 'Batal';
    case 'pending':
    default:
      return 'Belum Lulus';
  }
}

function getBookingStatusClass($status) {
  switch ($status) {
    case 'approved':
      return 'approved';
    case 'rejected':
      return 'rejected';
    case 'pending':
    default:
      return 'pending';
  }
}

function countBookings($conn, $status = null) {
  $where = '1';
  if ($status !== null) {
    if (is_array($status)) {
      $escaped = array_map(function ($item) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $item) . "'";
      }, $status);
      $where = 'booking_status IN (' . implode(',', $escaped) . ')';
    } elseif ($status === 'pending') {
      $where = "booking_status IN ('pending', '')";
    } else {
      $status = mysqli_real_escape_string($conn, $status);
      $where = "booking_status = '$status'";
    }
  }

  $query = "SELECT COUNT(*) AS total FROM bookings WHERE $where";
  $result = mysqli_query($conn, $query);
  return $result ? (int) mysqli_fetch_assoc($result)['total'] : 0;
}

$totalBookings = countBookings($conn);
$pendingBookings = countBookings($conn, 'pending');
$approvedBookings = countBookings($conn, 'approved');
$rejectedBookings = countBookings($conn, 'rejected');

$bookings = [];
$bookingQuery = "
  SELECT
    b.booking_id,
    b.slot_id,
    b.package_id,
    b.organization_name,
    b.contact_person,
    b.phone_number,
    b.email,
    b.total_participants,
    b.booking_status,
    b.admin_comment,
    b.admin_remark,
    b.created_at,
    s.slot_date,
    s.start_time,
    s.end_time,
    p.package_name
  FROM bookings b
  LEFT JOIN booking_slots s ON b.slot_id = s.slot_id
  LEFT JOIN packages p ON b.package_id = p.package_id
  ORDER BY b.created_at ASC
";
$bookingResult = mysqli_query($conn, $bookingQuery);
$bookingIds = [];

while ($row = mysqli_fetch_assoc($bookingResult)) {
  $row['display_id'] = 'BK' . $row['booking_id'];
  $row['status_label'] = getBookingStatusLabel($row['booking_status']);
  $row['status_class'] = getBookingStatusClass($row['booking_status']);

  if (!empty($row['slot_date'])) {
    $row['slot_display'] = date('j M Y', strtotime($row['slot_date']));
    if (!empty($row['start_time']) && !empty($row['end_time'])) {
      $row['slot_display'] .= ' (' . date('g.i A', strtotime($row['start_time'])) . ' - ' . date('g.i A', strtotime($row['end_time'])) . ')';
    }
  } else {
    $row['slot_display'] = '-';
  }

  $bookings[$row['booking_id']] = $row;
  $bookingIds[] = (int) $row['booking_id'];
}

if (!empty($bookingIds)) {
  $idList = implode(',', $bookingIds);
  $activityQuery = "
    SELECT
      ba.booking_id,
      a.activity_name,
      a.price,
      ba.participant_count
    FROM booking_activities ba
    LEFT JOIN activities a ON ba.activity_id = a.activity_id
    WHERE ba.booking_id IN ($idList)
    ORDER BY ba.booking_id
  ";

  $activityResult = mysqli_query($conn, $activityQuery);
  $bookingActivities = [];
  while ($activityRow = mysqli_fetch_assoc($activityResult)) {
    $bookingActivities[$activityRow['booking_id']][] = $activityRow;
  }

  foreach ($bookingActivities as $bookingId => $activities) {
    $parts = [];
    $totalFee = 0.00;
    foreach ($activities as $activityRow) {
      $parts[] = htmlspecialchars($activityRow['activity_name']) . ' (' . (int) $activityRow['participant_count'] . ')';
      $participants = (int) $activityRow['participant_count'];
      $price = isset($activityRow['price']) ? (float) $activityRow['price'] : 0.00;
      $totalFee += $participants * $price;
    }
    $bookings[$bookingId]['activity_list'] = implode(' + ', $parts);
    $bookings[$bookingId]['total_fee'] = $totalFee;
    $bookings[$bookingId]['formatted_total_fee'] = 'RM ' . number_format($totalFee, 2);
  }
}

foreach ($bookings as &$booking) {
  if (empty($booking['activity_list'])) {
    $booking['activity_list'] = 'Tiada';
  }

  // If no activity fees present and package is a lawatan, apply flat per-person fee
  $packageNameLower = strtolower($booking['package_name'] ?? '');
  if (empty($booking['total_fee']) && strpos($packageNameLower, 'lawatan') !== false) {
    $fee = (float) $booking['total_participants'] * 2.00;
    $booking['total_fee'] = $fee;
    $booking['formatted_total_fee'] = 'RM ' . number_format($fee, 2);
  }

  // Ensure fields exist
  if (!isset($booking['total_fee'])) {
    $booking['total_fee'] = 0.00;
    $booking['formatted_total_fee'] = 'RM 0.00';
  }
}
unset($booking);
include 'tempahan_view.php';
?>



