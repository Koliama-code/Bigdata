<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

$success = '';
$error = '';

// Récupérer l'ID du produit
$id_produit = $_GET['id'] ?? null;
if (!$id_produit) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer le produit
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id_produit = ?");
    $stmt->execute([$id_produit]);
    $produit = $stmt->fetch();

    if (!$produit) {
        $error = "Produit non trouvé!";
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $designation = sanitize($_POST['designation']);
        $id_categorie = $_POST['id_categorie'] ?? null;
        $prix_unitaire = floatval($_POST['prix_unitaire']);
        $quantite_stock = intval($_POST['quantite_stock']);
        $stock_alerte = intval($_POST['stock_alerte']);

        $stmt = $pdo->prepare("
            UPDATE produits 
            SET designation = ?, id_categorie = ?, prix_unitaire = ?, quantite_stock = ?, stock_alerte = ?
            WHERE id_produit = ?
        ");

        $stmt->execute([$designation, $id_categorie, $prix_unitaire, $quantite_stock, $stock_alerte, $id_produit]);

        $success = "✅ Produit modifié avec succès!";

        // Recharger les données
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE id_produit = ?");
        $stmt->execute([$id_produit]);
        $produit = $stmt->fetch();
    }
} catch (Exception $e) {
    $error = "❌ Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #a0aec0;
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #c6f6d5;
            color: #276749;
        }

        .alert-error {
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
                <h1>✏️ Modifier le Produit</h1>
                <p>Modifier les informations du produit</p>
            </div>

            <div class="form-container">
                <?php if ($error && !$produit): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                    <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                <?php else: ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="content-card">
                        <form method="POST">
                            <div class="form-group">
                                <label for="designation">Désignation du produit *</label>
                                <input type="text" id="designation" name="designation" required
                                    value="<?php echo $produit['designation']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="id_categorie">Catégorie</label>
                                <select id="id_categorie" name="id_categorie">
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php
                                    $pdo = getDBConnection();
                                    $stmt = $pdo->query("SELECT * FROM categories_produit ORDER BY nom_categorie");
                                    while ($categorie = $stmt->fetch()):
                                        $selected = ($categorie['id_categorie'] == $produit['id_categorie']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $categorie['id_categorie']; ?>" <?php echo $selected; ?>>
                                            <?php echo $categorie['nom_categorie']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="prix_unitaire">Prix unitaire (CDF) *</label>
                                <input type="number" id="prix_unitaire" name="prix_unitaire" step="0.01" min="0" required
                                    value="<?php echo $produit['prix_unitaire']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="quantite_stock">Quantité en stock *</label>
                                <input type="number" id="quantite_stock" name="quantite_stock" min="0" required
                                    value="<?php echo $produit['quantite_stock']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="stock_alerte">Stock d'alerte *</label>
                                <input type="number" id="stock_alerte" name="stock_alerte" min="1" required
                                    value="<?php echo $produit['stock_alerte']; ?>">
                                <small>Le système alertera quand le stock atteindra cette valeur</small>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
                                <a href="liste.php" class="btn btn-secondary">❌ Annuler</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>