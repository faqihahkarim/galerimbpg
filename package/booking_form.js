document.addEventListener("DOMContentLoaded", function () {
  const activityItems = document.querySelectorAll(".activity-item");

  activityItems.forEach(function (item) {
    const minusBtn = item.querySelector(".minus-btn");
    const plusBtn = item.querySelector(".plus-btn");
    const input = item.querySelector(".activity-count");

    plusBtn.addEventListener("click", function () {
      let currentValue = parseInt(input.value) || 0;
      let maxValue = parseInt(input.max) || 0;

      if (currentValue < maxValue) {
        input.value = currentValue + 1;
      }
    });

    minusBtn.addEventListener("click", function () {
      let currentValue = parseInt(input.value) || 0;

      if (currentValue > 0) {
        input.value = currentValue - 1;
      }
    });
  });
});

function validateActivityTotal() {
  const totalInput = document.querySelector('input[name="total_participants"]');
  const activityInputs = document.querySelectorAll(".activity-count");

  if (!totalInput || activityInputs.length === 0) {
    return true;
  }

  const totalParticipants = parseInt(totalInput.value) || 0;

  let allocationTotal = 0;

  activityInputs.forEach(function (input) {
    allocationTotal += parseInt(input.value) || 0;
  });

  if (allocationTotal !== totalParticipants) {
    alert(
      "Jumlah agihan aktiviti mesti sama dengan jumlah peserta.\n\n" +
      "Jumlah peserta: " + totalParticipants + "\n" +
      "Jumlah agihan: " + allocationTotal
    );

    return false;
  }

  return true;
}