<?php 
$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
include '../components/navbar.php'; 
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
  <link rel="stylesheet" href="activity.css">

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>


  <section class="activity-detail-page">

  <div class="detail-banner" style="background-image: url('../assets/images/act3.jpg');">
    <div class="detail-overlay">
      <div class="detail-content">
        <h1>Pembentukan Tanah Liat</h1>

        <p>
          Mempelajari tentang teknik asas pembentukan tanah liat serta pendedahan asas struktur tanah liat menggunakan teknik coil/ pinch/ slab
        </p>

        <div class="detail-info">
          <span><i class="fa-regular fa-clock"></i> 2–3 Jam</span>
          <span><i class="fa-solid fa-bullseye"></i> Sekolah Rendah & Menengah</span>
          <span><i class="fa-solid fa-people-group"></i> 10 Orang</span>
          <span><i class="fa-solid fa-dollar-sign"></i> RM 20.00</span>
        </div>
      </div>
    </div>
  </div>

  <div class="activity-gallery">
    <h2>Galeri Aktiviti</h2>

    <div class="gallery-grid">
      <img src="../assets/images/act1.jpg" alt="Aktiviti Mewarna">
      <img src="../assets/images/act2.jpg" alt="Aktiviti Mewarna">
      <img src="../assets/images/act3.jpg" alt="Aktiviti Mewarna">
    </div>
  </div>

</section>

</body>
</html>