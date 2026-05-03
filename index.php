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

<section class="intro-section">
  <div class="intro-container">

    <!-- Image -->
    <div class="intro-image">
      <img src="assets/images/galeri.jpg" alt="Galeri Seramik">
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

  <div class="pakej">
    <h2>Pakej Kami</h2>
    <ul>
      <li><strong>Pakej Asas:</strong> Bengkel seramik selama 2 jam dengan bahan disediakan.</li>
      <li><strong>Pakej Keluarga:</strong> Aktiviti seramik untuk keluarga dengan harga istimewa.</li>
      <li><strong>Pakej Korporat:</strong> Sesi team building dengan aktiviti seramik yang menyeronokkan.</li>
    </ul>
    </div>

    <div class="aktiviti">
      <h2>Aktiviti Kami</h2>
      <ul>
        <li><strong>Bengkel Seramik:</strong> Pelajari teknik membuat seramik dengan pakar kami.</li>
        <li><strong>Pameran Seni:</strong> Saksikan karya
    </div>

    <div class= "footer">
        <p>&copy; 2026 Galeri Seramik Pasir Gudang. Hak cipta terpelihara.</p>
    </div>

     <!-- SCRIPT-->
     <script src="assets/js/index.js"></script>
     
</body>
</html>