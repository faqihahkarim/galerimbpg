<?php
include '../db.php';

$base = "/web/galeriseramikmbpg/";
$pageType = "inner";

$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
$slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;
$selected_date = $_GET['date'] ?? "";

/* Package info */
$packageQuery = "
  SELECT package_id, package_name, requires_activity_selection
  FROM packages
  WHERE package_id = $package_id
  AND status = 'active'
";
$packageResult = mysqli_query($conn, $packageQuery);
$package = mysqli_fetch_assoc($packageResult);

/* Slot info */
$slotQuery = "
  SELECT slot_id, slot_date, start_time, end_time
  FROM booking_slots
  WHERE slot_id = $slot_id
  AND package_id = $package_id
";
$slotResult = mysqli_query($conn, $slotQuery);
$slot = mysqli_fetch_assoc($slotResult);

/* Activities */
$activityQuery = "
  SELECT 
    a.activity_id,
    a.activity_name,
    a.description,
    a.default_capacity,
    ai.image_url
  FROM activities a
  LEFT JOIN activity_images ai
    ON a.activity_id = ai.activity_id
    AND ai.is_main = 1
  WHERE a.status = 'active'
  ORDER BY a.activity_id ASC
";
$activityResult = mysqli_query($conn, $activityQuery);

$showActivitySection = $package && $package['requires_activity_selection'] == 1;
$additionalSectionNumber = $showActivitySection ? 5 : 4;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borang Tempahan</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= $base ?>assets/css/navbar.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/index.css">
  <link rel="stylesheet" href="<?= $base ?>package/package.css">
  <link rel="stylesheet" href="<?= $base ?>package/form.css">

  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="form-section">
  <div class="form-container">

    <div class="form-title">
      <h1>Borang Tempahan <?= htmlspecialchars($package['package_name'] ?? 'Pakej'); ?></h1>
      <p>Sila lengkapkan maklumat tempahan anda</p>
    </div>

    <form action="booking_process.php" method="POST" id="bookingForm">

      <input type="hidden" name="package_id" value="<?= $package_id; ?>">
      <input type="hidden" name="slot_id" value="<?= $slot_id; ?>">

      <!-- 1 -->
      <div class="form-box">
        <h3>1. Maklumat Organisasi</h3>

        <div class="form-grid">
          <div class="form-group">
            <label>Nama Sekolah / Agensi</label>
            <input type="text" name="organization_name" required>
          </div>

          <div class="form-group">
            <label>Nama Pegawai / Guru Pengiring</label>
            <input type="text" name="contact_person" required>
          </div>

          <div class="form-group">
            <label>No. Telefon Pegawai / Guru</label>
            <input type="text" name="phone_number" required>
          </div>

          <div class="form-group">
            <label>Emel Pegawai / Guru</label>
            <input type="email" name="email" required>
          </div>
        </div>
      </div>

      <div class="form-row">

        <!-- 2. Maklumat Peserta -->
        <div class="form-box small-box">
          <h3>2. Maklumat Peserta</h3>

          <div class="form-group">
            <label>Jumlah Peserta</label>
            <div class="input-with-text">
              <input type="number" name="total_participants" min="1" required>
              <span>orang</span>
            </div>
          </div>
        </div>

        <!-- 3. Tarikh & Slot Pilihan -->
        <div class="form-box small-box">
          <h3>3. Tarikh & Slot Pilihan</h3>

          <div class="form-grid two">
            <div class="form-group">
              <label>Tarikh</label>
              <input 
                type="text" 
                name="selected_date_display"
                value="<?= $slot ? date('d M Y', strtotime($slot['slot_date'])) : htmlspecialchars($selected_date); ?>" 
                readonly
              >
            </div>

            <div class="form-group">
              <label>Slot Pilihan</label>
              <input 
                type="text" 
                name="selected_slot_display"
                value="<?= $slot ? date('g.i A', strtotime($slot['start_time'])) . ' - ' . date('g.i A', strtotime($slot['end_time'])) : ''; ?>" 
                readonly
              >
            </div>
          </div>
        </div>

      </div>

      <?php if ($showActivitySection): ?>

      <!-- 4. Agihan Peserta Mengikut Aktiviti -->
      <div class="form-box">
        <h3>4. Agihan Peserta Mengikut Aktiviti</h3>
        <p class="box-desc">
          Tetapkan bilangan peserta bagi setiap aktiviti. Jumlah agihan mestilah sama dengan bilangan peserta.
        </p>

        <div class="activity-list">

          <?php while ($activity = mysqli_fetch_assoc($activityResult)): ?>

            <?php
              $activityImage = !empty($activity['image_url'])
                ? $base . $activity['image_url']
                : $base . "assets/images/default-activity.jpg";
            ?>

            <div class="activity-item">
              <img 
                src="<?= htmlspecialchars($activityImage); ?>" 
                alt="<?= htmlspecialchars($activity['activity_name']); ?>"
              >

              <div class="activity-info">
                <h4><?= htmlspecialchars($activity['activity_name']); ?></h4>
                <p><?= htmlspecialchars($activity['description']); ?></p>
              </div>

              <div class="counter">
               <button type="button" class="minus-btn">−</button>

                  <span>
                    <input style="text-align: center;"
                      type="number" 
                      name="activity_participants[<?= $activity['activity_id']; ?>]" 
                      value="0"
                      min="0"
                      max="<?= htmlspecialchars($activity['default_capacity']); ?>"
                      class="activity-count"
                      readonly
                    >
                    <br><small>orang</small>
                  </span>

                  <button type="button" class="plus-btn">+</button>
              </div>

              <div class="max-info">
                <i class="fa-solid fa-people-group"></i>
                <span>Maksimum: <?= htmlspecialchars($activity['default_capacity']); ?> Orang</span>
              </div>
            </div>

          <?php endwhile; ?>

        </div>
      </div>

      <?php endif; ?>

      <!-- 5. Maklumat Tambahan -->
      <div class="form-box">
        <h3><?= $additionalSectionNumber; ?>. Maklumat Tambahan</h3>

        <textarea 
          name="admin_remark" 
          maxlength="250" 
          placeholder="Contoh: Pelajar berkeperluan khas, permintaan khas, dan lain-lain"
        ></textarea>
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
        <button type="button" id="openConfirmModal">
          Hantar Tempahan
        </button>

      </div>

    </form>

  </div>
