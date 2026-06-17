<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Seramik MBPG</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/tempahan.css">
  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include '../../sidebar.php'; ?>

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

    <?php if (isset($_GET['success']) && $_GET['success'] === 'status_updated'): ?>
      <div class="alert success-alert">
        Status tempahan berjaya dikemaskini.
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert error-alert">
        Status tempahan gagal dikemaskini.
      </div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-left">
            <h3>Jumlah Tempahan</h3>
            <strong><?= $totalBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Semasa</h3>
            <strong><?= $pendingBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Diluluskan</h3>
            <strong><?= $approvedBookings ?></strong>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-left">
            <h3>Permohonan Dibatalkan</h3>
            <strong><?= $rejectedBookings ?></strong>
          </div>
        </div>
    </section>

    <section class="booking-panel">
        <div class="booking-toolbar">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="bookingSearch" type="text" placeholder="Search by name, type, status">
        </div>

        <select id="bookingTypeFilter">
            <option value="all">All Types</option>
            <option value="Pakej Pendidikan">Pakej Pendidikan</option>
            <option value="Lawatan Berkumpulan">Lawatan Berkumpulan</option>
        </select>

        <select id="bookingStatusFilter">
            <option value="all">All Status</option>
            <option value="Belum Lulus">Belum Lulus</option>
            <option value="Lulus">Lulus</option>
            <option value="Batal">Batal</option>
        </select>

        <button id="bookingResetBtn" type="button" class="reset-btn">Reset</button>
        <button id="bookingExportBtn" type="button" class="export-btn">
            Muat Turun <i class="fa-solid fa-download"></i>
        </button>

        <button id="addBookingBtn" type="button" class="add-btn" onclick="openAddBookingModal()">
            <i class="fa-solid fa-plus"></i> Tempahan Baharu
        </button>

        
        <br>
        
        <p id="bookingNoteText">
            <i class="fa-solid fa-info-circle"></i> 
            Klik pada ID Tempahan untuk melihat butiran dan mengurus tempahan
        </p>

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
                <th>Jumlah Bayaran</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td>
                    <a href="#" class="booking-detail-link" data-booking-id="<?= $booking['booking_id'] ?>" onclick="openBookingModal(<?= $booking['booking_id'] ?>); return false;"><?= htmlspecialchars($booking['display_id']) ?></a>
                  </td>
                  <td><?= htmlspecialchars($booking['organization_name']) ?></td>
                  <td><?= htmlspecialchars($booking['package_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($booking['slot_display']) ?></td>
                  <td><?= (int) $booking['total_participants'] ?></td>
                  <td><?= htmlspecialchars($booking['formatted_total_fee'] ?? 'RM 0.00') ?></td>
                  <td><span class="status <?= $booking['status_class'] ?>"><?= $booking['status_label'] ?></span></td>
                  <td><?= htmlspecialchars($booking['admin_comment'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center; padding: 24px;">Tiada rekod tempahan ditemui.</td>
              </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="table-footer">
          <p id="bookingFooterText">Showing 0 to 0 out of 0 entries</p>

          <div class="pagination">
            <button id="prevPageBtn" type="button"><i class="fa-solid fa-chevron-left"></i></button>
            <span id="paginationInfo">Page 1 of 1</span>
            <button id="nextPageBtn" type="button"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>
    </section>


    <!-- POP UP MODAL FOR BOOKING DETAILS-->

    <div class="booking-modal" id="bookingModal">
      <div class="booking-modal-card">
        <div class="booking-modal-header">
          <h2>Info Tempahan</h2>
          <button type="button" class="booking-modal-close">&times;</button>
        </div>

        <div class="booking-modal-id">
          <h3 id="modalBookingRef">-</h3>
          <span id="modalBookingStatus" class="status pending">-</span>
        </div>

        <div class="booking-info-list">
          <div class="booking-info-item">
            <i class="fa-regular fa-user"></i>
            <div>
              <p>Nama Sekolah/ Organisasi</p>
              <small id="modalOrganization">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-phone"></i>
            <div>
              <p>Nombor Telefon</p>
              <small id="modalPhone">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-envelope"></i>
            <div>
              <p>Emel</p>
              <small id="modalEmail">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-calendar-days"></i>
            <div>
              <p>Tarikh & Slot Masa</p>
              <small id="modalSlot">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-palette"></i>
            <div>
              <p>Jenis Pakej</p>
              <small id="modalPackage">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-gem"></i>
            <div>
              <p>Pilihan Aktiviti</p>
              <small id="modalActivities">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-users"></i>
            <div>
              <p>Bilangan Peserta</p>
              <small id="modalParticipants">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-solid fa-dollar-sign"></i>
            <div>
              <p>Jumlah Bayaran</p>
              <small id="modalTotalFee">-</small>
            </div>
          </div>

          <div class="booking-info-item">
            <i class="fa-regular fa-clipboard"></i>
            <div>
              <p>Catatan</p>
              <small id="modalRemark">-</small>
            </div>
          </div>
        </div>

        <div class="booking-action-title">Tindakan</div>
        <br>

        <!-- BORANG TINDAKAN UNTUK APPROVE/ REJECT BOOKING, DISERTAKAN DENGAN INPUT SEBAB JIKA REJECT DAN LINK WHATSAPP JIKA APPROVE/ REJECT-->
        <form action="booking_email.php" method="POST" class="booking-modal-actions" id="bookingActionForm">
            <input type="hidden" name="booking_id" id="actionBookingId">
            <input type="hidden" name="action" id="actionBookingStatus">
            <input type="hidden" name="admin_comment" id="adminCommentInput">

            <button type="button" class="approve-booking-btn" id="approveBookingBtn"  style="background-color: #5f903c; color: white;">
                <span class="btn-text">Terima</span>
                <span class="btn-loader"></span>
            </button>

            <button type="button" class="reject-booking-btn" id="rejectBookingBtn"  style="background-color: #e0160f; color: white;">
                Batal
            </button>

            <button type="button" class="edit-booking-btn" id="editBookingBtn" style="background-color: #007bff; color: white;">
                Edit
            </button>

               <a 
                href="#" 
                target="_blank"
                class="whatsapp-booking-btn"
                id="whatsappBookingBtn"
                style="display:none;"
              >
                WhatsApp Pengguna
              </a>

        </form>
      </div>
    </div>

    <!-- MODAL UNTUK MASUK SEBAB PENOLAKAN JIKA ADMIN TEKAN BUTANG REJECT -->

    <div class="reject-modal" id="rejectModal">

      <div class="reject-modal-content">

        <h3>Sebab Penolakan</h3>

        <textarea 
          id="rejectReason"
          placeholder="Masukkan sebab penolakan..."
        ></textarea>

        <div class="reject-modal-actions">
          <button type="button" id="cancelRejectBtn">
            Batal
          </button>

          <button type="button" id="confirmRejectBtn">
            <span class="btn-text">Hantar</span>
            <span class="btn-loader"></span>
          </button>

        </div>

      </div>

    </div>

   <!-- EDIT MODAL -->
        <div class="edit-modal" id="editModal">
            <div class="edit-modal-content">
                <h3>Edit Tempahan</h3>
                
                <form id="editBookingForm">
                    <input type="hidden" id="editBookingId">
                    
                    <!-- Section 1: Organization Info -->
                    <div class="form-section-title">1. Maklumat Organisasi</div>
                    
                    <div class="form-group">
                        <label for="editOrganization">Nama Organisasi</label>
                        <input type="text" id="editOrganization" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editContactPerson">Nama Pegawai</label>
                        <input type="text" id="editContactPerson" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPhoneNumber">Nombor Telefon</label>
                        <input type="text" id="editPhoneNumber" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEmail">Emel</label>
                        <input type="email" id="editEmail" class="form-control" required>
                    </div>
                    
                    <!-- Section 2: Package Selection -->
                    <div class="form-section-title">2. Pakej</div>
                    
                    <div class="form-group">
                        <label for="editPackageSelect">Jenis Pakej</label>
                        <select id="editPackageSelect" class="form-control" required>
                            <option value="">Pilih Pakej</option>
                            <!-- Packages will be loaded dynamically -->
                        </select>
                        <small style="color: #666; margin-top: 5px; display: block;">
                            <i class="fa-solid fa-info-circle"></i> 
                            <span id="packageChangeNote">Menukar pakej akan mengemaskini pilihan aktiviti</span>
                        </small>
                    </div>
                    
                    <!-- Section 3: Participants -->
                    <div class="form-section-title">3. Maklumat Peserta</div>
                    
                    <div class="form-group">
                        <label for="editParticipants">Jumlah Peserta</label>
                        <input type="number" id="editParticipants" class="form-control" min="1" required>
                        <small style="color: #666;">Jumlah ini akan digunakan untuk agihan aktiviti</small>
                    </div>
                    
                    <!-- Section 4: Slot Selection -->
                    <div class="form-section-title">4. Tarikh & Slot Masa</div>
                    
                    <div class="form-group">
                        <label>Slot Semasa</label>
                        <input type="text" id="editSlotDisplay" class="form-control" readonly style="background: #e8f4fd; border-color: #4a90e2; font-weight: 500;">
                    </div>
                    
                    <div class="form-group">
                        <label for="editSlotDate">Tukar Tarikh (Pilihan)</label>
                        <select id="editSlotDate" class="form-control">
                            <option value="">Kekalkan Tarikh Semasa</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editSlotTime">Tukar Slot Masa (Pilihan)</label>
                        <select id="editSlotTime" class="form-control">
                            <option value="">Pilih Tarikh Dahulu</option>
                        </select>
                    </div>
                    <input type="hidden" id="editSlotId">
                    
                    <!-- Section 5: Activities (Dynamic) -->
                    <div id="editActivitySection" style="display: none;">
                        <div class="form-section-title">5. Agihan Peserta Mengikut Aktiviti</div>
                        <p class="box-desc">
                            Tetapkan bilangan peserta bagi setiap aktiviti. Jumlah agihan mestilah sama dengan <strong id="editTotalParticipantsLabel">0</strong> peserta.
                        </p>
                        
                        <div id="editActivityList" class="edit-activity-list">
                            <!-- Activities will be loaded dynamically -->
                        </div>
                        
                        <div id="editActivityTotal">
                            <span>Jumlah Agihan: <strong id="editActivitySum">0</strong> / <strong id="editRequiredTotal">0</strong></span>
                            <span id="editActivityWarning" style="color: #e74c3c; display: none;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Jumlah tidak sepadan!
                            </span>
                            <span id="editActivityValid" style="color: #27ae60; display: none;">
                                <i class="fa-solid fa-circle-check"></i> Jumlah sepadan!
                            </span>
                        </div>
                    </div>
                    
                    <!-- Section 6: Additional Info -->
                    <div class="form-section-title">6. Maklumat Tambahan</div>
                    
                    <div class="form-group">
                        <label for="editAdminComment">Catatan Admin</label>
                        <textarea id="editAdminComment" class="form-control" rows="3" placeholder="Contoh: Pelajar berkeperluan khas, permintaan khas, dan lain-lain"></textarea>
                    </div>
                    
                    <div class="edit-modal-actions">
                        <button type="button" id="cancelEditBtn" class="btn-secondary">
                            Batal
                        </button>
                        <button type="submit" id="saveEditBtn" class="btn-primary">
                            <span class="btn-text">Simpan</span>
                            <span class="btn-loader"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <!--ADD BOOKING MODAL-->
    
        <div id="addBookingModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
            <h3><i class="fa-solid fa-plus-circle"></i> Tambah Tempahan Baharu</h3>
            <button type="button" class="close-modal" onclick="closeAddBookingModal()">&times;</button>
            </div>
            
            <form id="addBookingForm" onsubmit="submitNewBooking(event)">
            <div class="form-row">
                <div class="form-group">
                <label for="new_org_name">Nama Organisasi <span class="required">*</span></label>
                <input type="text" id="new_org_name" name="organization_name" required placeholder="Masukkan nama organisasi">
                </div>
                
                <div class="form-group">
                <label for="new_package">Jenis Tempahan <span class="required">*</span></label>
                <select id="new_package" name="package_id" required onchange="loadAvailableSlots()">
                    <option value="">-- Pilih Pakej --</option>
                    <?php foreach ($packages as $package): ?>
                    <option value="<?= $package['package_id'] ?>">
                        <?= htmlspecialchars($package['package_name']) ?> 
                        (RM <?= number_format($package['fee'], 2) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                <label for="new_slot">Tarikh & Slot Masa <span class="required">*</span></label>
                <select id="new_slot" name="slot_id" required>
                    <option value="">-- Pilih Tarikh & Slot --</option>
                </select>
                <small id="slotAvailabilityMsg" style="display:block; margin-top:4px;"></small>
                </div>
                
                <div class="form-group">
                <label for="new_pax">Bilangan Peserta (Pax) <span class="required">*</span></label>
                <input type="number" id="new_pax" name="total_participants" required min="1" placeholder="Contoh: 10">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                <label for="new_total_fee">Jumlah Bayaran (RM)</label>
                <input type="text" id="new_total_fee" name="total_fee" readonly style="background:#f5f5f5;">
                </div>
                
                <div class="form-group">
                <label for="new_status">Status</label>
                <select id="new_status" name="status">
                    <option value="Belum Lulus">Belum Lulus</option>
                    <option value="Lulus">Lulus</option>
                    <option value="Batal">Batal</option>
                </select>
                </div>
            </div>

            <div class="form-group">
                <label for="new_admin_comment">Catatan (Optional)</label>
                <textarea id="new_admin_comment" name="admin_comment" rows="2" placeholder="Sebarang catatan tambahan..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddBookingModal()">Batal</button>
                <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Simpan Tempahan
                </button>
            </div>
            </form>
        </div>
        </div>

</main>

<script type="application/json" id="booking-data">
<?= json_encode(array_values($bookings), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>
<script src="/web/galeriseramikmbpg/admin/js/sidebar.js"></script>
<script src="booking.js"></script>
</body>
</html>