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
  <link rel="stylesheet" href="tempahan.css">

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>

  <!-- include for components-->
  <?php include 'navbar_lawatan.php'; ?>

  <section class="booking-section">
  <div class="booking-container">

    <div class="booking-title">
      <h1>Lawatan Berkumpulan</h1>
      <p>Pilih tarikh yang mempunyai kekosongan pada kalendar untuk tempahan slot.</p>
      <span>Lakukan tempahan 3 hari sebelum.</span>
    </div>

    <div class="booking-controls">
      <button class="month-btn">&lt; Bulan Sebelum</button>

      <div class="month-select">
        <i class="fa-regular fa-calendar"></i>
        <span>Mei 2026</span>
        <i class="fa-solid fa-chevron-down"></i>
      </div>

      <button class="month-btn">Bulan Selepas &gt;</button>
    </div>

    <div class="booking-legend">
      <span><i class="dot available"></i> Tersedia</span>
      <span><i class="dot almost"></i> Hampir Penuh</span>
      <span><i class="dot full"></i> Penuh</span>
      <span><i class="dot none"></i> Tiada Slot</span>
    </div>

    <div class="booking-layout">

      <!-- CALENDAR -->
      <div class="calendar-box">
        <div class="calendar-days">
          <div>Isnin</div>
          <div>Selasa</div>
          <div>Rabu</div>
          <div>Khamis</div>
          <div>Jumaat</div>
          <div>Sabtu</div>
          <div>Ahad</div>
        </div>

        <div class="calendar-grid">
          <button class="date disabled"><span>1</span><small>Tutup</small></button>
          <button class="date disabled weekend"><span>2</span></button>

          <button class="date disabled weekend"><span>3</span></button>
          <button class="date available"><span>4</span><i></i></button>
          <button class="date available"><span>5</span><i></i></button>
          <button class="date available"><span>6</span><i></i></button>
          <button class="date almost active"><span>7</span><i></i></button>
          <button class="date available"><span>8</span><i></i></button>
          <button class="date disabled weekend"><span>9</span></button>

          <button class="date disabled weekend"><span>10</span></button>
          <button class="date available"><span>11</span><i></i></button>
          <button class="date full"><span>12</span><small>Penuh</small></button>
          <button class="date available"><span>13</span><i></i></button>
          <button class="date available"><span>14</span><i></i></button>
          <button class="date available"><span>15</span><i></i></button>
          <button class="date disabled weekend"><span>16</span></button>

          <button class="date disabled weekend"><span>17</span></button>
          <button class="date available"><span>18</span><i></i></button>
          <button class="date available"><span>19</span><i></i></button>
          <button class="date available"><span>20</span><i></i></button>
          <button class="date full"><span>21</span><small>Penuh</small></button>
          <button class="date available"><span>22</span><i></i></button>
          <button class="date disabled weekend"><span>23</span></button>

          <button class="date disabled weekend"><span>24</span></button>
          <button class="date available"><span>25</span><i></i></button>
          <button class="date almost"><span>26</span><i></i></button>
          <button class="date available"><span>27</span><i></i></button>
          <button class="date available"><span>28</span><i></i></button>
          <button class="date available"><span>29</span><i></i></button>
          <button class="date disabled weekend"><span>30</span></button>

          <button class="date disabled weekend"><span>31</span></button>
        </div>
      </div>

      <!-- SLOT DETAILS -->
      <div class="slot-card">
        <div class="slot-header">
          <h3>7 Mei 2026 <span>(Khamis)</span></h3>
          <span class="status almost-status">Hampir Penuh</span>
        </div>

        <p class="slot-summary">
          <i class="fa-solid fa-people-group"></i>
          Kekosongan hari ini: 3 slot
        </p>

        <hr>

        <h4>Slot yang tersedia</h4>

        <div class="slot-item">
          <div>
            <strong><i class="fa-regular fa-clock"></i> 9.00AM - 12.00PM</strong>
            <p>Bilangan Peserta: 0 / 50</p>
          </div>
          <a href="booking_form.php" class="book-btn">Pilih Slot</a>
        </div>

        <div class="slot-item">
          <div>
            <strong><i class="fa-regular fa-clock"></i> 9.00AM - 12.00PM</strong>
            <p>Bilangan Peserta: 0 / 50</p>
          </div>
          <a href="booking_form.php" class="book-btn">Pilih Slot</a>
        </div>

        <div class="slot-item">
          <div>
            <strong><i class="fa-regular fa-clock"></i> 9.00AM - 12.00PM</strong>
            <p>Bilangan Peserta: 0 / 50</p>
          </div>
          <a href="booking_form.php" class="book-btn">Pilih Slot</a>
        </div>

        <h4>Slot penuh / tidak tersedia</h4>

        <div class="slot-item unavailable">
          <strong><i class="fa-regular fa-clock"></i> 9.00AM - 12.00PM</strong>
        </div>

        <div class="slot-item unavailable">
          <strong><i class="fa-regular fa-clock"></i> 9.00AM - 12.00PM</strong>
        </div>

        <div class="slot-note">
          <i class="fa-solid fa-circle-info"></i>
          Klik pada tarikh di kalendar untuk melihat slot yang tersedia
        </div>
      </div>

    </div>

    <div class="booking-info">
      <i class="fa-solid fa-circle-info"></i>
      <p><strong>Makluman</strong><br>
      Slot yang dipaparkan adalah tertakluk kepada perubahan semasa. Sila pilih tarikh dan slot yang dikehendaki untuk membuat tempahan.</p>
    </div>

  </div>
</section>

</body>
</html>