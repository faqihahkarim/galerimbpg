<?php 
session_start();
$base="/web/galeriseramikmbpg/";

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';
include '../../timeout.php';


// =====================================================
// PAGINATION CALCULATIONS & CONFIGURATIONS
// =====================================================
$limit = 10; // Number of items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Count total active activitys for pagination
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM activities WHERE status = 'active'");
$totalActivities = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalActivities / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$activityQuery = "
    SELECT a.*, ai.image_url 
    FROM activities a
    LEFT JOIN activity_images ai 
        ON a.activity_id = ai.activity_id
        AND ai.is_main = 1
    WHERE a.status = 'active' & 'inactive'
    AND a.status != 'deleted'
    ORDER BY a.activity_id ASC
";

$activityResult = mysqli_query($conn, $activityQuery);

// Flash message
$flashMessage = '';
$flashClass = '';

if (isset($_GET['success'])) {
    $flashClass = 'success-alert';

    if ($_GET['success'] === 'activity_added') {
        $flashMessage = 'Aktiviti berjaya ditambah.';
    } elseif ($_GET['success'] === 'activity_updated') {
        $flashMessage = 'Aktiviti berjaya dikemaskini.';
    } elseif ($_GET['success'] === 'activity_deleted') {
        $flashMessage = 'Aktiviti berjaya dipadam.';
    } 
}