</section>

<!-- Confirmation Modal (POP-UP) -->

<div class="confirm-modal" id="confirmModal">
  <div class="confirm-box">

    <button type="button" class="confirm-close" id="closeConfirmModal">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <h2>Sahkan Tempahan Anda</h2>
    <p class="confirm-subtitle">
      Sila semak maklumat tempahan sebelum dihantar.
    </p>

    <div class="confirm-details">

      <div class="confirm-row">
        <span>Nama Organisasi</span>
        <strong id="confirmOrganization">-</strong>
      </div>

      <div class="confirm-row">
        <span>Nama Pegawai</span>
        <strong id="confirmPerson">-</strong>
      </div>

      <div class="confirm-row">
        <span>No. Telefon</span>
        <strong id="confirmPhone">-</strong>
      </div>

      <div class="confirm-row">
        <span>Emel</span>
        <strong id="confirmEmail">-</strong>
      </div>

      <div class="confirm-row">
        <span>Jumlah Peserta</span>
        <strong id="confirmParticipants">-</strong>
      </div>

      <div class="confirm-row">
        <span>Tarikh</span>
        <strong id="confirmDate">-</strong>
      </div>

      <div class="confirm-row">
        <span>Slot</span>
        <strong id="confirmSlot">-</strong>
      </div>

      <div class="confirm-activity" id="confirmActivityBox">
        <h4>Agihan Aktiviti</h4>
        <ul id="confirmActivities"></ul>
      </div>

      <div class="confirm-note">
        <span>Maklumat Tambahan</span>
        <p id="confirmRemark">-</p>
      </div>

    </div>

    <div class="confirm-actions">
      <button type="button" class="edit-btn" id="editBooking">
        Kembali Edit
      </button>

      <button type="button" class="confirm-btn" id="confirmSubmit">
        Sahkan Tempahan
      </button>
    </div>

  </div>
</div>

<script src="booking_form.js"></script>
<?php include '../components/footer.php'; ?>
<script src="booking_confirm.js"></script>


</body>
</html>