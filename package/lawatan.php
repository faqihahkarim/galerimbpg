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

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>

  <!-- include for components-->
  <?php include 'navbar.php'; ?>

    <section class="package-detail-section">
    <div class="package-detail-container">

        <div class="package-detail-image">
        <img src="../assets/images/lawatan.jpg" alt="Lawatan Berkumpulan">
        </div>

        <div class="package-detail-content">
        <h1>Lawatan Berkumpulan</h1>

        <p>Berikut merupakan sesi untuk lawatan berkumpulan:</p>

        <table class="schedule-table">
            <thead>
            <tr>
                <th>Isnin - Khamis</th>
                <th>Jumaat</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>10.00 Pagi</td>
                <td>10.00 Pagi</td>
            </tr>
            <tr>
                <td>11.00 Pagi</td>
                <td>11.00 Pagi</td>
            </tr>
            <tr>
                <td>12.00 Tengahari</td>
                <td></td>
            </tr>
            <tr>
                <td>2.30 Petang</td>
                <td></td>
            </tr>
            <tr>
                <td>3.30 Petang</td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <p class="price-note">
            Tiket Bayaran RM2.00 bagi pengunjung 7 tahun ke atas
        </p>

        <a href="tempahan_lawatan.php" class="booking-btn">Tempah Sekarang</a>
        </div>

        </div>
    </section>

</body>
</html>