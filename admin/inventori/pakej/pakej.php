<?php 
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';

// Fetch all active activities to populate the dropdown in the modal setup
$activityQuery = "SELECT activity_id, activity_name FROM activities WHERE status = 'active' ORDER BY activity_name ASC";
$activityResult = mysqli_query($conn, $activityQuery);
$activitiesList = [];
if ($activityResult) {
    while ($act = mysqli_fetch_assoc($activityResult)) {
        $activitiesList[] = $act;
    }
}

// Fetch packages with sub-aggregated selected activity IDs
$packageQuery = "
    SELECT p.*, 
           (SELECT GROUP_CONCAT(pa.activity_id SEPARATOR ', ') 
            FROM package_activities pa 
            WHERE pa.package_id = p.package_id) as activity_ids
    FROM packages p
    WHERE p.status = 'active'
    ORDER BY p.package_id ASC
";
$packageResult = mysqli_query($conn, $packageQuery);

// Flash message alert engine
$flashMessage = '';
$flashClass = '';

if (isset($_GET['success'])) {
    $flashClass = 'success-alert';
    switch ($_GET['success']) {
        case 'package_added': $flashMessage = 'Pakej berjaya ditambah.'; break;
        case 'package_updated': $flashMessage = 'Pakej berjaya dikemaskini.'; break;
        case 'package_deleted': $flashMessage = 'Pakej berjaya dipadam.'; break;
    }
}

