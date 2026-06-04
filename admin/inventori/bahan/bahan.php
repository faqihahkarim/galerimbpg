<?php 
session_start();
$base="/web/galeriseramikmbpg/";

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../../db.php';
include '../../timeout.php';

$materialQuery = "
    SELECT * FROM materials
    WHERE status = 'active'
    ORDER BY material_id DESC
";


// =====================================================
// PAGINATION CALCULATIONS & CONFIGURATIONS
// =====================================================
$limit = 10; // Number of items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Count total active materials for pagination
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM materials WHERE status = 'active'");
$totalMaterials = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalMaterials / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// Paginated main material query string
$materialQuery = "
    SELECT * FROM materials
    WHERE status = 'active'
    ORDER BY material_id DESC
    LIMIT $limit OFFSET $offset
";

$materialResult = mysqli_query($conn, $materialQuery);

// Flash message
$flashMessage = '';
$flashClass = '';

if (isset($_GET['success'])) {
    $flashClass = 'success-alert';

    if ($_GET['success'] === 'material_added') {
        $flashMessage = 'Bahan berjaya ditambah.';
    } elseif ($_GET['success'] === 'material_updated') {
        $flashMessage = 'Bahan berjaya dikemaskini.';
    } elseif ($_GET['success'] === 'material_deleted') {
        $flashMessage = 'Bahan berjaya dipadam.';
    } elseif ($_GET['success'] === 'stock_updated') {
        $flashMessage = 'Stok berjaya dikemaskini.';
    }
}

