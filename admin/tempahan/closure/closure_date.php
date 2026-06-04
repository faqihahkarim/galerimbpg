<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../../db.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM closure_dates");
$totalRules = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalRules / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$closureQuery = "
    SELECT closure_id, closure_date, closure_name, status
    FROM closure_dates
    ORDER BY closure_date DESC
    LIMIT $limit
    OFFSET $offset
";

$closureResult = mysqli_query($conn, $closureQuery);

$flashMessage = '';
$flashClass = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'closure_updated':
            $flashMessage = 'Tarikh tutup berjaya dikemaskini.';
            $flashClass = 'success-alert';
            break;
        case 'closure_added':
            $flashMessage = 'Tarikh tutup baru berjaya disimpan.';
            $flashClass = 'success-alert';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $flashMessage = 'ID tarikh tutup tidak sah.';
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
  <title>Tarikh Tutup - MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/tempahan.css">
  <link rel="stylesheet" href="../../css/rule.css">
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
            <h1>Tarikh Tutup</h1>
            <p>Urus Tarikh Tutup</p>
        </div>
    </header>

    <?php if ($flashMessage): ?>
      <div class="alert <?= $flashClass ?>">
        <?= htmlspecialchars($flashMessage) ?>
      </div>
    <?php endif; ?>

    <section class="booking-panel">

        <div class="rule-actions">
                <button type="button" class="red-btn" id="openClosureModal">
                    <i class="fa-solid fa-plus"></i> Tambah Tarikh Tutup
                </button>
            </div>

        <div class="table-wrap">
            <!-- Action Buttons -->
            

            <table>
                <thead>
                    <tr>
                    <th>No.</th>
                    <th>Tarikh Tutup</th>
                    <th>Sebab</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php while ($c = mysqli_fetch_assoc($closureResult)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('j M Y', strtotime($c['closure_date'])) ?></td>
                        <td><?= htmlspecialchars($c['closure_name']) ?></td>
                        <td><?= htmlspecialchars($c['status']) ?></td>
                        <td>
                            <button type="button" class="edit-closure-btn"
                                style="background: none; border: none; color: #1565c0; cursor: pointer; font-family: inherit; font-weight: bold;"
                                
                                data-id="<?= $c['closure_id'] ?>"
                                data-date="<?= $c['closure_date'] ?>"
                                data-name="<?= htmlspecialchars($c['closure_name']) ?>"
                                data-status="<?= htmlspecialchars($c['status']) ?>"
                                >Edit
                            </button>
                        </td>
                         <td>
                            <button class="delete-product-btn" onclick="return confirm('Padam tarikh tutup ini?')" 
                                    style="background: none; border: none; color: #c62828; cursor: pointer; font-family: inherit; font-weight: bold;">
                                Delete
                            </button>
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

                <?php
                // CONFIGURATION: Set how many pages to show around the active page item
                $adjacents = 2; 

                // Always show Page 1
                if ($page > ($adjacents + 1)) {
                    echo '<a href="?page=1" class="page-btn">1</a>';
                    if ($page > ($adjacents + 2)) {
                        echo '<span class="page-dots" style="padding: 8px 12px; color: var(--text-soft);">...</span>';
                    }
                }

                // Calculate dynamic sliding range window positions
                $startLoop = max(1, $page - $adjacents);
                $endLoop   = min($totalPages, $page + $adjacents);

                for ($i = $startLoop; $i <= $endLoop; $i++) {
                    $activeClass = ($page == $i) ? 'active-page' : '';
                    echo '<a href="?page=' . $i . '" class="page-btn ' . $activeClass . '">' . $i . '</a>';
                }

                // Always show the Last Page boundary 
                if ($page < ($totalPages - $adjacents)) {
                    if ($page < ($totalPages - $adjacents - 1)) {
                        echo '<span class="page-dots" style="padding: 8px 12px; color: var(--text-soft);">...</span>';
                    }
                    echo '<a href="?page=' . $totalPages . '" class="page-btn">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>

            </div>
        </div>

    </section>

<!-- POP UP TARIKH TUTUP -->
<div class="popup-modal" id="closureModal">
  <div class="popup-card">
    <h2>Tambah Tarikh Tutup</h2>

    <form id="closureForm" action="add_closure.php" method="POST">
      <input type="hidden" name="closure_id" id="closure_id" value="">
      <label>Tarikh Tutup</label>
      <input id="closureDate" type="date" name="closure_date" required>

      <label>Sebab Tutup</label>
      <input id="closureName" type="text" name="closure_name" placeholder="Contoh: Hari Wesak" required>

      <label>Status</label>
      <select id="closureStatus" name="status" required>
        <option value="active">Aktif</option>
        <option value="inactive">Tidak Aktif</option>
      </select>

      <div class="popup-actions">
        <button type="button" class="cancel-btn close-popup">Batal</button>
        <button type="submit" class="save-btn">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="/galeriseramikmbpg/admin/js/sidebar.js"></script>

<!-- SCRIPT POP UP -->
 <script>
const openClosureModal = document.getElementById('openClosureModal');
const closureModal = document.getElementById('closureModal');

openClosureModal.addEventListener('click', () => {
  // prepare form for add
  const form = document.getElementById('closureForm');
  form.action = 'add_closure.php';
  document.getElementById('closure_id').value = '';
  document.getElementById('closureDate').value = '';
  document.getElementById('closureName').value = '';
  document.getElementById('closureStatus').value = 'active';
  closureModal.classList.add('active');
});

// edit buttons
document.querySelectorAll('.edit-closure-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const form = document.getElementById('closureForm');
    form.action = 'edit_closure.php';
    document.getElementById('closure_id').value = id;
    document.getElementById('closureDate').value = btn.dataset.date || '';
    document.getElementById('closureName').value = btn.dataset.name || '';
    document.getElementById('closureStatus').value = btn.dataset.status || 'active';
    closureModal.classList.add('active');
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
