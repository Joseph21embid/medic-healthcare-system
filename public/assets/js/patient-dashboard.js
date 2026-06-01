var sidebar = document.getElementById("patientSidebar");
var sidebarToggle = document.getElementById("sidebarToggle");
var sidebarOverlay = document.getElementById("sidebarOverlay");

function openSidebar() {
    if (sidebar) {
        sidebar.classList.add("is-open");
    }

    document.body.classList.add("sidebar-open");
}

function closeSidebar() {
    if (sidebar) {
        sidebar.classList.remove("is-open");
    }

    document.body.classList.remove("sidebar-open");
}

if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
        if (sidebar && sidebar.classList.contains("is-open")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function () {
        closeSidebar();
    });
}

document.addEventListener("keydown", function (event) {
    if (event.key == "Escape") {
        closeSidebar();
    }
});
