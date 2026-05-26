<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$packageFilter = $_GET['package'] ?? 'all';

if (isset($_GET['ajax_date'])) {
  $ajaxDate = $_GET['ajax_date'];
  $validDate = DateTime::createFromFormat('Y-m-d', $ajaxDate);

  if (!$validDate || $validDate->format('Y-m-d') !== $ajaxDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date']);
    exit;
  }

  $ajaxDateEscaped = mysqli_real_escape_string($conn, $ajaxDate);

  $closureName = '';
  $closureQuery = "SELECT closure_name FROM closure_dates WHERE closure_date = '$ajaxDateEscaped' AND status = 'active' LIMIT 1";
  $closureResult = mysqli_query($conn, $closureQuery);
  if ($closureResult && $closureRow = mysqli_fetch_assoc($closureResult)) {
    $closureName = $closureRow['closure_name'];
  }

  $slotSql = "
    SELECT
      bs.slot_id,
      bs.start_time,
      bs.end_time,
      bs.slot_status,
      p.package_name,
      GROUP_CONCAT(DISTINCT b.organization_name SEPARATOR '||') AS organization_names,
      COUNT(CASE WHEN b.booking_status IN ('pending','approved') THEN b.booking_id END) AS booking_count
    FROM booking_slots bs
    LEFT JOIN bookings b ON bs.slot_id = b.slot_id
    LEFT JOIN packages p ON bs.package_id = p.package_id
    WHERE bs.slot_date = '$ajaxDateEscaped'
    GROUP BY bs.slot_id, bs.start_time, bs.end_time, bs.slot_status, p.package_name
    ORDER BY bs.start_time ASC
  ";

  $slotResult = mysqli_query($conn, $slotSql);
  $openSlots = [];
  $bookedSlots = [];
  $closedSlots = [];
  $packageNames = [];

  while ($slot = mysqli_fetch_assoc($slotResult)) {
    $orgNames = [];
    if (!empty($slot['organization_names'])) {
      $orgNames = explode('||', $slot['organization_names']);
    }

    $item = [
      'slot_id' => (int) $slot['slot_id'],
      'start_time' => date('g.i A', strtotime($slot['start_time'])),
      'end_time' => date('g.i A', strtotime($slot['end_time'])),
      'slot_status' => $slot['slot_status'],
      'organization_names' => $orgNames,
      'booking_count' => (int) $slot['booking_count'],
    ];

    if (!empty($slot['package_name']) && !in_array($slot['package_name'], $packageNames)) {
      $packageNames[] = $slot['package_name'];
    }

    if ($slot['slot_status'] === 'available') {
      $openSlots[] = $item;
    } elseif ($slot['slot_status'] === 'closed') {
      $closedSlots[] = $item;
    } else {
      $bookedSlots[] = $item;
    }
  }

  header('Content-Type: application/json');
  echo json_encode([
    'date' => $ajaxDate,
    'display_date' => date('d F Y', strtotime($ajaxDate)),
    'package_names' => $packageNames,
    'closure_name' => $closureName,
    'open_slots' => $openSlots,
    'booked_slots' => $bookedSlots,
    'closed_slots' => $closedSlots,
    'no_slots' => empty($openSlots) && empty($bookedSlots) && empty($closedSlots),
  ]);
  exit;
}

$firstDay = "$year-$month-01";
$daysInMonth = date('t', strtotime($firstDay));
$startDay = date('N', strtotime($firstDay));

$startMonth = sprintf('%04d-%02d-01', $year, $month);
$endMonth = date('Y-m-t', strtotime($startMonth));

$closureQuery = "
  SELECT closure_date, closure_name
  FROM closure_dates
  WHERE closure_date BETWEEN '$startMonth' AND '$endMonth'
  AND status = 'active'
";

$closureResult = mysqli_query($conn, $closureQuery);
$closureDates = [];

while ($closure = mysqli_fetch_assoc($closureResult)) {
  $closureDates[$closure['closure_date']] = $closure['closure_name'];
}

$prevMonth = $month - 1;
$prevYear = $year;
$nextMonth = $month + 1;
$nextYear = $year;

if ($prevMonth < 1) {
  $prevMonth = 12;
  $prevYear--;
}

if ($nextMonth > 12) {
  $nextMonth = 1;
  $nextYear++;
}

$monthName = [
  1=>'Januari', 2=>'Februari', 3=>'Mac', 4=>'April',
  5=>'Mei', 6=>'Jun', 7=>'Julai', 8=>'Ogos',
  9=>'September', 10=>'Oktober', 11=>'November', 12=>'Disember'
];