if (isset($_GET['error'])) {
    $flashClass = 'error-alert';

    switch ($_GET['error']) {
        case 'need_images':
            $flashMessage = 'Sila masukkan tepat gambar bahan.';
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
    <title>Pengurusan Bahan</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/inventory.css">
    <link rel="stylesheet" href="../../css/tempahan.css">
    <link rel="stylesheet" href="../../css/bahan.css">
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
                <p>Bahan</p>
            </div>
        </header>

        <?php if (!empty($flashMessage)): ?>
            <div class="alert <?= htmlspecialchars($flashClass) ?>">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="booking-panel">

            <div class="material-actions">
                <div class="legend">
                    <span>Stok Rendah</span>
                    <span class="legend-box low"></span>

                    <span>Tiada Stok</span>
                    <span class="legend-box empty"></span>
                </div>

                <button type="button" id="openmaterialModal" class="add-material-btn">
                    <i class="fa-solid fa-plus"></i> Tambah Bahan
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Bahan</th>
                            <th>Jenama</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Gambar</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($materialResult && mysqli_num_rows($materialResult) > 0): ?>

                        <?php while ($material = mysqli_fetch_assoc($materialResult)): ?>

                            <?php
                                $rowClass = '';

                                if ($material['stock_status'] === 'Stok Rendah') {
                                    $rowClass = 'low-stock-row';
                                } elseif ($material['stock_status'] === 'Tiada Stok') {
                                    $rowClass = 'no-stock-row';
                                }

                                $imagePath = !empty($material['material_image'])
                                    ? "../../../" . $material['material_image']
                                    : "../../../assets/images/materials/no-image.png";
                            ?>

                            <tr class="<?= htmlspecialchars($rowClass) ?>">
                                <td><?= htmlspecialchars($material['material_id']) ?></td>
                                <td><?= htmlspecialchars($material['material_name']) ?></td>
                                <td><?= htmlspecialchars($material['material_brand']) ?></td>
                                <td>RM <?= number_format((float)$material['material_price'], 2) ?></td>

                                <td 
                                    class="editable-stock"
                                    data-id="<?= htmlspecialchars($material['material_id']) ?>"
                                    title="Double click untuk edit stok"
                                >
                                    <?= htmlspecialchars($material['material_stock'] ?? '0') ?>
                                </td>

                                <td>
                                    <img 
                                        src="<?= htmlspecialchars($imagePath) ?>" 
                                        class="material-img" 
                                        alt="Bahan"
                                    >
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="edit-material-btn"
                                        data-id="<?= htmlspecialchars($material['material_id']) ?>"
                                    >
                                        Edit
                                    </button>
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="delete-material-btn"
                                        data-id="<?= htmlspecialchars($material['material_id']) ?>"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada bahan yang ditemukan.</td>
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

        <div class="material-modal" id="materialModal">
            <div class="material-modal-card">

                <div class="modal-header">
                    <h2 id="materialModalTitle">Tambah Bahan</h2>
                    <button type="button" id="closeMaterialModal">&times;</button>
                </div>

                <form 
                    action="bahan_process.php" 
                    method="POST" 
                    enctype="multipart/form-data"
                    id="materialForm"
                >
                    <input type="hidden" name="action" id="materialAction" value="add">
                    <input type="hidden" name="material_id" id="materialId">

                    <input type="hidden" name="existing_images" id="existingImages">
                    <input type="hidden" name="deleted_images" id="deletedImages">

                    <div class="form-grid two">

                        <div class="form-group">
                            <label>Nama Bahan</label>
                            <input type="text" name="material_name" id="materialName" required>
                        </div>

                        <div class="form-group">
                            <label>Jenama Bahan</label>
                            <input type="text" name="material_brand" id="materialBrand" required>
                        </div>

                        <div class="form-group">
                            <label>Harga</label>
                            <input type="number" step="0.01" name="material_price" id="materialPrice" min="0" required>
                        </div>

                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="material_stock" id="materialStock" min="0" required>
                        </div>

                    </div>

                    <br>

                    <label><strong>Masukkan Gambar bahan</strong></label>
                    <input 
                        type="file" 
                        name="material_images[]" 
                        id="materialImages" 
                        accept="image/*"
                    >

                    <div class="image-preview-row" id="imagePreviewRow">
                        <div class="image-holder">Preview</div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="save-btn" id="materialSaveBtn">Simpan</button>
                        <button type="button" class="cancel-btn" id="cancelmaterialModal">Batal</button>
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
const materialModal = document.getElementById('materialModal');
const materialForm = document.getElementById('materialForm');
const materialImages = document.getElementById('materialImages');
const imagePreviewRow = document.getElementById('imagePreviewRow');

let existingImagesArray = [];
let deletedImagesArray = [];


// =====================================================
// FUNCTION: Reset image preview kepada empty holder
// =====================================================
function resetImagePreview() {
    imagePreviewRow.innerHTML = `
        <div class="image-holder">Preview</div>
    `;
}


// =====================================================
// FUNCTION: Buka modal
// =====================================================
function openModal() {
    materialModal.style.display = 'flex';
}


// =====================================================
// FUNCTION: Tutup modal
// =====================================================
function closeModal() {
    materialModal.style.display = 'none';
}


// =====================================================
// FUNCTION: Reset form untuk tambah bahan baru
// =====================================================
function resetmaterialFormForAdd() {
    materialForm.reset();

    document.getElementById('materialModalTitle').textContent = 'Tambah Bahan';
    document.getElementById('materialAction').value = 'add';
    document.getElementById('materialId').value = '';

    existingImagesArray = [];
    deletedImagesArray = [];

    document.getElementById('existingImages').value = '';
    document.getElementById('deletedImages').value = '';

    resetImagePreview();
}


// =====================================================
// FUNCTION: Papar gambar lama semasa edit (UPDATED FOR SINGLE IMAGE)
// =====================================================
function showExistingImages(images) {
    // 1. Reset container
    imagePreviewRow.innerHTML = '';

    existingImagesArray = images;
    deletedImagesArray = [];

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);

    // 2. Jika ada data gambar, terus render element img tunggal tanpa bergantung pada array grid index
    if (images && images.length > 0 && images[0].image_url) {
        const image = images[0];
        
        imagePreviewRow.innerHTML = `
            <div class="preview-box">
                <img src="../../../${image.image_url}" alt="Bahan" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                <button 
                    type="button" 
                    class="remove-image-btn" 
                    data-image-id="${image.image_id}"
                    data-index="0"
                    style="position: absolute; background: red; color: white; border: none; cursor: pointer;"
                >
                    &times;
                </button>
            </div>
        `;
    } else {
        // Jika tiada gambar langsung, tunjuk balik placeholder default
        resetImagePreview();
    }
}


// =====================================================
// FUNCTION: Delete gambar lama dari preview semasa edit
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

    if(imagePreviewRow.children[index]) {
        imagePreviewRow.children[index].innerHTML = 'Preview';
    }
});


