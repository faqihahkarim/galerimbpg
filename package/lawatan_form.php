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
  <link rel="stylesheet" href="package.css">
  <link rel="stylesheet" href="form.css">

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>

<section class="form-section">
  <div class="form-container">

    <div class="form-title">
      <h1>Borang Tempahan Lawatan Berkumpulan</h1>
      <p>Sila lengkapkan maklumat tempahan dan peserta</p>
    </div>

    <form action="#" method="POST">

      <!-- 1 -->
      <div class="form-box">
        <h3>1. Maklumat Organisasi</h3>

        <div class="form-grid">
          <div class="form-group">
            <label>Nama Sekolah / Agensi</label>
            <input type="text" name="nama_organisasi">
          </div>

          <div class="form-group">
            <label>Nama Pegawai / Guru Pengiring</label>
            <input type="text" name="nama_pegawai">
          </div>

          <div class="form-group">
            <label>No. Telefon Pegawai / Guru</label>
            <input type="text" name="telefon">
          </div>

          <div class="form-group">
            <label>Emel Pegawai / Guru</label>
            <input type="email" name="emel">
          </div>
        </div>
      </div>

      <div class="form-row">

        <!-- 2 -->
        <div class="form-box small-box">
          <h3>2. Maklumat Peserta</h3>

          <div class="form-group">
            <label>Jumlah Peserta</label>
            <div class="input-with-text">
              <input type="number" value="20">
              <span>orang</span>
            </div>
            <small class="success-text">
              <i class="fa-regular fa-circle-check"></i>
              Bilangan peserta tidak melebihi kuota
            </small>
          </div>
        </div>

       <!-- 3 -->
        <div class="form-box small-box">
        <h3>3. Tarikh & Slot Pilihan</h3>

        <div class="form-grid two">
            <div class="form-group">
            <label>Tarikh</label>
            <input type="date" name="tarikh" id="tarikh" min="2026-05-07">
            </div>

            <div class="form-group">
            <label>Slot Pilihan</label>
            <select name="slot" id="slot">
                <option value="">-- Pilih Slot --</option>
                <option value="9.00 Pagi - 12.00 Tengahari">
                9.00 Pagi - 12.00 Tengahari
                </option>
                <option value="2.00 Petang - 5.00 Petang">
                2.00 Petang - 5.00 Petang
                </option>
            </select>
            </div>
        </div>
        </div>
      </div>

      
      <!-- 4 -->
      <div class="form-box">
        <h3>4. Maklumat Tambahan</h3>

        <textarea maxlength="250" placeholder="Contoh: Pelajar berkeperluan khas, permintaan khas, dan lain-lain"></textarea>
        <small>0/250 letters</small>
      </div>

      <div class="submit-bar">
        <div>
          <i class="fa-solid fa-circle-info"></i>
          <p>
            <strong>Makluman</strong><br>
            Permohonan ini akan disemak oleh pihak admin sebelum pengesahan dibuat.
            Status tempahan akan dimaklumkan melalui WhatsApp / emel yang diberikan.
          </p>
        </div>

        <button type="submit">Hantar Tempahan</button>
      </div>

    </form>

  </div>
</section>

<?php include '../components/footer.php'; ?>

</body>
</html>