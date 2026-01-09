<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer les paramètres
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');

try {
    $pdo = getDBConnection();

    // Récupérer les données
    $stmt = $pdo->prepare("
        SELECT 
            v.id_vente,
            v.date_vente,
            c.nom_client,
            v.montant_total,
            v.statut,
            COUNT(vd.id_detail) as nb_produits
        FROM ventes v
        LEFT JOIN clients c ON v.id_client = c.id_client
        LEFT JOIN vente_details vd ON v.id_vente = vd.id_vente
        WHERE DATE(v.date_vente) BETWEEN ? AND ?
        GROUP BY v.id_vente
        ORDER BY v.date_vente DESC
    ");
    $stmt->execute([$date_debut, $date_fin]);
    $ventes = $stmt->fetchAll();

    // En-têtes pour Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="rapport_ventes_' . date('Y-m-d') . '.xls"');

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID Vente</th>";
    echo "<th>Date</th>";
    echo "<th>Client</th>";
    echo "<th>Montant Total</th>";
    echo "<th>Nombre de Produits</th>";
    echo "<th>Statut</th>";
    echo "</tr>";

    foreach ($ventes as $vente) {
        echo "<tr>";
        echo "<td>" . $vente['id_vente'] . "</td>";
        echo "<td>" . $vente['date_vente'] . "</td>";
        echo "<td>" . ($vente['nom_client'] ?? 'Client anonyme') . "</td>";
        echo "<td>" . $vente['montant_total'] . "</td>";
        echo "<td>" . $vente['nb_produits'] . "</td>";
        echo "<td>" . $vente['statut'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de l'export: " . $e->getMessage();
    header('Location: ventes.php');
    exit;
}
