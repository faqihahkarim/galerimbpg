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

  <!-- Favicon -->
  <link rel="icon" href="../assets/images/logogaleri.png" type="image/png" style="width: 32px; height: 32px;">
</head>

<body>


    <section class="package-detail-section">
    <div class="package-detail-container">

        <div class="package-detail-image">
        <img src="../assets/images/pendidikan.jpg" alt="Pendidikan">
        </div>

        <div class="package-detail-content">
        <h1>Pakej Pendidikan</h1>

        <p>Terdiri daripada 4 aktiviti yang boleh dipilih untuk sesi selama 3 jam:</p>

        <table class="schedule-table">
            <thead>
            <tr>
                <th>Sabtu</th>
                <th>Ahad</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>9.00 Pagi - 12.00 Tengahari</td>
                <td>9.00 Pagi - 12.00 Tengahari</td>
            </tr>
            <tr>
                <td>2.00 Petang - 5.00 Petang</td>
                <td>2.00 Petang - 5.00 Petang</td>
            </tr>
            </tbody>
        </table>

        <p>Bayaran bagi setiap aktiviti:</p>

        <table class="price-table">
            <colgroup>
                <col style="width: 50%">
                <col style="width: 50%">
            </colgroup>

            <thead>
                <tr>
                <th>Aktiviti</th>
                <th>Harga</th>
                </tr>
            </thead>

            <tbody>
            <tr>
                <td>Interaktif Mewarna</td>
                <td>RM10.00</td>
            </tr>
            <tr>
                <td>Melukis dan Mewarna</td>
                <td>RM15.00</td>
            </tr>
            <tr>
                <td>Pembentukan Tanah Liat</td>
                <td>RM20.00</td>
            </tr>
            <tr>
                <td>Teknik Lempar Alin</td>
                <td>RM30.00</td>
            </tr>
            </tbody>
        </table>



        <a href="tempahan_pendidikan.php" class="booking-btn">Tempah Sekarang</a>
        </div>

        </div>
    </section>

</body>
</html>