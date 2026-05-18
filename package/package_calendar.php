<?php
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";

$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$selected_date = $_GET['date'] ?? date('Y-m-d');

$minBookingDate = date('Y-m-d', strtotime('+3 days'));

$startMonth = sprintf("%04d-%02d-01", $year, $month);
$endMonth = date("Y-m-t", strtotime($startMonth));

/* PACKAGE INFO */
$packageQuery = "
  SELECT package_id, package_name
  FROM packages
  WHERE package_id = $package_id
  AND status = 'active'
";

$packageResult = mysqli_query($conn, $packageQuery);
$package = mysqli_fetch_assoc($packageResult);

/* ALLOWED DAYS */
$rulesQuery = "
  SELECT day_of_week
  FROM booking_rules
  WHERE package_id = $package_id
  AND status = 'active'
";

$rulesResult = mysqli_query($conn, $rulesQuery);

$allowedDays = [];

while ($rule = mysqli_fetch_assoc($rulesResult)) {
  if (!in_array($rule['day_of_week'], $allowedDays)) {
    $allowedDays[] = $rule['day_of_week'];
  }
}

/* CLOSURE DATES */
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

/* ALL SLOTS FOR CURRENT MONTH */
$slotQuery = "
  SELECT 
    bs.slot_id,
    bs.slot_date,
    bs.start_time,
    bs.end_time,
    bs.slot_status,
    br.max_booking_per_slot,

    COUNT(
      CASE 
        WHEN b.booking_status IN ('pending', 'approved') 
        THEN b.booking_id 
      END
    ) AS booking_count

  FROM booking_slots bs

  LEFT JOIN booking_rules br
    ON bs.package_id = br.package_id
    AND bs.start_time = br.start_time
    AND bs.end_time = br.end_time
    AND br.status = 'active'
    AND br.day_of_week = DAYNAME(bs.slot_date)

  LEFT JOIN bookings b
    ON bs.slot_id = b.slot_id

  WHERE bs.package_id = $package_id
  AND bs.slot_date BETWEEN '$startMonth' AND '$endMonth'

  GROUP BY 
    bs.slot_id,
    bs.slot_date,
    bs.start_time,
    bs.end_time,
    bs.slot_status,
    br.max_booking_per_slot

  ORDER BY bs.slot_date ASC, bs.start_time ASC
";

$slotResult = mysqli_query($conn, $slotQuery);

$slotsByDate = [];

while ($slot = mysqli_fetch_assoc($slotResult)) {
  $date = $slot['slot_date'];

  if (!isset($slotsByDate[$date])) {
    $slotsByDate[$date] = [
      'total' => 0,
      'available' => 0,
      'full' => 0
    ];
  }

  $slotsByDate[$date]['total']++;

  $maxBooking = $slot['max_booking_per_slot'] ?? 1;
  $bookingCount = $slot['booking_count'];

  $isUnavailable = (
    $slot['slot_status'] != 'available' ||
    $bookingCount >= $maxBooking
  );

  if ($isUnavailable) {
    $slotsByDate[$date]['full']++;
  } else {
    $slotsByDate[$date]['available']++;
  }
}

/* SELECTED DATE SLOTS */
$selectedSlotQuery = "
  SELECT 
    bs.slot_id,
    bs.slot_date,
    bs.start_time,
    bs.end_time,
    bs.slot_status,
    br.max_booking_per_slot,

    COUNT(
      CASE 
        WHEN b.booking_status IN ('pending', 'approved') 
        THEN b.booking_id 
      END
    ) AS booking_count

  FROM booking_slots bs

  LEFT JOIN booking_rules br
    ON bs.package_id = br.package_id
    AND bs.start_time = br.start_time
    AND bs.end_time = br.end_time
    AND br.status = 'active'
    AND br.day_of_week = DAYNAME(bs.slot_date)

  LEFT JOIN bookings b
    ON bs.slot_id = b.slot_id

  WHERE bs.package_id = $package_id
  AND bs.slot_date = '$selected_date'

  GROUP BY 
    bs.slot_id,
    bs.slot_date,
    bs.start_time,
    bs.end_time,
    bs.slot_status,
    br.max_booking_per_slot

  ORDER BY bs.start_time ASC
";

$selectedSlotResult = mysqli_query($conn, $selectedSlotQuery);

