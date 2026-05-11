<?php
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tempahan Dihantar</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>package/form.css">
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="success-section">
  <div class="success-box">
    <div class="success-icon">✓</div>

    <h1>Tempahan Berjaya Dihantar</h1>

    <p>
      Permohonan tempahan anda telah diterima dan sedang menunggu semakan pihak admin.
    </p>

    <p>
      Status tempahan akan dimaklumkan melalui WhatsApp atau emel yang telah diberikan.
    </p>

    <?php if ($booking_id > 0): ?>
      <p class="booking-ref">
        No. Rujukan Tempahan: <strong>#<?= $booking_id; ?></strong>
      </p>
    <?php endif; ?>

    <a href="<?= $base ?>index.php" class="success-btn">Kembali ke Laman Utama</a>
  </div>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>