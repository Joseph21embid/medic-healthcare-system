document.querySelectorAll("[data-toggle-password]").forEach(function (button) {
    button.addEventListener("click", function () {
        var inputId = button.getAttribute("data-toggle-password");
        var input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        var isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        button.classList.toggle("is-visible", isHidden);
        button.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
    });
});

document.querySelectorAll("[data-google-auth]").forEach(function (button) {
    button.addEventListener("click", function () {
        var mode = button.getAttribute("data-google-auth") || "signup";
        var selectedRole = document.querySelector('input[name="role"]:checked');
        var hiddenRole = document.querySelector('input[name="role"][type="hidden"]');
        var role = "patient";

        if (selectedRole) {
            role = selectedRole.value;
        }

        if (hiddenRole) {
            role = hiddenRole.value;
        }

        window.location.href = "../auth/google_redirect.php?mode=" + encodeURIComponent(mode) + "&role=" + encodeURIComponent(role);
    });
});

var signupSelector = document.querySelector("[data-signup-selector]");

if (signupSelector) {
    var selectorTrigger = signupSelector.querySelector("[data-selector-trigger]");
    var selectorMenu = signupSelector.querySelector("[data-selector-menu]");

    if (selectorTrigger && selectorMenu) {
        selectorTrigger.addEventListener("click", function () {
            var isOpen = signupSelector.classList.contains("is-open");

            if (isOpen) {
                signupSelector.classList.remove("is-open");
                selectorTrigger.setAttribute("aria-expanded", "false");
            } else {
                signupSelector.classList.add("is-open");
                selectorTrigger.setAttribute("aria-expanded", "true");
            }
        });

        document.addEventListener("click", function (event) {
            if (!signupSelector.contains(event.target)) {
                signupSelector.classList.remove("is-open");
                selectorTrigger.setAttribute("aria-expanded", "false");
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                signupSelector.classList.remove("is-open");
                selectorTrigger.setAttribute("aria-expanded", "false");
            }
        });
    }
}