// =====================================================
// FUNCTION: Preview gambar baru sebelum upload
// =====================================================
materialImages.addEventListener('change', function() {
    const action = document.getElementById('materialAction').value;
    const files = Array.from(this.files);

    if (files.length > 1) {
        alert('Maksimum 1 gambar sahaja.');
        this.value = '';
        return;
    }

    if (action === 'add') {
        if (files.length !== 1) { 
            alert('Sila masukkan gambar bahan.');
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
        materialImages.value = '';
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {
        const holder = imagePreviewRow.children[index];
        if (holder) {
            holder.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
    };

    reader.readAsDataURL(file);
}


// =====================================================
// FUNCTION: Validate sebelum submit form
// =====================================================
materialForm.addEventListener('submit', function(e) {
    const action = document.getElementById('materialAction').value;
    const newImageCount = materialImages.files.length;
    const existingImageCount = existingImagesArray.length;
    const totalImages = existingImageCount + newImageCount;

    if (action === 'add' && newImageCount !== 1) {
        e.preventDefault();
        alert('Sila masukkan gambar bahan sebelum simpan.');
        return;
    }

    if (action === 'edit' && totalImages !== 1) {
        e.preventDefault();
        alert('Setiap bahan mesti mempunyai tepat 1 gambar.');
        return;
    }

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);
});


// =====================================================
// FUNCTION: Button tambah bahan
// =====================================================
document.getElementById('openmaterialModal').addEventListener('click', function() {
    resetmaterialFormForAdd();
    openModal();
});


// =====================================================
// FUNCTION: Button close dan cancel modal (FIXED ID CASE)
// =====================================================
document.getElementById('closeMaterialModal').addEventListener('click', closeModal);
document.getElementById('cancelmaterialModal').addEventListener('click', closeModal);


// =====================================================
// FUNCTION: Tutup modal bila click luar modal card
// =====================================================
materialModal.addEventListener('click', function(e) {
    if (e.target === materialModal) {
        closeModal();
    }
});


// =====================================================
// FUNCTION: Button edit bahan (FIXED EXTRANEOUS FIELDS)
// =====================================================
document.querySelectorAll('.edit-material-btn').forEach(button => {
    button.addEventListener('click', function() {
        const materialId = this.dataset.id;

        fetch(`get_bahan.php?id=${materialId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Gagal mendapatkan data bahan.');
                    return;
                }

                const material = data.material;
                const images = data.images;

                materialForm.reset();

                document.getElementById('materialModalTitle').textContent = 'Edit bahan';
                document.getElementById('materialAction').value = 'edit';
                document.getElementById('materialId').value = material.material_id;

                document.getElementById('materialName').value = material.material_name;
                document.getElementById('materialBrand').value = material.material_brand;
                document.getElementById('materialPrice').value = material.material_price;
                document.getElementById('materialStock').value = material.material_stock;

                materialImages.value = '';

                showExistingImages(images);
                openModal();
            })
            .catch(error => {
                console.error(error);
                alert('Ralat berlaku semasa membuka data bahan.');
            });
    });
});


// =====================================================
// FUNCTION: Button delete bahan
// =====================================================
document.querySelectorAll('.delete-material-btn').forEach(button => {
    button.addEventListener('click', function() {
        const materialId = this.dataset.id;
        const confirmDelete = confirm('Adakah anda pasti mahu padam bahan ini?');

        if (!confirmDelete) return;

        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = 'bahan_process.php';

        deleteForm.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="material_id" value="${materialId}">
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
        const materialId = this.dataset.id;
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
                updateStock(cell, materialId, input.value, oldStock);
            }

            if (e.key === 'Escape') {
                cell.textContent = oldStock;
            }
        });

        input.addEventListener('blur', function() {
            updateStock(cell, materialId, input.value, oldStock);
        });
    });
});


// =====================================================
// FUNCTION: Update stok ke update_stock.php
// =====================================================
function updateStock(cell, materialId, newStock, oldStock) {
    if (newStock === '' || Number(newStock) < 0) {
        alert('Stok tidak sah.');
        cell.textContent = oldStock;
        return;
    }

    fetch('update_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `material_id=${encodeURIComponent(materialId)}&material_stock=${encodeURIComponent(newStock)}`
    })
    .then(response => response.text())
    .then(text => {
        console.log('Response from update_stock.php:', text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            alert('update_stock.php tidak return JSON. Check Console untuk error sebenar.');
            cell.textContent = oldStock;
            return;
        }

        if (data.status === 'success') {
            cell.textContent = newStock;
            updateStockRowColor(cell, Number(newStock));
            showStockMessage('Stok berjaya dikemaskini.', 'success');
        } else {
            alert(data.message || 'Gagal update stok.');
            cell.textContent = oldStock;
        }
    })
    .catch(error => {
        console.error(error);
        alert('Ralat berlaku semasa update stok.');
        cell.textContent = oldStock;
    });
}

function updateStockRowColor(cell, stock) {
    const row = cell.closest('tr');
    row.classList.remove('low-stock-row', 'no-stock-row');

    if (stock === 0) {
        row.classList.add('no-stock-row');
    } else if (stock <= 5) {
        row.classList.add('low-stock-row');
    }
}

function showStockMessage(message) {
    let alertBox = document.querySelector('.stock-alert');

    if (!alertBox) {
        alertBox = document.createElement('div');
        alertBox.className = 'alert success-alert stock-alert';

        const topbar = document.querySelector('.topbar');
        topbar.insertAdjacentElement('afterend', alertBox);
    }

    alertBox.textContent = message;

    setTimeout(() => {
        alertBox.remove();
    }, 2500);
}
</script>

</body>
</html>