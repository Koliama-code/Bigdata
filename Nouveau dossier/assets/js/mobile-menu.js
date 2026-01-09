// assets/js/mobile-menu.js - Gestion simplifiée du menu mobile

class MobileMenu {
  constructor() {
    this.menuToggle = document.getElementById("menuToggle");
    this.sidebar = document.querySelector(".sidebar");
    this.overlay = null;

    this.init();
  }

  init() {
    if (!this.menuToggle || !this.sidebar) return;

    // Événement du bouton menu
    this.menuToggle.addEventListener("click", (e) => this.toggleMenu(e));

    // Fermer le menu en redimensionnant
    window.addEventListener("resize", () => this.handleResize());

    // Fermer le menu avec Échap
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") this.closeMenu();
    });
  }

  toggleMenu(e) {
    e.stopPropagation();

    if (this.sidebar.classList.contains("active")) {
      this.closeMenu();
    } else {
      this.openMenu();
    }
  }

  openMenu() {
    this.sidebar.classList.add("active");
    this.menuToggle.classList.add("active");
    this.createOverlay();
    this.lockBodyScroll(true);
  }

  closeMenu() {
    this.sidebar.classList.remove("active");
    this.menuToggle.classList.remove("active");
    this.removeOverlay();
    this.lockBodyScroll(false);
  }

  createOverlay() {
    if (this.overlay) return;

    this.overlay = document.createElement("div");
    this.overlay.className = "sidebar-overlay";
    this.overlay.style.cssText = `
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        `;

    document.body.appendChild(this.overlay);

    // Fermer le menu en cliquant sur l'overlay
    this.overlay.addEventListener("click", () => this.closeMenu());
  }

  removeOverlay() {
    if (this.overlay) {
      this.overlay.remove();
      this.overlay = null;
    }
  }

  lockBodyScroll(lock) {
    if (lock) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
  }

  handleResize() {
    // Fermer le menu automatiquement sur desktop
    if (window.innerWidth >= 768) {
      this.closeMenu();
    }
  }
}

// Initialiser quand le DOM est chargé
document.addEventListener("DOMContentLoaded", () => {
  new MobileMenu();
});

// Exporter pour utilisation globale
window.MobileMenu = MobileMenu;
