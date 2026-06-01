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
        var role = selectedRole ? selectedRole.value : "patient";

        window.location.href = "../auth/google_redirect.php?mode=" + encodeURIComponent(mode) + "&role=" + encodeURIComponent(role);
    });
});
