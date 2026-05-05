<?php
$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Produk Galeri Seramik</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="galeri.css">

</head>

<body>

<?php include '../components/navbar.php'; ?>


<section class="station-hero">

  <div class="station-title">
    <h1>Zon Untuk Dilawati</h1>
    <p>Pilih zon untuk melihat maklumat lanjut</p>
  </div>

  <div class="curved-carousel" id="stationCarousel">

    <div class="station-panel" data-station="mencorak" data-number="01">
      <img src="../assets/images/galeri/stesen1.jpg" alt="Zon 1">
    </div>

    <div class="station-panel" data-station="membentuk" data-number="02">
      <img src="../assets/images/galeri/stesen2.jpg" alt="Zon 2">
    </div>

    <div class="station-panel" data-station="membakar" data-number="03">
      <img src="../assets/images/galeri/stesen3.jpg" alt="Zon 3">
    </div>

    <div class="station-panel" data-station="mewarna" data-number="04">
      <img src="../assets/images/galeri/stesen4.jpg" alt="Zon 4">
    </div>

    <div class="station-panel" data-station="pameran" data-number="05">
      <img src="../assets/images/galeri/stesen5.jpg" alt="Zon 5">
    </div>

  </div>

</section>

<section class="station-detail-box" id="stationDetail">
  <h2>Zon 1: Sejarah Seramik dan Pasir Gudang Kraf</h2>
  <p>
    Pengunjung dapat membaca dan mendalami sejarah seramik, termasuk sejarah seramik di Malaysia, sejarah Pasir Gudang Kraf, serta proses pembuatan seramik secara umum. 
    Terdapat juga paparan tentang jenis-jenis seramik yang dihasilkan di Pasir Gudang Kraf.
  </p>
</section>

<script src="galeri.js"></script>
<?php include '../components/footer.php'; ?>
</body>
</html>