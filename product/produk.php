<?php
$base = "/web/galeriseramikmbpg/";
$pageType = "inner";
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
</head>

<body>

<?php include '../components/navbar.php'; ?>

<section class="product-section">
  <div class="product-container">

    <div class="product-filter">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Cari produk...">
      </div>

      <select>
        <option>Kategori</option>
        <option>Pasu</option>
        <option>Pinggan</option>
        <option>Jubin</option>
      </select>

      <select>
        <option>Harga</option>
        <option>Rendah ke Tinggi</option>
        <option>Tinggi ke Rendah</option>
      </select>

      <select>
        <option>Stok</option>
        <option>Ada Stok</option>
        <option>Habis Stok</option>
      </select>
    </div>

    <div class="product-grid">

      <div class="product-card"
        data-name="Pasu Bunga Mediterranean"
        data-price="RM120.00"
        data-stock="0"
        data-status="Tiada Stok"
        data-image="assets/images/product3.png"
        data-jenis="Pasu"
        data-motif="Flora"
        data-tinggi="28 cm"
        data-diameter="18 cm"
        data-berat="1.4 kg">

        <img src="assets/images/product3.png" alt="Pasu Mediterranean">

        <div class="product-info">
            <h3>Pasu Bunga Mediterranean</h3>
            <p class="price">RM120.00</p>
            <p class="stock"><span class="red"></span> Stok: 0</p>
        </div>
    </div>

      <div class="product-card">
        <img src="<?= $base ?>../assets/images/product2.png" alt="Pasu Marble">
        <div class="product-info">
          <h3>Pasu Marble</h3>
          <p class="price">RM150.00</p>
          <p class="stock"><span class="yellow"></span> Stok: 5</p>
        </div>
      </div>

      <div class="product-card">
        <img src="<?= $base ?>../assets/images/product3.png" alt="Pasu Mediterranean">
        <div class="product-info">
          <h3>Pasu Mediterranean</h3>
          <p class="price">RM120.00</p>
          <p class="stock"><span class="red"></span> Stok: 0</p>
        </div>
      </div>

      <div class="product-card">
        <img src="<?= $base ?>../assets/images/product4.png" alt="Pasu Pink">
        <div class="product-info">
          <h3>Pasu Pink</h3>
          <p class="price">RM120.00</p>
          <p class="stock"><span class="green"></span> Stok: 15</p>
        </div>
      </div>

      <div class="product-card">
        <img src="<?= $base ?>../assets/images/product5.png" alt="Pasu Hokaido">
        <div class="product-info">
          <h3>Pasu Hokaido</h3>
          <p class="price">RM120.00</p>
          <p class="stock"><span class="green"></span> Stok: 15</p>
        </div>
      </div>

      <div class="product-card">
        <img src="<?= $base ?>../assets/images/product6.png" alt="Pasu Toil de Jouy">
        <div class="product-info">
          <h3>Pasu Toil de<br>Jouy</h3>
          <p class="price">RM220.00</p>
          <p class="stock"><span class="green"></span> Stok: 15</p>
        </div>
      </div>

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
          <img id="modalThumb1" src="" alt="">
          <img id="modalThumb2" src="" alt="">
          <img id="modalThumb3" src="" alt="">
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


<?php include '../components/footer.php'; ?>

</body>
</html>