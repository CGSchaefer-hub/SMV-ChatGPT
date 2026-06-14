document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll(".cm-registration-form");

    forms.forEach(function (form) {
        form.addEventListener("submit", function () {
            const button = form.querySelector("button[type='submit']");

            if (button) {
                button.disabled = true;
                button.innerText = "Wird gesendet...";
            }
        });
    });
});
