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

  <!-- include for components-->
  <?php include 'components/navbar.php'; ?>

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

    <!-- Arrows -->
    <button class="prev">&#10094;</button>
    <button class="next">&#10095;</button>

    <!-- Dots -->
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

<section class="activity-section">
  <div class="activity-container">

    <div class="section-title">
      <h2>Aktiviti Kami</h2>
      <p>Terokai pelbagai aktiviti menarik yang kami<br> sediakan</p>
    </div>

    <div class="activity-grid">

      <div class="activity-card">
        <img src="assets/images/act1.jpg" alt="Interaktif Mewarna">
        <h3>Interaktif<br>Mewarna</h3>
        <p>Mewarna produk seramik yang sudah siap dibakar</p>
      </div>

      <div class="activity-card">
        <img src="assets/images/act2.jpg" alt="Melukis dan Mewarna">
        <h3>Melukis &<br>Mewarna</h3>
        <p>Teknik asas melukis motif. Pewarnaan asas seramik</p>
      </div>

      <div class="activity-card">
        <img src="assets/images/act3.jpg" alt="Pembentukan Tanah Liat">
        <h3>Pembentukan Tanah<br>Liat</h3>
        <p>Teknik coil/ pinch/ slab/ Pendedahan asas struktur tanah liat</p>
      </div>

      <div class="activity-card">
        <img src="assets/images/act4.jpg" alt="Teknik Lempar Alin">
        <h3>Teknik Lempar<br>Alin</h3>
        <p>Teknik membentuk menggunakan mesin roda tembikar</p>
      </div>

    </div>

  </div>
</section>

<!-- PACKAGE -->

<section class="package-section">
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

          <a href="#" class="package-btn">Baca Lebih Lanjut</a>
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

          <a href="#" class="package-btn">Baca Lebih Lanjut</a>
        </div>
      </div>

    </div>

  </div>
</section>

    <div class= "footer">
        <p>&copy; 2026 Galeri Seramik Pasir Gudang. Hak cipta terpelihara.</p>
    </div>

     <!-- SCRIPT-->
     <script src="assets/js/index.js"></script>
     
</body>
</html>