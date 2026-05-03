document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".slide");
  const prev = document.querySelector(".prev");
  const next = document.querySelector(".next");
  const dotsContainer = document.querySelector(".dots");
  const hero = document.querySelector(".hero");

  if (!slides.length || !prev || !next || !dotsContainer || !hero) {
    console.log("Slider elements not found");
    return;
  }

  let index = 0;
  let interval;

  slides.forEach(function (slide, i) {
    const dot = document.createElement("span");

    dot.addEventListener("click", function () {
      showSlide(i);
    });

    dotsContainer.appendChild(dot);
  });

  const dots = document.querySelectorAll(".dots span");

  function showSlide(i) {
    slides[index].classList.remove("active");
    dots[index].classList.remove("active");

    index = i;

    slides[index].classList.add("active");
    dots[index].classList.add("active");
  }

  function nextSlide() {
    const i = (index + 1) % slides.length;
    showSlide(i);
  }

  function prevSlide() {
    const i = (index - 1 + slides.length) % slides.length;
    showSlide(i);
  }

  next.addEventListener("click", nextSlide);
  prev.addEventListener("click", prevSlide);

  function startAuto() {
    interval = setInterval(nextSlide, 5000);
  }

  function stopAuto() {
    clearInterval(interval);
  }

  hero.addEventListener("mouseover", stopAuto);
  hero.addEventListener("mouseout", startAuto);

  showSlide(0);
  startAuto();
});