document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("menu-toggle");
    const overlay = document.querySelector(".overlay");
    const menus = document.querySelectorAll(".has-submenu");

    // =====================================================
    // MOBILE SIDEBAR TOGGLE MECHANICS
    // =====================================================
    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        });
    }

    // CLOSE SIDEBAR ON OUTSIDE SELECTION CLICK 
    document.addEventListener("click", (e) => {
        if (
            sidebar &&
            toggleBtn &&
            sidebar.classList.contains("active") &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)
        ) {
            sidebar.classList.remove("active");
            if (overlay) overlay.classList.remove("active");
        }
    });

    /// =====================================================
    // SUBMENU DROPDOWN
    // =====================================================
    menus.forEach(menu => {

        const link = menu.querySelector(".submenu-toggle");

        if (!link) return;

        link.addEventListener("click", function (e) {

            e.preventDefault();
            e.stopPropagation();

            // Toggle ONLY current menu
            menu.classList.toggle("open");

        });

    });

    // =====================================================
    // GENERIC BOOKING MODAL HANDLERS
    // =====================================================
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

    document.querySelectorAll(".booking-modal-close").forEach(button => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            const closestModal = this.closest(".booking-modal");
            if (closestModal) closestModal.classList.remove("active");
        });
    });

    document.querySelectorAll(".booking-modal").forEach(modal => {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
            }
        });
    });

});