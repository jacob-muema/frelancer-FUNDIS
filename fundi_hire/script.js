
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector(".menu-toggle");

    if (sidebar && toggleBtn) {
        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("active");
        });
    }
});