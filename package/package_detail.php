<?php 
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* Package info */
$packageQuery = "
  SELECT 
    package_id,
    package_name,
    description,
    image_url
  FROM packages 
  WHERE package_id = $package_id
  AND status = 'active'
";

$packageResult = mysqli_query($conn, $packageQuery);
$package = mysqli_fetch_assoc($packageResult);

/* Booking schedule */
$bookingRulesQuery = "
  SELECT
    rule_id,
    package_id,
    day_of_week,
    start_time,
    end_time
  FROM booking_rules
  WHERE status = 'active'
  AND package_id = $package_id
  ORDER BY rule_id ASC
";

/*tarik package_activities dengan activities untuk brief desc aktiviti apa yang pakej ni ambil*/
$packageActivityQuery = mysqli_query($conn, "
  SELECT pa.activity_id, a.activity_name
  FROM package_activities pa
  JOIN activities a ON pa.activity_id = a.activity_id
  WHERE a.status = 'active'
  AND pa.package_id = $package_id
");

$bookingRulesResult = mysqli_query($conn, $bookingRulesQuery);

/* Arrange schedule into columns */
$slotsByDay = [
  'Isnin' => [],
  'Selasa' => [],
  'Rabu' => [],
  'Khamis' => [],
  'Jumaat' => [],
  'Sabtu' => [],
  'Ahad' => []
];

$dayMap = [
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
  'Ahad' => 'Ahad'
];

while ($rule = mysqli_fetch_assoc($bookingRulesResult)) {
  $day = $dayMap[$rule['day_of_week']] ?? null;

  if (!$day) {
    continue;
  }

  $start = date("g.i A", strtotime($rule['start_time']));
  $end = !empty($rule['end_time']) ? date("g.i A", strtotime($rule['end_time'])) : "";
  $time = $end ? "$start - $end" : $start;

  if (!in_array($time, $slotsByDay[$day])) {
    $slotsByDay[$day][] = $time;
  }
}

/* Only display days that have slots */
$visibleDays = [];

foreach ($slotsByDay as $day => $slots) {
  if (!empty($slots)) {
    $visibleDays[$day] = $slots;
  }
}

/*only display in the brief desc of ativity in the package detail page*/
$activityNames = [];
while ($activity = mysqli_fetch_assoc($packageActivityQuery)) {
  if (!empty($activity['activity_name'])) {
    $activityNames[] = $activity['activity_name'];
  }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($package['package_name']); ?></title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= $base ?>assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/index.css">
  <link rel="stylesheet" href="<?= $base ?>package/package.css">

  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="package-detail-section">
  <div class="package-detail-container">

    <div class="package-detail-image">
      <img 
        src="<?= $base . htmlspecialchars($package['image_url']); ?>" 
        alt="<?= htmlspecialchars($package['package_name']); ?>"
      >
    </div>

    <div class="package-detail-content">
      <h1><?= htmlspecialchars($package['package_name']); ?></h1>

      <p>Berikut merupakan sesi untuk pakej ini:</p>

      <table class="schedule-table">
        <thead>
          <tr>
            <?php foreach ($visibleDays as $day => $slots): ?>
              <th><?= htmlspecialchars($day); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>

        <tbody>
          <?php
            $maxRows = 0;

            foreach ($visibleDays as $slots) {
              $maxRows = max($maxRows, count($slots));
            }

            for ($i = 0; $i < $maxRows; $i++):
          ?>
            <tr>
              <?php foreach ($visibleDays as $day => $slots): ?>
                <td><?= htmlspecialchars($slots[$i] ?? ''); ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <br>
      <p>Aktiviti yang disertakan dalam pakej ini:</p>
      <?php if (!empty($activityNames)): ?>
        <table class="schedule-table">
          <thead>
            <tr>
              <th>Aktiviti</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($activityNames as $name): ?>
              <tr>
                <td><?= htmlspecialchars($name); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>Pakej ini tidak disertakan dengan aktiviti</p>
      <?php endif; ?>

      <a href="package_calendar.php?package_id=<?= $package['package_id']; ?>" class="booking-btn" style="margin-top: 20px;">
        Tempah Sekarang
      </a>
    </div>

  </div>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>