  <nav class="navbar">
    <div class="container">

      <div class="logo">
        <img src="<?= $base ?>assets/images/logombpg.png" alt="Logo MBPG">
        <img src="<?= $base ?>assets/images/logogaleri.png" alt="Logo Galeri">
        <span>Galeri Seramik Pasir Gudang</span>
      </div>

      <input type="checkbox" id="menu-toggle">

      <label for="menu-toggle" class="menu-icon">
        <i class="fa-solid fa-bars"></i>
      </label>

      <div class="menu-wrapper">
        <ul class="nav-links">
          <li><a href="<?= $base ?>index.php">Utama</a></li>
          <li><a href="<?= $base ?>#activity-section">Aktiviti</a></li>
          <li><a href="<?= $base ?>#package-section">Pakej</a></li>
          <li><a href="<?= $base ?>product/produk.php">Produk</a></li>
          <li><a href="<?= $base ?>gallery/galeri.php">Galeri</a></li>
        </ul>

        <div class="nav-action">
          <?php if(isset($pageType) && $pageType === "inner"): ?>

            <?php
              $back = $_SERVER['HTTP_REFERER'] ?? $base . "index.php";
            ?>
            <a href="<?= $back ?>" class="btn-primary">Kembali</a>

          <?php else: ?>

            <a href="login.php" class="btn-primary">Admin</a>

          <?php endif; ?>
        </div>
      </div>

    </div>
  </nav>