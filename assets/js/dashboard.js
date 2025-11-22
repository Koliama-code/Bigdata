// assets/js/dashboard.js
document.addEventListener("DOMContentLoaded", function () {
  // Animation des cartes de statistiques
  const statCards = document.querySelectorAll(".stat-card");
  statCards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    card.classList.add("animate-in");
  });

  // Gestion du menu responsive
  const menuToggle = document.createElement("button");
  menuToggle.innerHTML = "☰";
  menuToggle.className = "menu-toggle";
  document.querySelector(".header-left").appendChild(menuToggle);

  menuToggle.addEventListener("click", function () {
    document.querySelector(".sidebar").classList.toggle("active");
  });

  // Fermer le menu en cliquant à l'extérieur
  document.addEventListener("click", function (event) {
    const sidebar = document.querySelector(".sidebar");
    const menuToggle = document.querySelector(".menu-toggle");

    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
      sidebar.classList.remove("active");
    }
  });
});
