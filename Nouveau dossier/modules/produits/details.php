<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer l'ID du produit
$id_produit = $_GET['id'] ?? null;
if (!$id_produit) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer le produit avec sa catégorie
    $stmt = $pdo->prepare("
        SELECT p.*, cp.nom_categorie, cp.description as desc_categorie 
        FROM produits p 
        LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
        WHERE p.id_produit = ?
    ");
    $stmt->execute([$id_produit]);
    $produit = $stmt->fetch();

    if (!$produit) {
        $_SESSION['error'] = "Produit non trouvé!";
        header('Location: liste.php');
        exit;
    }

    // Récupérer l'historique des ventes du produit
    $stmt = $pdo->prepare("
        SELECT 
            vd.quantite_vendue,
            vd.prix_vente,
            v.date_vente,
            c.nom_client,
            v.id_vente
        FROM vente_details vd
        JOIN ventes v ON vd.id_vente = v.id_vente
        LEFT JOIN clients c ON v.id_client = c.id_client
        WHERE vd.id_produit = ?
        AND v.statut = 'confirmee'
        ORDER BY v.date_vente DESC
        LIMIT 10
    ");
    $stmt->execute([$id_produit]);
    $historique_ventes = $stmt->fetchAll();

    // Statistiques du produit
    $stmt = $pdo->prepare("
        SELECT 
            SUM(vd.quantite_vendue) as total_vendu,
            SUM(vd.quantite_vendue * vd.prix_vente) as chiffre_affaires,
            COUNT(DISTINCT v.id_vente) as nb_ventes,
            AVG(vd.quantite_vendue) as moyenne_quantite
        FROM vente_details vd
        JOIN ventes v ON vd.id_vente = v.id_vente
        WHERE vd.id_produit = ?
        AND v.statut = 'confirmee'
    ");
    $stmt->execute([$id_produit]);
    $stats = $stmt->fetch();
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
    header('Location: liste.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Détails Produit - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #c6f6d5;
            color: #276749;
        }

        .badge-warning {
            background: #feebcb;
            color: #c05621;
        }

        .badge-danger {
            background: #fed7d7;
            color: #c53030;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3748;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
        }

        .detail-item {
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }

        .stock-indicator {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 10px;
        }

        .stock-normal {
            background: #c6f6d5;
            color: #276749;
        }

        .stock-alert {
            background: #feebcb;
            color: #c05621;
        }

        .stock-rupture {
            background: #fed7d7;
            color: #c53030;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>📦 Détails du Produit</h1>
                <p>Informations complètes et historique des ventes</p>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2><?php echo $produit['designation']; ?></h2>
                    <div style="display: flex; gap: 10px;">
                        <a href="modifier.php?id=<?php echo $id_produit; ?>" class="btn btn-warning">✏️ Modifier</a>
                        <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                    </div>
                </div>

                <div class="card-content">
                    <!-- Statistiques -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $stats['total_vendu'] ?? 0; ?></div>
                            <div class="stat-label">Total vendu</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['chiffre_affaires'] ?? 0, 0, ',', ' '); ?> CDF</div>
                            <div class="stat-label">Chiffre d'affaires</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $stats['nb_ventes'] ?? 0; ?></div>
                            <div class="stat-label">Nombre de ventes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['moyenne_quantite'] ?? 0, 1); ?></div>
                            <div class="stat-label">Moyenne/vente</div>
                        </div>
                    </div>

                    <!-- Informations du produit -->
                    <div class="info-grid">
                        <div>
                            <h3>Informations Générales</h3>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">ID Produit:</td>
                                    <td style="padding: 8px 0;">#<?php echo $produit['id_produit']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Désignation:</td>
                                    <td style="padding: 8px 0;"><?php echo $produit['designation']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Catégorie:</td>
                                    <td style="padding: 8px 0;">
                                        <?php echo $produit['nom_categorie'] ?? 'Non catégorisé'; ?>
                                        <?php if (!empty($produit['desc_categorie'])): ?>
                                            <br><small style="color: #666;"><?php echo $produit['desc_categorie']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Prix unitaire:</td>
                                    <td style="padding: 8px 0;">
                                        <strong><?php echo number_format($produit['prix_unitaire'], 0, ',', ' '); ?> CDF</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Date de création:</td>
                                    <td style="padding: 8px 0;"><?php echo date('d/m/Y H:i', strtotime($produit['date_creation'])); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div>
                            <h3>Stock et Statut</h3>
                            <table style="width: 100%;">
                                <?php
                                $stock = $produit['quantite_stock'];
                                $stock_alerte = $produit['stock_alerte'];

                                if ($stock == 0) {
                                    $status_class = 'stock-rupture';
                                    $status_text = 'RUPTURE DE STOCK';
                                    $status_badge = 'badge-danger';
                                    $status_message = 'Le produit est en rupture de stock';
                                } elseif ($stock <= $stock_alerte) {
                                    $status_class = 'stock-alert';
                                    $status_text = 'STOCK FAIBLE';
                                    $status_badge = 'badge-warning';
                                    $status_message = 'Le stock est inférieur au niveau d\'alerte';
                                } else {
                                    $status_class = 'stock-normal';
                                    $status_text = 'STOCK NORMAL';
                                    $status_badge = 'badge-success';
                                    $status_message = 'Le stock est suffisant';
                                }
                                ?>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Stock actuel:</td>
                                    <td style="padding: 8px 0;">
                                        <span style="font-size: 1.2rem; font-weight: bold;"><?php echo $stock; ?> unités</span>
                                        <span class="stock-indicator <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Stock d'alerte:</td>
                                    <td style="padding: 8px 0;"><?php echo $stock_alerte; ?> unités</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Statut:</td>
                                    <td style="padding: 8px 0;">
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo $status_message; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Niveau de stock:</td>
                                    <td style="padding: 8px 0;">
                                        <div style="background: #e2e8f0; height: 20px; border-radius: 10px; overflow: hidden; margin-top: 5px;">
                                            <?php
                                            $pourcentage = min(100, ($stock / ($stock_alerte * 2)) * 100);
                                            $couleur = $stock == 0 ? '#e53e3e' : ($stock <= $stock_alerte ? '#ed8936' : '#38a169');
                                            ?>
                                            <div style="height: 100%; width: <?php echo $pourcentage; ?>%; background: <?php echo $couleur; ?>; transition: width 0.3s;"></div>
                                        </div>
                                        <small style="color: #666; display: block; text-align: center; margin-top: 5px;">
                                            <?php echo number_format($pourcentage, 1); ?>% du stock optimal
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Recommandations selon le statut du stock -->
                    <?php if ($stock == 0): ?>
                        <div style="background: #fed7d7; color: #c53030; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                            <h4 style="margin: 0 0 0.5rem 0;">🚨 Action Requise</h4>
                            <p style="margin: 0;">Ce produit est en rupture de stock. Veuillez réapprovisionner rapidement.</p>
                        </div>
                    <?php elseif ($stock <= $stock_alerte): ?>
                        <div style="background: #feebcb; color: #c05621; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                            <h4 style="margin: 0 0 0.5rem 0;">⚠️ Recommandation</h4>
                            <p style="margin: 0;">Le stock est faible. Pensez à commander ce produit prochainement.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Historique des ventes -->
                    <h3>📈 Historique des Ventes Récentes</h3>
                    <?php if (count($historique_ventes) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Quantité</th>
                                    <th>Prix de vente</th>
                                    <th>Sous-total</th>
                                    <th>ID Vente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historique_ventes as $vente): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                                        <td><?php echo $vente['nom_client'] ?? 'Client anonyme'; ?></td>
                                        <td><?php echo $vente['quantite_vendue']; ?></td>
                                        <td><?php echo number_format($vente['prix_vente'], 0, ',', ' '); ?> CDF</td>
                                        <td><strong><?php echo number_format($vente['quantite_vendue'] * $vente['prix_vente'], 0, ',', ' '); ?> CDF</strong></td>
                                        <td>#<?php echo $vente['id_vente']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <p>📝 Aucune vente enregistrée pour ce produit</p>
                            <p><small>Les ventes apparaîtront ici une fois que le produit sera vendu</small></p>
                        </div>
                    <?php endif; ?>

                    <!-- Actions rapides -->
                    <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <h3>⚡ Actions Rapides</h3>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="modifier.php?id=<?php echo $id_produit; ?>" class="btn btn-warning">✏️ Modifier le produit</a>
                            <a href="../ventes/nouvelle.php?produit=<?php echo $id_produit; ?>" class="btn btn-primary">💰 Vendre ce produit</a>
                            <?php if ($stock <= $stock_alerte): ?>
                                <a href="modifier.php?id=<?php echo $id_produit; ?>" class="btn btn-success">📦 Réapprovisionner</a>
                            <?php endif; ?>
                            <a href="liste.php" class="btn btn-secondary">📋 Retour à la liste</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Animation de la barre de progression
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.info-grid .stock-indicator');
            if (progressBar) {
                progressBar.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    progressBar.style.transform = 'scale(1)';
                }, 300);
            }
        });
    </script>
</body>

</html>