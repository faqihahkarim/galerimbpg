<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/tempahan.css">
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main">

    <header class="topbar">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div>
            <h1>Pengurusan Tempahan</h1>
            <p>Tempahan</p>
        </div>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Tempahan</h3>
            <strong>128</strong>
            <span class="trend">↑ 18 dari bulan lepas</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Semasa</h3>
            <strong>9</strong>
            <span class="trend">↑ 3 dari bulan lepas</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Lulus</h3>
            <strong>2</strong>
            <span class="trend">↑ 1 dari bulan lepas</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Produk</h3>
            <strong>52</strong>
            <span class="trend">↑ 5 produk baru</span>
          </div>
        </div>
    </section>

    <section class="booking-panel">
        <div class="booking-toolbar">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search by name, type, status">
        </div>

        <select>
            <option>All Types</option>
            <option>Pendidikan</option>
            <option>Lawatan</option>
        </select>

        <select>
            <option>All Status</option>
            <option>Belum Lulus</option>
            <option>Lulus</option>
            <option>Batal</option>
        </select>

        <button class="reset-btn">Reset</button>
        <button class="export-btn">
            Export <i class="fa-solid fa-download"></i>
        </button>
        </div>

        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID Tempahan</th>
                <th>Nama Organisasi</th>
                <th>Jenis Tempahan</th>
                <th>Tarikh & Slot Masa</th>
                <th>Pax</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>
                    <a href="#" class="booking-detail-link" data-modal="bookingModalBK2601">BK2601</a>
                </td>
                <td>SK Taman Rinting</td>
                <td>Pendidikan</td>
                <td>11 May 2025 (2.00PM - 5.00PM)</td>
                <td>30</td>
                <td><span class="status pending">Belum Lulus</span></td>
            </tr>

            <tr>
                <td><a href="#">BK2602</a></td>
                <td>PIBG SMK Pasir Gudang</td>
                <td>Pendidikan</td>
                <td>11 May 2025 (09.00AM-12.00PM)</td>
                <td>10</td>
                <td><span class="status approved">Lulus</span></td>
            </tr>

            <tr>
                <td><a href="#">BK2603</a></td>
                <td>Universiti Teknologi Malaysia</td>
                <td>Pendidikan</td>
                <td>21 May 2025 (09.00AM-12.00PM)</td>
                <td>8</td>
                <td><span class="status cancelled">Batal</span></td>
            </tr>

            <tr>
                <td><a href="#">BK2601</a></td>
                <td>SK Taman Rinting</td>
                <td>Lawatan</td>
                <td>10 May 2025 (10.00AM-11.00AM)</td>
                <td>30</td>
                <td>Lulus</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div class="table-footer">
        <p>Showing 1 to 12 out of 128 entries</p>

        <div class="pagination">
            <button><i class="fa-solid fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <span>...</span>
            <button>19</button>
            <button><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        </div>
    </section>

    <div class="booking-modal" id="bookingModalBK2601">
    <div class="booking-modal-card">

        <div class="booking-modal-header">
        <h2>Info Tempahan</h2>
        <button type="button" class="booking-modal-close">&times;</button>
        </div>

        <div class="booking-modal-id">
        <h3>BK2601</h3>
        <span>Pending</span>
        </div>

        <div class="booking-info-list">

        <div class="booking-info-item">
            <i class="fa-regular fa-user"></i>
            <div>
            <p>Nama Sekolah/ Organisasi</p>
            <small>SK Taman Rinting</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-solid fa-phone"></i>
            <div>
            <p>Nombor Telefon</p>
            <small>012-3156288</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-regular fa-envelope"></i>
            <div>
            <p>Emel</p>
            <small>amira@gmail.com</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-regular fa-calendar-days"></i>
            <div>
            <p>Tarikh & Slot Masa</p>
            <small>11 May 2025 (2.00PM - 5.00PM)</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-solid fa-palette"></i>
            <div>
            <p>Jenis Pakej</p>
            <small>Pendidikan</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-regular fa-gem"></i>
            <div>
            <p>Pilihan Aktiviti</p>
            <small>IM (3) + MM (4) + PT (4) + LA (3)</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-solid fa-users"></i>
            <div>
            <p>Bilangan Peserta</p>
            <small>30</small>
            </div>
        </div>

        <div class="booking-info-item">
            <i class="fa-regular fa-clipboard"></i>
            <div>
            <p>Catatan</p>
            <small>Tiada</small>
            </div>
        </div>

        </div>

        <div class="booking-action-title">Tindakan</div>

        <div class="booking-modal-actions">
        <button type="button" class="approve-booking-btn">Terima</button>
        <button type="button" class="reject-booking-btn">Batal</button>
        </div>

    </div>
    </div>

    </main>

</div>

<script src="js/sidebar.js"></script>

</body>