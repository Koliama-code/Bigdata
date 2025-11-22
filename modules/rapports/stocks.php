<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

try {
    $pdo = getDBConnection();

    // Produits en rupture
    $stmt = $pdo->query("
        SELECT p.*, cp.nom_categorie 
        FROM produits p 
        LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
        WHERE p.quantite_stock = 0 
        ORDER BY p.designation
    ");
    $rupture_stock = $stmt->fetchAll();

    // Produits en alerte
    $stmt = $pdo->query("
        SELECT p.*, cp.nom_categorie 
        FROM produits p 
        LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
        WHERE p.quantite_stock > 0 AND p.quantite_stock <= p.stock_alerte 
        ORDER BY p.quantite_stock ASC
    ");
    $alerte_stock = $stmt->fetchAll();

    // Statistiques stocks
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_produits,
            SUM(quantite_stock) as total_stock,
            SUM(quantite_stock * prix_unitaire) as valeur_stock,
            COUNT(CASE WHEN quantite_stock = 0 THEN 1 END) as nb_rupture,
            COUNT(CASE WHEN quantite_stock > 0 AND quantite_stock <= stock_alerte THEN 1 END) as nb_alerte
        FROM produits
    ");
    $stats_stocks = $stmt->fetch();

    // Mouvements de stocks (récent)
    $stmt = $pdo->query("
        SELECT 
            p.designation,
            vd.quantite_vendue,
            v.date_vente,
            c.nom_client
        FROM vente_details vd
        JOIN produits p ON vd.id_produit = p.id_produit
        JOIN ventes v ON vd.id_vente = v.id_vente
        LEFT JOIN clients c ON v.id_client = c.id_client
        WHERE v.statut = 'confirmee'
        ORDER BY v.date_vente DESC
        LIMIT 20
    ");
    $mouvements_stock = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports des Stocks - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-danger {
            background: #fed7d7;
            color: #c53030;
        }

        .badge-warning {
            background: #feebcb;
            color: #c05621;
        }

        .badge-success {
            background: #c6f6d5;
            color: #276749;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .btn-print {
            background: #3182ce;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>📦 Rapports des Stocks</h1>
                <p>Surveillez votre inventaire et vos niveaux de stock</p>
                <div class="no-print">
                    <button onclick="window.print()" class="btn-print">🖨️ Imprimer le rapport</button>
                </div>
            </div>

            <!-- Statistiques stocks -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $stats_stocks['total_produits']; ?></div>
                    <div class="stat-label">Produits</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-value"><?php echo number_format($stats_stocks['total_stock'], 0, ',', ' '); ?></div>
                    <div class="stat-label">Unités en stock</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value"><?php echo number_format($stats_stocks['valeur_stock'], 0, ',', ' '); ?> CDF</div>
                    <div class="stat-label">Valeur du stock</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value" style="color: #c53030;"><?php echo $stats_stocks['nb_rupture']; ?></div>
                    <div class="stat-label">Ruptures de stock</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-value" style="color: #c05621;"><?php echo $stats_stocks['nb_alerte']; ?></div>
                    <div class="stat-label">Stocks en alerte</div>
                </div>
            </div>

            <div class="content-grid">
                <!-- Rupture de stock -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>❌ Rupture de Stock</h3>
                        <span class="badge badge-danger"><?php echo count($rupture_stock); ?> produit(s)</span>
                    </div>
                    <div class="card-content">
                        <?php if (count($rupture_stock) > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Catégorie</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rupture_stock as $produit): ?>
                                        <tr>
                                            <td><?php echo $produit['designation']; ?></td>
                                            <td><?php echo $produit['nom_categorie']; ?></td>
                                            <td><?php echo number_format($produit['prix_unitaire'], 0, ',', ' '); ?> CDF</td>
                                            <td><span class="badge badge-danger">Rupture</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #666;">
                                <p>✅ Aucune rupture de stock</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stocks en alerte -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>⚠️ Stocks en Alerte</h3>
                        <span class="badge badge-warning"><?php echo count($alerte_stock); ?> produit(s)</span>
                    </div>
                    <div class="card-content">
                        <?php if (count($alerte_stock) > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Stock</th>
                                        <th>Alerte</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alerte_stock as $produit): ?>
                                        <tr>
                                            <td><?php echo $produit['designation']; ?></td>
                                            <td><?php echo $produit['quantite_stock']; ?></td>
                                            <td><?php echo $produit['stock_alerte']; ?></td>
                                            <td><span class="badge badge-warning">Alerte</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #666;">
                                <p>✅ Aucun stock en alerte</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mouvements récents -->
                <div class="content-card" style="grid-column: 1 / -1;">
                    <div class="card-header">
                        <h3>📋 Mouvements de Stock Récents</h3>
                    </div>
                    <div class="card-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mouvements_stock as $mouvement): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($mouvement['date_vente'])); ?></td>
                                        <td><?php echo $mouvement['designation']; ?></td>
                                        <td><span style="color: #e53e3e;">-<?php echo $mouvement['quantite_vendue']; ?></span></td>
                                        <td><?php echo $mouvement['nom_client'] ?? 'Client anonyme'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>