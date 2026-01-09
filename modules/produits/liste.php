<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .actions {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-warning {
            background: #ed8936;
            color: white;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-info {
            background: #4299e1;
            color: white;
        }

        .btn-secondary {
            background: #a0aec0;
            color: white;
        }

        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-bar input,
        .search-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-bar input {
            flex: 1;
            min-width: 200px;
        }

        .search-bar select {
            min-width: 150px;
        }

        .stock-display {
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
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
                <h1>📦 Gestion des Produits</h1>
                <p>Gérez votre inventaire de produits BRALIMA</p>
            </div>

            <!-- Barre d'actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Actions Rapides</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="ajouter.php" class="btn btn-primary">➕ Ajouter un produit</a>
                        <a href="liste.php" class="btn btn-info">🔄 Actualiser</a>
                    </div>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="content-card">
                <form method="GET" id="searchForm">
                    <div class="search-bar">
                        <input type="text" name="search" id="searchInput"
                            placeholder="🔍 Rechercher un produit..."
                            value="<?php echo $_GET['search'] ?? ''; ?>">

                        <select name="categorie" id="categoryFilter">
                            <option value="">Toutes les catégories</option>
                            <?php
                            $pdo = getDBConnection();
                            try {
                                $stmt = $pdo->query("SELECT * FROM categories_produit ORDER BY nom_categorie");
                                $selected_categorie = $_GET['categorie'] ?? '';
                                while ($categorie = $stmt->fetch()):
                                    $selected = ($categorie['id_categorie'] == $selected_categorie) ? 'selected' : '';
                            ?>
                                    <option value="<?php echo $categorie['id_categorie']; ?>" <?php echo $selected; ?>>
                                        <?php echo $categorie['nom_categorie']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php } catch (Exception $e) { ?>
                                <option value="">Aucune catégorie trouvée</option>
                            <?php } ?>
                        </select>

                        <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                        <a href="liste.php" class="btn btn-secondary">🗑️ Effacer</a>
                    </div>
                </form>
            </div>

            <!-- Liste des produits -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Liste des Produits</h3>
                    <span id="productCount">
                        <?php
                        try {
                            $pdo = getDBConnection();

                            // Construction de la requête avec filtres
                            $where = [];
                            $params = [];

                            if (!empty($_GET['search'])) {
                                $where[] = "(p.designation LIKE ? OR p.description LIKE ?)";
                                $search_term = '%' . $_GET['search'] . '%';
                                $params[] = $search_term;
                                $params[] = $search_term;
                            }

                            if (!empty($_GET['categorie'])) {
                                $where[] = "p.id_categorie = ?";
                                $params[] = $_GET['categorie'];
                            }

                            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

                            // Compter le total
                            $count_sql = "SELECT COUNT(*) as total FROM produits p $where_clause";
                            $stmt = $pdo->prepare($count_sql);
                            $stmt->execute($params);
                            $total = $stmt->fetch()['total'];

                            echo $total . ' produit(s) trouvé(s)';
                        } catch (Exception $e) {
                            echo 'Erreur de comptage';
                        }
                        ?>
                    </span>
                </div>
                <div class="card-content">
                    <?php
                    try {
                        $pdo = getDBConnection();

                        // Requête pour les produits avec filtres
                        $sql = "
                            SELECT p.*, cp.nom_categorie 
                            FROM produits p 
                            LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
                            $where_clause
                            ORDER BY p.designation
                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $produits = $stmt->fetchAll();

                        if (count($produits) > 0):
                    ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Désignation</th>
                                        <th>Catégorie</th>
                                        <th>Prix unitaire</th>
                                        <th>Stock</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produits as $produit):
                                        $stock = $produit['quantite_stock'];
                                        $stock_alerte = $produit['stock_alerte'];

                                        // Déterminer le statut du stock
                                        if ($stock == 0) {
                                            $status_class = 'status-rupture';
                                            $status_text = 'Rupture';
                                            $stock_class = 'stock-rupture';
                                        } elseif ($stock <= $stock_alerte) {
                                            $status_class = 'status-alerte';
                                            $status_text = 'Alerte';
                                            $stock_class = 'stock-alert';
                                        } else {
                                            $status_class = 'status-confirmee';
                                            $status_text = 'Normal';
                                            $stock_class = 'stock-normal';
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo $produit['id_produit']; ?></td>
                                            <td>
                                                <strong><?php echo $produit['designation']; ?></strong>
                                            </td>
                                            <td><?php echo $produit['nom_categorie'] ?? 'Non catégorisé'; ?></td>
                                            <td><strong><?php echo number_format($produit['prix_unitaire'], 0, ',', ' '); ?> CDF</strong></td>
                                            <td>
                                                <span class="stock-display <?php echo $stock_class; ?>">
                                                    <?php echo $stock; ?> unités
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <a href="details.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-primary" title="Voir détails">
                                                    👁️
                                                </a>
                                                <a href="modifier.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-warning" title="Modifier">
                                                    ✏️
                                                </a>
                                                <a href="supprimer.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-danger" title="Supprimer"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer le produit \'<?php echo $produit['designation']; ?>\'?')">
                                                    🗑️
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: #666;">
                                <p style="font-size: 1.2rem; margin-bottom: 1rem;">📝 Aucun produit trouvé</p>
                                <p>
                                    <?php if (!empty($_GET['search']) || !empty($_GET['categorie'])): ?>
                                        Aucun produit ne correspond à vos critères de recherche.
                                        <br>
                                        <a href="liste.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            🔄 Afficher tous les produits
                                        </a>
                                    <?php else: ?>
                                        Aucun produit n'a été enregistré dans le système.
                                        <br>
                                        <a href="ajouter.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            ➕ Ajouter le premier produit
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    <?php
                    } catch (Exception $e) {
                        echo '<div style="text-align: center; padding: 2rem; color: red;">';
                        echo '<p>❌ Erreur lors du chargement des produits</p>';
                        echo '<p><small>' . $e->getMessage() . '</small></p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>