// assets/js/responsive.js - Gestion du responsive

document.addEventListener("DOMContentLoaded", function () {
  // Menu burger pour mobile
  const menuToggle = document.createElement("button");
  menuToggle.className = "menu-toggle";
  menuToggle.innerHTML = "☰";
  menuToggle.setAttribute("aria-label", "Menu principal");

  // Ajouter le bouton menu
  const headerLeft = document.querySelector(".header-left");
  if (headerLeft) {
    headerLeft.appendChild(menuToggle);
  }

  // Gestion du clic sur le menu
  menuToggle.addEventListener("click", function () {
    document.querySelector(".sidebar").classList.toggle("active");
    document.querySelector(".main-content").classList.toggle("menu-open");
  });

  // Fermer le menu en cliquant à l'extérieur
  document.addEventListener("click", function (event) {
    const sidebar = document.querySelector(".sidebar");
    const menuToggle = document.querySelector(".menu-toggle");

    if (sidebar && menuToggle) {
      const isClickInsideSidebar = sidebar.contains(event.target);
      const isClickOnMenuToggle = menuToggle.contains(event.target);

      if (
        !isClickInsideSidebar &&
        !isClickOnMenuToggle &&
        sidebar.classList.contains("active")
      ) {
        sidebar.classList.remove("active");
        document.querySelector(".main-content").classList.remove("menu-open");
      }
    }
  });

  // Fermer le menu en appuyant sur Échap
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      const sidebar = document.querySelector(".sidebar");
      if (sidebar && sidebar.classList.contains("active")) {
        sidebar.classList.remove("active");
        document.querySelector(".main-content").classList.remove("menu-open");
      }
    }
  });

  // Adapter les tables pour mobile
  adaptTablesForMobile();

  // Gérer le redimensionnement de la fenêtre
  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      adaptTablesForMobile();
      updateSidebarState();
    }, 250);
  });

  // Fonction pour adapter les tables
  function adaptTablesForMobile() {
    document.querySelectorAll(".data-table").forEach((table) => {
      const wrapper =
        table.closest(".data-table-wrapper") || table.parentElement;
      if (window.innerWidth < 768) {
        if (!wrapper.classList.contains("data-table-wrapper")) {
          table.parentElement.classList.add("data-table-wrapper");
        }
      } else {
        wrapper.classList.remove("data-table-wrapper");
      }
    });
  }

  // Fonction pour mettre à jour l'état de la sidebar
  function updateSidebarState() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    if (window.innerWidth >= 768) {
      sidebar.classList.remove("active");
      mainContent.classList.remove("menu-open");
    }
  }

  // Améliorer l'accessibilité
  improveAccessibility();
});

// Améliorations d'accessibilité
function improveAccessibility() {
  // Ajouter des labels aux boutons sans texte
  document.querySelectorAll(".btn[title]").forEach((btn) => {
    if (!btn.getAttribute("aria-label")) {
      btn.setAttribute("aria-label", btn.getAttribute("title"));
    }
  });

  // Gérer le focus dans les modales
  const trapFocus = (element) => {
    const focusableElements = element.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusableElement = focusableElements[0];
    const lastFocusableElement =
      focusableElements[focusableElements.length - 1];

    element.addEventListener("keydown", function (e) {
      if (e.key === "Tab") {
        if (e.shiftKey) {
          if (document.activeElement === firstFocusableElement) {
            lastFocusableElement.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === lastFocusableElement) {
            firstFocusableElement.focus();
            e.preventDefault();
          }
        }
      }
    });
  };

  // Appliquer le trap focus aux modales
  document.querySelectorAll(".modal").forEach((modal) => {
    trapFocus(modal);
  });
}
