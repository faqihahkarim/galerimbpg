<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking_slots");
$totalRules = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalRules / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$slotsQuery = "
    SELECT bs.slot_id, bs.package_id, p.package_name, bs.slot_date, bs.start_time, bs.end_time, bs.slot_status, cd.closure_name
    FROM booking_slots bs
    LEFT JOIN packages p ON bs.package_id = p.package_id
    LEFT JOIN closure_dates cd ON bs.slot_date = cd.closure_date
    ORDER BY bs.slot_date DESC, bs.start_time DESC
    LIMIT $limit
    OFFSET $offset
";

$slotsResult = mysqli_query($conn, $slotsQuery);

$packagesResult = mysqli_query($conn, "SELECT package_id, package_name FROM packages");

$flashMessage = '';
$flashClass = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'slot_updated':
            $flashMessage = 'Slot berjaya dikemaskini.';
            $flashClass = 'success-alert';
            break;
        case 'slots_generated':
            $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
            $flashMessage = $count . ' slot berjaya dijana.';
            $flashClass = 'success-alert';

            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $flashMessage = 'ID slot tidak sah.';
            $flashClass = 'error-alert';
            break;
        default:
            if ($flashMessage === '') {
                $flashMessage = 'Terdapat ralat. Sila cuba lagi.';
                $flashClass = 'error-alert';
            }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Slot - MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/tempahan.css">
  <link rel="stylesheet" href="css/rule.css">
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main">

    <header class="topbar">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div>
            <h1>Pengurusan Slot</h1>
            <p>Senarai Slot Tempahan</p>
        </div>
    </header>

    <?php if ($flashMessage): ?>
      <div class="alert <?= $flashClass ?>">
        <?= htmlspecialchars($flashMessage) ?>
      </div>
    <?php endif; ?>

    <section class="booking-panel">

        <div class="table-wrap">
            <!-- Action Buttons -->
            <div class="rule-actions">
                <button type="button" class="blue-btn" id="openGenerateModal">
                    <i class="fa-solid fa-gear"></i> Jana Slot Baru
                </button>
            </div>

            <table>
                <thead>
                    <tr>
                    <th>No.</th>
                    <th>Pakej</th>
                    <th>Tarikh</th>
                    <th>Masa Mula</th>
                    <th>Masa Akhir</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php while ($slot = mysqli_fetch_assoc($slotsResult)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($slot['package_name'] ?? '') ?></td>
                        <td><?= date('j M Y', strtotime($slot['slot_date'])) ?></td>
                        <td><?= date('g:iA', strtotime($slot['start_time'])) ?></td>
                        <td><?= date('g:iA', strtotime($slot['end_time'])) ?></td>
                        <td><?= htmlspecialchars($slot['slot_status'] ?? '') ?></td>
                        <td>
                            <button type="button" class="edit-slot-btn"
                              data-id="<?= $slot['slot_id'] ?>"
                              data-package="<?= $slot['package_id'] ?>"
                              data-date="<?= $slot['slot_date'] ?>"
                              data-start="<?= $slot['start_time'] ?>"
                              data-end="<?= $slot['end_time'] ?>"
                              data-status="<?= htmlspecialchars($slot['slot_status'] ?? '') ?>"
                            >Edit</button>
                         </td>
                        <td>
                            <a href="delete_slot.php?id=<?= $slot['slot_id'] ?>" onclick="return confirm('Padam slot ini?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!--pagination-->
            <div class="pagination">

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="page-btn">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <a 
                        href="?page=<?= $i ?>" 
                        class="page-btn <?= $page == $i ? 'active-page' : '' ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>

            </div>
        </div>

    </section>

<!-- POP UP JANA SLOT -->
<div class="popup-modal" id="generateModal">
  <div class="popup-card">
    <h2>Jana Slot Tempahan</h2>

    <form action="generate_slots.php" method="POST">
      <p>Proses ini akan menjana slot tempahan berdasarkan peraturan yang telah ditetapkan. Pastikan semua peraturan sudah dikemaskini sebelum menjana slot.</p>
      <label>Pilih Bulan</label>
      <input type="month" name="generate_month" required>

      <div class="popup-actions">
        <button type="button" class="cancel-btn close-popup">Batal</button>
        <button type="submit" class="save-btn">Jana Slot</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT SLOT MODAL -->
<div class="popup-modal" id="editSlotModal">
  <div class="popup-card">
    <h2>Edit Slot</h2>

    <form id="slotForm" action="edit_slot.php" method="POST">
      <input type="hidden" name="slot_id" id="slot_id" value="">
      <label>Pakej</label>
      <select id="slotPackage" name="package_id" required>
        <option value="">Pilih Pakej</option>
        <?php mysqli_data_seek($packagesResult, 0); ?>
        <?php while ($package = mysqli_fetch_assoc($packagesResult)): ?>
          <option value="<?= $package['package_id'] ?>"><?= htmlspecialchars($package['package_name']) ?></option>
        <?php endwhile; ?>
      </select>

      <label>Tarikh</label>
      <input id="slotDate" type="date" name="slot_date" required>

      <label>Masa Mula</label>
      <input id="slotStart" type="time" name="start_time" required>

      <label>Masa Akhir</label>
      <input id="slotEnd" type="time" name="end_time" required>

      <label>Status</label>
      <select id="slotStatus" name="slot_status" required>
        <option value="available">available</option>
        <option value="closed">closed</option>
        <option value="booked">booked</option>
      </select>

      <div class="popup-actions">
        <button type="button" class="cancel-btn close-popup">Batal</button>
        <button type="submit" class="save-btn">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="js/sidebar.js"></script>

<!-- SCRIPT POP UP -->
 <script>
const openGenerateModal = document.getElementById('openGenerateModal');
const generateModal = document.getElementById('generateModal');
const editSlotModal = document.getElementById('editSlotModal');

openGenerateModal.addEventListener('click', () => {
  generateModal.classList.add('active');
});

// edit slot buttons
document.querySelectorAll('.edit-slot-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    document.getElementById('slot_id').value = id;
    document.getElementById('slotPackage').value = btn.dataset.package || '';
    document.getElementById('slotDate').value = btn.dataset.date || '';
    document.getElementById('slotStart').value = btn.dataset.start || '';
    document.getElementById('slotEnd').value = btn.dataset.end || '';
    document.getElementById('slotStatus').value = btn.dataset.status || '';
    editSlotModal.classList.add('active');
  });
});

document.querySelectorAll('.close-popup').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.popup-modal').forEach(modal => {
      modal.classList.remove('active');
    });
  });
});

document.querySelectorAll('.popup-modal').forEach(modal => {
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.remove('active');
    }
  });
});
</script>

</body>
</html>
