<?php
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";

$activity_id = $_GET['id'] ?? 0;

$activityQuery = "
  SELECT
    a.activity_id,
    a.activity_name,
    a.description,
    a.default_capacity,
    a.duration,
    a.target,
    a.price,
    ai.image_url
  FROM activities a
  LEFT JOIN activity_images ai
    ON a.activity_id = ai.activity_id
    AND ai.is_main = 1
  WHERE a.activity_id = $activity_id
  AND a.status = 'active'
";

$activityResult = mysqli_query($conn, $activityQuery);
$activity = mysqli_fetch_assoc($activityResult);

$galleryQuery = "
  SELECT image_url
  FROM activity_images
  WHERE activity_id = $activity_id
  ORDER BY sort_order ASC
";

$galleryResult = mysqli_query($conn, $galleryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($activity['activity_name']); ?></title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= $base ?>assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/index.css">
  <link rel="stylesheet" href="<?= $base ?>activity/activity.css">

  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="activity-detail-page">

  <div class="detail-banner" style="background-image: url('<?= $base . htmlspecialchars($activity['image_url']); ?>');">
    <div class="detail-overlay">
      <div class="detail-content">

        <h1><?= htmlspecialchars($activity['activity_name']); ?></h1>

        <p><?= htmlspecialchars($activity['description']); ?></p>

        <div class="detail-info">
          <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($activity['duration']); ?></span>
          <span><i class="fa-solid fa-bullseye"></i> <?= htmlspecialchars($activity['target']); ?></span>
          <span><i class="fa-solid fa-people-group"></i> <?= htmlspecialchars($activity['default_capacity']); ?> Orang</span>
          <span><i class="fa-solid fa-dollar-sign"></i> RM <?= number_format($activity['price'], 2); ?></span>
        </div>

      </div>
    </div>
  </div>

  <div class="activity-gallery">
    <h2>Galeri Aktiviti</h2>

    <div class="gallery-grid">
      <?php while ($image = mysqli_fetch_assoc($galleryResult)): ?>
        <img 
          src="<?= $base . htmlspecialchars($image['image_url']); ?>" 
          alt="<?= htmlspecialchars($activity['activity_name']); ?>"
        >
        
      <?php endwhile; ?>
    </div>
  </div>

</section>

<?php include '../components/footer.php'; ?>

</body>
</html>