if (isset($_GET['error'])) {
    $flashClass = 'error-alert';

    switch ($_GET['error']) {
        case 'need_3_images':
            $flashMessage = 'Sila masukkan tepat 3 gambar aktiviti.';
            break;
        case 'invalid_image_type':
            $flashMessage = 'Jenis gambar tidak sah. Gunakan JPG, JPEG, PNG atau WEBP.';
            break;
        case 'image_too_large':
            $flashMessage = 'Saiz gambar terlalu besar. Maksimum 5MB.';
            break;
        default:
            $flashMessage = 'Ralat berlaku. Sila cuba lagi.' . htmlspecialchars($_GET['error']);
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Aktiviti</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/activity.css">
    <link rel="stylesheet" href="../../css/inventory.css">
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
                <h1>Pengurusan Galeri</h1>
                <p>Aktiviti</p>
            </div>
        </header>

        <?php if (!empty($flashMessage)): ?>
            <div class="alert <?= htmlspecialchars($flashClass) ?>">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="booking-panel">

            <div class="activity-actions">
                <button type="button" id="openActivityModal" class="add-activity-btn">
                    <i class="fa-solid fa-plus"></i> Tambah Aktiviti
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Aktiviti</th>
                            <th>Penerangan</th>
                            <th>Harga (RM)</th>
                            <th>Peserta Target</th>
                            <th>Tempoh Masa</th>
                            <th>Bil Peserta/Slot</th>
                            <th>Status</th>
                            <th>Gambar</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($activityResult && mysqli_num_rows($activityResult) > 0): ?>

                        <?php while ($activity = mysqli_fetch_assoc($activityResult)): ?>

                            <?php

                                $rowClass = ($activity['status'] === 'inactive') ? 'inactive-row' : '';
                                $imagePath = !empty($activity['image_url'])
                                    ? "../../../" . $activity['image_url']
                                    : "../../../assets/images/no-image.png";
                            ?>

                            <tr class="<?= htmlspecialchars($rowClass) ?>">
                                <td><?= htmlspecialchars($activity['activity_id']) ?></td>
                                <td><?= htmlspecialchars($activity['activity_name']) ?></td>
                                <td><?= htmlspecialchars($activity['description']) ?></td>
                                <td><?= number_format((float)$activity['price'], 2) ?></td>
                                <td><?= htmlspecialchars($activity['target']) ?></td>
                                <td><?= htmlspecialchars($activity['duration']) ?></td>
                                <td><?= htmlspecialchars($activity['default_capacity']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($activity['status'])) ?></td>
                                <td>
                                    <img 
                                        src="<?= htmlspecialchars($imagePath) ?>" 
                                        class="activity-img" 
                                        alt="Aktiviti"
                                    >
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="edit-activity-btn"
                                        data-id="<?= htmlspecialchars($activity['activity_id']) ?>"
                                    >
                                        Edit
                                    </button>
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="delete-activity-btn"
                                        data-id="<?= htmlspecialchars($activity['activity_id']) ?>"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada aktiviti yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">

                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="page-btn">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $adjacents = 2; 

                    // Always print link to first page bound if sliding offset exists
                    if ($page > ($adjacents + 1)) {
                        echo '<a href="?page=1" class="page-btn">1</a>';
                        if ($page > ($adjacents + 2)) {
                            echo '<span class="page-dots" style="padding: 8px 12px; color: var(--text-soft);">...</span>';
                        }
                    }

                    // Dynamically calculate midframe boundaries
                    $startLoop = max(1, $page - $adjacents);
                    $endLoop   = min($totalPages, $page + $adjacents);

                    for ($i = $startLoop; $i <= $endLoop; $i++) {
                        $activeClass = ($page == $i) ? 'active-page' : '';
                        echo '<a href="?page=' . $i . '" class="page-btn ' . $activeClass . '">' . $i . '</a>';
                    }

                    // Always print link to tail page bound if trailing offset exists
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

        <!-- Modal tambah/edit aktiviti -->
        <div class="activity-modal" id="activityModal">
            <div class="activity-modal-card">

                <div class="modal-header">
                    <h2 id="activityModalTitle">Tambah Aktiviti</h2>
                    <button type="button" id="closeActivityModal">&times;</button>
                </div>

                <form 
                    action="activity_process.php" 
                    method="POST" 
                    enctype="multipart/form-data"
                    id="activityForm"
                >
                    <input type="hidden" name="action" id="activityAction" value="add">
                    <input type="hidden" name="activity_id" id="activityId">

                    <input type="hidden" name="existing_images" id="existingImages">
                    <input type="hidden" name="deleted_images" id="deletedImages">

                    <div class="form-grid two">

                        <div class="form-group">
                            <label>Nama Aktiviti</label>
                            <input type="text" name="activity_name" id="activityName" required>
                        </div>

                        <div class="form-group">
                            <label>Harga (RM)</label>
                            <input type="number" step="0.01" name="activity_price" id="activityPrice" min="0" required>
                        </div>

                        <div class="form-group full-width">
                            <label>Penerangan</label>
                            <textarea name="description" id="description" rows="3"></textarea>
                        </div>

                        
                        <div class="form-group">
                            <label>Peserta Target</label>
                            <input type="text" name="target" id="target" required>
                        </div>

                        <div class="form-group">
                            <label>Bil Peserta/slot</label>
                            <input type="number" name="default_capacity" id="default_capacity" min="1">
                        </div>

                        <div class="form-group">
                            <label>Tempoh Masa</label>
                            <input type="text" name="duration" id="duration">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <br>

                    <label><strong>Masukkan Gambar Aktiviti</strong></label>
                    <input 
                        type="file" 
                        name="activity_images[]" 
                        id="activityImages" 
                        multiple 
                        accept="image/*"
                    >

                    <div class="image-preview-row" id="imagePreviewRow">
                        <div class="image-holder">Preview</div>
                        <div class="image-holder">Preview</div>
                        <div class="image-holder">Preview</div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="save-btn" id="activitySaveBtn">Simpan</button>
                        <button type="button" class="cancel-btn" id="cancelActivityModal">Batal</button>
                    </div>

                </form>

            </div>
        </div>

    </main>
</div>

<script src="/galeriseramikmbpg/admin/js/sidebar.js"></script>

<script>
// =====================================================
// GLOBAL VARIABLE
// =====================================================
const activityModal = document.getElementById('activityModal');
const activityForm = document.getElementById('activityForm');
const activityImages = document.getElementById('activityImages');
const imagePreviewRow = document.getElementById('imagePreviewRow');

let existingImagesArray = [];
let deletedImagesArray = [];


// =====================================================
// FUNCTION: Reset image preview kepada 3 empty holder
// =====================================================
function resetImagePreview() {
    imagePreviewRow.innerHTML = `
        <div class="image-holder">Preview</div>
        <div class="image-holder">Preview</div>
        <div class="image-holder">Preview</div>
    `;
}


// =====================================================
// FUNCTION: Buka modal
// =====================================================
function openModal() {
    activityModal.style.display = 'flex';
}


// =====================================================
// FUNCTION: Tutup modal
// =====================================================
function closeModal() {
    activityModal.style.display = 'none';
}


// =====================================================
// FUNCTION: Reset form untuk tambah activity baru
// =====================================================
function resetActivityFormForAdd() {
    activityForm.reset();

    document.getElementById('activityModalTitle').textContent = 'Tambah Aktiviti';
    document.getElementById('activityAction').value = 'add';
    document.getElementById('activityId').value = '';

    existingImagesArray = [];
    deletedImagesArray = [];

    document.getElementById('existingImages').value = '';
    document.getElementById('deletedImages').value = '';

    resetImagePreview();
    document.getElementById('status').value = 'active';
}


// =====================================================
// FUNCTION: Papar gambar lama semasa edit
// =====================================================
function showExistingImages(images) {
    resetImagePreview();

    existingImagesArray = images;
    deletedImagesArray = [];

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);

    images.forEach((image, index) => {
        if (!image.image_url) return;

        const holder = imagePreviewRow.children[index];

        holder.innerHTML = `
            <div class="preview-box">
                <img src="../../../${image.image_url}" alt="Aktiviti">
                <button 
                    type="button" 
                    class="remove-image-btn" 
                    data-image-id="${image.image_id}"
                    data-index="${index}"
                >
                    &times;
                </button>
            </div>
        `;
    });
}


// =====================================================
// FUNCTION: Delete gambar lama dari preview semasa edit
// Nota: Gambar hanya betul-betul delete bila admin tekan Simpan
// =====================================================
imagePreviewRow.addEventListener('click', function(e) {
    if (!e.target.classList.contains('remove-image-btn')) return;

    const imageId = e.target.dataset.imageId;
    const index = e.target.dataset.index;

    deletedImagesArray.push(imageId);

    existingImagesArray = existingImagesArray.filter(image => {
        return String(image.image_id) !== String(imageId);
    });

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);

    imagePreviewRow.children[index].innerHTML = 'Preview';
});


