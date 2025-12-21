<?php
// includes/header.php - Version avec bouton menu
?>
<header class="header">
    <div class="header-left">
        <!-- BOUTON MENU BURGER MOBILE -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu principal">
            <span class="menu-icon">☰</span>
        </button>

        <div class="logo">
            <h1>🍻 BRALIMA</h1>
            <span class="logo-subtitle">MegaData</span>
        </div>
    </div>

    <div class="header-right">
        <div class="user-menu">
            <div class="user-info">
                <span class="user-name"><?php echo $_SESSION['nom_complet']; ?></span>
                <span class="user-role"><?php echo $_SESSION['role']; ?></span>
            </div>
            <div class="dropdown">
                <button class="dropdown-toggle" aria-label="Menu utilisateur">
                    👤
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php" class="dropdown-item">📋 Mon Profil</a>
                    <a href="logout.php" class="dropdown-item logout">🚪 Déconnexion</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Overlay pour mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    /* Styles pour le bouton menu */
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #2d3748;
        padding: 0.5rem;
        margin-right: 1rem;
        border-radius: 0.25rem;
        transition: background-color 0.3s;
    }

    .mobile-menu-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 767px) {
        .mobile-menu-btn {
            display: block;
        }

        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed;
            z-index: 999;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-overlay {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }
    }

    @media (min-width: 768px) {
        .sidebar {
            transform: translateX(0) !important;
        }
    }
</style>

<script>
    // Script pour gérer le menu mobile
    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (menuBtn && sidebar) {
            // Ouvrir/fermer le menu
            menuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                if (overlay) overlay.classList.toggle('active');
            });

            // Fermer avec l'overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            // Fermer avec Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                }
            });

            // Fermer automatiquement sur desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 767) {
                    sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                }
            });
        }
    });
</script>