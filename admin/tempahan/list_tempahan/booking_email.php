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
include '../../log.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
$action = strtolower(trim($_POST['action'] ?? ''));

// ========================
// HANDLE ADD ACTION (Walk-in Booking)
// ========================
if ($action === 'add') {
    $organization_name = trim($_POST['organization_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $package_id = isset($_POST['package_id']) ? (int) $_POST['package_id'] : 0;
    $total_participants = isset($_POST['total_participants']) ? (int) $_POST['total_participants'] : 0;
    $slot_id = isset($_POST['slot_id']) ? (int) $_POST['slot_id'] : 0;
    $admin_comment = trim($_POST['admin_comment'] ?? 'Walk-in booking');
    
    // Validation
    if (empty($organization_name) || empty($contact_person) || empty($phone_number) || 
        $package_id <= 0 || $total_participants <= 0 || $slot_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila isi semua maklumat yang diperlukan.'
        ]);
        exit;
    }
    
    mysqli_begin_transaction($conn);
    
    try {
        // Check if slot is available
        $checkSlotQuery = "SELECT slot_status FROM booking_slots WHERE slot_id = $slot_id";
        $slotResult = mysqli_query($conn, $checkSlotQuery);
        $slotData = mysqli_fetch_assoc($slotResult);
        
        if ($slotData['slot_status'] !== 'available') {
            throw new Exception('Slot tidak tersedia. Sila pilih slot lain.');
        }
        
        // Insert booking with approved status
        $insertQuery = "
            INSERT INTO bookings (
                package_id, 
                slot_id, 
                organization_name, 
                contact_person, 
                phone_number, 
                email, 
                total_participants, 
                booking_status, 
                admin_comment,
                created_at
            ) VALUES (
                $package_id,
                $slot_id,
                '" . mysqli_real_escape_string($conn, $organization_name) . "',
                '" . mysqli_real_escape_string($conn, $contact_person) . "',
                '" . mysqli_real_escape_string($conn, $phone_number) . "',
                '" . mysqli_real_escape_string($conn, $email) . "',
                $total_participants,
                'approved',
                '" . mysqli_real_escape_string($conn, $admin_comment) . "',
                NOW()
            )
        ";
        
        if (!mysqli_query($conn, $insertQuery)) {
            throw new Exception('Gagal menyimpan tempahan: ' . mysqli_error($conn));
        }
        
        $new_booking_id = mysqli_insert_id($conn);
        
        // Mark slot as booked
        $updateSlotQuery = "UPDATE booking_slots SET slot_status = 'booked' WHERE slot_id = $slot_id";
        if (!mysqli_query($conn, $updateSlotQuery)) {
            throw new Exception('Gagal mengemaskini slot.');
        }
        
        // Insert activities if provided
        if (isset($_POST['activity_participants']) && is_array($_POST['activity_participants'])) {
            foreach ($_POST['activity_participants'] as $activityId => $count) {
                $activityId = (int) $activityId;
                $count = (int) $count;
                
                if ($count > 0) {
                    $insertActivityQuery = "
                        INSERT INTO booking_activities (booking_id, activity_id, participant_count)
                        VALUES ($new_booking_id, $activityId, $count)
                    ";
                    if (!mysqli_query($conn, $insertActivityQuery)) {
                        throw new Exception('Gagal menyimpan aktiviti: ' . mysqli_error($conn));
                    }
                }
            }
        }
        
        // Log the action
        $adminId = $_SESSION['admin_id'];
        $adminName = 'Admin';
        
        $adminQuery = "SELECT admin_name FROM admins WHERE admin_id = '$adminId' LIMIT 1";
        $adminResult = mysqli_query($conn, $adminQuery);
        
        if ($adminResult && mysqli_num_rows($adminResult) > 0) {
            $adminData = mysqli_fetch_assoc($adminResult);
            $adminName = $adminData['admin_name'];
        }
        
        $bookingCode = 'BK' . $new_booking_id;
        
        addAdminLog(
            $conn,
            $adminId,
            'booking_added_walkin',
            'Tempahan walk-in ' . $bookingCode . ' ditambah oleh admin ' . $adminName . ' untuk ' . $organization_name,
            'bookings',
            $new_booking_id
        );
        
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tempahan walk-in berjaya ditambah! ID: ' . $bookingCode
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah tempahan: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// ========================
// HANDLE EDIT ACTION
// ========================
if ($action === 'edit') {
    $organization_name = trim($_POST['organization_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $total_participants = isset($_POST['total_participants']) ? (int) $_POST['total_participants'] : 0;
    $admin_comment = trim($_POST['admin_comment'] ?? '');
    $new_package_id = isset($_POST['package_id']) ? (int) $_POST['package_id'] : 0;
    $new_slot_id = isset($_POST['slot_id']) ? (int) $_POST['slot_id'] : 0;
    
    if ($booking_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking ID.'
        ]);
        exit;
    }
    
    // Validate required fields
    if (empty($organization_name) || empty($contact_person) || empty($phone_number) || empty($email) || $total_participants <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila isi semua maklumat yang diperlukan.'
        ]);
        exit;
    }
    
    // Validate package
    if ($new_package_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila pilih pakej.'
        ]);
        exit;
    }
    
    // Start transaction for data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // Get current booking data for logging
        $currentBookingQuery = "
            SELECT slot_id, package_id, organization_name, contact_person, 
                   phone_number, email, total_participants, admin_comment
            FROM bookings 
            WHERE booking_id = $booking_id
        ";
        $currentResult = mysqli_query($conn, $currentBookingQuery);
        $currentBooking = mysqli_fetch_assoc($currentResult);
        $old_slot_id = $currentBooking['slot_id'];
        $old_package_id = $currentBooking['package_id'];
        
        // Handle slot change if requested
        if ($new_slot_id > 0 && $new_slot_id != $old_slot_id) {
            // Release old slot
            $releaseOldSlotQuery = "
                UPDATE booking_slots 
                SET slot_status = 'available' 
                WHERE slot_id = $old_slot_id
            ";
            mysqli_query($conn, $releaseOldSlotQuery);
            
            // Book new slot
            $bookNewSlotQuery = "
                UPDATE booking_slots 
                SET slot_status = 'booked' 
                WHERE slot_id = $new_slot_id
            ";
            mysqli_query($conn, $bookNewSlotQuery);
        }
        
        // Handle package change
        $packageChanged = ($new_package_id != $old_package_id);
        
        // If package changed and new package doesn't require activities, clear activities
        if ($packageChanged) {
            $checkPackageQuery = "
                SELECT requires_activity_selection 
                FROM packages 
                WHERE package_id = $new_package_id
            ";
            $packageResult = mysqli_query($conn, $checkPackageQuery);
            $packageData = mysqli_fetch_assoc($packageResult);
            
            if ($packageData['requires_activity_selection'] == 0) {
                // Clear activities if new package doesn't need them
                $deleteActivitiesQuery = "DELETE FROM booking_activities WHERE booking_id = $booking_id";
                mysqli_query($conn, $deleteActivitiesQuery);
            }
        }
        
        // Update booking main data
        $updateQuery = "
            UPDATE bookings 
            SET 
                organization_name = '" . mysqli_real_escape_string($conn, $organization_name) . "',
                contact_person = '" . mysqli_real_escape_string($conn, $contact_person) . "',
                phone_number = '" . mysqli_real_escape_string($conn, $phone_number) . "',
                email = '" . mysqli_real_escape_string($conn, $email) . "',
                total_participants = $total_participants,
                admin_comment = '" . mysqli_real_escape_string($conn, $admin_comment) . "',
                package_id = $new_package_id"
                . ($new_slot_id > 0 ? ", slot_id = $new_slot_id" : "") . "
            WHERE booking_id = $booking_id
        ";
        
        if (!mysqli_query($conn, $updateQuery)) {
            throw new Exception('Gagal mengemaskini tempahan: ' . mysqli_error($conn));
        }
        
        // Handle activity updates if provided
        if (isset($_POST['activity_participants']) && is_array($_POST['activity_participants'])) {
            // Delete existing activities
            $deleteQuery = "DELETE FROM booking_activities WHERE booking_id = $booking_id";
            mysqli_query($conn, $deleteQuery);
            
            // Insert new activity data
            foreach ($_POST['activity_participants'] as $activityId => $count) {
                $activityId = (int) $activityId;
                $count = (int) $count;
                
                if ($count > 0) {
                    $insertActivityQuery = "
                        INSERT INTO booking_activities (booking_id, activity_id, participant_count)
                        VALUES ($booking_id, $activityId, $count)
                    ";
                    if (!mysqli_query($conn, $insertActivityQuery)) {
                        throw new Exception('Gagal menyimpan aktiviti: ' . mysqli_error($conn));
                    }
                }
            }
        }
        
        // Log the edit action
        $adminId = $_SESSION['admin_id'];
        $adminName = 'Admin';
        
        $adminQuery = "SELECT admin_name FROM admins WHERE admin_id = '$adminId' LIMIT 1";
        $adminResult = mysqli_query($conn, $adminQuery);
        
        if ($adminResult && mysqli_num_rows($adminResult) > 0) {
            $adminData = mysqli_fetch_assoc($adminResult);
            $adminName = $adminData['admin_name'];
        }
        
        $bookingCode = 'BK' . $booking_id;
        
        // Build log details
        $logDetails = "Tempahan $bookingCode dikemaskini oleh admin $adminName. Perubahan: ";
        $changes = [];
        
        if ($currentBooking['organization_name'] != $organization_name) $changes[] = "organisasi";
        if ($currentBooking['contact_person'] != $contact_person) $changes[] = "pegawai";
        if ($currentBooking['phone_number'] != $phone_number) $changes[] = "telefon";
        if ($currentBooking['email'] != $email) $changes[] = "emel";
        if ($currentBooking['total_participants'] != $total_participants) $changes[] = "peserta";
        if ($old_package_id != $new_package_id) $changes[] = "pakej";
        if ($new_slot_id > 0 && $old_slot_id != $new_slot_id) $changes[] = "slot";
        if (isset($_POST['activity_participants'])) $changes[] = "aktiviti";
        
        $logDetails .= !empty($changes) ? implode(", ", $changes) : "tiada perubahan ketara";
        
        addAdminLog(
            $conn,
            $adminId,
            'booking_edited',
            $logDetails,
            'bookings',
            $booking_id
        );
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tempahan berjaya dikemaskini.'
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengemaskini tempahan: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// ========================
// HANDLE APPROVE/REJECT ACTIONS
// ========================

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

