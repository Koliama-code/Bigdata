<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer l'ID du produit
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer le produit avec sa catégorie
    $stmt = $pdo->prepare("
        SELECT p.*, c.nom_categorie 
        FROM produits p 
        LEFT JOIN categories c ON p.id_categorie = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();

    if (!$produit) {
        $_SESSION['error'] = "Produit non trouvé!";
        header('Location: liste.php');
        exit;
    }
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
    <title>Détails Produit - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>👁️ Détails du Produit</h1>
                <p>Informations complètes du produit</p>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2><?php echo $produit['designation'] ?? $produit['nom']; ?></h2>
                    <div style="display: flex; gap: 10px;">
                        <a href="modifier.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Modifier</a>
                        <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                    </div>
                </div>

                <div class="card-content">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h3>Informations Générales</h3>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">ID:</td>
                                    <td style="padding: 8px 0;"><?php echo $id; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Désignation:</td>
                                    <td style="padding: 8px 0;"><?php echo $produit['designation'] ?? $produit['nom']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Catégorie:</td>
                                    <td style="padding: 8px 0;"><?php echo $produit['nom_categorie'] ?? 'Non catégorisé'; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Prix de vente:</td>
                                    <td style="padding: 8px 0;">
                                        <strong><?php echo number_format($produit['prix_vente'] ?? $produit['prix'], 0, ',', ' '); ?> CDF</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div>
                            <h3>Stock et Statut</h3>
                            <table style="width: 100%;">
                                <?php
                                $stock = $produit['quantite_stock'] ?? $produit['stock'] ?? 0;
                                $stock_alerte = $produit['stock_alerte'] ?? 10;

                                if ($stock == 0) {
                                    $status_class = 'status-rupture';
                                    $status_text = 'Rupture de stock';
                                } elseif ($stock <= $stock_alerte) {
                                    $status_class = 'status-alerte';
                                    $status_text = 'Stock faible';
                                } else {
                                    $status_class = 'status-confirmee';
                                    $status_text = 'Stock normal';
                                }
                                ?>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Stock actuel:</td>
                                    <td style="padding: 8px 0;">
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $stock; ?> unités
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
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($produit['description'])): ?>
                        <div style="margin-top: 2rem;">
                            <h3>Description</h3>
                            <div style="background: #f7fafc; padding: 1rem; border-radius: 5px;">
                                <?php echo nl2br($produit['description']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>