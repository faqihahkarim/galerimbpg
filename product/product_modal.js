document.addEventListener("DOMContentLoaded", function () {
  const base = "/web/galeriseramikmbpg/";

  const cards = document.querySelectorAll(".product-card");
  const modal = document.getElementById("productModal");
  const closeBtn = document.getElementById("modalClose");

  const modalImage = document.getElementById("modalImage");
  const thumbs = document.querySelectorAll(".modal-thumb");

  cards.forEach(function (card) {
    card.addEventListener("click", function () {
      const mainImage = card.dataset.image;

      const images = card.dataset.images
        ? card.dataset.images.split("|").map(img => base + img)
        : [mainImage];

      document.getElementById("modalName").textContent = card.dataset.displayName;
      document.getElementById("modalPrice").textContent = card.dataset.priceDisplay;
      document.getElementById("modalStock").textContent = card.dataset.stock;

      const modalStatus = document.getElementById("modalStatus");
      modalStatus.textContent = card.dataset.stockStatus;
      modalStatus.className = "modal-status " + card.dataset.stockClass;

      modalImage.src = images[0] || mainImage;

      thumbs.forEach((thumb, index) => {
        if (images[index]) {
          thumb.src = images[index];
          thumb.style.display = "block";
          thumb.classList.toggle("active", index === 0);
        } else {
          thumb.src = "";
          thumb.style.display = "none";
          thumb.classList.remove("active");
        }
      });

      document.getElementById("modalJenis").textContent = card.dataset.jenis;
      document.getElementById("modalMotif").textContent = card.dataset.motif;
      document.getElementById("modalTinggi").textContent = card.dataset.tinggi;
      document.getElementById("modalDiameter").textContent = card.dataset.diameter;
      document.getElementById("modalBerat").textContent = card.dataset.berat;

      const modalNote = document.getElementById("modalNote");
      modalNote.style.display = Number(card.dataset.stock) === 0 ? "block" : "none";

      modal.classList.add("active");
    });
  });

  thumbs.forEach(function (thumb) {
    thumb.addEventListener("click", function () {
      modalImage.src = thumb.src;

      thumbs.forEach(t => t.classList.remove("active"));
      thumb.classList.add("active");
    });
  });

  closeBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });

  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});