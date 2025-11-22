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
    <title>Rapports et Statistiques - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .report-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .report-card:hover {
            transform: translateY(-5px);
        }

        .report-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }

        .report-description {
            color: #718096;
            margin-bottom: 1.5rem;
        }

        .btn-report {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>📊 Rapports et Statistiques</h1>
                <p>Analysez les performances de votre entreprise</p>
            </div>

            <div class="reports-grid">
                <div class="report-card">
                    <div class="report-icon">💰</div>
                    <div class="report-title">Rapport des Ventes</div>
                    <div class="report-description">
                        Analysez vos performances commerciales, chiffre d'affaires, top produits et clients.
                    </div>
                    <a href="ventes.php" class="btn-report">Voir le rapport</a>
                </div>

                <div class="report-card">
                    <div class="report-icon">📦</div>
                    <div class="report-title">Rapport des Stocks</div>
                    <div class="report-description">
                        Surveillez vos niveaux de stock, ruptures et mouvements d'inventaire.
                    </div>
                    <a href="stocks.php" class="btn-report">Voir le rapport</a>
                </div>

                <div class="report-card">
                    <div class="report-icon">👥</div>
                    <div class="report-title">Rapport Clients</div>
                    <div class="report-description">
                        Analysez le comportement d'achat et la fidélité de votre clientèle.
                    </div>
                    <a href="clients.php" class="btn-report">Voir le rapport</a>
                </div>

                <div class="report-card">
                    <div class="report-icon">📈</div>
                    <div class="report-title">Tableaux de Bord</div>
                    <div class="report-description">
                        Vue d'ensemble en temps réel des indicateurs clés de performance.
                    </div>
                    <a href="dashboard.php" class="btn-report">Voir le dashboard</a>
                </div>

                <div class="report-card">
                    <div class="report-icon">📋</div>
                    <div class="report-title">Rapports Personnalisés</div>
                    <div class="report-description">
                        Créez vos propres rapports avec des critères spécifiques.
                    </div>
                    <a href="personnalises.php" class="btn-report">Créer un rapport</a>
                </div>

                <div class="report-card">
                    <div class="report-icon">🔄</div>
                    <div class="report-title">Rapports Automatiques</div>
                    <div class="report-description">
                        Programmez l'envoi automatique de rapports par email.
                    </div>
                    <a href="automatiques.php" class="btn-report">Configurer</a>
                </div>
            </div>
        </main>
    </div>
</body>

</html>