document.addEventListener("DOMContentLoaded", function () {
  const panels = Array.from(document.querySelectorAll(".station-panel"));
  const detailTitle = document.querySelector("#stationDetail h2");
  const detailText = document.querySelector("#stationDetail p");

  let activeIndex = 2; // middle card first

  const stationData = {
    mencorak: {
      title: "Zon 1: Sejarah Seramik dan Pasir Gudang Kraf  ",
      text: "Pengunjung dapat membaca dan mendalami sejarah seramik, termasuk sejarah seramik di Malaysia, sejarah Pasir Gudang Kraf, serta proses pembuatan seramik secara umum. Terdapat juga paparan tentang jenis-jenis seramik yang dihasilkan di Pasir Gudang Kraf."
    },
    membentuk: {
      title: "Zon 2: Kenali Bahan Pembuatan Seramik",
      text: "Zon ini menunjukkan bahan-bahan yang digunakan dalam pembuatan seramik, termasuk tanah liat, pasir, bahan kimia, dan bahan tambahan lainnya."
    },
    membakar: {
      title: "Zon 3: Proses Lengkap Pembuatan Seramik",
      text: "Pengunjung dapat melihat proses pembuatan seramik dari awal hingga selesai, termasuk proses pembentukan, pembakaran, dan finishing."
    },
    mewarna: {
      title: "Zon 4: Proses Mewarna Seramik",
      text: "Zon ini memaparkan proses mewarna dan kemasan akhir produk seramik."
    },
    pameran: {
      title: "Zon 5: Stesen Pameran",
      text: "Pengunjung boleh melihat hasil produk seramik yang telah siap dan dipamerkan di galeri."
    }
  };

  const positions = {
    "-2": { x: "-520px", rotate: "35deg", scale: "0.88", z: 1, opacity: 0.75 },
    "-1": { x: "-300px", rotate: "22deg", scale: "0.95", z: 2, opacity: 0.9 },
    "0":  { x: "-105px", rotate: "0deg",  scale: "1.10", z: 5, opacity: 1 },
    "1":  { x: "100px",  rotate: "-22deg", scale: "0.95", z: 2, opacity: 0.9 },
    "2":  { x: "320px",  rotate: "-35deg", scale: "0.88", z: 1, opacity: 0.75 }
  };

  function updateCarousel() {
    panels.forEach(function (panel, index) {
      const offset = index - activeIndex;
      const pos = positions[offset];

      if (!pos) {
        panel.style.opacity = "0";
        panel.style.pointerEvents = "none";
        return;
      }

      panel.style.setProperty("--x", pos.x);
      panel.style.setProperty("--rotate", pos.rotate);
      panel.style.setProperty("--scale", pos.scale);
      panel.style.setProperty("--z", pos.z);
      panel.style.setProperty("--opacity", pos.opacity);
      panel.style.pointerEvents = "auto";

      panel.classList.toggle("active", index === activeIndex);
    });

    const selectedStation = panels[activeIndex].dataset.station;
    detailTitle.textContent = stationData[selectedStation].title;
    detailText.textContent = stationData[selectedStation].text;
  }

  panels.forEach(function (panel, index) {
    panel.addEventListener("click", function () {
      activeIndex = index;
      updateCarousel();

      document.getElementById("stationDetail").scrollIntoView({
        behavior: "smooth",
        block: "center"
      });
    });
  });

  updateCarousel();
});