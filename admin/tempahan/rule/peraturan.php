<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../../db.php';

function getMalayDayName($day) {
    $day = trim($day);
    $map = [
        'Monday' => 'Isnin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Khamis',
        'Friday' => 'Jumaat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Ahad',
        'Isnin' => 'Isnin',
        'Selasa' => 'Selasa',
        'Rabu' => 'Rabu',
        'Khamis' => 'Khamis',
        'Jumaat' => 'Jumaat',
        'Sabtu' => 'Sabtu',
        'Ahad' => 'Ahad',
    ];
    return $map[$day] ?? $day;
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking_rules");
$totalRules = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalRules / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$rulesQuery = "
    SELECT 
        br.rule_id,
        br.package_id,
        br.day_of_week,
        br.start_time,
        br.end_time,
        br.max_booking_per_slot,
        br.status,
        p.package_name
    FROM booking_rules br
    JOIN packages p ON br.package_id = p.package_id
    ORDER BY br.rule_id DESC
    LIMIT $limit
    OFFSET $offset
";

$rulesResult = mysqli_query($conn, $rulesQuery);

$packagesResult = mysqli_query($conn, "
    SELECT package_id, package_name 
    FROM packages
    WHERE status = 'active'
");

$flashMessage = '';
$flashClass = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'rule_updated':
            $flashMessage = 'Peraturan berjaya dikemaskini.';
            $flashClass = 'success-alert';
            break;
        case 'rule_added':
            $flashMessage = 'Peraturan baru berjaya disimpan.';
            $flashClass = 'success-alert';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $flashMessage = 'ID peraturan tidak sah.';
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
  <title>MBPG</title>

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
            <h1>Pengurusan Tempahan</h1>
            <p>Pengaturan Tempahan</p>
        </div>
    </header>

    <?php if ($flashMessage): ?>
      <div class="alert <?= $flashClass ?>">
        <?= htmlspecialchars($flashMessage) ?>
      </div>
    <?php endif; ?>

    <section class="booking-panel">

    <div class="rule-actions">
              <button type="button" class="red-btn" id="openRuleModal">
                <i class="fa-solid fa-plus"></i> Tambah Peraturan
              </button>
            </div>

        <div class="table-wrap">
            <!-- Action Buttons -->
            
            <table>
                <thead>
                    <tr>
                    <th>No.</th>
                    <th>Pakej</th>
                    <th>Hari</th>
                    <th>Masa Mula</th>
                    <th>Masa Akhir</th>
                    <th>Max Tempah/Slot</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php while ($rule = mysqli_fetch_assoc($rulesResult)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($rule['package_name']) ?></td>
                        <td><?= htmlspecialchars(getMalayDayName($rule['day_of_week'])) ?></td>
                        <td><?= date('g:iA', strtotime($rule['start_time'])) ?></td>
                        <td><?= date('g:iA', strtotime($rule['end_time'])) ?></td>
                        <td><?= $rule['max_booking_per_slot'] ?></td>
                        <td><?= htmlspecialchars($rule['status']) ?></td>
                        <td>
                        <button type="button" class="edit-rule-btn" 
                          style="background: none; border: none; color: #1565c0; cursor: pointer; font-family: inherit; font-weight: bold;" 
                          data-id="<?= $rule['rule_id'] ?>" 
                          data-package="<?= $rule['package_id'] ?>" 
                          data-day="<?= htmlspecialchars($rule['day_of_week']) ?>" 
                          data-start="<?= $rule['start_time'] ?>" 
                          data-end="<?= $rule['end_time'] ?>" 
                          data-max="<?= $rule['max_booking_per_slot'] ?>" 
                          data-status="<?= htmlspecialchars($rule['status']) ?>"
                        >Edit</button>
                        </td>
                        <td>
                            <button class="delete-product-btn" onclick="return confirm('Padam peraturan ini?')" 
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

<!-- POP UP TAMBAH PERATURAN -->
<div class="popup-modal" id="ruleModal">
  <div class="popup-card">
    <h2>Tambah Peraturan Slot</h2>

    <form id="ruleForm" action="add_rule.php" method="POST">
      <input type="hidden" name="rule_id" id="rule_id" value="">
      <label>Pakej</label>
      <select id="rulePackage" name="package_id" required>
        <option value="">Pilih Pakej</option>
        <?php mysqli_data_seek($packagesResult, 0); ?>
        <?php while ($package = mysqli_fetch_assoc($packagesResult)): ?>
          <option value="<?= $package['package_id'] ?>">
            <?= htmlspecialchars($package['package_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label>Hari</label>
      <select id="ruleDay" name="day_of_week" required>
        <option value="Monday">Isnin</option>
        <option value="Tuesday">Selasa</option>
        <option value="Wednesday">Rabu</option>
        <option value="Thursday">Khamis</option>
        <option value="Friday">Jumaat</option>
        <option value="Saturday">Sabtu</option>
        <option value="Sunday">Ahad</option>
      </select>

      <label>Masa Mula</label>
      <input id="ruleStart" type="time" name="start_time" required>

      <label>Masa Akhir</label>
      <input id="ruleEnd" type="time" name="end_time" required>

      <label>Max Tempah Per Slot</label>
      <input id="ruleMax" type="number" name="max_booking_per_slot" min="1" value="1" required>

      <label>Status</label>
      <select id="ruleStatus" name="status" required>
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
const openRuleModal = document.getElementById('openRuleModal');
const ruleModal = document.getElementById('ruleModal');

openRuleModal.addEventListener('click', () => {
  // prepare form for adding
  const form = document.getElementById('ruleForm');
  form.action = 'add_rule.php';
  document.getElementById('rule_id').value = '';
  document.getElementById('rulePackage').value = '';
  document.getElementById('ruleDay').value = 'Isnin';
  document.getElementById('ruleStart').value = '';
  document.getElementById('ruleEnd').value = '';
  document.getElementById('ruleMax').value = '1';
  document.getElementById('ruleStatus').value = 'Aktif';
  ruleModal.classList.add('active');
});

// edit buttons
document.querySelectorAll('.edit-rule-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const form = document.getElementById('ruleForm');
    form.action = 'edit_rule.php';
    document.getElementById('rule_id').value = id;
    document.getElementById('rulePackage').value = btn.dataset.package || '';
    document.getElementById('ruleDay').value = btn.dataset.day || 'Isnin';
    document.getElementById('ruleStart').value = btn.dataset.start || '';
    document.getElementById('ruleEnd').value = btn.dataset.end || '';
    document.getElementById('ruleMax').value = btn.dataset.max || '1';
    document.getElementById('ruleStatus').value = btn.dataset.status || 'Aktif';
    ruleModal.classList.add('active');
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