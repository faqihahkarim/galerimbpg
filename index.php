<?php

include 'db.php';
$activityQuery = "SELECT
                   a.activity_id,
                   a.activity_name,
                   a.description,
                   ai.image_url
                  FROM activities a
                  LEFT JOIN activity_images ai
                  ON a.activity_id = ai.activity_id
                  AND ai.is_main = 1
                  WHERE a.status = 'active'
                  ORDER BY a.activity_id ASC";

$activityResult = mysqli_query($conn, $activityQuery);


$packageQuery= "SELECT
                package_id,
                package_name,
                description,
                image_url
              FROM packages
              WHERE status = 'active'
              ORDER BY package_id ASC";

$packageResult = mysqli_query($conn, $packageQuery);

$base = "/galeriseramikmbpg";
$pageType = "home";

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
  <link rel="stylesheet" href="<?= $base ?>/assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/index.css">

  <!-- Favicon -->
  <link rel="icon" href="<?= $base ?>/assets/images/logogaleri.png" type="image/png">

</head>

<body>
  <?php include 'components/navbar.php'; ?>

  <!-- NEWS BANNER-->
  <section class="hero">
  <div class="hero-slider">

    <!-- Slide 1 -->
    <div class="slide active" style="background-image: url('assets/images/banner1.jpg');">
      <div class="overlay"></div>
      <div class="content">
        <h1>Koleksi Seramik Terbaru:</h1>
        <p>Dapatkan koleksi seramik terbaru dengan segera, lihat katalog di inventori</p>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="slide" style="background-image: url('assets/images/banner2.jpg');">
      <div class="overlay"></div>
      <div class="content">
        <h1>Bengkel Seramik Mingguan</h1>
        <p>Sertai aktiviti menarik bersama keluarga dan pelajar</p>
      </div>
    </div>

    <!-- navigation button for next and previous banner-->

    <button class="prev">&#10094;</button>
    <button class="next">&#10095;</button>

    <div class="dots"></div>

  </div>

</section>


<!-- INTRO -->

<section class="intro-section">
  <div class="intro-container">

    <!-- Image -->
    <div class="intro-image">
      <img src="assets/images/galeri.webp" alt="Galeri Seramik">
    </div>

    <!-- Text -->
    <div class="intro-text">
      <h2>Selamat Datang Ke<br>Galeri Seramik Pasir Gudang</h2>

      <p>
        Temui warisan yang kaya mengenai seni seramik di galeri kami.
        Kami menempatkan koleksi yang luas merangkumi tembikar tradisional,
        jubin, dan kepingan hiasan yang merentas berabad-abad kecemerlangan artistik.
      </p>

      <p>
        Galeri kami memaparkan rekaan yang rumit, warna-warna yang terang,
        dan ketukangan mahir yang mencerminkan pencapaian budaya dan artistik.
        Daripada pinggan yang berhias kepada jubin yang elegan.
      </p>
    </div>

  </div>
</section>

<!-- ACTIVITY -->

<section class="activity-section" id="activity-section">
  <div class="activity-container">

    <div class="section-title">
      <h2>Aktiviti Kami</h2>
      <p>Terokai pelbagai aktiviti menarik yang kami<br> sediakan</p>
    </div>

    <div class="activity-grid">

      <?php while ($activity = mysqli_fetch_assoc($activityResult)): ?>

        <?php
          $image = !empty($activity['image_url'])
            ? $activity['image_url']
            : "assets/images/default-activity.jpg";
        ?>

        <a href="activity/activity_detail.php?id=<?= $activity['activity_id']; ?>" class="activity-card">

          <img 
            src="<?= htmlspecialchars($image); ?>" 
            alt="<?= htmlspecialchars($activity['activity_name']); ?>"
          >

          <h3>
            <?= htmlspecialchars($activity['activity_name']); ?>
          </h3>

          <p>
            <?= htmlspecialchars($activity['description']); ?>
          </p>

        </a>

      <?php endwhile; ?>

    </div>

  </div>
</section>

<!-- PACKAGE -->
<section class="package-section" id="package-section">
  <div class="package-container">

    <div class="package-title">
      <h2>Pakej Kami</h2>
      <p>
        Terokai pakej kami yang kami sediakan untuk<br>
        pengalaman anda di Galeri Seramik Pasir Gudang
      </p>
    </div>

    <div class="package-grid">

      <?php while ($package = mysqli_fetch_assoc($packageResult)): ?>

        <?php
          $image = !empty($package['image_url'])
            ? $package['image_url']
            : "assets/images/default-package.jpg";
        ?>

        <div class="package-card">
          <img 
            src="<?= htmlspecialchars($image); ?>" 
            alt="<?= htmlspecialchars($package['package_name']); ?>"
          >

          <div class="package-content">
            <h3><?= htmlspecialchars($package['package_name']); ?></h3>

            <p><?= htmlspecialchars($package['description']); ?></p>

            <a 
              href="<?= $base ?>/package/package_detail.php?id=<?= $package['package_id']; ?>" 
              class="package-btn"
            >
              Lihat Slot Pakej
            </a>
          </div>
        </div>

      <?php endwhile; ?>

    </div>

  </div>
