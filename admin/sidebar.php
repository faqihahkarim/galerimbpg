<?php
$current = basename($_SERVER['PHP_SELF']);

$base = "/web/galeriseramikmbpg/admin";

$bookingPages = [
  'tempahan.php',
  'slot.php',
  'peraturan.php',
  'closure_date.php'
];

$galleryPages = [
  'produk.php',
  'bahan.php',
  'aktiviti.php',
  'pakej.php'
];
?>

<aside class="sidebar">

  <div class="logo">
    <div class="logo-icon">
      <img src="/web/galeriseramikmbpg/assets/images/logombpg.png" alt="Logo">
      <i class="fa-solid fa-vase"></i>
    </div>

    <div>
      <h2>GALERI<br>SERAMIK</h2>
      <span>Pasir Gudang</span>
    </div>
  </div>

  <nav class="nav">

    <a 
      href="<?= $base ?>/dashboard.php"
      class="<?= ($current == 'dashboard.php') ? 'active' : '' ?>"
    >
      <i class="fa-solid fa-table-cells-large"></i>
      Dashboard
    </a>

    <!-- =========================
         BOOKING MANAGEMENT
    ========================== -->

    <div class="nav-item has-submenu <?= in_array($current, $bookingPages) ? 'active' : '' ?>">

      <a href="#" class="submenu-toggle">
        <i class="fa-regular fa-calendar-check"></i>

        <span>Pengurusan Tempahan</span>

        <span class="arrow">▾</span>
      </a>

      <div class="submenu">

        <a 
          href="<?= $base ?>/tempahan/tempahan.php"
          class="<?= ($current == 'tempahan.php') ? 'active' : '' ?>"
        >
          Tempahan
        </a>

        <a 
          href="<?= $base ?>/tempahan/peraturan.php"
          class="<?= ($current == 'peraturan.php') ? 'active' : '' ?>"
        >
          Peraturan Tempahan
        </a>

        <a 
          href="<?= $base ?>/tempahan/slot.php"
          class="<?= ($current == 'slot.php') ? 'active' : '' ?>"
        >
          Slot Tempahan
        </a>

        <a 
          href="<?= $base ?>/tempahan/closure_date.php"
          class="<?= ($current == 'closure_date.php') ? 'active' : '' ?>"
        >
          Tarikh Tutup
        </a>

      </div>
    </div>

    <!-- =========================
         GALLERY MANAGEMENT
    ========================== -->

    <div class="nav-item has-submenu <?= in_array($current, $galleryPages) ? 'active' : '' ?>">

      <a href="#" class="submenu-toggle">
        <i class="fa-solid fa-palette"></i>

        <span>Pengurusan Galeri</span>

        <span class="arrow">▾</span>
      </a>

      <div class="submenu">

        <a 
          href="<?= $base ?>/inventori/produk/produk.php"
          class="<?= ($current == 'produk.php') ? 'active' : '' ?>"
        >
          Produk
        </a>

        <a 
          href="<?= $base ?>/inventori/bahan/bahan.php"
          class="<?= ($current == 'bahan.php') ? 'active' : '' ?>"
        >
          Bahan
        </a>

        <a 
          href="<?= $base ?>/inventori/aktiviti/aktiviti.php"
          class="<?= ($current == 'aktiviti.php') ? 'active' : '' ?>"
        >
          Aktiviti
        </a>

        <a 
          href="<?= $base ?>/inventori/pakej/pakej.php"
          class="<?= ($current == 'pakej.php') ? 'active' : '' ?>"
        >
          Pakej
        </a>

      </div>
    </div>

    <!-- =========================
         REPORT
    ========================== -->

    <a 
      href="<?= $base ?>/laporan.php"
      class="<?= ($current == 'laporan.php') ? 'active' : '' ?>"
    >
      <i class="fa-solid fa-chart-column"></i>
      Laporan
    </a>

    <!-- =========================
         LOGOUT
    ========================== -->

    <a href="<?= $base ?>../../login.php">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      Log Keluar
    </a>

  </nav>

  <div class="sidebar-footer">
    <strong>Galeri Seramik</strong><br>
    Pasir Gudang<br><br>
    © 2026
  </div>

</aside>