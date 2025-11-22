<?php
include 'includes/config.php';
include 'includes/auth.php';

// Vérifier la connexion
requireLogin();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Tableau de Bord</h1>
                <p>Bienvenue, <strong><?php echo $_SESSION['nom_complet']; ?></strong></p>
            </div>

            <!-- Cartes de Statistiques -->
            <div class="stats-grid">
                <?php
                $pdo = getDBConnection();

                // CHIFFRE D'AFFAIRES - Version flexible
                try {
                    $stmt = $pdo->query("SELECT SUM(montant_total) as ca_total FROM ventes WHERE statut = 'confirmee'");
                    $ca_total = $stmt->fetch()['ca_total'] ?? 0;
                } catch (Exception $e) {
                    $ca_total = 0;
                }

                // NOMBRE DE VENTES - Version flexible  
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total_ventes FROM ventes WHERE statut = 'confirmee'");
                    $total_ventes = $stmt->fetch()['total_ventes'] ?? 0;
                } catch (Exception $e) {
                    $total_ventes = 0;
                }

                // NOMBRE DE PRODUITS - Version flexible
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total_produits FROM produits");
                    $total_produits = $stmt->fetch()['total_produits'] ?? 0;
                } catch (Exception $e) {
                    $total_produits = 0;
                }

                // PRODUITS EN RUPTURE - Version flexible
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as rupture_stock FROM produits WHERE quantite_stock <= stock_alerte");
                    $rupture_stock = $stmt->fetch()['rupture_stock'] ?? 0;
                } catch (Exception $e) {
                    $rupture_stock = 0;
                }

                // NOMBRE DE CLIENTS - Version flexible
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total_clients FROM clients WHERE statut = 'actif'");
                    $total_clients = $stmt->fetch()['total_clients'] ?? 0;
                } catch (Exception $e) {
                    $total_clients = 0;
                }
                ?>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Chiffre d'Affaires</h3>
                        <div class="stat-value"><?php echo number_format($ca_total, 0, ',', ' '); ?> CDF</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3>Total Ventes</h3>
                        <div class="stat-value"><?php echo $total_ventes; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3>Produits</h3>
                        <div class="stat-value"><?php echo $total_produits; ?></div>
                    </div>
                </div>

                <div class="stat-card <?php echo $rupture_stock > 0 ? 'warning' : ''; ?>">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <h3>Rupture Stock</h3>
                        <div class="stat-value"><?php echo $rupture_stock; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Clients</h3>
                        <div class="stat-value"><?php echo $total_clients; ?></div>
                    </div>
                </div>
            </div>

            <!-- Sections récentes -->
            <div class="content-grid">
                <!-- Dernières Ventes - Version FLEXIBLE -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>📈 Dernières Ventes</h2>
                        <a href="modules/ventes/liste.php" class="btn-link">Voir tout</a>
                    </div>
                    <div class="card-content">
                        <?php
                        try {
                            // Essayer différentes structures de tables
                            $query = "
                                SELECT v.*, c.nom_client, c.nom 
                                FROM ventes v 
                                LEFT JOIN clients c ON v.id_client = c.id OR v.id_client = c.id_client
                                WHERE v.statut = 'confirmee' OR v.statut IS NULL
                                ORDER BY v.date_vente DESC 
                                LIMIT 5
                            ";
                            $stmt = $pdo->query($query);
                            $ventes = $stmt->fetchAll();

                            if (count($ventes) > 0):
                        ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client</th>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventes as $vente):
                                            $nom_client = $vente['nom_client'] ?? $vente['nom'] ?? 'Client Anonyme';
                                            $montant = $vente['montant_total'] ?? $vente['montant'] ?? 0;
                                            $date_vente = $vente['date_vente'] ?? $vente['date'] ?? date('Y-m-d H:i:s');
                                        ?>
                                            <tr>
                                                <td>#<?php echo $vente['id_vente'] ?? $vente['id']; ?></td>
                                                <td><?php echo $nom_client; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($date_vente)); ?></td>
                                                <td><?php echo number_format($montant, 0, ',', ' '); ?> CDF</td>
                                                <td><span class="status-badge status-confirmee">Confirmée</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div style="text-align: center; padding: 2rem; color: #666;">
                                    <p>📝 Aucune vente enregistrée</p>
                                    <p><small>Les ventes apparaîtront ici une fois créées</small></p>
                                </div>
                        <?php endif;
                        } catch (Exception $e) {
                            echo '<div style="text-align: center; padding: 2rem; color: #666;">';
                            echo '<p>📊 Module Ventes en préparation</p>';
                            echo '<p><small>Le module des ventes sera bientôt disponible</small></p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Produits en Alerte - Version FLEXIBLE -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>⚠️ Produits en Alerte</h2>
                        <a href="modules/produits/liste.php" class="btn-link">Gérer</a>
                    </div>
                    <div class="card-content">
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT designation, nom, quantite_stock, stock, stock_alerte, quantite
                                FROM produits 
                                WHERE quantite_stock <= stock_alerte OR stock <= stock_alerte OR quantite <= stock_alerte
                                ORDER BY quantite_stock ASC, stock ASC, quantite ASC
                                LIMIT 5
                            ");
                            $produits = $stmt->fetchAll();

                            if (count($produits) > 0):
                        ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Stock Actuel</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produits as $produit):
                                            $designation = $produit['designation'] ?? $produit['nom'] ?? 'Produit';
                                            $stock = $produit['quantite_stock'] ?? $produit['stock'] ?? $produit['quantite'] ?? 0;
                                            $stock_alerte = $produit['stock_alerte'] ?? 10;
                                            $status = $stock == 0 ? 'rupture' : 'alerte';
                                        ?>
                                            <tr>
                                                <td><?php echo $designation; ?></td>
                                                <td><?php echo $stock; ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $status; ?>">
                                                        <?php echo $status == 'rupture' ? 'Rupture' : 'Alerte'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div style="text-align: center; padding: 2rem; color: #666;">
                                    <p>✅ Aucun produit en alerte</p>
                                    <p><small>Tous les produits sont bien approvisionnés</small></p>
                                </div>
                        <?php endif;
                        } catch (Exception $e) {
                            echo '<div style="text-align: center; padding: 2rem; color: #666;">';
                            echo '<p>📦 Gestion des stocks en préparation</p>';
                            echo '<p><small>Le module des stocks sera bientôt disponible</small></p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/dashboard.js"></script>
</body>

</html>