// =====================================================
// FUNCTION: Preview gambar baru sebelum upload
// =====================================================
activityImages.addEventListener('change', function() {
    const action = document.getElementById('activityAction').value;
    const files = Array.from(this.files);

    if (files.length > 3) {
        alert('Maksimum 3 gambar sahaja.');
        this.value = '';
        return;
    }

    if (action === 'add') {
        if (files.length !== 3) {
            alert('Sila masukkan tepat 3 gambar aktiviti.');
            this.value = '';
            resetImagePreview();
            return;
        }

        resetImagePreview();

        files.forEach((file, index) => {
            previewSelectedImage(file, index);
        });
    }

    if (action === 'edit') {
        let emptySlots = [];

        Array.from(imagePreviewRow.children).forEach((holder, index) => {
            if (holder.textContent.trim() === 'Preview') {
                emptySlots.push(index);
            }
        });

        if (files.length > emptySlots.length) {
            alert('Jumlah gambar melebihi slot kosong. Padam gambar lama dahulu jika mahu ganti.');
            this.value = '';
            return;
        }

        files.forEach((file, fileIndex) => {
            previewSelectedImage(file, emptySlots[fileIndex]);
        });
    }
});


// =====================================================
// FUNCTION: Baca file image dan papar dalam holder
// =====================================================
function previewSelectedImage(file, index) {
    if (!file.type.startsWith('image/')) {
        alert('Sila pilih fail gambar sahaja.');
        activityImages.value = '';
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {
        const holder = imagePreviewRow.children[index];
        holder.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };

    reader.readAsDataURL(file);
}


// =====================================================
// FUNCTION: Validate sebelum submit form
// Add: wajib pilih 3 gambar baru
// Edit: gambar lama yang kekal + gambar baru mesti cukup 3
// =====================================================
activityForm.addEventListener('submit', function(e) {
    const action = document.getElementById('activityAction').value;
    const newImageCount = activityImages.files.length;
    const existingImageCount = existingImagesArray.length;
    const totalImages = existingImageCount + newImageCount;

    if (action === 'add' && newImageCount !== 3) {
        e.preventDefault();
        alert('Sila masukkan tepat 3 gambar aktiviti sebelum simpan.');
        return;
    }

    if (action === 'edit' && totalImages !== 3) {
        e.preventDefault();
        alert('Setiap aktiviti mesti mempunyai tepat 3 gambar.');
        return;
    }

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);
});


