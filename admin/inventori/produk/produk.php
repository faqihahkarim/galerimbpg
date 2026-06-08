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

// Count total active products
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE status = 'active'");
$totalProducts = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalProducts / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// Paginated main product query string
$productQuery = "
    SELECT p.*, pi.image_url 
    FROM products p
    LEFT JOIN product_images pi 
        ON p.product_id = pi.product_id
        AND pi.is_main = 1
    WHERE p.status = 'active'
    ORDER BY p.product_id ASC
    LIMIT $limit OFFSET $offset
";

$productResult = mysqli_query($conn, $productQuery);

// Flash message
$flashMessage = '';
$flashClass = '';

if (isset($_GET['success'])) {
    $flashClass = 'success-alert';

    if ($_GET['success'] === 'product_added') {
        $flashMessage = 'Produk berjaya ditambah.';
    } elseif ($_GET['success'] === 'product_updated') {
        $flashMessage = 'Produk berjaya dikemaskini.';
    } elseif ($_GET['success'] === 'product_deleted') {
        $flashMessage = 'Produk berjaya dipadam.';
    } elseif ($_GET['success'] === 'stock_updated') {
        $flashMessage = 'Stok berjaya dikemaskini.';
    }
}

if (isset($_GET['error'])) {
    $flashClass = 'error-alert';

    switch ($_GET['error']) {
        case 'need_3_images':
            $flashMessage = 'Sila masukkan tepat 3 gambar produk.';
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
    <title>Pengurusan Produk</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../../css/style.css">
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
                <p>Produk</p>
            </div>
        </header>

        <?php if (!empty($flashMessage)): ?>
            <div class="alert <?= htmlspecialchars($flashClass) ?>">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="booking-panel">

            <div class="product-actions">
                <div class="legend">
                    <span>Stok Rendah</span>
                    <span class="legend-box low"></span>

                    <span>Tiada Stok</span>
                    <span class="legend-box empty"></span>
                </div>

                <button type="button" id="openProductModal" class="add-product-btn">
                    <i class="fa-solid fa-plus"></i> Tambah Produk
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Gambar</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($productResult && mysqli_num_rows($productResult) > 0): ?>

                        <?php while ($product = mysqli_fetch_assoc($productResult)): ?>

                            <?php
                                $rowClass = '';

                                if ($product['stock_status'] === 'Stok Rendah') {
                                    $rowClass = 'low-stock-row';
                                } elseif ($product['stock_status'] === 'Tiada Stok') {
                                    $rowClass = 'no-stock-row';
                                }

                                $imagePath = !empty($product['image_url'])
                                    ? "../../../" . $product['image_url']
                                    : "../../../assets/images/no-image.png";
                            ?>

                            <tr class="<?= htmlspecialchars($rowClass) ?>">
                                <td><?= htmlspecialchars($product['product_id']) ?></td>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td>RM <?= number_format((float)$product['product_price'], 2) ?></td>

                                <td 
                                    class="editable-stock"
                                    data-id="<?= htmlspecialchars($product['product_id']) ?>"
                                    title="Double click untuk edit stok"
                                >
                                    <?= htmlspecialchars($product['product_stock'] ?? '0') ?>
                                </td>

                                <td><?= htmlspecialchars($product['product_type']?? '') ?></td>
                                <td><?= htmlspecialchars($product['category'] ?? '') ?></td>

                                <td>
                                    <img 
                                        src="<?= htmlspecialchars($imagePath) ?>" 
                                        class="product-img" 
                                        alt="Produk"
                                    >
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="edit-product-btn"
                                        data-id="<?= htmlspecialchars($product['product_id']) ?>"
                                    >
                                        Edit
                                    </button>
                                </td>

                                <td>
                                    <button 
                                        type="button"
                                        class="delete-product-btn"
                                        data-id="<?= htmlspecialchars($product['product_id']) ?>"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada produk yang ditemukan.</td>
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

        <div class="product-modal" id="productModal">
            <div class="product-modal-card">

                <div class="modal-header">
                    <h2 id="productModalTitle">Tambah Produk</h2>
                    <button type="button" id="closeProductModal">&times;</button>
                </div>

                <form 
                    action="product_process.php" 
                    method="POST" 
                    enctype="multipart/form-data"
                    id="productForm"
                >
                    <input type="hidden" name="action" id="productAction" value="add">
                    <input type="hidden" name="product_id" id="productId">

                    <input type="hidden" name="existing_images" id="existingImages">
                    <input type="hidden" name="deleted_images" id="deletedImages">

                    <div class="form-grid two">

                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="product_name" id="productName" required>
                        </div>

                        <div class="form-group">
                            <label>Jenis Produk</label>
                            <select name="product_type" id="productType" required>
                                <option value="">-- Pilih Jenis Produk --</option>
                                <option value="Pasu">Pasu</option>
                                <option value="Pinggan">Pinggan</option>
                                <option value="Mangkuk">Mangkuk</option>
                                <option value="Tempat Hiasan">Tempat Hiasan</option>
                                <option value="Aksesori">Aksesori</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Kategori Produk</label>
                            <select name="category" id="productCategory" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Aktiviti">Aktiviti</option>
                                <option value="Jualan">Jualan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Motif Produk</label>
                            <input type="text" name="product_motif" id="productMotif" required>
                        </div>

                        <div class="form-group">
                            <label>Berat</label>
                            <input type="number" step="0.01" name="product_weight" id="productWeight">
                        </div>

                        <div class="form-group">
                            <label>Tinggi</label>
                            <input type="number" step="0.01" name="product_height" id="productHeight">
                        </div>

                        <div class="form-group">
                            <label>Diameter</label>
                            <input type="number" step="0.01" name="product_diameter" id="productDiameter">
                        </div>

                        <div class="form-group">
                            <label>Harga</label>
                            <input type="number" step="0.01" name="product_price" id="productPrice" min="0" required>
                        </div>

                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="product_stock" id="productStock" min="0" required>
                        </div>

                    </div>

                    <br>

                    <label><strong>Masukkan Gambar Produk</strong></label>
                    <input 
                        type="file" 
                        name="product_images[]" 
                        id="productImages" 
                        multiple 
                        accept="image/*"
                    >

                    <div class="image-preview-row" id="imagePreviewRow">
                        <div class="image-holder">Preview</div>
                        <div class="image-holder">Preview</div>
                        <div class="image-holder">Preview</div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="save-btn" id="productSaveBtn">Simpan</button>
                        <button type="button" class="cancel-btn" id="cancelProductModal">Batal</button>
                    </div>

                </form>

            </div>
        </div>

    </main>
</div>

<script src="/web/galeriseramikmbpg/admin/js/sidebar.js"></script>

<script>
// Remaining Script content is fully preserved exactly as originally defined.
const productModal = document.getElementById('productModal');
const productForm = document.getElementById('productForm');
const productImages = document.getElementById('productImages');
const imagePreviewRow = document.getElementById('imagePreviewRow');

let existingImagesArray = [];
let deletedImagesArray = [];

function resetImagePreview() {
    imagePreviewRow.innerHTML = `
        <div class="image-holder">Preview</div>
        <div class="image-holder">Preview</div>
        <div class="image-holder">Preview</div>
    `;
}

function openModal() {
    productModal.style.display = 'flex';
}

function closeModal() {
    productModal.style.display = 'none';
}

function resetProductFormForAdd() {
    productForm.reset();
    document.getElementById('productModalTitle').textContent = 'Tambah Produk';
    document.getElementById('productAction').value = 'add';
    document.getElementById('productId').value = '';
    existingImagesArray = [];
    deletedImagesArray = [];
    document.getElementById('existingImages').value = '';
    document.getElementById('deletedImages').value = '';
    resetImagePreview();
}

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
                <img src="../../../${image.image_url}" alt="Produk">
                <button type="button" class="remove-image-btn" data-image-id="${image.image_id}" data-index="${index}">&times;</button>
            </div>
        `;
    });
}

imagePreviewRow.addEventListener('click', function(e) {
    if (!e.target.classList.contains('remove-image-btn')) return;
    const imageId = e.target.dataset.imageId;
    const index = e.target.dataset.index;
    deletedImagesArray.push(imageId);
    existingImagesArray = existingImagesArray.filter(image => String(image.image_id) !== String(imageId));
    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);
    imagePreviewRow.children[index].innerHTML = 'Preview';
});

productImages.addEventListener('change', function() {
    const action = document.getElementById('productAction').value;
    const files = Array.from(this.files);

    if (files.length > 3) {
        alert('Maksimum 3 gambar sahaja.');
        this.value = '';
        return;
    }

    if (action === 'add') {
        if (files.length !== 3) {
            alert('Sila masukkan tepat 3 gambar produk.');
            this.value = '';
            resetImagePreview();
            return;
        }
        resetImagePreview();
        files.forEach((file, index) => { previewSelectedImage(file, index); });
    }

    if (action === 'edit') {
        let emptySlots = [];
        Array.from(imagePreviewRow.children).forEach((holder, index) => {
            if (holder.textContent.trim() === 'Preview') emptySlots.push(index);
        });

        if (files.length > emptySlots.length) {
            alert('Jumlah gambar melebihi slot kosong. Padam gambar lama dahulu jika mahu ganti.');
            this.value = '';
            return;
        }
        files.forEach((file, fileIndex) => { previewSelectedImage(file, emptySlots[fileIndex]); });
    }
});

function previewSelectedImage(file, index) {
    if (!file.type.startsWith('image/')) {
        alert('Sila pilih fail gambar sahaja.');
        productImages.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const holder = imagePreviewRow.children[index];
        holder.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(file);
}

productForm.addEventListener('submit', function(e) {
    const action = document.getElementById('productAction').value;
    const newImageCount = productImages.files.length;
    const existingImageCount = existingImagesArray.length;
    const totalImages = existingImageCount + newImageCount;

    if (action === 'add' && newImageCount !== 3) {
        e.preventDefault();
        alert('Sila masukkan tepat 3 gambar produk sebelum simpan.');
        return;
    }

    if (action === 'edit' && totalImages !== 3) {
        e.preventDefault();
        alert('Setiap produk mesti mempunyai tepat 3 gambar.');
        return;
    }

    document.getElementById('existingImages').value = JSON.stringify(existingImagesArray);
    document.getElementById('deletedImages').value = JSON.stringify(deletedImagesArray);
});

document.getElementById('openProductModal').addEventListener('click', function() {
    resetProductFormForAdd();
    openModal();
});

document.getElementById('closeProductModal').addEventListener('click', closeModal);
document.getElementById('cancelProductModal').addEventListener('click', closeModal);

productModal.addEventListener('click', function(e) {
    if (e.target === productModal) closeModal();
});

document.querySelectorAll('.edit-product-btn').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.id;
        fetch(`get_product.php?id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Gagal mendapatkan data produk.');
                    return;
                }
                const product = data.product;
                const images = data.images;
                productForm.reset();

                document.getElementById('productModalTitle').textContent = 'Edit Produk';
                document.getElementById('productAction').value = 'edit';
                document.getElementById('productId').value = product.product_id;
                document.getElementById('productName').value = product.product_name;
                document.getElementById('productType').value = product.product_type;
                document.getElementById('productCategory').value = product.category ?? '';
                document.getElementById('productMotif').value = product.product_motif;
                document.getElementById('productWeight').value = product.product_weight ?? '';
                document.getElementById('productHeight').value = product.product_height ?? '';
                document.getElementById('productDiameter').value = product.product_diameter ?? '';
                document.getElementById('productPrice').value = product.product_price;
                document.getElementById('productStock').value = product.product_stock;

                productImages.value = '';
                showExistingImages(images);
                openModal();
            })
            .catch(error => {
                console.error(error);
                alert('Ralat berlaku semasa membuka data produk.');
            });
    });
});