$sql = "
  SELECT 
    s.slot_date,
    p.package_name,
    COUNT(b.booking_id) AS total_booking
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  JOIN packages p ON b.package_id = p.package_id
  WHERE MONTH(s.slot_date) = $month
  AND YEAR(s.slot_date) = $year
  GROUP BY s.slot_date, p.package_name
";

$result = mysqli_query($conn, $sql);

$slotQuery = "
  SELECT slot_date, COUNT(*) AS total_slots
  FROM booking_slots
  WHERE MONTH(slot_date) = $month
  AND YEAR(slot_date) = $year
  GROUP BY slot_date
";

$slotResult = mysqli_query($conn, $slotQuery);
$slotData = [];
while ($slotRow = mysqli_fetch_assoc($slotResult)) {
  $slotData[$slotRow['slot_date']] = (int) $slotRow['total_slots'];
}

$bookingData = [];

while ($row = mysqli_fetch_assoc($result)) {
  $date = $row['slot_date'];
  $packageType = strtolower($row['package_name']);

  if (!isset($bookingData[$date])) {
    $bookingData[$date] = [
      'total' => 0,
      'types' => []
    ];
  }

  $bookingData[$date]['total'] += $row['total_booking'];
  $bookingData[$date]['types'][] = $packageType;
}
?>



<!-- CARD-->
 <?php
function getCount($conn, $sql) {
  $result = mysqli_query($conn, $sql);
  if (!$result) return 0;

  $row = mysqli_fetch_assoc($result);
  return (int)($row['total'] ?? 0);
}

function compareTrend($current, $previous) {
  $diff = $current - $previous;
  if ($diff > 0) {
    return ['text' => "↑ {$diff} dari bulan lepas", 'class' => 'up'];
  }

  if ($diff < 0) {
    return ['text' => "↓ " . abs($diff) . " dari bulan lepas", 'class' => 'down'];
  }

  return ['text' => 'Tiada perubahan dari bulan lepas', 'class' => 'neutral'];
}

$currentMonthBookings = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE MONTH(s.slot_date) = $month
  AND YEAR(s.slot_date) = $year
");

$prevMonthBookings = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE MONTH(s.slot_date) = $prevMonth
  AND YEAR(s.slot_date) = $prevYear
");

$bookingTrend = compareTrend($currentMonthBookings, $prevMonthBookings);

$currentApplications = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE b.booking_status = 'pending'
  AND MONTH(s.slot_date) = $month
  AND YEAR(s.slot_date) = $year
");

$prevApplications = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE b.booking_status = 'pending'
  AND MONTH(s.slot_date) = $prevMonth
  AND YEAR(s.slot_date) = $prevYear
");

$applicationTrend = compareTrend($currentApplications, $prevApplications);

$approvedBookings = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE b.booking_status = 'approved'
  AND MONTH(s.slot_date) = $month
  AND YEAR(s.slot_date) = $year
");

$prevApprovedBookings = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM bookings b
  JOIN booking_slots s ON b.slot_id = s.slot_id
  WHERE b.booking_status = 'approved'
  AND MONTH(s.slot_date) = $prevMonth
  AND YEAR(s.slot_date) = $prevYear
");

$approvedTrend = compareTrend($approvedBookings, $prevApprovedBookings);

