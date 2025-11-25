// Gestion des interactions utilisateur
document.addEventListener("DOMContentLoaded", function () {
  // Animation des champs de formulaire
  const inputs = document.querySelectorAll(".form-control");
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.parentElement.classList.add("focused");
    });

    input.addEventListener("blur", function () {
      if (!this.value) {
        this.parentElement.classList.remove("focused");
      }
    });
  });

  // Force du mot de passe
  const passwordInput = document.getElementById("password");
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      const strengthBar = document.querySelector(".strength-bar");
      if (strengthBar) {
        const strength = calculatePasswordStrength(this.value);
        strengthBar.className = "strength-bar " + strength.class;
        strengthBar.style.width = strength.width;
      }
    });
  }
});

function calculatePasswordStrength(password) {
  let strength = 0;

  if (password.length >= 6) strength++;
  if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
  if (password.match(/\d/)) strength++;
  if (password.match(/[^a-zA-Z\d]/)) strength++;

  const classes = [
    "strength-weak",
    "strength-fair",
    "strength-good",
    "strength-strong",
  ];
  const widths = ["25%", "50%", "75%", "100%"];

  return {
    class: classes[strength] || "strength-weak",
    width: widths[strength] || "0%",
  };
}