</section>

<!-- FOOTER -->

<footer class="footer-section">
  <div class="footer-container">

    <div class="footer-title">
      <h2>Mari Beramai-Ramai ke Galeri Seramik Pasir Gudang</h2>
      <p>Hubungi kami untuk sebarang pertanyaan<br>berkenaan Galeri Seramik Pasir Gudang</p>
    </div>

    <div class="footer-content">

      <div class="footer-vase">
        <img src="assets/images/vase.png" alt="Seramik Vase">
      </div>

      <div class="footer-info">
        <div class="footer-column">
          <h3>Lokasi</h3>
          <p>
            Galeri seramik MBPG,<br>
            Lot 97769, Plot 6,<br>
            Taman Bandar, 81700<br>
            Pasir Gudang, Johor
          </p>
        </div>

        <div class="footer-column">
          <h3>Waktu Operasi</h3>
          <p>
            Sabtu - Khamis<br>
            9.00 pagi - 1.00 petang<br>
            2.00 petang - 5.00 petang
          </p>

          <p>
            Jumaat<br>
            9.00 pagi - 12.15 tengahari<br>
            2.45 petang - 5.00 petang
          </p>
        </div>

        <div class="footer-column">
          <h3>Hubungi Kami</h3>
          <p>
            Telefon: 013-2988693 / 019-2028241<br>
            Emel: galeriseramik.mbpg@gmail.com
          </p>

         <!--question box-->
         <form id="footerQnaForm" action="chatbot/submit_question.php" method="POST" style="margin-top: 15px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <textarea 
                name="question" 
                id="footerQuestionInput" 
                placeholder="Hantarkan sebarang pertanyaan anda di sini..." 
                rows="3" 
                required 
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; font-size: 14px; resize: none; color: #333;"
              ></textarea>
              
              <button 
                type="submit" 
                id="footerSubmitBtn"
                style="background-color: #fbc02d; color: #111; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: pointer; transition: background 0.2s; align-self: flex-start;"
              >Hantar</button>
            </div>
            <div id="footerFormStatus" style="margin-top: 8px; font-size: 13px; font-weight: 500;"></div>
          </form>
        </div>

        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© <?php echo date("Y"); ?> Galeri Seramik Pasir Gudang. All Rights Reserved</p>

      <div class="footer-social">
        <a href="#"><i class="fa-brands fa-facebook"></i> Majlis Bandaraya Pasir Gudang</a>
        <a href="https://www.instagram.com/pasirgudangkraf/"><i class="fa-brands fa-instagram"></i> pasirgudangkraf</a>
      </div>
    </div>

  </div>
</footer>

<!-- CHATBOT BUBBLE -->
<div class="chatbot-wrapper" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Poppins', sans-serif;">
  
  <button id="chatToggleBtn" style="background-color: #fbc02d; color: #111; border: none; width: 60px; height: 60px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.2); cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;">
    <i class="fa-solid fa-comments"></i>
  </button>

  <div id="chatWindow" style="display: none; width: 360px; height: 480px; background-color: #fff; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.15); flex-direction: column; position: absolute; bottom: 75px; right: 0; overflow: hidden; border: 1px solid #eee;">
    
    <div style="background-color: #111; color: #fff; padding: 15px; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <img src="<?= $base ?>assets/images/logogaleri.png" style="width: 30px; height: 30px; object-fit: contain;">
        <div>
          <h4 style="margin: 0; font-size: 14px; font-weight: 600;">Anda Perlukan Bantuan?</h4>
          <span style="font-size: 11px; color: #a5d6a7; display: flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Online
          </span>
        </div>
      </div>
      <button id="closeChatBtn" style="background: none; border: none; color: #fff; cursor: pointer; font-size: 16px;"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div id="chatMessages" style="flex: 1; padding: 15px; overflow-y: auto; background-color: #f8f9fa; display: flex; flex-direction: column; gap: 12px;">
      <div class="msg-bot" style="align-self: flex-start; max-width: 80%; background-color: #fff; padding: 10px 14px; border-radius: 4px 12px 12px 12px; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); color: #333;">
        Selamat datang ke Galeri Seramik Pasir Gudang! Ada apa yang boleh saya bantu? Sila pilih soalan popular di bawah atau taip soalan anda.
      </div>
      
      <div id="chatSuggestions" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px;">
        <div style="font-size: 11px; color: #888;">Memuatkan soalan lazim...</div>
      </div>
    </div>

    <form id="chatInputForm" style="border-top: 1px solid #eee; padding: 10px; display: flex; gap: 8px; background-color: #fff;">
      <input type="text" id="chatMessageInput" placeholder="Taip soalan anda di sini..." autocomplete="off" required style="flex: 1; border: 1px solid #ddd; padding: 8px 12px; border-radius: 20px; font-size: 13px; outline: none; font-family: inherit;">
      <button type="submit" style="background-color: #111; color: #fff; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-paper-plane" style="font-size: 12px;"></i></button>
    </form>

  </div>
</div>


     <!-- SCRIPT-->
     <script src="assets/js/index.js"></script>
     
</body>
</html>