$totalProducts = getCount($conn, "
  SELECT COUNT(*) AS total
  FROM products
  WHERE status = 'active'
");

$productTrend = ['text' => 'Jumlah produk keseluruhan', 'class' => 'neutral'];
?>






<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Seramik MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/pop-up.css">
  <link rel="favicon" href="../assets/images/logogaleri.png" type="image/png">
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
          <h1>Dashboard</h1>
            <p>Selamat Datang, Admin</p>
        </div>
      </header>

      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Tempahan</h3>
            <strong><?= $currentMonthBookings ?></strong>
            <span class="trend <?= $bookingTrend['class'] ?>"><?= $bookingTrend['text'] ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Semasa</h3>
            <strong><?= $currentApplications ?></strong>
            <span class="trend <?= $applicationTrend['class'] ?>"><?= $applicationTrend['text'] ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Lulus</h3>
            <strong><?= $approvedBookings ?></strong>
            <span class="trend <?= $approvedTrend['class'] ?>"><?= $approvedTrend['text'] ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Produk</h3>
            <strong><?= $totalProducts ?></strong>
            <span class="trend <?= $productTrend['class'] ?>"><?= $productTrend['text'] ?></span>
          </div>
        </div>
      </section>

      <section class="content-grid">
        <div class="panel">
        <div class="panel-header">
          <h2>Kalendar Slot</h2>

          <form method="GET">
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
          </form>
        </div>

        <div class="panel-header calendar-nav">
          <a class="nav-btn" href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&package=<?= $packageFilter ?>">
            <i class="fa-solid fa-chevron-left"></i>
          </a>

          <strong><?= $monthName[$month] . ' ' . $year ?></strong>

          <a class="nav-btn" href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&package=<?= $packageFilter ?>">
            <i class="fa-solid fa-chevron-right"></i>
          </a>
        </div>

        <div class="calendar-head">
          <span>Isnin</span>
          <span>Selasa</span>
          <span>Rabu</span>
          <span>Khamis</span>
          <span>Jumaat</span>
          <span>Sabtu</span>
          <span>Ahad</span>
        </div>

        <div class="calendar-grid">

          <?php for ($blank = 1; $blank < $startDay; $blank++): ?>
            <div class="day muted"></div>
          <?php endfor; ?>

          <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>

            <?php
              $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
              $isClosure = isset($closureDates[$date]);
              $hasSlots = isset($slotData[$date]);
              $hasBookings = isset($bookingData[$date]) && $bookingData[$date]['total'] > 0;

              if ($isClosure) {
                $class = 'closure-day';
              } elseif ($hasBookings) {
                $class = 'booking-day';
              } elseif ($hasSlots) {
                $class = 'available-day';
              } else {
                $class = '';
              }
            ?>

            <div class="day calendar-day <?= $class ?>" data-date="<?= $date ?>">
              <span><?= $day ?></span>

              <?php if ($isClosure): ?>
                <small class="closure-name"><?= htmlspecialchars($closureDates[$date]) ?></small>
              <?php elseif (isset($bookingData[$date])): ?>
                <small><?= $bookingData[$date]['total'] ?> Tempahan</small>
              <?php endif; ?>
            </div>

          <?php endfor; ?>

        </div>

  <div class="legend">
        <span><b class="legend-box booking-box"></b> Tempahan</span>
        <span><b class="legend-box available-box"></b> Slot Tiada Tempahan</span>
        <span><b class="legend-box closure-box"></b> Tarikh Tutup</span>
      </div>
    </div>         

         


    <!-- ACTIVITY LOGS-->

       <div class="panel">
        <div class="panel-header">
            <h2>Aktiviti Terkini</h2>
            <a class="link" href="#">Lihat Semua</a>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-calendar-plus"></i></div>
            <div>
            <p>Tempahan baru oleh SK Taman Rinting</p>
            <small>10 May 2025, 10:00 AM</small>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-circle-check"></i></div>
            <div>
            <p>Permohonan diluluskan oleh PIBG SMK Pasir Gudang</p>
            <small>11 May 2025, 09:15 AM</small>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-clock"></i></div>
            <div>
            <p>Tempahan menunggu kelulusan</p>
            <small>12 May 2025, 02:30 PM</small>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-solid fa-ban"></i></div>
            <div>
            <p>Slot ditutup (Cuti Umum)</p>
            <small>17 May 2025</small>
            </div>
        </div>
        </div>
      </section>
    </main>
  </div>

</div>






<!-- POP-UP FOR DAYE SLOTS DETAILS -->
<div class="slot-modal" id="slotModal">

  <div class="slot-modal-content">

    <div class="slot-modal-header" id="slotModalHeader">
      <h2 id="slotModalDate">Tarikh</h2>

      <button class="close-modal" id="closeModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div id="noSlotMessage" class="slot-no-data" style="display: none;"></div>

    <div class="slot-section" id="openSlotSection">
      <h3>Slot Yang Tersedia</h3>
      <div id="openSlotContainer"></div>
    </div>

    <div class="slot-section" id="bookedSlotSection">
      <h3>Slot Ditempah</h3>
      <div id="bookedSlotContainer"></div>
    </div>

    <div class="slot-section" id="closedSlotSection">
      <h3>Slot Ditutup</h3>
      <div id="closedSlotContainer"></div>
    </div>

  </div>

</div>







<script src="js/sidebar.js"></script>

<!-- SCRIPT FOR POP_UP -->
 <script>

const modal = document.getElementById('slotModal');
const closeModal = document.getElementById('closeModal');
const modalDate = document.getElementById('slotModalDate');
const slotModalHeader = document.getElementById('slotModalHeader');
const noSlotMessage = document.getElementById('noSlotMessage');
const openSlotContainer = document.getElementById('openSlotContainer');
const bookedSlotContainer = document.getElementById('bookedSlotContainer');
const closedSlotContainer = document.getElementById('closedSlotContainer');
const openSlotSection = document.getElementById('openSlotSection');
const bookedSlotSection = document.getElementById('bookedSlotSection');
const closedSlotSection = document.getElementById('closedSlotSection');

function createSlotCard(slot, type) {
  const title = `${slot.start_time} - ${slot.end_time}`;
  const statusMap = {
    available: 'Tersedia',
    booked: 'Ditempah',
    closed: 'Ditutup'
  };
  const statusClass = `${type}-status`;
  const extra = type === 'booked' && slot.organization_names.length
    ? `<div class="slot-org">${slot.organization_names.map(name => `<p>${name}</p>`).join('')}</div>`
    : '';

  return `
    <div class="slot-card ${type}">
      <div class="slot-top">
        <strong>${title}</strong>
        <span class="status ${statusClass}">${statusMap[type]}</span>
      </div>
      ${extra}
    </div>
  `;
}

function showEmptyMessage(container, message) {
  container.innerHTML = `<div class="slot-card">
    <div class="slot-top">
      <strong>${message}</strong>
    </div>
  </div>`;
}

function renderSlots(data) {
  let headerText = data.display_date;
  let headerClass = 'header-default';

  if (data.package_names && data.package_names.length) {
    if (data.package_names.length === 1) {
      headerText += ` (${data.package_names[0]})`;
      const pkg = data.package_names[0].toLowerCase();
      if (pkg.includes('lawatan')) {
        headerClass = 'header-lawatan';
      } else if (pkg.includes('pendidikan')) {
        headerClass = 'header-pendidikan';
      }
    } else {
      headerText += ` (${data.package_names.length} Pakej)`;
      const lower = data.package_names.join(' ').toLowerCase();
      if (lower.includes('lawatan') && lower.includes('pendidikan')) {
        headerClass = 'header-mixed';
      }
    }
  }

  modalDate.textContent = headerText;
  slotModalHeader.className = 'slot-modal-header ' + headerClass;
  const noSlots = data.no_slots;

  noSlotMessage.style.display = noSlots ? 'block' : 'none';
  noSlotMessage.textContent = noSlots ? 'Tiada slot pada tarikh ini.' : '';

  openSlotSection.style.display = noSlots ? 'none' : 'block';
  bookedSlotSection.style.display = noSlots ? 'none' : 'block';
  closedSlotSection.style.display = noSlots ? 'none' : 'block';

  if (noSlots) {
    openSlotContainer.innerHTML = '';
    bookedSlotContainer.innerHTML = '';
    closedSlotContainer.innerHTML = '';
    return;
  }

  openSlotContainer.innerHTML = data.open_slots.length
    ? data.open_slots.map(slot => createSlotCard(slot, 'available')).join('')
    : '<div class="slot-card"><div class="slot-top"><strong>Tiada slot terbuka.</strong></div></div>';

  bookedSlotContainer.innerHTML = data.booked_slots.length
    ? data.booked_slots.map(slot => createSlotCard(slot, 'booked')).join('')
    : '<div class="slot-card"><div class="slot-top"><strong>Tiada slot ditempah.</strong></div></div>';

  closedSlotContainer.innerHTML = data.closed_slots.length
    ? data.closed_slots.map(slot => createSlotCard(slot, 'closed')).join('')
    : '<div class="slot-card"><div class="slot-top"><strong>Tiada slot ditutup.</strong></div></div>';
}

async function fetchSlotDetails(date) {
  const response = await fetch(`${window.location.pathname}?ajax_date=${date}`);
  if (!response.ok) {
    console.error('Gagal memuatkan slot:', response.statusText);
    return null;
  }

  return await response.json();
}

async function handleDayClick(day) {
  const selectedDate = day.dataset.date;
  const data = await fetchSlotDetails(selectedDate);

  if (!data) {
    return;
  }

  renderSlots(data);
  modal.classList.add('active');
}

document.querySelectorAll('.calendar-day').forEach(day => {
  if (day.classList.contains('closure-day')) {
    return;
  }

  day.addEventListener('click', () => handleDayClick(day));
});

closeModal.addEventListener('click', () => {
  modal.classList.remove('active');
});

window.addEventListener('click', (e) => {

  if(e.target === modal){
    modal.classList.remove('active');
  }

});

</script>

</body>