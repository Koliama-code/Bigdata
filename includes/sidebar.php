<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="../index.php" class="nav-link">
                📊 Tableau de Bord
            </a>
        </li>

        <li class="nav-item">
            <a href="../modules/ventes/liste.php" class="nav-link">
                💰 Ventes
            </a>
        </li>

        <li class="nav-item active">
            <!-- 🔥 CHANGEMENT ICI - Lien direct vers la liste -->
            <a href="../modules/produits/liste.php" class="nav-link">
                📦 Produits
            </a>
        </li>

        <li class="nav-item">
            <a href="../modules/clients/liste.php" class="nav-link">
                👥 Clients
            </a>
        </li>

        <li class="nav-item">
            <a href="../modules/rapports/ventes.php" class="nav-link">
                📈 Rapports
            </a>
        </li>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-section">Administration</li>
            <li class="nav-item">
                <a href="../modules/admin/utilisateurs.php" class="nav-link">
                    👨‍💼 Utilisateurs
                </a>
            </li>
            <li class="nav-item">
                <a href="../modules/admin/parametres.php" class="nav-link">
                    ⚙️ Paramètres
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>