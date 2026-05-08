<?php 
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
$package_id = 1;

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

$bookingRulesResult = mysqli_query($conn, $bookingRulesQuery);

/* Arrange schedule into columns */
$weekdaySlots = [];
$fridaySlots = [];

while ($rule = mysqli_fetch_assoc($bookingRulesResult)) {

  $start = date("g.i A", strtotime($rule['start_time']));
  $end = !empty($rule['end_time']) ? date("g.i A", strtotime($rule['end_time'])) : "";

  $time = $end ? "$start - $end" : $start;

  if (
    in_array($rule['day_of_week'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday']) 
    || $rule['day_of_week'] == 'Isnin-Khamis'
  ) {
    if (!in_array($time, $weekdaySlots)) {
      $weekdaySlots[] = $time;
    }
  }

  if ($rule['day_of_week'] == 'Friday' || $rule['day_of_week'] == 'Jumaat') {
    if (!in_array($time, $fridaySlots)) {
      $fridaySlots[] = $time;
    }
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

      <p>Berikut merupakan sesi untuk lawatan berkumpulan:</p>

      <table class="schedule-table">
        <thead>
          <tr>
            <th>Isnin - Khamis</th>
            <th>Jumaat</th>
          </tr>
        </thead>

        <tbody>
          <?php
            $maxRows = max(count($weekdaySlots), count($fridaySlots));

            for ($i = 0; $i < $maxRows; $i++):
          ?>
            <tr>
              <td><?= htmlspecialchars($weekdaySlots[$i] ?? ''); ?></td>
              <td><?= htmlspecialchars($fridaySlots[$i] ?? ''); ?></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <p class="price-note">
        Tiket Bayaran RM 2.00 bagi pengunjung 7 tahun ke atas
      </p>

      <a href="tempahan_lawatan.php?id=<?= $package['package_id']; ?>" class="booking-btn">
        Tempah Sekarang
      </a>
    </div>

  </div>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>