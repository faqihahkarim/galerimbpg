document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".product-card");
  const modal = document.getElementById("productModal");
  const closeBtn = document.getElementById("modalClose");

  cards.forEach(function (card) {
    card.addEventListener("click", function () {
      const image = card.dataset.image;

      document.getElementById("modalName").textContent = card.dataset.name;
      document.getElementById("modalPrice").textContent = card.dataset.price;
      document.getElementById("modalStock").textContent = card.dataset.stock;
      document.getElementById("modalStatus").textContent = card.dataset.status;
      document.getElementById("modalImage").src = image;

      document.getElementById("modalThumb1").src = image;
      document.getElementById("modalThumb2").src = image;
      document.getElementById("modalThumb3").src = image;

      document.getElementById("modalJenis").textContent = card.dataset.jenis;
      document.getElementById("modalMotif").textContent = card.dataset.motif;
      document.getElementById("modalTinggi").textContent = card.dataset.tinggi;
      document.getElementById("modalDiameter").textContent = card.dataset.diameter;
      document.getElementById("modalBerat").textContent = card.dataset.berat;

      modal.classList.add("active");
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