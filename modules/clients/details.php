<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer l'ID du client
$id_client = $_GET['id'] ?? null;
if (!$id_client) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer le client
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $client = $stmt->fetch();

    if (!$client) {
        $_SESSION['error'] = "Client non trouvé!";
        header('Location: liste.php');
        exit;
    }

    // Récupérer l'historique des ventes du client
    $stmt = $pdo->prepare("
        SELECT v.*, COUNT(vd.id_detail) as nb_produits
        FROM ventes v 
        LEFT JOIN vente_details vd ON v.id_vente = vd.id_vente 
        WHERE v.id_client = ?
        GROUP BY v.id_vente
        ORDER BY v.date_vente DESC
        LIMIT 10
    ");
    $stmt->execute([$id_client]);
    $ventes = $stmt->fetchAll();

    // Statistiques du client
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_ventes,
            SUM(v.montant_total) as total_achats,
            AVG(v.montant_total) as moyenne_achat
        FROM ventes v 
        WHERE v.id_client = ? AND v.statut = 'confirmee'
    ");
    $stmt->execute([$id_client]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Client - <?php echo APP_NAME; ?></title>
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
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>👤 Détails du Client</h1>
                <p>Informations complètes et historique d'achat</p>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2><?php echo $client['nom_client']; ?></h2>
                    <div style="display: flex; gap: 10px;">
                        <a href="modifier.php?id=<?php echo $id_client; ?>" class="btn btn-warning">✏️ Modifier</a>
                        <?php if ($client['statut'] == 'actif'): ?>
                            <a href="desactiver.php?id=<?php echo $id_client; ?>" class="btn btn-danger">🚫 Désactiver</a>
                        <?php else: ?>
                            <a href="activer.php?id=<?php echo $id_client; ?>" class="btn btn-success">✅ Activer</a>
                        <?php endif; ?>
                        <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                    </div>
                </div>

                <div class="card-content">
                    <!-- Statistiques -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $stats['total_ventes'] ?? 0; ?></div>
                            <div class="stat-label">Total des ventes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['total_achats'] ?? 0, 0, ',', ' '); ?> CDF</div>
                            <div class="stat-label">Total des achats</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['moyenne_achat'] ?? 0, 0, ',', ' '); ?> CDF</div>
                            <div class="stat-label">Moyenne par achat</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php if ($client['statut'] == 'actif'): ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactif</span>
                                <?php endif; ?>
                            </div>
                            <div class="stat-label">Statut du client</div>
                        </div>
                    </div>

                    <!-- Informations du client -->
                    <div class="info-grid">
                        <div>
                            <h3>Informations Personnelles</h3>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">ID Client:</td>
                                    <td style="padding: 8px 0;">#<?php echo $client['id_client']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Nom:</td>
                                    <td style="padding: 8px 0;"><?php echo $client['nom_client']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Date d'inscription:</td>
                                    <td style="padding: 8px 0;"><?php echo date('d/m/Y', strtotime($client['date_creation'])); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div>
                            <h3>Coordonnées</h3>
                            <table style="width: 100%;">
                                <?php if (!empty($client['telephone'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; font-weight: bold;">Téléphone:</td>
                                        <td style="padding: 8px 0;">📞 <?php echo $client['telephone']; ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($client['email'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                                        <td style="padding: 8px 0;">📧 <?php echo $client['email']; ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($client['adresse'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; font-weight: bold;">Adresse:</td>
                                        <td style="padding: 8px 0;">📍 <?php echo nl2br($client['adresse']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Historique des ventes -->
                    <h3>📈 Historique des Ventes Récentes</h3>
                    <?php if (count($ventes) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Vente</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Produits</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventes as $vente):
                                    switch ($vente['statut']) {
                                        case 'confirmee':
                                            $statut_class = 'badge-success';
                                            $statut_text = 'Confirmée';
                                            break;
                                        case 'en_attente':
                                            $statut_class = 'badge-warning';
                                            $statut_text = 'En attente';
                                            break;
                                        case 'annulee':
                                            $statut_class = 'badge-danger';
                                            $statut_text = 'Annulée';
                                            break;
                                    }
                                ?>
                                    <tr>
                                        <td>#<?php echo $vente['id_vente']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                                        <td><strong><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> CDF</strong></td>
                                        <td><?php echo $vente['nb_produits']; ?> produit(s)</td>
                                        <td>
                                            <span class="badge <?php echo $statut_class; ?>">
                                                <?php echo $statut_text; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <p>📝 Aucune vente enregistrée pour ce client</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>