<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer l'ID de la vente
$id_vente = $_GET['id'] ?? null;
if (!$id_vente) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer la vente avec le client
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom_client, c.telephone, c.email 
        FROM ventes v 
        LEFT JOIN clients c ON v.id_client = c.id_client 
        WHERE v.id_vente = ?
    ");
    $stmt->execute([$id_vente]);
    $vente = $stmt->fetch();

    if (!$vente) {
        $_SESSION['error'] = "Vente non trouvée!";
        header('Location: liste.php');
        exit;
    }

    // Récupérer les détails de la vente
    $stmt = $pdo->prepare("
        SELECT vd.*, p.designation, p.prix_unitaire 
        FROM vente_details vd 
        JOIN produits p ON vd.id_produit = p.id_produit 
        WHERE vd.id_vente = ?
    ");
    $stmt->execute([$id_vente]);
    $details = $stmt->fetchAll();
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
    <title>Détails Vente - <?php echo APP_NAME; ?></title>
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

        .detail-item {
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>📋 Détails de la Vente #<?php echo $vente['id_vente']; ?></h1>
                <p>Informations complètes de la transaction</p>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2>Informations de la Vente</h2>
                    <div style="display: flex; gap: 10px;">
                        <?php if ($vente['statut'] == 'en_attente'): ?>
                            <a href="confirmer.php?id=<?php echo $id_vente; ?>" class="btn btn-success">✅ Confirmer</a>
                            <a href="annuler.php?id=<?php echo $id_vente; ?>" class="btn btn-danger">❌ Annuler</a>
                        <?php endif; ?>
                        <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                    </div>
                </div>

                <div class="card-content">
                    <div class="info-grid">
                        <div>
                            <h3>Informations Générales</h3>
                            <div class="detail-item">
                                <span class="detail-label">ID Vente:</span> #<?php echo $vente['id_vente']; ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date:</span> <?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Statut:</span>
                                <?php
                                switch ($vente['statut']) {
                                    case 'confirmee':
                                        echo '<span class="badge badge-success">Confirmée</span>';
                                        break;
                                    case 'en_attente':
                                        echo '<span class="badge badge-warning">En attente</span>';
                                        break;
                                    case 'annulee':
                                        echo '<span class="badge badge-danger">Annulée</span>';
                                        break;
                                }
                                ?>
                            </div>
                        </div>

                        <div>
                            <h3>Client</h3>
                            <?php if ($vente['nom_client']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Nom:</span> <?php echo $vente['nom_client']; ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Téléphone:</span> <?php echo $vente['telephone'] ?? 'Non renseigné'; ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email:</span> <?php echo $vente['email'] ?? 'Non renseigné'; ?>
                                </div>
                            <?php else: ?>
                                <div class="detail-item">
                                    <span class="detail-label">Client:</span> Non spécifié
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3>Détails des Produits</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $detail): ?>
                                <tr>
                                    <td><?php echo $detail['designation']; ?></td>
                                    <td><?php echo number_format($detail['prix_vente'], 0, ',', ' '); ?> CDF</td>
                                    <td><?php echo $detail['quantite_vendue']; ?></td>
                                    <td><strong><?php echo number_format($detail['prix_vente'] * $detail['quantite_vendue'], 0, ',', ' '); ?> CDF</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                                <td style="font-weight: bold; font-size: 1.1rem;">
                                    <?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> CDF
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>