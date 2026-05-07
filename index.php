<?php

include 'db.php';
$activityQuery = "SELECT
                   a.activity_id,
                   a.activity_name,
                   a.description,
                   ai.image_url
                  FROM activities a
                  LEFT JOIN activity_images ai
                  ON a.activity_id = ai.activity_id
                  AND ai.is_main = 1
                  WHERE a.status = 'active'
                  ORDER BY a.activity_id ASC";

$activityResult = mysqli_query($conn, $activityQuery);

$base = "/web/galeriseramikmbpg/";
$pageType = "home";
include 'components/navbar.php'; 
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
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/index.css">

  <!-- Favicon -->
  <link rel="icon" href="assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>

  <!-- NEWS BANNER-->
  <section class="hero">
  <div class="hero-slider">

    <!-- Slide 1 -->
    <div class="slide active" style="background-image: url('assets/images/banner1.jpg');">
      <div class="overlay"></div>
      <div class="content">
        <h1>Koleksi Seramik Terbaru:</h1>
        <p>Dapatkan koleksi seramik terbaru dengan segera, lihat katalog di inventori</p>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="slide" style="background-image: url('assets/images/banner2.jpg');">
      <div class="overlay"></div>
      <div class="content">
        <h1>Bengkel Seramik Mingguan</h1>
        <p>Sertai aktiviti menarik bersama keluarga dan pelajar</p>
      </div>
    </div>

    <button class="prev">&#10094;</button>
    <button class="next">&#10095;</button>

    <div class="dots"></div>

  </div>

</section>


<!-- INTRO -->

<section class="intro-section">
  <div class="intro-container">

    <!-- Image -->
    <div class="intro-image">
      <img src="assets/images/galeri.webp" alt="Galeri Seramik">
    </div>

    <!-- Text -->
    <div class="intro-text">
      <h2>Selamat Datang Ke<br>Galeri Seramik Pasir Gudang</h2>

      <p>
        Temui warisan yang kaya mengenai seni seramik di galeri kami.
        Kami menempatkan koleksi yang luas merangkumi tembikar tradisional,
        jubin, dan kepingan hiasan yang merentas berabad-abad kecemerlangan artistik.
      </p>

      <p>
        Galeri kami memaparkan rekaan yang rumit, warna-warna yang terang,
        dan ketukangan mahir yang mencerminkan pencapaian budaya dan artistik.
        Daripada pinggan yang berhias kepada jubin yang elegan.
      </p>
    </div>

  </div>
</section>

<!-- ACTIVITY -->

<section class="activity-section" id="activity-section">
  <div class="activity-container">

    <div class="section-title">
      <h2>Aktiviti Kami</h2>
      <p>Terokai pelbagai aktiviti menarik yang kami<br> sediakan</p>
    </div>

    <div class="activity-grid">

      <?php while ($activity = mysqli_fetch_assoc($activityResult)): ?>

        <?php
          $image = !empty($activity['image_url'])
            ? $activity['image_url']
            : "assets/images/default-activity.jpg";
        ?>

        <a href="activity/activity_detail.php?id=<?= $activity['activity_id']; ?>" class="activity-card">

          <img 
            src="<?= htmlspecialchars($image); ?>" 
            alt="<?= htmlspecialchars($activity['activity_name']); ?>"
          >

          <h3>
            <?= htmlspecialchars($activity['activity_name']); ?>
          </h3>

          <p>
            <?= htmlspecialchars($activity['description']); ?>
          </p>

        </a>

      <?php endwhile; ?>

    </div>

  </div>
</section>

<!-- PACKAGE -->

<section class="package-section" id="package-section">
  <div class="package-container">

    <div class="package-title">
      <h2>Pakej Kami</h2>
      <p>
        Terokai pakej kami yang kami sediakan untuk<br>
        pengalaman anda di Galeri Seramik Pasir Gudang
      </p>
    </div>

    <div class="package-grid">

      <div class="package-card">
        <img src="assets/images/lawatan.jpg" alt="Sesi Lawatan Berkumpulan">

        <div class="package-content">
          <h3>Sesi Lawatan Berkumpulan</h3>
          <p>
            Pengalaman lawatan berkumpulan yang komprehensif untuk meneroka
            koleksi seramik Islam kami dengan panduan pakar serendah RM2.00
          </p>

          <a href="package/lawatan.php" class="package-btn">Baca Lebih Lanjut</a>
        </div>
      </div>

      <div class="package-card">
        <img src="assets/images/pendidikan.jpg" alt="Pakej Pendidikan">

        <div class="package-content">
          <h3>Pakej Pendidikan</h3>
          <p>
            Rasai pengalaman hands-on pembuatan seramik dalam bentuk pembelajaran
            interaktif berdasarkan aktiviti yang dipilih serendah RM10.00
          </p>

          <a href="package/pendidikan.php" class="package-btn">Baca Lebih Lanjut</a>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- FOOTER -->

<footer class="footer-section">
  <div class="footer-container">

    <div class="footer-title">
      <h2>Mari Beramai-Ramai ke Galeri Seramik Pasir Gudang</h2>
      <p>Hubungi kami untuk sebarang pertanyaan<br>berkenaan Galeri Seramik Pasir Gudang</p>
    </div>

    <div class="footer-content">

      <div class="footer-vase">
        <img src="assets/images/vase.png" alt="Seramik Vase">
      </div>

      <div class="footer-info">
        <div class="footer-column">
          <h3>Lokasi</h3>
          <p>
            Galeri seramik MBPG,<br>
            Lot 97769, Plot 6,<br>
            Taman Bandar, 81700<br>
            Pasir Gudang, Johor
          </p>
        </div>

        <div class="footer-column">
          <h3>Waktu Operasi</h3>
          <p>
            Sabtu - Khamis<br>
            9.00 pagi - 1.00 petang<br>
            2.00 petang - 5.00 petang
          </p>

          <p>
            Jumaat<br>
            9.00 pagi - 12.15 tengahari<br>
            2.45 petang - 5.00 petang
          </p>
        </div>

        <div class="footer-column">
          <h3>Hubungi Kami</h3>
          <p>
            Telefon: 013-2988693 / 019-2028241<br>
            Emel: galeriseramik.mbpg@gmail.com
          </p>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© <?php echo date("Y"); ?> Galeri Seramik Pasir Gudang. All Rights Reserved</p>

      <div class="footer-social">
        <a href="#"><i class="fa-brands fa-facebook"></i> Majlis Bandaraya Pasir Gudang</a>
        <a href="https://www.instagram.com/pasirgudangkraf/"><i class="fa-brands fa-instagram"></i> pasirgudangkraf</a>
      </div>
    </div>

  </div>
</footer>

     <!-- SCRIPT-->
     <script src="assets/js/index.js"></script>
     
</body>
</html>