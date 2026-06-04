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
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Seramik MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/tempahan.css">
  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include '../../sidebar.php'; ?>

    <main class="main">

    <header class="topbar">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div>
            <h1>Pengurusan Tempahan</h1>
            <p>Tempahan</p>
        </div>
    </header>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'status_updated'): ?>
      <div class="alert success-alert">
        Status tempahan berjaya dikemaskini.
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert error-alert">
        Status tempahan gagal dikemaskini.
      </div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Tempahan</h3>
            <strong><?= $totalBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Semasa</h3>
            <strong><?= $pendingBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Diluluskan</h3>
            <strong><?= $approvedBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Dibatalkan</h3>
            <strong><?= $rejectedBookings ?></strong>
          </div>
        </div>
    </section>

    <section class="booking-panel">
        <div class="booking-toolbar">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="bookingSearch" type="text" placeholder="Search by name, type, status">
        </div>

        <select id="bookingTypeFilter">
            <option value="all">All Types</option>
            <option value="Pakej Pendidikan">Pakej Pendidikan</option>
            <option value="Lawatan Berkumpulan">Lawatan Berkumpulan</option>
        </select>

        <select id="bookingStatusFilter">
            <option value="all">All Status</option>
            <option value="Belum Lulus">Belum Lulus</option>
            <option value="Lulus">Lulus</option>
            <option value="Batal">Batal</option>
        </select>

        <button id="bookingResetBtn" type="button" class="reset-btn">Reset</button>
        <button id="bookingExportBtn" type="button" class="export-btn">
            Export <i class="fa-solid fa-download"></i>
        </button>
        <br>
        <p id="bookingNoteText"><i class="fa-solid fa-info-circle" style="margin-top: 20px;"></i> Klik pada ID Tempahan untuk melihat butiran dan mengurus tempahan</p>

        </div>

        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID Tempahan</th>
                <th>Nama Organisasi</th>
                <th>Jenis Tempahan</th>
                <th>Tarikh & Slot Masa</th>
                <th>Pax</th>
                <th>Jumlah Bayaran</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td>
                    <a href="#" class="booking-detail-link" data-booking-id="<?= $booking['booking_id'] ?>" onclick="openBookingModal(<?= $booking['booking_id'] ?>); return false;"><?= htmlspecialchars($booking['display_id']) ?></a>
                  </td>
                  <td><?= htmlspecialchars($booking['organization_name']) ?></td>
                  <td><?= htmlspecialchars($booking['package_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($booking['slot_display']) ?></td>
                  <td><?= (int) $booking['total_participants'] ?></td>
                  <td><?= htmlspecialchars($booking['formatted_total_fee'] ?? 'RM 0.00') ?></td>
                  <td><span class="status <?= $booking['status_class'] ?>"><?= $booking['status_label'] ?></span></td>
                  <td><?= htmlspecialchars($booking['admin_comment'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center; padding: 24px;">Tiada rekod tempahan ditemui.</td>
              </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="table-footer">
          <p id="bookingFooterText">Showing 0 to 0 out of 0 entries</p>

          <div class="pagination">
            <button id="prevPageBtn" type="button"><i class="fa-solid fa-chevron-left"></i></button>
            <span id="paginationInfo">Page 1 of 1</span>
            <button id="nextPageBtn" type="button"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>
    </section>


    <!-- POP UP MODAL FOR BOOKING DETAILS-->

    <div class="booking-modal" id="bookingModal">
      <div class="booking-modal-card">
        <div class="booking-modal-header">
          <h2>Info Tempahan</h2>
          <button type="button" class="booking-modal-close">&times;</button>
        </div>

        <div class="booking-modal-id">
          <h3 id="modalBookingRef">-</h3>
          <span id="modalBookingStatus" class="status pending">-</span>
        </div>

        <div class="booking-info-list">
          <div class="booking-info-item">
            <i class="fa-regular fa-user"></i>
            <div>
              <p>Nama Sekolah/ Organisasi</p>
              <small id="modalOrganization">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-phone"></i>
            <div>
              <p>Nombor Telefon</p>
              <small id="modalPhone">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-envelope"></i>
            <div>
              <p>Emel</p>
              <small id="modalEmail">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-calendar-days"></i>
            <div>
              <p>Tarikh & Slot Masa</p>
              <small id="modalSlot">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-palette"></i>
            <div>
              <p>Jenis Pakej</p>
              <small id="modalPackage">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-gem"></i>
            <div>
              <p>Pilihan Aktiviti</p>
              <small id="modalActivities">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-users"></i>
            <div>
              <p>Bilangan Peserta</p>
              <small id="modalParticipants">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-dollar-sign"></i>
            <div>
              <p>Jumlah Bayaran</p>
              <small id="modalTotalFee">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-clipboard"></i>
            <div>
              <p>Catatan</p>
              <small id="modalRemark">-</small>
            </div>
          </div>
        </div>

        <div class="booking-action-title">Tindakan</div>

        <form action="booking_update_status.php" method="POST" class="booking-modal-actions" id="bookingActionForm">
            <input type="hidden" name="booking_id" id="actionBookingId">
            <input type="hidden" name="action" id="actionBookingStatus">
            <input type="hidden" name="admin_comment" id="adminCommentInput">

            <button type="button" class="approve-booking-btn" id="approveBookingBtn">
                <span class="btn-text">Terima</span>
                <span class="btn-loader"></span>
            </button>

            <button type="button" class="reject-booking-btn" id="rejectBookingBtn">
                Batal
            </button>

               <a 
                href="#" 
                target="_blank"
                class="whatsapp-booking-btn"
                id="whatsappBookingBtn"
                style="display:none; align-items: center;  padding: 8px 8px; background-color: #0ba042; color: white; border-radius: 10px; text-decoration: none; font-size: 14px; width: fit-content; margin-top: 8px;"
              >
                WhatsApp Pengguna
              </a>

            </form>
      </div>
    </div>

    <div class="reject-modal" id="rejectModal">

      <div class="reject-modal-content">

        <h3>Sebab Penolakan</h3>

        <textarea 
          id="rejectReason"
          placeholder="Masukkan sebab penolakan..."
        ></textarea>

        <div class="reject-modal-actions">
          <button type="button" id="cancelRejectBtn">
            Batal
          </button>

          <button type="button" id="confirmRejectBtn">
            <span class="btn-text">Hantar</span>
            <span class="btn-loader"></span>
          </button>

        </div>

      </div>

    </div>

    </main>

</div>

<script>

const bookingData = <?= json_encode(array_values($bookings), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

const modal = document.getElementById('bookingModal');
const modalClose = document.querySelector('.booking-modal-close');

const modalBookingRef = document.getElementById('modalBookingRef');
const modalBookingStatus = document.getElementById('modalBookingStatus');
const modalOrganization = document.getElementById('modalOrganization');
const modalPhone = document.getElementById('modalPhone');
const modalEmail = document.getElementById('modalEmail');
const modalSlot = document.getElementById('modalSlot');
const modalPackage = document.getElementById('modalPackage');
const modalActivities = document.getElementById('modalActivities');
const modalParticipants = document.getElementById('modalParticipants');
const modalRemark = document.getElementById('modalRemark');
const modalTotalFee = document.getElementById('modalTotalFee');

const actionBookingId = document.getElementById('actionBookingId');
const actionBookingStatus = document.getElementById('actionBookingStatus');

const approveBookingBtn = document.getElementById('approveBookingBtn');
const rejectBookingBtn = document.getElementById('rejectBookingBtn');
const whatsappBookingBtn = document.getElementById('whatsappBookingBtn');

const rejectModal = document.getElementById('rejectModal');
const rejectReason = document.getElementById('rejectReason');
const confirmRejectBtn = document.getElementById('confirmRejectBtn');
const cancelRejectBtn = document.getElementById('cancelRejectBtn');

const bookingSearch = document.getElementById('bookingSearch');
const bookingTypeFilter = document.getElementById('bookingTypeFilter');
const bookingStatusFilter = document.getElementById('bookingStatusFilter');
const bookingResetBtn = document.getElementById('bookingResetBtn');
const bookingExportBtn = document.getElementById('bookingExportBtn');
const bookingTableBody = document.querySelector('.table-wrap table tbody');
const bookingFooterText = document.getElementById('bookingFooterText');
const bookingPaginationInfo = document.getElementById('paginationInfo');
const prevPageBtn = document.getElementById('prevPageBtn');
const nextPageBtn = document.getElementById('nextPageBtn');

let currentPage = 1;
const pageSize = 10;
const allRows = bookingTableBody ? Array.from(bookingTableBody.querySelectorAll('tr')) : [];

function findBooking(bookingId) {
  return bookingData.find(item => String(item.booking_id) === String(bookingId));
}

function setStatusLabel(statusClass, label) {
  modalBookingStatus.textContent = label;
  modalBookingStatus.className = 'status ' + statusClass;
}

// Fallback helper for inline onclick links - must be defined early for global access
function openBookingModal(bookingId) {
  try {
    const booking = findBooking(bookingId);
    if (!booking) {
      alert('Maklumat tempahan tidak dijumpai.');
      return;
    }
    showBookingModal(booking);
  } catch (e) {
    console.error('openBookingModal error', e);
    alert('Gagal membuka modal. Semak konsol.');
  }
}


/**************************/
/*WHATSAPP LINK GENERATION*/
/*************************/
function formatWhatsappPhone(phone) {
  let cleanPhone = String(phone || '').replace(/\D/g, '');

  if (cleanPhone.startsWith('0')) {
    cleanPhone = '6' + cleanPhone;
  }

  return cleanPhone;
}

/*Whatsapp message templates based on booking status*/

function createWhatsappLink(booking) {
  const phone = formatWhatsappPhone(booking.phone_number || '');

  let message = '';

  if (booking.booking_status === 'approved') {
    const feeDisplay = booking.formatted_total_fee || (booking.total_fee !== undefined ? ('RM ' + Number(booking.total_fee).toFixed(2)) : 'RM 0.00');
    const isLawatan = String(booking.package_name || '').toLowerCase().includes('lawatan');
    message = `GALERI SERAMIK MBPG.
    
Tempahan anda, ${booking.display_id || '-'} telah DILULUSKAN.

Tarikh & Masa: ${booking.slot_display || '-'}
Pakej: ${booking.package_name || '-'}
Aktiviti: ${booking.activity_list || '-'}
Jumlah Peserta: ${booking.total_participants || '-'}
Jumlah Bayaran: ${feeDisplay}${isLawatan ? ' (jika pakej lawatan berkumpulan)' : ''}

Untuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.
019-20828241 (En. Ahmad)

Terima kasih.`;
  }

  if (booking.booking_status === 'rejected') {
    message = `GALERI SERAMIK MBPG. 
    
Tempahan anda, ${booking.display_id || '-'} telah DITOLAK.

Sebab: ${booking.admin_remark || '-'}

Untuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.
019-20828241 (En. Ahmad)

Terima kasih.`;
  }

  return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
}



/**************************/
/*BOOKING FUNCTIONS      */
/*************************/

function showBookingModal(booking) {
  modalBookingRef.textContent = booking.display_id || '-';
  setStatusLabel(booking.status_class || 'pending', booking.status_label || '-');

  modalOrganization.textContent = booking.organization_name || '-';
  modalPhone.textContent = booking.phone_number || '-';
  modalEmail.textContent = booking.email || '-';
  modalSlot.textContent = booking.slot_display || '-';
  modalPackage.textContent = booking.package_name || '-';
  modalActivities.textContent = booking.activity_list || 'Tiada';
  modalParticipants.textContent = booking.total_participants || '-';
  modalRemark.textContent = booking.admin_comment || booking.admin_remark || '-';
  modalTotalFee.textContent = booking.formatted_total_fee || ('RM ' + (booking.total_fee !== undefined ? Number(booking.total_fee).toFixed(2) : '0.00'));

  actionBookingId.value = booking.booking_id || '';
  actionBookingStatus.value = booking.booking_status || '';

  if (booking.booking_status === 'pending' || booking.status_class === 'pending') {
    approveBookingBtn.style.display = 'inline-block';
    rejectBookingBtn.style.display = 'inline-block';
    whatsappBookingBtn.style.display = 'none';
    whatsappBookingBtn.href = '#';
  } else {
    approveBookingBtn.style.display = 'none';
    rejectBookingBtn.style.display = 'none';

    if (booking.booking_status === 'approved' || booking.booking_status === 'rejected') {
      whatsappBookingBtn.href = createWhatsappLink(booking);
      whatsappBookingBtn.style.display = 'flex';
    } else {
      whatsappBookingBtn.style.display = 'none';
      whatsappBookingBtn.href = '#';
    }
  }

  modal.classList.add('active');
}

async function sendBookingStatus(formData) {
  const response = await fetch('booking_update_status.php', {
    method: 'POST',
    body: formData
  });

  const text = await response.text();

  console.log('PHP RESPONSE START');
  console.log(text);
  console.log('PHP RESPONSE END');

  try {
    return JSON.parse(text);
  } catch (error) {
    console.error('JSON PARSE ERROR:', error);
    return {
      success: false,
      message: 'PHP tidak return JSON. Semak Console untuk PHP RESPONSE.'
    };
  }
}


/**************************/
/*APPROVE BOOKING FUNCTION*/
/*************************/

if (approveBookingBtn) {
  approveBookingBtn.addEventListener('click', async function (event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('booking_id', actionBookingId.value);
    formData.append('action', 'approved');
    formData.append('admin_comment', '');

    approveBookingBtn.classList.add('loading');
    approveBookingBtn.disabled = true;
    rejectBookingBtn.disabled = true;

    const data = await sendBookingStatus(formData);
    console.log(data);
    console.log(data.email_error);
   

    approveBookingBtn.classList.remove('loading');
    approveBookingBtn.disabled = false;
    rejectBookingBtn.disabled = false;

    if (data.success) {
      alert(data.message || 'Tempahan berjaya diluluskan.');

      modalBookingStatus.textContent = 'Diluluskan';
      modalBookingStatus.className = 'status approved';

      approveBookingBtn.style.display = 'none';
      rejectBookingBtn.style.display = 'none';

      const approvedBooking = findBooking(actionBookingId.value);
      if (approvedBooking) {
        approvedBooking.booking_status = 'approved';
        whatsappBookingBtn.href = createWhatsappLink(approvedBooking);
      } else {
        whatsappBookingBtn.href = createWhatsappLink({
          booking_status: 'approved',
          phone_number: modalPhone.textContent,
          display_id: modalBookingRef.textContent,
          slot_display: modalSlot.textContent,
          package_name: modalPackage.textContent,
          activity_list: modalActivities.textContent,
          total_participants: modalParticipants.textContent,
          formatted_total_fee: modalTotalFee ? modalTotalFee.textContent : undefined
        });
      }

      whatsappBookingBtn.style.display = 'flex';
    } else {
      alert(data.message || 'Gagal mengemaskini tempahan.');
    }
  });
}



/**************************/
/*REJECT BOOKING FUNCTION*/
/*************************/

if (rejectBookingBtn) {
  rejectBookingBtn.addEventListener('click', function (event) {
    event.preventDefault();
    rejectReason.value = '';
    rejectModal.classList.add('active');
  });
}

if (confirmRejectBtn) {
  confirmRejectBtn.addEventListener('click', async function (event) {
    event.preventDefault();

    const reason = rejectReason.value.trim();

    if (reason === '') {
      alert('Sila masukkan sebab penolakan.');
      return;
    }

    const formData = new FormData();
    formData.append('booking_id', actionBookingId.value);
    formData.append('action', 'rejected');
    formData.append('admin_comment', reason);

    confirmRejectBtn.classList.add('loading');
    confirmRejectBtn.disabled = true;
    cancelRejectBtn.disabled = true;

    const data = await sendBookingStatus(formData);
    console.log(data);
    console.log(data.email_error);


    confirmRejectBtn.classList.remove('loading');
    confirmRejectBtn.disabled = false;
    cancelRejectBtn.disabled = false;

    if (data.success) {
      alert(data.message || 'Tempahan berjaya ditolak.');

      modalBookingStatus.textContent = 'Ditolak';
      modalBookingStatus.className = 'status rejected';
      modalRemark.textContent = reason;

      approveBookingBtn.style.display = 'none';
      rejectBookingBtn.style.display = 'none';

      const rejectedBooking = findBooking(actionBookingId.value);
      if (rejectedBooking) {
        rejectedBooking.booking_status = 'rejected';
        rejectedBooking.admin_remark = reason;
        whatsappBookingBtn.href = createWhatsappLink(rejectedBooking);
      } else {
        whatsappBookingBtn.href = createWhatsappLink({
          booking_status: 'rejected',
          phone_number: modalPhone.textContent,
          display_id: modalBookingRef.textContent,
          admin_remark: reason,
          formatted_total_fee: modalTotalFee ? modalTotalFee.textContent : undefined
        });
      }

      whatsappBookingBtn.style.display = 'flex';

      rejectModal.classList.remove('active');
      rejectReason.value = '';
    } else {
      alert(data.message || 'Gagal mengemaskini tempahan.');
    }
  });
}

if (cancelRejectBtn) {
  cancelRejectBtn.addEventListener('click', function () {
    rejectModal.classList.remove('active');
    rejectReason.value = '';
  });
}

function normalizeText(text) {
  return String(text || '').trim().toLowerCase();
}

function rowMatchesFilters(row, searchTerm, typeFilter, statusFilter) {
  const cells = row.querySelectorAll('td');

  const rowText = [
    cells[1]?.textContent || '',
    cells[2]?.textContent || '',
    cells[3]?.textContent || '',
    cells[5]?.textContent || ''
  ].join(' ').toLowerCase();

  const statusText = normalizeText(cells[5]?.textContent || '');
  const typeText = normalizeText(cells[2]?.textContent || '');

  return (
    (!searchTerm || rowText.includes(searchTerm)) &&
    (typeFilter === 'all' || typeText === typeFilter) &&
    (statusFilter === 'all' || statusText === statusFilter)
  );
}

function getFilteredRows() {
  const searchTerm = normalizeText(bookingSearch?.value || '');
  const typeFilter = normalizeText(bookingTypeFilter?.value || 'all');
  const statusFilter = normalizeText(bookingStatusFilter?.value || 'all');

  return allRows.filter(row => rowMatchesFilters(row, searchTerm, typeFilter, statusFilter));
}

function renderTablePage() {
  const filteredRows = getFilteredRows();
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));

  if (currentPage > totalPages) {
    currentPage = totalPages;
  }

  allRows.forEach(row => {
    row.style.display = 'none';
  });

  filteredRows.forEach((row, index) => {
    const pageIndex = Math.floor(index / pageSize) + 1;
    row.style.display = pageIndex === currentPage ? '' : 'none';
  });
}

function updateFooter() {
  const filteredRows = getFilteredRows();
  const total = filteredRows.length;
  const totalPages = Math.max(1, Math.ceil(total / pageSize));
  const first = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
  const last = total === 0 ? 0 : Math.min(currentPage * pageSize, total);

  if (bookingFooterText) {
    bookingFooterText.textContent = `Showing ${first} to ${last} out of ${total} entries`;
  }

  if (bookingPaginationInfo) {
    bookingPaginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
  }

  if (prevPageBtn) {
    prevPageBtn.disabled = currentPage <= 1;
  }

  if (nextPageBtn) {
    nextPageBtn.disabled = currentPage >= totalPages;
  }
}

function applyFilters() {
  currentPage = 1;
  renderTablePage();
  updateFooter();
}

function changePage(offset) {
  const filteredRows = getFilteredRows();
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));

  currentPage = Math.min(Math.max(currentPage + offset, 1), totalPages);

  renderTablePage();
  updateFooter();
}

function resetFilters() {
  if (bookingSearch) bookingSearch.value = '';
  if (bookingTypeFilter) bookingTypeFilter.value = 'all';
  if (bookingStatusFilter) bookingStatusFilter.value = 'all';
  applyFilters();
}

function downloadCsv(filename, csvContent) {
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);

  link.setAttribute('href', url);
  link.setAttribute('download', filename);

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  URL.revokeObjectURL(url);
}

