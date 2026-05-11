document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("menu-toggle");
    const overlay = document.querySelector(".overlay");
    const menus = document.querySelectorAll(".has-submenu");

    // TOGGLE SIDEBAR
    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        });
    }

    // CLICK ANYWHERE OUTSIDE CLOSE SIDEBAR
    document.addEventListener("click", (e) => {
        if (
            sidebar &&
            toggleBtn &&
            sidebar.classList.contains("active") &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)
        ) {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        }
    });

    // SUBMENU
    menus.forEach(menu => {
        const link = menu.querySelector(":scope > a");

        if (!link) return;

        link.addEventListener("click", function (e) {
            e.preventDefault();

            menus.forEach(item => {
                if (item !== menu) {
                    item.classList.remove("active");
                }
            });

            menu.classList.toggle("active");
        });
    });

    // BOOKING MODAL OPEN
    document.querySelectorAll(".booking-detail-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const modalId = this.getAttribute("data-modal");
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.classList.add("active");
            }
        });
    });

    // BOOKING MODAL CLOSE BUTTON
    document.querySelectorAll(".booking-modal-close").forEach(button => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            this.closest(".booking-modal").classList.remove("active");
        });
    });

    // CLICK OUTSIDE MODAL CARD TO CLOSE
    document.querySelectorAll(".booking-modal").forEach(modal => {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
            }
        });
    });

});