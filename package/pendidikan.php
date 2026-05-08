<?php 

include '../db.php';
$base = "/web/galeriseramikmbpg/";
$pageType = "inner";

$packageQuery= "SELECT
                package_id,
                package_name,
                description,
                image_url
              FROM packages
              WHERE status = 'active'
              ORDER BY package_id ASC";

$packageResult = mysqli_query($conn, $packageQuery);

$bookingRulesQuery = "SELECT
                rule_id,
                package_id,
                day_of_week,
                start_time,
                end_time
              FROM booking_rules
              WHERE status = 'active'
              AND package_id = 2
              ORDER BY rule_id ASC";

$bookingRulesResult = mysqli_query($conn, $bookingRulesQuery);

$activityQuery = "SELECT
                activity_id,
                activity_name,
                description,
                price
              FROM activities
              WHERE status = 'active'
              ORDER BY activity_id ASC";
$activityResult = mysqli_query($conn, $activityQuery);



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Galeri Seramik Pasir Gudang</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <!-- CSS -->
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/index.css">
  <link rel="stylesheet" href="package.css">

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>

<?php include '../components/navbar.php'; ?>

    <section class="package-detail-section">
    <div class="package-detail-container">

        <div class="package-detail-image">
        <img src="../assets/images/pendidikan.jpg" alt="Pendidikan">
        </div>

        <div class="package-detail-content">
        <h1>Pakej Pendidikan</h1>

        <p>Terdiri daripada 4 aktiviti yang boleh dipilih untuk sesi selama 3 jam:</p>

        <?php

            $saturday = [];
            $sunday = [];

            while ($rule = mysqli_fetch_assoc($bookingRulesResult)) {

                $time =
                    date("g.i A", strtotime($rule['start_time'])) .
                    " - " .
                    date("g.i A", strtotime($rule['end_time']));

                if ($rule['day_of_week'] == 'Saturday') {
                    $saturday[] = $time;
                }

                if ($rule['day_of_week'] == 'Sunday') {
                    $sunday[] = $time;
                }
            }
            ?>

        <table class="schedule-table">

    <thead>
        <tr>
            <th>Saturday</th>
            <th>Sunday</th>
        </tr>
    </thead>

            <tbody>

                <?php
                $maxRows = max(count($saturday), count($sunday));

                for ($i = 0; $i < $maxRows; $i++):
                ?>

                <tr>

                    <td>
                        <?= $saturday[$i] ?? '-'; ?>
                    </td>

                    <td>
                        <?= $sunday[$i] ?? '-'; ?>
                    </td>

                </tr>

                <?php endfor; ?>

            </tbody>

        </table>

        <p>Bayaran bagi setiap aktiviti:</p>

        <table class="price-table">
            <colgroup>
                <col style="width: 50%">
                <col style="width: 50%">
            </colgroup>

            <thead>
                <tr>
                <th>Aktiviti</th>
                <th>Harga</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($activity = mysqli_fetch_assoc($activityResult)): ?>
                <tr>
                    <td><?= htmlspecialchars($activity['activity_name']); ?></td>
                    <td>RM <?= number_format($activity['price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>



        <a href="tempahan_pendidikan.php" class="booking-btn">Tempah Sekarang</a>
        </div>

        </div>
    </section>
<?php include '../components/footer.php'; ?>


</body>
</html>