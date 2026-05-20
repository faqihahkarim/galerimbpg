<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../../db.php';

function getBookingStatusLabel($status) {
  switch ($status) {
    case 'approved':
    case 'accepted':
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
    case 'accepted':
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
$approvedBookings = countBookings($conn, ['approved', 'accepted']);
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
    foreach ($activities as $activityRow) {
      $parts[] = htmlspecialchars($activityRow['activity_name']) . ' (' . (int) $activityRow['participant_count'] . ')';
    }
    $bookings[$bookingId]['activity_list'] = implode(' + ', $parts);
  }
}

foreach ($bookings as &$booking) {
  if (empty($booking['activity_list'])) {
    $booking['activity_list'] = 'Tiada';
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
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/tempahan.css">
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include '../sidebar.php'; ?>

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
        <p id="bookingNoteText"><i class="fa-solid fa-info-circle"></i> Klik pada ID Tempahan untuk melihat butiran dan mengurus tempahan</p>

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
                <th>Status</th>
                <th>Catatan</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td>
                    <a href="#" class="booking-detail-link" data-booking-id="<?= $booking['booking_id'] ?>"><?= htmlspecialchars($booking['display_id']) ?></a>
                  </td>
                  <td><?= htmlspecialchars($booking['organization_name']) ?></td>
                  <td><?= htmlspecialchars($booking['package_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($booking['slot_display']) ?></td>
                  <td><?= (int) $booking['total_participants'] ?></td>
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
                Terima
            </button>

            <button type="button" class="reject-booking-btn" id="rejectBookingBtn">
                Batal
            </button>
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
            Hantar
          </button>
        </div>

      </div>

    </div>

    </main>

</div>

<script>
  const bookingData = <?= json_encode(array_values($bookings), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

  const modal = document.getElementById('bookingModal');
  const modalClose = modal.querySelector('.booking-modal-close');
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


  const actionBookingId = document.getElementById('actionBookingId');
  const actionBookingStatus = document.getElementById('actionBookingStatus');
  const bookingActionForm = document.getElementById('bookingActionForm');
  const approveBookingBtn = document.getElementById('approveBookingBtn');
  const rejectBookingBtn = document.getElementById('rejectBookingBtn');

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

  const rejectModal = document.getElementById('rejectModal');
  const rejectReason = document.getElementById('rejectReason');
  const confirmRejectBtn = document.getElementById('confirmRejectBtn');
  const cancelRejectBtn = document.getElementById('cancelRejectBtn');
  const adminCommentInput = document.getElementById('adminCommentInput');

  let currentPage = 1;
  const pageSize = 10;
  const allRows = Array.from(bookingTableBody.querySelectorAll('tr'));

  function findBooking(bookingId) {
    return bookingData.find(item => String(item.booking_id) === String(bookingId));
  }

  function setStatusLabel(statusClass, label) {
    modalBookingStatus.textContent = label;
    modalBookingStatus.className = 'status ' + statusClass;
  }

  function showBookingModal(booking) {
    modalBookingRef.textContent = booking.display_id;
    setStatusLabel(booking.status_class, booking.status_label);
    modalOrganization.textContent = booking.organization_name || '-';
    modalPhone.textContent = booking.phone_number || '-';
    modalEmail.textContent = booking.email || '-';
    modalSlot.textContent = booking.slot_display || '-';
    modalPackage.textContent = booking.package_name || '-';
    modalActivities.textContent = booking.activity_list || 'Tiada';
    modalParticipants.textContent = booking.total_participants ? booking.total_participants : '-';
    modalRemark.textContent = booking.admin_remark ? booking.admin_remark : '-';
    modal.classList.add('active');

    actionBookingId.value = booking.booking_id;
    actionBookingStatus.value = booking.booking_status;
    // Use the normalized status_class (set by PHP) to decide visibility
    if (booking.status_class === 'pending') {
      approveBookingBtn.style.display = 'inline-block';
      rejectBookingBtn.style.display = 'inline-block';
    } else {
      approveBookingBtn.style.display = 'none';
      rejectBookingBtn.style.display = 'none';
    }

    modal.classList.add('active');
  }

  function normalizeText(text) {
    return text.trim().toLowerCase();
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

    const matchesSearch = !searchTerm || rowText.includes(searchTerm);
    const matchesType = typeFilter === 'all' || typeText === typeFilter;
    const matchesStatus = statusFilter === 'all' || statusText === statusFilter;

    return matchesSearch && matchesType && matchesStatus;
  }

  function getFilteredRows() {
    const searchTerm = normalizeText(bookingSearch.value || '');
    const typeFilter = normalizeText(bookingTypeFilter.value || 'all');
    const statusFilter = normalizeText(bookingStatusFilter.value || 'all');
    return allRows.filter(row => rowMatchesFilters(row, searchTerm, typeFilter, statusFilter));
  }

  function renderTablePage() {
    const filteredRows = getFilteredRows();
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));

    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    filteredRows.forEach((row, index) => {
      const pageIndex = Math.floor(index / pageSize) + 1;
      row.style.display = pageIndex === currentPage ? '' : 'none';
    });

    allRows.filter(row => !filteredRows.includes(row)).forEach(row => {
      row.style.display = 'none';
    });
  }

  function updateFooter() {
    const filteredRows = getFilteredRows();
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    const first = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
    const last = total === 0 ? 0 : Math.min(currentPage * pageSize, total);

    bookingFooterText.textContent = `Showing ${first} to ${last} out of ${total} entries`;
    bookingPaginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages;
  }

 approveBookingBtn.addEventListener('click', () => {

  if (confirm('Adakah anda pasti mahu meluluskan tempahan ini?')) {

    actionBookingStatus.value = 'approved';

    adminCommentInput.value = 'Tempahan diterima';

    bookingActionForm.submit();

  }

});

rejectBookingBtn.addEventListener('click', () => {

  rejectModal.classList.add('active');

});

confirmRejectBtn.addEventListener('click', () => {

  const reason = rejectReason.value.trim();

  if (reason === '') {
    alert('Sila masukkan sebab penolakan.');
    return;
  }

  actionBookingStatus.value = 'rejected';

  adminCommentInput.value = reason;

  bookingActionForm.submit();

});

cancelRejectBtn.addEventListener('click', () => {
  rejectModal.classList.remove('active');
  rejectReason.value = '';
});

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
    bookingSearch.value = '';
    bookingTypeFilter.value = 'all';
    bookingStatusFilter.value = 'all';
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
    const csvRows = [];
    csvRows.push(['ID Tempahan', 'Nama Organisasi', 'Jenis Tempahan', 'Tarikh & Slot Masa', 'Pax', 'Status'].join(','));

    rows.forEach(row => {
      const cells = Array.from(row.querySelectorAll('td')).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
      csvRows.push(cells.join(','));
    });

    if (rows.length === 0) {
      alert('Tiada baris untuk dieksport.');
      return;
    }

    downloadCsv('tempahan-export.csv', csvRows.join('\n'));
  }

  document.querySelectorAll('.booking-detail-link').forEach(link => {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      const bookingId = this.dataset.bookingId;
      const booking = findBooking(bookingId);
      if (!booking) return;
      showBookingModal(booking);
    });
  });

  bookingSearch.addEventListener('input', applyFilters);
  bookingTypeFilter.addEventListener('change', applyFilters);
  bookingStatusFilter.addEventListener('change', applyFilters);
  bookingResetBtn.addEventListener('click', resetFilters);
  bookingExportBtn.addEventListener('click', exportVisibleRows);
  prevPageBtn.addEventListener('click', () => changePage(-1));
  nextPageBtn.addEventListener('click', () => changePage(1));

  modalClose.addEventListener('click', () => modal.classList.remove('active'));
  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.classList.remove('active');
    }
  });

  applyFilters();
</script>

<script src="/web/galeriseramikmbpg/admin/js/sidebar.js"></script>


</body>