// =====================================================
// FUNCTION: Button tambah aktiviti
// =====================================================
document.getElementById('openActivityModal').addEventListener('click', function() {
    resetActivityFormForAdd();
    openModal();
});


// =====================================================
// FUNCTION: Button close dan cancel modal
// =====================================================
document.getElementById('closeActivityModal').addEventListener('click', closeModal);
document.getElementById('cancelActivityModal').addEventListener('click', closeModal);


// =====================================================
// FUNCTION: Tutup modal bila click luar modal card
// =====================================================
activityModal.addEventListener('click', function(e) {
    if (e.target === activityModal) {
        closeModal();
    }
});


// =====================================================
// FUNCTION: Button edit aktiviti
// Ambil data dari get_activity.php dan masukkan ke dalam modal
// =====================================================
document.querySelectorAll('.edit-activity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const activityId = this.dataset.id;

        fetch(`get_activity.php?id=${activityId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Gagal mendapatkan data aktiviti.');
                    return;
                }

                const activity = data.activity;
                const images = data.images;

                activityForm.reset();

                document.getElementById('activityModalTitle').textContent = 'Edit Aktiviti';
                document.getElementById('activityAction').value = 'edit';
                document.getElementById('activityId').value = activity.activity_id;

                //tukar sini ikut field dalam table aktiviti 
                document.getElementById('activityName').value = activity.activity_name;
                document.getElementById('description').value = activity.description;
                document.getElementById('activityPrice').value = activity.price;
                document.getElementById('target').value = activity.target;
                document.getElementById('duration').value = activity.duration;
                document.getElementById('default_capacity').value = activity.default_capacity;
                document.getElementById('status').value = activity.status;


                activityImages.value = '';

                showExistingImages(images);
                openModal();
            })
            .catch(error => {
                console.error(error);
                alert('Ralat berlaku semasa membuka data aktiviti.');
            });
    });
});


// =====================================================
// FUNCTION: Button delete aktiviti
// Hantar action delete ke activity_process.php
// =====================================================
document.querySelectorAll('.delete-activity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const activityId = this.dataset.id;

        const confirmDelete = confirm('Adakah anda pasti mahu padam aktiviti ini?');

        if (!confirmDelete) return;

        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = 'activity_process.php';

        deleteForm.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="activity_id" value="${activityId}">
        `;

        document.body.appendChild(deleteForm);
        deleteForm.submit();
    });
});


// =====================================================
// FUNCTION: Double click stok untuk fast edit
// =====================================================
document.querySelectorAll('.editable-stock').forEach(cell => {
    cell.addEventListener('dblclick', function() {
        const productId = this.dataset.id;
        const oldStock = this.textContent.trim();

        if (this.querySelector('input')) return;

        this.innerHTML = `
            <input 
                type="number" 
                class="stock-input" 
                value="${oldStock}" 
                min="0"
            >
        `;

        const input = this.querySelector('input');
        input.focus();
        input.select();

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                updateStock(cell, productId, input.value, oldStock);
            }

            if (e.key === 'Escape') {
                cell.textContent = oldStock;
            }
        });

        input.addEventListener('blur', function() {
            updateStock(cell, productId, input.value, oldStock);
        });
    });
});

</script>

</body>
</html>