<?php if (!isset($no_header)): ?>
    <header class="header">
        <div class="">
            <div class="logo">
                <h1>🍻 BRALIMA</h1>
                <span>MegaData</span>
            </div>
        </div>

        <div class="header-right">
            <div class="user-menu">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['nom_complet']; ?></span>
                    <span class="user-role"><?php echo $_SESSION['role']; ?></span>
                </div>
                <div class="dropdown">
                    <button class="dropdown-toggle">
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
<?php endif; ?>