if (isset($_GET['error'])) {
    $flashClass = 'error-alert';
    switch ($_GET['error']) {
        case 'need_image': $flashMessage = 'Sila masukkan gambar pakej.'; break;
        case 'invalid_image_type': $flashMessage = 'Jenis gambar tidak sah. Gunakan JPG, JPEG, PNG atau WEBP.'; break;
        case 'image_too_large': $flashMessage = 'Saiz gambar terlalu besar. Maksimum 5MB.'; break;
        default: $flashMessage = 'Ralat berlaku. Sila cuba lagi.'; break;
    }
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM packages");
$totalRules = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalRules / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Pakej</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/inventory.css">
    <link rel="stylesheet" href="../../css/tempahan.css">
    <link rel="stylesheet" href="../../css/activity.css"> </head>

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
                <h1>Pengurusan Galeri</h1>
                <p>Pakej</p>
            </div>
        </header>

        <?php if (!empty($flashMessage)): ?>
            <div class="alert <?= htmlspecialchars($flashClass) ?>">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="booking-panel">

            <div class="activity-actions">
                <button type="button" id="openPackageModal" class="add-activity-btn">
                    <i class="fa-solid fa-plus"></i> Tambah Pakej
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pakej</th>
                            <th>Kapasiti (Pax)</th>
                            <th>Dengan Aktiviti</th>
                            <th>Aktiviti Pilihan (ID)</th>
                            <th>Gambar</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($packageResult && mysqli_num_rows($packageResult) > 0): ?>
                        <?php while ($package = mysqli_fetch_assoc($packageResult)): ?>
                            <?php
                                $imagePath = !empty($package['image_url'])
                                    ? "../../../" . $package['image_url']
                                    : "../../../assets/images/no-image.png";
                                
                                $hasActivity = ((int)$package['requires_activity_selection'] === 1);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($package['package_id']) ?></td>
                                <td><strong><?= htmlspecialchars($package['package_name']) ?></strong></td>
                                <td><?= htmlspecialchars($package['capacity']) ?> Pax</td>
                                <td><?= htmlspecialchars($package['description']) ?></td>
                                <td>
                                    <span class="badge <?= $hasActivity ? 'status-confirm' : 'status-pending' ?>">
                                        <?= $hasActivity ? 'Ya' : 'Tidak' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $hasActivity && !empty($package['activity_ids']) ? htmlspecialchars($package['activity_ids']) : '-' ?>
                                </td>
                                <td>
                                    <img src="<?= htmlspecialchars($imagePath) ?>" class="activity-img" alt="Pakej">
                                </td>
                                <td>
                                    <button type="button" class="edit-activity-btn" data-id="<?= htmlspecialchars($package['package_id']) ?>">Edit</button>
                                </td>
                                <td>
                                    <button type="button" class="delete-activity-btn" data-id="<?= htmlspecialchars($package['package_id']) ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data pakej ditemui.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

                <!--pagination-->
                <div class="pagination" style="margin-top: 20px; display: flex; justify-content: right; align-items: flex-end; gap: 8px; color: inherit; text-decoration:none;">

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="page-btn">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                // CONFIGURATION: Set how many pages to show around the active page item
                $adjacents = 2; 

                // Always show Page 1
                if ($page > ($adjacents + 1)) {
                    echo '<a href="?page=1" class="page-btn">1</a>';
                    if ($page > ($adjacents + 2)) {
                        echo '<span class="page-dots" style="padding: 8px 12px; color: var(--text-soft);">...</span>';
                    }
                }

                // Calculate dynamic sliding range window positions
                $startLoop = max(1, $page - $adjacents);
                $endLoop   = min($totalPages, $page + $adjacents);

                for ($i = $startLoop; $i <= $endLoop; $i++) {
                    $activeClass = ($page == $i) ? 'active-page' : '';
                    echo '<a href="?page=' . $i . '" class="page-btn ' . $activeClass . '">' . $i . '</a>';
                }

                // Always show the Last Page boundary 
                if ($page < ($totalPages - $adjacents)) {
                    if ($page < ($totalPages - $adjacents - 1)) {
                        echo '<span class="page-dots" style="padding: 8px 12px; color: var(--text-soft);">...</span>';
                    }
                    echo '<a href="?page=' . $totalPages . '" class="page-btn">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>

            </div>
        

        </section>

        <div class="activity-modal" id="packageModal">
            <div class="activity-modal-card">

                <div class="modal-header">
                    <h2 id="packageModalTitle">Tambah Pakej</h2>
                    <button type="button" id="closePackageModal">&times;</button>
                </div>

                <form action="pakej_process.php" method="POST" enctype="multipart/form-data" id="packageForm">
                    <input type="hidden" name="action" id="packageAction" value="add">
                    <input type="hidden" name="package_id" id="packageId">

                    <div class="form-grid two">
                        <div class="form-group">
                            <label>Nama Pakej</label>
                            <input type="text" name="package_name" id="packageName" required>
                        </div>

                        <div class="form-group">
                            <label>Kapasiti Max (Pax)</label>
                            <input type="number" name="capacity" id="packageCapacity" min="1" required>
                        </div>

                        <div class="form-group full-width">
                            <label>Penerangan</label>
                            <textarea name="package_description" id="packageDescription" rows="3" required></textarea>
                        </div>
                        
                    
                    </div>

                    <div class="form-group conditional-block">
                        <label>Adakah Pakej Ini Mempunyai Aktiviti?</label>
                        <div class="radio-group-wrapper">
                            <label>
                                <input type="radio" name="requires_activity_selection" value="1" id="reqActivityYes"> Ya
                            </label>
                            <label>
                                <input type="radio" name="requires_activity_selection" value="0" id="reqActivityNo" checked> Tidak
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="activityDropdownRow" style="display: none;">
                        <label>Pilih Aktiviti (Boleh Pilih Lebih Dari Satu)</label>
                        <div id="activityCheckboxesWrapper" style="background: #ffffff; border: 1px solid #aaa; border-radius: 10px; padding: 12px; max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; width: 100%; box-sizing: border-box;">
                            <?php if (!empty($activitiesList)): ?>
                                <?php foreach ($activitiesList as $activity): ?>
                                    <label style="font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 10px; margin: 0;">
                                        <input type="checkbox" name="activity_ids[]" value="<?= htmlspecialchars($activity['activity_id']) ?>" class="activity-checkbox" style="width: auto;">
                                        <span>ID: <?= htmlspecialchars($activity['activity_id']) ?> - <?= htmlspecialchars($activity['activity_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #999; font-size: 13px;">Tiada aktiviti aktif ditemui.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gambar Pakej</label>
                        <input type="file" name="package_image" id="packageImage" accept="image/*">
                        
                        <div class="image-preview-row">
                            <div id="packageImagePreview" class="image-holder">
                                Preview Gambar
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="save-btn">Simpan</button>
                        <button type="button" id="cancelPackageModal" class="cancel-btn">Batal</button>
                    </div>
                </form>

            </div>
        </div>

    </main>
</div>

<script src="/galeriseramikmbpg/admin/js/sidebar.js"></script>


<script>
const packageModal = document.getElementById('packageModal');
const packageForm = document.getElementById('packageForm');
const packageImage = document.getElementById('packageImage');
const imagePreview = document.getElementById('packageImagePreview');
const activityDropdownRow = document.getElementById('activityDropdownRow');
const activitySelect = document.getElementById('activitySelect');

const radioYes = document.getElementById('reqActivityYes');
const radioNo = document.getElementById('reqActivityNo');


//checkboxes for activity selection in package modal
const checkboxes = document.querySelectorAll('.activity-checkbox');

function toggleActivityDropdown() {
    if (radioYes.checked) {
        activityDropdownRow.style.display = 'flex';
        // HTML5 required attribute doesn't natively work on grouped checkboxes, 
        // we handle custom validation on submit instead.
    } else {
        activityDropdownRow.style.display = 'none';
        // Uncheck everything if "Tidak" is selected
        checkboxes.forEach(cb => cb.checked = false);
    }
}

// Optional: Add simple custom form validation on submission
packageForm.addEventListener('submit', function(e) {
    if (radioYes.checked) {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (!anyChecked) {
            e.preventDefault();
            alert('Sila pilih sekurang-kurangnya satu aktiviti jika memilih "Ya".');
        }
    }
});

radioYes.addEventListener('change', toggleActivityDropdown);
radioNo.addEventListener('change', toggleActivityDropdown);

packageImage.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) {
        imagePreview.innerHTML = 'Preview Gambar';
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        alert('Sila masukkan jenis file gambar sahaja.');
        this.value = '';
        imagePreview.innerHTML = 'Preview Gambar';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.innerHTML = `
            <div class="preview-box">
                <img src="${e.target.result}" alt="Preview">
            </div>
        `;
    }
    reader.readAsDataURL(file);
});

function openModal() { packageModal.classList.add('active'); }
function closeModal() { packageModal.classList.remove('active'); }

document.getElementById('openPackageModal').addEventListener('click', function() {
    packageForm.reset();
    document.getElementById('packageModalTitle').textContent = 'Tambah Pakej';
    document.getElementById('packageAction').value = 'add';
    document.getElementById('packageId').value = '';
    imagePreview.innerHTML = 'Preview Gambar';
    packageImage.setAttribute('required', 'required');
    toggleActivityDropdown();
    openModal();
});

document.getElementById('closePackageModal').addEventListener('click', closeModal);
document.getElementById('cancelPackageModal').addEventListener('click', closeModal);
packageModal.addEventListener('click', function(e) { if(e.target === packageModal) closeModal(); });

document.querySelectorAll('.edit-activity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const pkgId = this.dataset.id;

        fetch(`get_pakej.php?id=${pkgId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Gagal mengambil maklumat data pakej.');
                    return;
                }

                packageForm.reset();
                packageImage.removeAttribute('required'); 

                const pkg = data.package;
                
                document.getElementById('packageModalTitle').textContent = 'Edit Pakej';
                document.getElementById('packageAction').value = 'edit';
                document.getElementById('packageId').value = pkg.package_id;
                document.getElementById('packageName').value = pkg.package_name;
                document.getElementById('packageCapacity').value = pkg.capacity;
                document.getElementById('packageDescription').value = pkg.description;

                // Handle checkbox mappings
                if (parseInt(pkg.requires_activity_selection) === 1) {
                    radioYes.checked = true;
                    toggleActivityDropdown();
                    
                    // If your get_pakej.php returns an array of assigned activity IDs
                    if (data.assigned_activity_ids && Array.isArray(data.assigned_activity_ids)) {
                        checkboxes.forEach(cb => {
                            if (data.assigned_activity_ids.includes(parseInt(cb.value))) {
                                cb.checked = true;
                            }
                        });
                    }
                } else {
                    radioNo.checked = true;
                    toggleActivityDropdown();
                }

                if (pkg.image_url) {
                    imagePreview.innerHTML = `
                        <div class="preview-box">
                            <img src="../../../${pkg.image_url}" alt="Pakej">
                        </div>
                    `;
                } else {
                    imagePreview.innerHTML = 'Tiada Gambar';
                }

                openModal();
            })
            .catch(err => {
                console.error(err);
                alert('Ralat sistem berlaku semasa melancarkan borang edit.');
            });
    });
});



document.querySelectorAll('.delete-activity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Adakah anda pasti mahu memadam pakej data ini secara kekal?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'pakej_process.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="package_id" value="${this.dataset.id}">
        `;
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
</body>
</html>