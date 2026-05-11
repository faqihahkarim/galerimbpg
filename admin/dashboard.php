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
  <link rel="stylesheet" href="css/dashboard.css">
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
          <h1>Dashboard</h1>
            <p>Selamat Datang, Admin</p>
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

      <section class="content-grid">
        <div class="panel">
          <div class="panel-header">
            <h2>Kalendar Slot</h2>
            <div>
              <select>
                <option>Semua Pakej</option>
                <option>Pakej Pendidikan</option>
                <option>Lawatan Berkumpulan</option>
              </select>
              <button>Hari Ini</button>
            </div>
          </div>

          <div class="panel-header">
            <button><i class="fa-solid fa-chevron-left"></i></button>
            <strong>Mei 2025</strong>
            <button><i class="fa-solid fa-chevron-right"></i></button>
          </div>

          <div class="calendar-head">
            <span>Isn</span><span>Sel</span><span>Rab</span><span>Kha</span><span>Jum</span><span>Sab</span><span>Aha</span>
          </div>

          <div class="calendar-grid">
            <div class="day muted">28</div>
            <div class="day muted">29</div>
            <div class="day muted">30</div>
            <div class="day">1</div>
            <div class="day">2</div>
            <div class="day">3<div class="dot blue-dot"></div></div>
            <div class="day">4<div class="dot blue-dot"></div></div>

            <div class="day">5</div>
            <div class="day">6</div>
            <div class="day">7</div>
            <div class="day">8</div>
            <div class="day">9</div>
            <div class="day">10<div class="dot green-dot"></div></div>
            <div class="day">11<div class="dot orange-dot"></div></div>

            <div class="day">12</div>
            <div class="day">13</div>
            <div class="day">14</div>
            <div class="day">15</div>
            <div class="day">16</div>
            <div class="day">17<div class="dot orange-dot"></div></div>
            <div class="day">18<div class="dot red-dot"></div></div>

            <div class="day">19</div>
            <div class="day">20</div>
            <div class="day"><span class="today">21</span></div>
            <div class="day">22</div>
            <div class="day">23</div>
            <div class="day">24</div>
            <div class="day">25</div>

            <div class="day">26</div>
            <div class="day">27</div>
            <div class="day">28</div>
            <div class="day">29</div>
            <div class="day">30</div>
            <div class="day">31</div>
            <div class="day muted">1</div>
          </div>

          <div class="legend">
            <span><b class="dot green-dot"></b> Tersedia</span>
            <span><b class="dot blue-dot"></b> Ditempah</span>
            <span><b class="dot orange-dot"></b> Tertangguh</span>
            <span><b class="dot red-dot"></b> Ditutup</span>
          </div>
        </div>

       <div class="panel">
        <div class="panel-header">
            <h2>Aktiviti Terkini</h2>
            <a class="link" href="#">Lihat Semua</a>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-calendar-plus"></i></div>
            <div>
            <p>Tempahan baru oleh SK Taman Rinting</p>
            <small>10 May 2025, 10:00 AM</small>
            </div>
            <span class="badge">Baru</span>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-circle-check"></i></div>
            <div>
            <p>Permohonan diluluskan oleh PIBG SMK Pasir Gudang</p>
            <small>11 May 2025, 09:15 AM</small>
            </div>
            <span class="badge">Lulus</span>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-regular fa-clock"></i></div>
            <div>
            <p>Tempahan menunggu kelulusan</p>
            <small>12 May 2025, 02:30 PM</small>
            </div>
            <span class="badge">Pending</span>
        </div>

        <div class="activity-item">
            <div class="activity-icon"><i class="fa-solid fa-ban"></i></div>
            <div>
            <p>Slot ditutup (Cuti Umum)</p>
            <small>17 May 2025</small>
            </div>
            <span class="badge">Ditutup</span>
        </div>
        </div>
      </section>

      <section class="bottom-grid">
        <div class="panel">
          <div class="panel-header">
            <h2>Stok Rendah</h2>
            <a class="link" href="#">Lihat Semua</a>
          </div>

          <div class="stock-item">
            <div class="stock-img"></div>
            <div>
              <p>Pasu 3 Layer</p>
              <small>Stok: 3</small>
            </div>
          </div>

          <div class="stock-item">
            <div class="stock-img"></div>
            <div>
              <p>Cawan Seramik</p>
              <small>Stok: 4</small>
            </div>
          </div>

          <div class="stock-item">
            <div class="stock-img"></div>
            <div>
              <p>Mangkuk Seramik</p>
              <small>Stok: 5</small>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Akses Pantas</h2>
          </div>

          <div class="quick-grid">
            <div class="quick-btn"><i class="fa-regular fa-calendar-plus"></i> Buat Slot Baru</div>
            <div class="quick-btn"><i class="fa-solid fa-vase"></i> Tambah Produk</div>
            <div class="quick-btn"><i class="fa-solid fa-box-open"></i> Tambah Stok Bahan</div>
            <div class="quick-btn"><i class="fa-solid fa-users"></i> Tambah Aktiviti</div>
          </div>
        </div>
      </section>

    </main>
  </div>

</div>

<script src="js/sidebar.js"></script>

</body>