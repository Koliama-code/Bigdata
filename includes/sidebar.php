<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="/bralima_app/index.php" class="nav-link">
                📊 Tableau de Bord
            </a>
        </li>

        <li class="nav-item">
            <a href="/bralima_app/modules/ventes/liste.php" class="nav-link">
                💰 Ventes
            </a>
        </li>

        <li class="nav-item active">
            <a href="/bralima_app/modules/produits/liste.php" class="nav-link">
                📦 Produits
            </a>
        </li>

        <li class="nav-item">
            <a href="/bralima_app/modules/clients/liste.php" class="nav-link">
                👥 Clients
            </a>
        </li>

        <li class="nav-item">
            <a href="/bralima_app/modules/rapports/ventes.php" class="nav-link">
                📈 Rapports
            </a>
        </li>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-section">Administration</li>
            <li class="nav-item">
                <a href="/bralima_app/modules/admin/utilisateurs.php" class="nav-link">
                    👨‍💼 Utilisateurs
                </a>
            </li>
            <li class="nav-item">
                <a href="/bralima_app/modules/admin/parametres.php" class="nav-link">
                    ⚙️ Paramètres
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>