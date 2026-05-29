

<?php
session_start();

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../PHPMailer/src/Exception.php';
require '../../../PHPMailer/src/PHPMailer.php';
require '../../../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['admin_login'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

include '../../../db.php';
include'../../log.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
$action = strtolower(trim($_POST['action'] ?? ''));
$admin_comment = trim($_POST['admin_comment'] ?? '');

$allowedActions = ['approved', 'rejected'];

if ($booking_id <= 0 || !in_array($action, $allowedActions, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid booking request.'
    ]);
    exit;
}

$bookingQuery = "
    SELECT 
        b.booking_id,
        b.booking_status,
        b.organization_name,
        b.contact_person,
        b.phone_number,
        b.email,
        b.total_participants,
        b.admin_comment,
        s.slot_date,
        s.start_time,
        s.end_time,
        p.package_name
    FROM bookings b
    LEFT JOIN booking_slots s ON b.slot_id = s.slot_id
    LEFT JOIN packages p ON b.package_id = p.package_id
    WHERE b.booking_id = $booking_id
    LIMIT 1
";

$bookingResult = mysqli_query($conn, $bookingQuery);

if (!$bookingResult || mysqli_num_rows($bookingResult) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found.'
    ]);
    exit;
}

$booking = mysqli_fetch_assoc($bookingResult);

$currentStatus = strtolower(trim((string) $booking['booking_status']));

if ($currentStatus !== '' && $currentStatus !== 'pending') {
    echo json_encode([
        'success' => false,
        'message' => 'This booking has already been processed.'
    ]);
    exit;
}

$action_safe = mysqli_real_escape_string($conn, $action);
$admin_comment_safe = mysqli_real_escape_string($conn, $admin_comment);

$updateQuery = "
    UPDATE bookings
    SET 
        booking_status = '$action_safe',
        admin_comment = '$admin_comment_safe'
    WHERE booking_id = $booking_id
";

$updateResult = mysqli_query($conn, $updateQuery);

if (!$updateResult) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update booking status.'
    ]);
    exit;
}

/* ACTIVITY LOG */

$adminId = $_SESSION['admin_id'];

// Get admin name
$adminName = 'Admin';

$adminQuery = "
    SELECT admin_name
    FROM admins
    WHERE admin_id = '$adminId'
    LIMIT 1
";

$adminResult = mysqli_query($conn, $adminQuery);

if ($adminResult && mysqli_num_rows($adminResult) > 0) {
    $adminData = mysqli_fetch_assoc($adminResult);
    $adminName = $adminData['admin_name'];
}

// Booking code
$bookingCode = 'BK' . $booking_id;

if ($action === 'approved') {

    addAdminLog(
        $conn,
        $adminId,
        'booking_approved',
        'Tempahan ' . $bookingCode . ' diluluskan oleh admin ' . $adminName,
        'bookings',
        $booking_id
    );

} elseif ($action === 'rejected') {

    addAdminLog(
        $conn,
        $adminId,
        'booking_rejected',
        'Tempahan ' . $bookingCode . ' ditolak oleh admin ' . $adminName,
        'bookings',
        $booking_id
    );
}



$to = $booking['email'];
$subject = "Status Tempahan Galeri Seramik MBPG";

$slotDate = !empty($booking['slot_date'])
    ? date('d/m/Y', strtotime($booking['slot_date']))
    : '-';

$startTime = !empty($booking['start_time'])
    ? date('g:i A', strtotime($booking['start_time']))
    : '-';

$endTime = !empty($booking['end_time'])
    ? date('g:i A', strtotime($booking['end_time']))
    : '-';

$activityText = "Tiada aktiviti dipilih";

$activityQuery = "
    SELECT 
        a.activity_name,
        ba.participant_count
    FROM booking_activities ba
    LEFT JOIN activities a ON ba.activity_id = a.activity_id
    WHERE ba.booking_id = $booking_id
";

$activityResult = mysqli_query($conn, $activityQuery);

if ($activityResult && mysqli_num_rows($activityResult) > 0) {
    $activityLines = [];

    while ($activity = mysqli_fetch_assoc($activityResult)) {
        $activityLines[] = "- " . $activity['activity_name'] . " (" . $activity['participant_count'] . " peserta)";
    }

    $activityText = implode("\n", $activityLines);
}

if ($action === 'approved') {
    $message = "
Assalamualaikum / Salam Sejahtera,

Sukacita dimaklumkan bahawa tempahan anda telah DILULUSKAN.

Maklumat tempahan:
Nama Organisasi: {$booking['organization_name']}
Nama Wakil: {$booking['contact_person']}
Pakej: {$booking['package_name']}
Aktiviti yang dipilih: 
{$activityText}
Tarikh: {$slotDate}
Masa: {$startTime} - {$endTime}
Jumlah Peserta: {$booking['total_participants']}

Sila hadir mengikut tarikh dan masa yang telah ditetapkan.

Terima kasih.
Galeri Seramik MBPG
";
} else {
    $reasonText = $admin_comment !== '' ? $admin_comment : 'Tidak dinyatakan';

    $message = "
Assalamualaikum / Salam Sejahtera,

Dukacita dimaklumkan bahawa tempahan anda telah DITOLAK.

Maklumat tempahan:
Nama Organisasi: {$booking['organization_name']}
Nama Wakil: {$booking['contact_person']}
Pakej: {$booking['package_name']}
Tarikh: {$slotDate}
Masa: {$startTime} - {$endTime}

Sebab penolakan:
{$reasonText}

Sila hubungi pihak Galeri Seramik MBPG untuk maklumat lanjut.

Terima kasih.
Galeri Seramik MBPG
";
}

$emailSent = false;
$emailError = '';

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // CHANGE THIS
    $mailConfig = require '../../../config/config_mail.php';
    $mail->Username = $mailConfig['email'];

    // CHANGE THIS - use Google App Password, not normal Gmail password
    $mail->Password = $mailConfig['app_password'];

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($mailConfig['email'], 'Galeri Seramik MBPG');
    $mail->addAddress($to);

    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body = $message;

    $mail->send();

    $emailSent = true;

} catch (Exception $e) {
    $emailError = $mail->ErrorInfo;
}

echo json_encode([
    'success' => true,
    'email_sent' => $emailSent,
    'message' => $emailSent
        ? 'Status tempahan berjaya dikemaskini. Emel berjaya dihantar.'
        : 'Status tempahan berjaya dikemaskini, namun emel gagal dihantar.',
    'email_error' => $emailError
]);

exit;


?>