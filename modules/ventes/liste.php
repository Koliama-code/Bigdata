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
    <title>Gestion des Ventes - <?php echo APP_NAME; ?></title>
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

        .btn-success {
            background: #38a169;
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
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>💰 Gestion des Ventes</h1>
                <p>Gérez les ventes et transactions commerciales</p>
            </div>

            <!-- Barre d'actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Actions Rapides</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="nouvelle.php" class="btn btn-primary">➕ Nouvelle vente</a>
                        <a href="liste.php" class="btn btn-info">🔄 Actualiser</a>
                    </div>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="content-card">
                <form method="GET" id="searchForm">
                    <div class="search-bar">
                        <input type="text" name="search" id="searchInput"
                            placeholder="🔍 Rechercher un client..."
                            value="<?php echo $_GET['search'] ?? ''; ?>">

                        <select name="statut" id="statutFilter">
                            <option value="">Tous les statuts</option>
                            <option value="confirmee" <?php echo ($_GET['statut'] ?? '') == 'confirmee' ? 'selected' : ''; ?>>Confirmées</option>
                            <option value="en_attente" <?php echo ($_GET['statut'] ?? '') == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="annulee" <?php echo ($_GET['statut'] ?? '') == 'annulee' ? 'selected' : ''; ?>>Annulées</option>
                        </select>

                        <input type="date" name="date_debut" value="<?php echo $_GET['date_debut'] ?? ''; ?>">
                        <input type="date" name="date_fin" value="<?php echo $_GET['date_fin'] ?? ''; ?>">

                        <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                        <a href="liste.php" class="btn btn-secondary">🗑️ Effacer</a>
                    </div>
                </form>
            </div>

            <!-- Liste des ventes -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Historique des Ventes</h3>
                    <span id="venteCount">
                        <?php
                        try {
                            $pdo = getDBConnection();

                            // Construction de la requête avec filtres
                            $where = [];
                            $params = [];

                            if (!empty($_GET['search'])) {
                                $where[] = "(c.nom_client LIKE ?)";
                                $search_term = '%' . $_GET['search'] . '%';
                                $params[] = $search_term;
                            }

                            if (!empty($_GET['statut'])) {
                                $where[] = "v.statut = ?";
                                $params[] = $_GET['statut'];
                            }

                            if (!empty($_GET['date_debut'])) {
                                $where[] = "DATE(v.date_vente) >= ?";
                                $params[] = $_GET['date_debut'];
                            }

                            if (!empty($_GET['date_fin'])) {
                                $where[] = "DATE(v.date_vente) <= ?";
                                $params[] = $_GET['date_fin'];
                            }

                            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

                            // Compter le total
                            $count_sql = "
                                SELECT COUNT(*) as total 
                                FROM ventes v 
                                LEFT JOIN clients c ON v.id_client = c.id_client 
                                $where_clause
                            ";
                            $stmt = $pdo->prepare($count_sql);
                            $stmt->execute($params);
                            $total = $stmt->fetch()['total'];

                            echo $total . ' vente(s) trouvée(s)';
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

                        // Requête pour les ventes avec filtres
                        $sql = "
                            SELECT v.*, c.nom_client 
                            FROM ventes v 
                            LEFT JOIN clients c ON v.id_client = c.id_client 
                            $where_clause
                            ORDER BY v.date_vente DESC
                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $ventes = $stmt->fetchAll();

                        if (count($ventes) > 0):
                    ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID Vente</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventes as $vente):
                                        // Déterminer la classe du statut
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
                                            default:
                                                $statut_class = 'badge-warning';
                                                $statut_text = $vente['statut'];
                                        }
                                    ?>
                                        <tr>
                                            <td><strong>#<?php echo $vente['id_vente']; ?></strong></td>
                                            <td><?php echo $vente['nom_client'] ?? 'Client non spécifié'; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                                            <td><strong><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> CDF</strong></td>
                                            <td>
                                                <span class="badge <?php echo $statut_class; ?>">
                                                    <?php echo $statut_text; ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <a href="details.php?id=<?php echo $vente['id_vente']; ?>" class="btn btn-primary" title="Voir détails">
                                                    👁️
                                                </a>
                                                <?php if ($vente['statut'] == 'en_attente'): ?>
                                                    <a href="confirmer.php?id=<?php echo $vente['id_vente']; ?>" class="btn btn-success" title="Confirmer">
                                                        ✅
                                                    </a>
                                                    <a href="annuler.php?id=<?php echo $vente['id_vente']; ?>" class="btn btn-danger" title="Annuler">
                                                        ❌
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: #666;">
                                <p style="font-size: 1.2rem; margin-bottom: 1rem;">📝 Aucune vente trouvée</p>
                                <p>
                                    <?php if (!empty($_GET['search']) || !empty($_GET['statut']) || !empty($_GET['date_debut'])): ?>
                                        Aucune vente ne correspond à vos critères de recherche.
                                        <br>
                                        <a href="liste.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            🔄 Afficher toutes les ventes
                                        </a>
                                    <?php else: ?>
                                        Aucune vente n'a été enregistrée dans le système.
                                        <br>
                                        <a href="nouvelle.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            ➕ Créer la première vente
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    <?php
                    } catch (Exception $e) {
                        echo '<div style="text-align: center; padding: 2rem; color: red;">';
                        echo '<p>❌ Erreur lors du chargement des ventes</p>';
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