var navToggle = document.getElementById("navToggle");
var mainNav = document.getElementById("mainNav");

if (navToggle && mainNav) {
    navToggle.addEventListener("click", function () {
        mainNav.classList.toggle("is-open");
    });
}

var moduleData = {
    patients: {
        image: "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80",
        label: "Individual workspace",
        title: "A dashboard that begins with the individual profile.",
        text: "Medic starts by collecting individual information that can later power medical summaries, appointments, emergency response, and medication awareness."
    },
    hospitals: {
        image: "https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=1200&q=80",
        label: "Organization network",
        title: "A structured path for hospitals, clinics, and medical firms to join.",
        text: "Medical organizations will later complete verification questionnaires before getting full access to appointment and record management features."
    },
    emergency: {
        image: "https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=1200&q=80",
        label: "Emergency response",
        title: "Critical individual information prepared before urgent moments.",
        text: "The emergency layer is planned to connect individual location, medical summary, emergency contacts, and nearby organization response workflows."
    }
};

var moduleButtons = document.querySelectorAll("[data-module]");
var moduleImage = document.getElementById("moduleImage");
var moduleLabel = document.getElementById("moduleLabel");
var moduleTitle = document.getElementById("moduleTitle");
var moduleText = document.getElementById("moduleText");

moduleButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        var moduleName = button.getAttribute("data-module");
        var selectedModule = moduleData[moduleName];

        if (!selectedModule) {
            return;
        }

        moduleButtons.forEach(function (item) {
            item.classList.remove("active");
        });

        button.classList.add("active");
        moduleImage.src = selectedModule.image;
        moduleLabel.textContent = selectedModule.label;
        moduleTitle.textContent = selectedModule.title;
        moduleText.textContent = selectedModule.text;
    });
});

var revealItems = document.querySelectorAll(".module-card, .module-preview, .records-copy, .records-media, .emergency-section, .workflow-grid article");

revealItems.forEach(function (item) {
    item.classList.add("reveal-on-scroll");
});

var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
        }
    });
}, {
    threshold: 0.14
});

revealItems.forEach(function (item) {
    observer.observe(item);
});
