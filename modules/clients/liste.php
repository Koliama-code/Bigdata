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
    <title>Gestion des Clients - <?php echo APP_NAME; ?></title>
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

        .btn-success {
            background: #38a169;
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

        .client-info {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>👥 Gestion des Clients</h1>
                <p>Gérez votre base de données clients</p>
            </div>

            <!-- Barre d'actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Actions Rapides</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="ajouter.php" class="btn btn-primary">➕ Ajouter un client</a>
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
                            <option value="actif" <?php echo ($_GET['statut'] ?? '') == 'actif' ? 'selected' : ''; ?>>Actifs</option>
                            <option value="inactif" <?php echo ($_GET['statut'] ?? '') == 'inactif' ? 'selected' : ''; ?>>Inactifs</option>
                        </select>

                        <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                        <a href="liste.php" class="btn btn-secondary">🗑️ Effacer</a>
                    </div>
                </form>
            </div>

            <!-- Liste des clients -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Liste des Clients</h3>
                    <span id="clientCount">
                        <?php
                        try {
                            $pdo = getDBConnection();

                            // Construction de la requête avec filtres
                            $where = [];
                            $params = [];

                            if (!empty($_GET['search'])) {
                                $where[] = "(nom_client LIKE ? OR telephone LIKE ? OR email LIKE ?)";
                                $search_term = '%' . $_GET['search'] . '%';
                                $params[] = $search_term;
                                $params[] = $search_term;
                                $params[] = $search_term;
                            }

                            if (!empty($_GET['statut'])) {
                                $where[] = "statut = ?";
                                $params[] = $_GET['statut'];
                            }

                            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

                            // Compter le total
                            $count_sql = "SELECT COUNT(*) as total FROM clients $where_clause";
                            $stmt = $pdo->prepare($count_sql);
                            $stmt->execute($params);
                            $total = $stmt->fetch()['total'];

                            echo $total . ' client(s) trouvé(s)';
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

                        // Requête pour les clients avec filtres
                        $sql = "
                            SELECT * FROM clients 
                            $where_clause
                            ORDER BY nom_client
                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $clients = $stmt->fetchAll();

                        if (count($clients) > 0):
                    ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom du Client</th>
                                        <th>Coordonnées</th>
                                        <th>Date d'inscription</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td><?php echo $client['id_client']; ?></td>
                                            <td>
                                                <strong><?php echo $client['nom_client']; ?></strong>
                                                <?php if (!empty($client['adresse'])): ?>
                                                    <div class="client-info">
                                                        📍 <?php echo substr($client['adresse'], 0, 50); ?>...
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($client['telephone'])): ?>
                                                    <div class="client-info">📞 <?php echo $client['telephone']; ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($client['email'])): ?>
                                                    <div class="client-info">📧 <?php echo $client['email']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($client['date_creation'])); ?></td>
                                            <td>
                                                <?php if ($client['statut'] == 'actif'): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions">
                                                <a href="details.php?id=<?php echo $client['id_client']; ?>" class="btn btn-primary" title="Voir détails">
                                                    👁️
                                                </a>
                                                <a href="modifier.php?id=<?php echo $client['id_client']; ?>" class="btn btn-warning" title="Modifier">
                                                    ✏️
                                                </a>
                                                <?php if ($client['statut'] == 'actif'): ?>
                                                    <a href="desactiver.php?id=<?php echo $client['id_client']; ?>" class="btn btn-danger" title="Désactiver"
                                                        onclick="return confirm('Désactiver le client \'<?php echo $client['nom_client']; ?>\'?')">
                                                        🚫
                                                    </a>
                                                <?php else: ?>
                                                    <a href="activer.php?id=<?php echo $client['id_client']; ?>" class="btn btn-success" title="Activer">
                                                        ✅
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: #666;">
                                <p style="font-size: 1.2rem; margin-bottom: 1rem;">📝 Aucun client trouvé</p>
                                <p>
                                    <?php if (!empty($_GET['search']) || !empty($_GET['statut'])): ?>
                                        Aucun client ne correspond à vos critères de recherche.
                                        <br>
                                        <a href="liste.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            🔄 Afficher tous les clients
                                        </a>
                                    <?php else: ?>
                                        Aucun client n'a été enregistré dans le système.
                                        <br>
                                        <a href="ajouter.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            ➕ Ajouter le premier client
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    <?php
                    } catch (Exception $e) {
                        echo '<div style="text-align: center; padding: 2rem; color: red;">';
                        echo '<p>❌ Erreur lors du chargement des clients</p>';
                        echo '<p><small>' . $e->getMessage() . '</small></p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Fonction pour valider la désactivation
        function confirmDesactivation(clientName) {
            return confirm('Êtes-vous sûr de vouloir désactiver le client "' + clientName + '" ?');
        }
    </script>
</body>

</html>