// If rejected, release the slot
if ($action === 'rejected') {
    $releaseSlotQuery = "
        UPDATE booking_slots 
        SET slot_status = 'available' 
        WHERE slot_id = (SELECT slot_id FROM bookings WHERE booking_id = $booking_id)
    ";
    mysqli_query($conn, $releaseSlotQuery);
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

// Email notification
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
$totalFee = 0.00;

$activityQuery = "
    SELECT 
        a.activity_name, a.price,
        ba.participant_count
    FROM booking_activities ba
    LEFT JOIN activities a ON ba.activity_id = a.activity_id
    WHERE ba.booking_id = $booking_id
";

$activityResult = mysqli_query($conn, $activityQuery);

if ($activityResult && mysqli_num_rows($activityResult) > 0) {
    $activityLines = [];

    while ($activity = mysqli_fetch_assoc($activityResult)) {
        $participants = (int)$activity['participant_count'];
        $price = (float)$activity['price'];
        $subtotal = $participants * $price;
        $totalFee += $subtotal;

        $activityLines[] = "- " . $activity['activity_name'] . " (" . $participants . " peserta x RM " . number_format($price, 2) . ") = RM " . number_format($subtotal, 2);
    }

    $activityText = implode("\n", $activityLines);
}

$packageNameLower = strtolower($booking['package_name'] ?? '');
if (strpos($packageNameLower, 'lawatan') !== false) {
    $totalFee = (float)$booking['total_participants'] * 2.00;
}

$formattedTotalFee = 'RM ' . number_format($totalFee, 2);

if ($action === 'approved') {
    $message = "
GALERI SERAMIK MBPG

Tempahan anda, BK{$booking['booking_id']} telah DILULUSKAN.

Maklumat tempahan:
Nama Organisasi: {$booking['organization_name']}
Nama Wakil: {$booking['contact_person']}
Pakej: {$booking['package_name']}
Aktiviti yang dipilih: 
{$activityText}
Tarikh: {$slotDate}
Masa: {$startTime} - {$endTime}
Jumlah Peserta: {$booking['total_participants']}
Jumlah Bayaran: {$formattedTotalFee}

Sila hadir mengikut tarikh dan masa yang telah ditetapkan.

Sila hubungi pihak Galeri Seramik MBPG untuk maklumat lanjut.

019-20828241 (En. Ahmad)

Terima kasih.
Galeri Seramik MBPG
";
} else {
    $reasonText = $admin_comment !== '' ? $admin_comment : 'Tidak dinyatakan';

    $message = "

GALERI SERAMIK MBPG

Tempahan anda, BK{$booking['booking_id']} telah DITOLAK.

Maklumat tempahan:
Nama Organisasi: {$booking['organization_name']}
Nama Wakil: {$booking['contact_person']}
Pakej: {$booking['package_name']}
Tarikh: {$slotDate}
Masa: {$startTime} - {$endTime}

Sebab penolakan:
{$reasonText}

Sila hubungi pihak Galeri Seramik MBPG untuk maklumat lanjut.

019-20828241 (En. Ahmad)

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

    $mailConfig = require '../../../config/config_mail.php';
    $mail->Username = $mailConfig['email'];
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