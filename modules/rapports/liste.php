<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Dates par défaut (mois en cours)
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');

// Récupérer les statistiques
try {
    $pdo = getDBConnection();

    // Statistiques générales
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_ventes,
            SUM(montant_total) as chiffre_affaires,
            AVG(montant_total) as panier_moyen,
            MAX(montant_total) as plus_grosse_vente,
            COUNT(DISTINCT id_client) as clients_actifs
        FROM ventes 
        WHERE statut = 'confirmee' 
        AND DATE(date_vente) BETWEEN ? AND ?
    ");
    $stmt->execute([$date_debut, $date_fin]);
    $stats = $stmt->fetch();

    // Ventes par jour
    $stmt = $pdo->prepare("
        SELECT 
            DATE(date_vente) as date,
            COUNT(*) as nb_ventes,
            SUM(montant_total) as ca_jour
        FROM ventes 
        WHERE statut = 'confirmee' 
        AND DATE(date_vente) BETWEEN ? AND ?
        GROUP BY DATE(date_vente)
        ORDER BY date
    ");
    $stmt->execute([$date_debut, $date_fin]);
    $ventes_par_jour = $stmt->fetchAll();

    // Top produits
    $stmt = $pdo->prepare("
        SELECT 
            p.designation,
            SUM(vd.quantite_vendue) as quantite_vendue,
            SUM(vd.quantite_vendue * vd.prix_vente) as chiffre_affaires
        FROM vente_details vd
        JOIN produits p ON vd.id_produit = p.id_produit
        JOIN ventes v ON vd.id_vente = v.id_vente
        WHERE v.statut = 'confirmee'
        AND DATE(v.date_vente) BETWEEN ? AND ?
        GROUP BY p.id_produit
        ORDER BY chiffre_affaires DESC
        LIMIT 10
    ");
    $stmt->execute([$date_debut, $date_fin]);
    $top_produits = $stmt->fetchAll();

    // Top clients
    $stmt = $pdo->prepare("
        SELECT 
            c.nom_client,
            COUNT(v.id_vente) as nb_ventes,
            SUM(v.montant_total) as total_achats
        FROM ventes v
        LEFT JOIN clients c ON v.id_client = c.id_client
        WHERE v.statut = 'confirmee'
        AND DATE(v.date_vente) BETWEEN ? AND ?
        GROUP BY v.id_client
        ORDER BY total_achats DESC
        LIMIT 10
    ");
    $stmt->execute([$date_debut, $date_fin]);
    $top_clients = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Rapports des Ventes - <?php echo APP_NAME; ?></title>
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

        .filter-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            height: 300px;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        .btn-export {
            background: #38a169;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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

        @media print {
            .no-print {
                display: none;
            }

            .stat-card {
                break-inside: avoid;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>📈 Rapports des Ventes</h1>
                <p>Analysez vos performances commerciales</p>
            </div>

            <!-- Filtres -->
            <div class="filter-bar no-print">
                <form method="GET" class="search-bar">
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Date début:</label>
                            <input type="date" name="date_debut" value="<?php echo $date_debut; ?>" style="padding: 8px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Date fin:</label>
                            <input type="date" name="date_fin" value="<?php echo $date_fin; ?>" style="padding: 8px;">
                        </div>
                        <div style="align-self: end;">
                            <button type="submit" class="btn btn-primary">🔍 Appliquer</button>
                            <a href="ventes.php" class="btn btn-secondary">🔄 Réinitialiser</a>
                        </div>
                        <div style="align-self: end; margin-left: auto;">
                            <button type="button" onclick="window.print()" class="btn-print">🖨️ Imprimer</button>
                            <a href="export_ventes.php?date_debut=<?php echo $date_debut; ?>&date_fin=<?php echo $date_fin; ?>" class="btn-export">📊 Exporter Excel</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Période -->
            <div class="content-card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Période d'analyse</h3>
                </div>
                <div class="card-content">
                    <p>
                        <strong>Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au <?php echo date('d/m/Y', strtotime($date_fin)); ?></strong>
                        <?php
                        $jours = (strtotime($date_fin) - strtotime($date_debut)) / (60 * 60 * 24) + 1;
                        echo "($jours jours)";
                        ?>
                    </p>
                </div>
            </div>

            <!-- Statistiques générales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value"><?php echo number_format($stats['chiffre_affaires'] ?? 0, 0, ',', ' '); ?> CDF</div>
                    <div class="stat-label">Chiffre d'Affaires</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $stats['total_ventes'] ?? 0; ?></div>
                    <div class="stat-label">Ventes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-value"><?php echo number_format($stats['panier_moyen'] ?? 0, 0, ',', ' '); ?> CDF</div>
                    <div class="stat-label">Panier Moyen</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo $stats['clients_actifs'] ?? 0; ?></div>
                    <div class="stat-label">Clients Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-value"><?php echo number_format($stats['plus_grosse_vente'] ?? 0, 0, ',', ' '); ?> CDF</div>
                    <div class="stat-label">Plus Grosse Vente</div>
                </div>
            </div>

            <!-- Graphiques et tableaux -->
            <div class="content-grid">
                <!-- Ventes par jour -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>📅 Ventes par Jour</h3>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="chartVentesJour"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top produits -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>🏆 Top 10 Produits</h3>
                    </div>
                    <div class="card-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Chiffre d'Affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_produits as $produit): ?>
                                        <tr>
                                            <td><?php echo $produit['designation']; ?></td>
                                            <td><?php echo $produit['quantite_vendue']; ?></td>
                                            <td><strong><?php echo number_format($produit['chiffre_affaires'], 0, ',', ' '); ?> CDF</strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top clients -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>👥 Top 10 Clients</h3>
                    </div>
                    <div class="card-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Ventes</th>
                                        <th>Total Achats</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_clients as $client): ?>
                                        <tr>
                                            <td><?php echo $client['nom_client'] ?? 'Client Anonyme'; ?></td>
                                            <td><?php echo $client['nb_ventes']; ?></td>
                                            <td><strong><?php echo number_format($client['total_achats'], 0, ',', ' '); ?> CDF</strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Évolution CA -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>📈 Évolution du Chiffre d'Affaires</h3>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="chartEvolutionCA"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Données pour les graphiques
        const ventesParJour = <?php echo json_encode($ventes_par_jour); ?>;

        // Graphique des ventes par jour
        const ctxVentes = document.getElementById('chartVentesJour').getContext('2d');
        new Chart(ctxVentes, {
            type: 'bar',
            data: {
                labels: ventesParJour.map(v => new Date(v.date).toLocaleDateString()),
                datasets: [{
                    label: 'Nombre de ventes',
                    data: ventesParJour.map(v => v.nb_ventes),
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique d'évolution du CA
        const ctxCA = document.getElementById('chartEvolutionCA').getContext('2d');
        new Chart(ctxCA, {
            type: 'line',
            data: {
                labels: ventesParJour.map(v => new Date(v.date).toLocaleDateString()),
                datasets: [{
                    label: 'Chiffre d\'Affaires (CDF)',
                    data: ventesParJour.map(v => v.ca_jour),
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>