document.querySelectorAll('.delete-product-btn').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.id;
        const confirmDelete = confirm('Adakah anda pasti mahu padam produk ini?');
        if (!confirmDelete) return;

        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = 'product_process.php';
        deleteForm.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" value="${productId}">
        `;
        document.body.appendChild(deleteForm);
        deleteForm.submit();
    });
});

document.querySelectorAll('.editable-stock').forEach(cell => {
    cell.addEventListener('dblclick', function() {
        const productId = this.dataset.id;
        const oldStock = this.textContent.trim();
        if (this.querySelector('input')) return;

        this.innerHTML = `<input type="number" class="stock-input" value="${oldStock}" min="0">`;
        const input = this.querySelector('input');
        input.focus();
        input.select();

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') updateStock(cell, productId, input.value, oldStock);
            if (e.key === 'Escape') cell.textContent = oldStock;
        });
        input.addEventListener('blur', function() { updateStock(cell, productId, input.value, oldStock); });
    });
});

function updateStock(cell, productId, newStock, oldStock) {
    if (newStock === '' || Number(newStock) < 0) {
        alert('Stok tidak sah.');
        cell.textContent = oldStock;
        return;
    }

    fetch('update_stock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${encodeURIComponent(productId)}&product_stock=${encodeURIComponent(newStock)}`
    })
    .then(response => response.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch (error) {
            alert('update_stock.php tidak return JSON.');
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
        cell.textContent = oldStock;
    });
}

function updateStockRowColor(cell, stock) {
    const row = cell.closest('tr');
    row.classList.remove('low-stock-row', 'no-stock-row');
    if (stock === 0) row.classList.add('no-stock-row');
    else if (stock <= 5) row.classList.add('low-stock-row');
}

function showStockMessage(message) {
    let alertBox = document.querySelector('.stock-alert');
    if (!alertBox) {
        alertBox = document.createElement('div');
        alertBox.className = 'alert success-alert stock-alert';
        document.querySelector('.topbar').insertAdjacentElement('afterend', alertBox);
    }
    alertBox.textContent = message;
    setTimeout(() => { alertBox.remove(); }, 2500);
}
</script>
</body>
</html>