function exportVisibleRows() {
  const rows = Array.from(bookingTableBody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');

  if (rows.length === 0) {
    alert('Tiada baris untuk dieksport.');
    return;
  }

  const csvRows = [];
  csvRows.push(['ID Tempahan', 'Nama Organisasi', 'Jenis Tempahan', 'Tarikh & Slot Masa', 'Pax', 'Jumlah Bayaran', 'Status'].join(','));

  rows.forEach(row => {
    const cells = Array.from(row.querySelectorAll('td')).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
    csvRows.push(cells.join(','));
  });

  downloadCsv('tempahan-export.csv', csvRows.join('\n'));
}

document.querySelectorAll('.booking-detail-link').forEach(link => {
  link.addEventListener('click', function (event) {
    event.preventDefault();

    const bookingId = this.dataset.bookingId;
    const booking = findBooking(bookingId);

    if (!booking) {
      alert('Maklumat tempahan tidak dijumpai.');
      return;
    }

    showBookingModal(booking);
  });
});

if (bookingSearch) bookingSearch.addEventListener('input', applyFilters);
if (bookingTypeFilter) bookingTypeFilter.addEventListener('change', applyFilters);
if (bookingStatusFilter) bookingStatusFilter.addEventListener('change', applyFilters);
if (bookingResetBtn) bookingResetBtn.addEventListener('click', resetFilters);
if (bookingExportBtn) bookingExportBtn.addEventListener('click', exportVisibleRows);
if (prevPageBtn) prevPageBtn.addEventListener('click', () => changePage(-1));
if (nextPageBtn) nextPageBtn.addEventListener('click', () => changePage(1));

if (modalClose) {
  modalClose.addEventListener('click', () => modal.classList.remove('active'));
}

if (modal) {
  modal.addEventListener('click', function (event) {
    if (event.target === modal) {
      modal.classList.remove('active');
    }
  });
}

if (rejectModal) {
  rejectModal.addEventListener('click', function (event) {
    if (event.target === rejectModal) {
      rejectModal.classList.remove('active');
    }
  });
}

applyFilters();

</script>

<script src="/galeriseramikmbpg/admin/js/sidebar.js"></script>


</body>