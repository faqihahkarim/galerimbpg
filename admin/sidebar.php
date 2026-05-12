<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="logo">
        <div class="logo-icon">
            <img src="../assets/images/logombpg.png" alt="Logo">
            <i class="fa-solid fa-vase"></i>
        </div>
        <div>
            <h2>GALERI<br>SERAMIK</h2>
            <span>Pasir Gudang</span>
        </div>
    </div>

    <nav class="nav">
        <a href="dashboard.php" class="<?= ($current == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-table-cells-large"></i> Dashboard
        </a>

        <div class="nav-item has-submenu <?= in_array($current, ['tempahan.php','slot.php','peraturan.php']) ? 'active' : '' ?>">
            <a href="#">
                <i class="fa-regular fa-calendar-check"></i> Pengurusan Tempahan
                <span class="arrow">▾</span>
            </a>

            <div class="submenu">
                <a href="tempahan.php">Tempahan</a>
                <a href="peraturan.php">Peraturan Tempahan</a>
            </div>
        </div>

        <div class="nav-item has-submenu <?= in_array($current, ['tempahan.php','slot.php','peraturan.php']) ? 'active' : '' ?>">
            <a href="#">
                <i class="fa-solid fa-palette"></i> Pengurusan Galeri
                <span class="arrow">▾</span>
            </a>

            <div class="submenu">
                <a href="tempahan.php">Produk</a>
                <a href="slot.php">Bahan</a>
                <a href="peraturan.php">Aktiviti</a>
                <a href="peraturan.php">Pakej</a>
            </div>
        </div>

        <!-- <a href="#"><i class="fa-regular fa-calendar-days"></i> Kalendar Slot</a>
        <a href="#"><i class="fa-solid fa-users"></i> Pelanggan</a>
        <a href="#"><i class="fa-solid fa-layer-group"></i> Produk</a>
        <a href="#"><i class="fa-solid fa-palette"></i> Aktiviti</a> -->
        <a href="#"><i class="fa-solid fa-chart-column"></i> Laporan</a>
        <!-- <a href="#"><i class="fa-solid fa-gear"></i> Tetapan</a> -->
        <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Keluar</a>
    </nav>

    <div class="sidebar-footer">
        <strong>Galeri Seramik</strong><br>
        Pasir Gudang<br><br>
        © 2026
    </div>
</aside>