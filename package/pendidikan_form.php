
<?php $base = "/web/galeriseramikmbpg/"; ?>
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

  <!-- include for components-->
  <?php include 'navbar.php'; ?>
<section class="form-section">
  <div class="form-container">

    <div class="form-title">
      <h1>Borang Tempahan Lawatan Berkumpulan</h1>
      <p>Sila lengkapkan maklumat tempahan dan agihan peserta</p>
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
        <h3>4. Agihan Peserta Mengikut Aktiviti</h3>
        <p class="box-desc">
          Tetapkan bilangan peserta bagi setiap aktiviti. Jumlah agihan mestilah sama dengan bilangan peserta.
        </p>

        <div class="activity-list">

          <div class="activity-item">
            <img src="../assets/images/act1.jpg" alt="Interaktif Mewarna">

            <div class="activity-info">
              <h4>Interaktif Mewarna</h4>
              <p>Mewarna produk seramik yang sudah siap dibakar</p>
            </div>

            <div class="counter">
              <button type="button">−</button>
              <span>5<br><small>orang</small></span>
              <button type="button">+</button>
            </div>

            <div class="max-info">
              <i class="fa-solid fa-people-group"></i>
              <span>Maksimum: 10 Orang</span>
            </div>
          </div>

          <div class="activity-item">
            <img src="../assets/images/act2.jpg" alt="Melukis dan Mewarna">

            <div class="activity-info">
              <h4>Melukis dan Mewarna</h4>
              <p>Teknik asas melukis motif dan pewarnaan asas</p>
            </div>

            <div class="counter">
              <button type="button">−</button>
              <span>5<br><small>orang</small></span>
              <button type="button">+</button>
            </div>

            <div class="max-info">
              <i class="fa-solid fa-people-group"></i>
              <span>Maksimum: 10 Orang</span>
            </div>
          </div>

          <div class="activity-item">
            <img src="../assets/images/act3.jpg" alt="Pembentukan Tanah Liat">

            <div class="activity-info">
              <h4>Pembentukan Tanah Liat</h4>
              <p>Teknik coil, pinch dan slab</p>
            </div>

            <div class="counter">
              <button type="button">−</button>
              <span>5<br><small>orang</small></span>
              <button type="button">+</button>
            </div>

            <div class="max-info">
              <i class="fa-solid fa-people-group"></i>
              <span>Maksimum: 10 Orang</span>
            </div>
          </div>

          <div class="activity-item">
            <img src="../assets/images/act4.jpg" alt="Teknik Lempar Alin">

            <div class="activity-info">
              <h4>Teknik Lempar Alin</h4>
              <p>Teknik membentuk menggunakan mesin roda tembikar</p>
            </div>

            <div class="counter">
              <button type="button">−</button>
              <span>5<br><small>orang</small></span>
              <button type="button">+</button>
            </div>

            <div class="max-info">
              <i class="fa-solid fa-people-group"></i>
              <span>Maksimum: 10 Orang</span>
            </div>
          </div>

        </div>

        <div class="form-actions-small">
          <button type="button" class="outline-btn">Agih Sama Rata</button>
          <button type="button" class="reset-btn">Set Semula Agihan</button>
        </div>
      </div>

      
      <!-- 5 -->
      <div class="form-box">
        <h3>5. Maklumat Tambahan</h3>

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