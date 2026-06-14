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
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".cm-toggle-form").forEach(function (btn) {

        btn.addEventListener("click", function () {

            const id = this.getAttribute("data-id");
            const form = document.getElementById("cm-form-" + id);

            if (!form) return;

            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }

        });

    });

});