/* MONTH NAVIGATION */
$prevMonth = date('m', strtotime("$startMonth -1 month"));
$prevYear = date('Y', strtotime("$startMonth -1 month"));

$nextMonth = date('m', strtotime("$startMonth +1 month"));
$nextYear = date('Y', strtotime("$startMonth +1 month"));

$monthName = date('F Y', strtotime($startMonth));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($package['package_name'] ?? 'Tempahan'); ?></title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= $base ?>assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/index.css">
  <link rel="stylesheet" href="<?= $base ?>package/package.css">
  <link rel="stylesheet" href="<?= $base ?>package/calendar_page.css">

  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="booking-section">
  <div class="booking-container">

    <div class="booking-title">
      <h1><?= htmlspecialchars($package['package_name'] ?? 'Pakej'); ?></h1>
      <p>Pilih tarikh yang mempunyai kekosongan pada kalendar untuk tempahan slot.</p>
      <span>Lakukan tempahan 3 hari sebelum.</span>
    </div>

    <div class="booking-controls">
      <a class="month-btn" href="?package_id=<?= $package_id ?>&month=<?= $prevMonth ?>&year=<?= $prevYear ?>">
        &lt; Bulan Sebelum
      </a>

      <form method="GET" class="month-select-form">
        <input type="hidden" name="package_id" value="<?= $package_id; ?>">

        <div class="month-select">
          <i class="fa-regular fa-calendar"></i>

          <select name="month" onchange="this.form.submit()">
            <?php
              $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Mac',
                4 => 'April', 5 => 'Mei', 6 => 'Jun',
                7 => 'Julai', 8 => 'Ogos', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Disember'
              ];

              foreach ($months as $num => $name):
            ?>
              <option value="<?= $num; ?>" <?= ($month == $num) ? 'selected' : ''; ?>>
                <?= $name; ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="year" onchange="this.form.submit()">
            <?php
              $currentYear = date('Y');
                // Allow selection of current year and next 2 years
              for ($y = $currentYear; $y <= $currentYear + 2; $y++):
            ?>
              <option value="<?= $y; ?>" <?= ($year == $y) ? 'selected' : ''; ?>>
                <?= $y; ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
      </form>

      <a class="month-btn" href="?package_id=<?= $package_id ?>&month=<?= $nextMonth ?>&year=<?= $nextYear ?>">
        Bulan Selepas &gt;
      </a>
    </div>

    <div class="booking-legend">
      <span><i class="dot available"></i> Tersedia</span>
      <span><i class="dot almost"></i> Hampir Penuh</span>
      <span><i class="dot full"></i> Penuh</span>
      <span><i class="dot less"></i> Kurang 3 Hari</span>
      <span><i class="dot none"></i> Tiada Slot</span>
    </div>

    <div class="booking-layout">

      <div class="calendar-box">
        <div class="calendar-days">
          <div>Isnin</div>
          <div>Selasa</div>
          <div>Rabu</div>
          <div>Khamis</div>
          <div>Jumaat</div>
          <div>Sabtu</div>
          <div>Ahad</div>
        </div>

        <div class="calendar-grid">
          <?php
            $firstDay = date('N', strtotime($startMonth));
            $daysInMonth = date('t', strtotime($startMonth));

            for ($blank = 1; $blank < $firstDay; $blank++) {
              echo '<div class="date empty"></div>';
            }

            for ($day = 1; $day <= $daysInMonth; $day++):
              $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $day);
              $dayName = date('l', strtotime($currentDate));

              $isAllowedDay = in_array($dayName, $allowedDays);
              $isClosure = isset($closureDates[$currentDate]);
              $isTooEarly = $currentDate < $minBookingDate;

              $dateStatus = "disabled";
              $label = "";

            if ($isClosure) {
              $dateStatus = "closure";
              $label = $closureDates[$currentDate];
              /*-- Shorten long closure names*/
              if (strlen($label) > 8) {
                $label = substr($label, 0, 8) . '...';
              }

            } elseif (!$isAllowedDay) {
              $dateStatus = "disabled";
              $label = "";

            } elseif ($isTooEarly) {
              $dateStatus = "too-early";
              $label = "";

            } elseif (!isset($slotsByDate[$currentDate])) {
              $dateStatus = "disabled";
              $label = "";

            } else {
              $total = $slotsByDate[$currentDate]['total'];
              $available = $slotsByDate[$currentDate]['available'];

              if ($available == 0) {
                $dateStatus = "full";
                $label = "Penuh";
              } elseif ($available < $total) {
                $dateStatus = "almost";
                $label = "";
              } else {
                $dateStatus = "available";
                $label = "";
              }
            }

              $activeClass = ($currentDate == $selected_date) ? "active" : "";
          ?>

            <?php if ($dateStatus == "available" || $dateStatus == "almost"): ?>
              <a 
                href="?package_id=<?= $package_id ?>&month=<?= $month ?>&year=<?= $year ?>&date=<?= $currentDate ?>"
                class="date <?= $dateStatus ?> <?= $activeClass ?>"
              >
                <span><?= $day; ?></span>
              </a>
            <?php else: ?>
              <button class="date disabled <?= $dateStatus ?>" disabled>
                <span><?= $day; ?></span>
                <?php if (!empty($label)): ?>
                  <small><?= $label; ?></small>
                <?php endif; ?>
              </button>
            <?php endif; ?>

          <?php endfor; ?>
        </div>
      </div>

      <div class="slot-card">
        <div class="slot-header">
          <h3><?= date("j F Y", strtotime($selected_date)); ?></h3>
        </div>

        <hr>

        <h4>Slot yang tersedia</h4>

        <?php
          mysqli_data_seek($selectedSlotResult, 0);
          $hasAvailable = false;

          while ($slot = mysqli_fetch_assoc($selectedSlotResult)):
            $maxBooking = $slot['max_booking_per_slot'] ?? 1;
            $bookingCount = $slot['booking_count'];

            $isUnavailable = (
              $slot['slot_status'] != 'available' ||
              $bookingCount >= $maxBooking
            );

            if (!$isUnavailable):
              $hasAvailable = true;
        ?>

          <div class="slot-item">
            <div>
              <strong>
                <i class="fa-regular fa-clock"></i>
                <?= date("g.i A", strtotime($slot['start_time'])); ?>
                -
                <?= date("g.i A", strtotime($slot['end_time'])); ?>
              </strong>
            </div>

            <a 
              href="booking_form.php?package_id=<?= $package_id; ?>&slot_id=<?= $slot['slot_id']; ?>&date=<?= $selected_date; ?>" 
              class="book-btn"
            >
              Pilih Slot
            </a>
          </div>

        <?php endif; endwhile; ?>

        <?php if (!$hasAvailable): ?>
          <p>Tiada slot tersedia untuk tarikh ini.</p>
        <?php endif; ?>

        <h4>Slot penuh / tidak tersedia</h4>

        <?php
          mysqli_data_seek($selectedSlotResult, 0);
          $hasUnavailable = false;

          while ($slot = mysqli_fetch_assoc($selectedSlotResult)):
            $maxBooking = $slot['max_booking_per_slot'] ?? 1;
            $bookingCount = $slot['booking_count'];

            $isUnavailable = (
              $slot['slot_status'] != 'available' ||
              $bookingCount >= $maxBooking
            );

            if ($isUnavailable):
              $hasUnavailable = true;
        ?>

          <div class="slot-item unavailable">
            <div>
              <strong>
                <i class="fa-regular fa-clock"></i>
                <?= date("g.i A", strtotime($slot['start_time'])); ?>
                -
                <?= date("g.i A", strtotime($slot['end_time'])); ?>
              </strong>

              <p>Status: <?= htmlspecialchars($slot['slot_status']); ?></p>
            </div>
          </div>

        <?php endif; endwhile; ?>

        <?php if (!$hasUnavailable): ?>
          <p>Tiada slot penuh / tidak tersedia untuk tarikh ini.</p>
        <?php endif; ?>

        <div class="slot-note">
          <i class="fa-solid fa-circle-info"></i>
          Klik pada tarikh di kalendar untuk melihat slot yang tersedia
        </div>
      </div>

    </div>

    <div class="booking-info">
      <i class="fa-solid fa-circle-info"></i>
      <p>
        <strong>Makluman</strong><br>
        Slot yang dipaparkan adalah tertakluk kepada perubahan semasa. Sila pilih tarikh dan slot yang dikehendaki untuk membuat tempahan.
      </p>
    </div>

  </div>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>