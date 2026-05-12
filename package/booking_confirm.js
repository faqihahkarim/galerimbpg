console.log("booking confirm JS loaded");

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("bookingForm");
  const openBtn = document.getElementById("openConfirmModal");
  const modal = document.getElementById("confirmModal");
  const closeBtn = document.getElementById("closeConfirmModal");
  const editBtn = document.getElementById("editBooking");
  const confirmSubmit = document.getElementById("confirmSubmit");

  if (!openBtn||!modal||!closeBtn||!editBtn||!confirmSubmit) {
    console.error("One or more elements not found. Please check the IDs.");
    return;
  }

  openBtn.addEventListener("click", function () {
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    if (!validateActivityTotal()) {
      return;
    }

    document.getElementById("confirmOrganization").textContent =
      form.organization_name.value || "-";

    document.getElementById("confirmPerson").textContent =
      form.contact_person.value || "-";

    document.getElementById("confirmPhone").textContent =
      form.phone_number.value || "-";

    document.getElementById("confirmEmail").textContent =
      form.email.value || "-";

    document.getElementById("confirmParticipants").textContent =
      (form.total_participants.value || "0") + " orang";

    const dateSelect = document.querySelector('select[name="slot_date"]');
    const slotSelect = document.querySelector('select[name="slot_id"]');

    document.getElementById("confirmDate").textContent =
      dateSelect ? dateSelect.options[dateSelect.selectedIndex].text : "-";

    document.getElementById("confirmSlot").textContent =
      slotSelect ? slotSelect.options[slotSelect.selectedIndex].text : "-";

    const remark = form.admin_remark ? form.admin_remark.value : "";
    document.getElementById("confirmRemark").textContent =
      remark.trim() !== "" ? remark : "-";

    const activityBox = document.getElementById("confirmActivityBox");
    const activityList = document.getElementById("confirmActivities");
    activityList.innerHTML = "";

    const activityInputs = document.querySelectorAll(".activity-count");

    if (activityInputs.length > 0) {
      let hasActivity = false;

      activityInputs.forEach(function (input) {
        const value = parseInt(input.value) || 0;

        if (value > 0) {
          hasActivity = true;

          const item = input.closest(".activity-item");
          const name = item.querySelector(".activity-info h4").textContent;

          const li = document.createElement("li");
          li.textContent = name + ": " + value + " orang";
          activityList.appendChild(li);
        }
      });

      activityBox.style.display = hasActivity ? "block" : "none";
    } else {
      activityBox.style.display = "none";
    }

    modal.classList.add("active");
  });

  closeBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });

  editBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });

  confirmSubmit.addEventListener("click", function () {
    form.submit();
  });

  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});