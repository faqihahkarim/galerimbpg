<?php
$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
include '../db.php';

$productQuery = "
  SELECT 
    p.product_id,
    p.product_name,
    p.product_price,
    p.product_stock,
    p.product_type,
    p.product_motif,
    p.product_height,
    p.product_diameter,
    p.product_weight,
    p.status,
    MAX(CASE WHEN pi.is_main = 1 THEN pi.image_url END) AS main_image,
    GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order ASC SEPARATOR '|') AS all_images
  FROM products p
  LEFT JOIN product_images pi 
    ON p.product_id = pi.product_id
  WHERE p.status = 'active'
  GROUP BY p.product_id
  ORDER BY p.product_id DESC
";

$productResult = mysqli_query($conn, $productQuery);

function getStockStatus($stock) {
  if ($stock == 0) {
    return ['text' => 'Tiada Stok', 'class' => 'status-red'];
  } elseif ($stock < 10) {
    return ['text' => 'Stok Rendah', 'class' => 'status-yellow'];
  } else {
    return ['text' => 'Ada Stok', 'class' => 'status-green'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Produk Galeri Seramik</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="produk.css">
  <link rel="stylesheet" href="popup.css">

  <style>
    .status-green {
      background: #d9f7df;
      color: #137333;
    }

    .status-yellow {
      background: #fff4cc;
      color: #9a6700;
    }

    .status-red {
      background: #fde2e2;
      color: #b3261e;
    }

    .modal-status {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      margin: 10px 0 20px;
    }

    .product-card.hide {
      display: none;
    }

    .no-product {
      display: none;
      text-align: center;
      padding: 40px;
      font-size: 20px;
      color: #777;
    }

    .no-product.show {
      display: block;
    }
  </style>
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="product-section">
  <div class="product-container">

    <div class="product-filter">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari produk...">
      </div>

      <select id="categoryFilter">
        <option value="">Kategori</option>
        <option value="Pasu">Pasu</option>
        <option value="Pinggan">Pinggan</option>
        <option value="Jubin">Jubin</option>
      </select>

      <select id="priceFilter">
        <option value="">Harga</option>
        <option value="low-high">Rendah ke Tinggi</option>
        <option value="high-low">Tinggi ke Rendah</option>
      </select>

      <select id="stockFilter">
        <option value="">Stok</option>
        <option value="ada">Ada Stok</option>
        <option value="rendah">Stok Rendah</option>
        <option value="tiada">Tiada Stok</option>
      </select>
    </div>

    <div class="product-grid" id="productGrid">
      <?php while ($product = mysqli_fetch_assoc($productResult)): ?>

        <?php
          $stock = (int)$product['product_stock'];
          $stockStatus = getStockStatus($stock);

          $mainImage = !empty($product['main_image'])
            ? $base . $product['main_image']
            : $base . "assets/images/no-image.png";

          $allImages = !empty($product['all_images'])
            ? $product['all_images']
            : "";
        ?>

        <div class="product-card"
          data-name="<?= htmlspecialchars($product['product_name']); ?>"
          data-display-name="<?= htmlspecialchars($product['product_name']); ?>"
          data-price="<?= $product['product_price']; ?>"
          data-price-display="RM <?= number_format($product['product_price'], 2); ?>"          data-stock="<?= $stock; ?>"
                    data-stock-status="<?= $stockStatus['text']; ?>"
          data-stock-class="<?= $stockStatus['class']; ?>"
          data-image="<?= htmlspecialchars($mainImage); ?>"
          data-images="<?= htmlspecialchars($allImages); ?>"
          data-jenis="<?= htmlspecialchars($product['product_type']); ?>"
          data-motif="<?= htmlspecialchars($product['product_motif']); ?>"
          data-tinggi="<?= htmlspecialchars($product['product_height']); ?> cm"
          data-diameter="<?= htmlspecialchars($product['product_diameter']); ?> cm"
          data-berat="<?= htmlspecialchars($product['product_weight']); ?> kg">

          <img src="<?= htmlspecialchars($mainImage); ?>" alt="<?= htmlspecialchars($product['product_name']); ?>">

          <div class="product-info">
            <h3><?= htmlspecialchars($product['product_name']); ?></h3>
            <p class="price">RM <?= number_format($product['product_price'], 2); ?></p>

            <?php
              if ($stock == 0) {
                $stockClass = "red";
                $stockText = "Tiada Stok";
              } elseif ($stock < 10) {
                $stockClass = "yellow";
                $stockText = "Stok Rendah";
              } else {
                $stockClass = "green";
                $stockText = "Ada Stok";
              }
              ?>

              <p class="stock <?= $stockClass; ?>">
                <?= $stockText; ?>
              </p>
          </div>
        </div>

      <?php endwhile; ?>
    </div>

    <div class="no-product" id="noProduct">
      Tiada produk dijumpai.
    </div>

  </div>
</section>



<!-- produk detail modal (POP-UP)-->
 <div class="product-modal" id="productModal">
  <div class="modal-box">

    <button class="modal-close" id="modalClose">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="modal-content">

      <div class="modal-left">
        <img id="modalImage" src="" alt="Product Image">

        <div class="modal-thumbs">
          <img id="modalThumb1" class="modal-thumb" src="">
          <img id="modalThumb2" class="modal-thumb" src="">
          <img id="modalThumb3" class="modal-thumb" src="">
        </div>
      </div>

      <div class="modal-right">
        <h2 id="modalName"></h2>
        <h3 id="modalPrice"></h3>
        <span id="modalStatus" class="modal-status"></span>

        <table class="modal-table">
          <tr>
            <td>Jenis Produk</td>
            <td id="modalJenis"></td>
          </tr>
          <tr>
            <td>Motif</td>
            <td id="modalMotif"></td>
          </tr>
          <tr>
            <td>Tinggi</td>
            <td id="modalTinggi"></td>
          </tr>
          <tr>
            <td>Diameter</td>
            <td id="modalDiameter"></td>
          </tr>
          <tr>
            <td>Berat</td>
            <td id="modalBerat"></td>
          </tr>
          <tr>
            <td>Stok</td>
            <td id="modalStock"></td>
          </tr>
        </table>
      </div>

    </div>

    <div class="modal-note" id="modalNote">
      <i class="fa-solid fa-circle-info"></i>
      Produk ini tiada dalam stok. Sila lawati galeri untuk mendapatkan produk lain
    </div>

  </div>
</div>

<script src="product_modal.js"></script>

<!-- filter script -->
<script>
const base = "<?= $base; ?>";

const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const priceFilter = document.getElementById("priceFilter");
const stockFilter = document.getElementById("stockFilter");
const productGrid = document.getElementById("productGrid");
const noProduct = document.getElementById("noProduct");

function getStockFilterStatus(stock) {
  stock = Number(stock);

  if (stock === 0) return "tiada";
  if (stock < 10) return "rendah";
  return "ada";
}

function filterProducts() {
  const searchValue = searchInput.value.toLowerCase();
  const categoryValue = categoryFilter.value;
  const stockValue = stockFilter.value;

  const cards = Array.from(document.querySelectorAll(".product-card"));
  let visibleCount = 0;

  cards.forEach(card => {
    const name = card.dataset.name;
    const jenis = card.dataset.jenis;
    const stock = Number(card.dataset.stock);
    const stockStatus = getStockFilterStatus(stock);

    const matchSearch = name.includes(searchValue);
    const matchCategory = categoryValue === "" || jenis === categoryValue;
    const matchStock = stockValue === "" || stockStatus === stockValue;

    if (matchSearch && matchCategory && matchStock) {
      card.classList.remove("hide");
      visibleCount++;
    } else {
      card.classList.add("hide");
    }
  });

  noProduct.classList.toggle("show", visibleCount === 0);
}

function sortProducts() {
  const sortValue = priceFilter.value;
  const cards = Array.from(document.querySelectorAll(".product-card"));

  if (sortValue === "low-high") {
    cards.sort((a, b) => Number(a.dataset.price) - Number(b.dataset.price));
  } else if (sortValue === "high-low") {
    cards.sort((a, b) => Number(b.dataset.price) - Number(a.dataset.price));
  }

  cards.forEach(card => productGrid.appendChild(card));
}

searchInput.addEventListener("input", filterProducts);
categoryFilter.addEventListener("change", filterProducts);
stockFilter.addEventListener("change", filterProducts);

priceFilter.addEventListener("change", () => {
  sortProducts();
  filterProducts();
});
</script>

<?php include '../components/footer.php'; ?>




</body>
</html>