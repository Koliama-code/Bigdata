<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

$id_vente = $_GET['id'] ?? null;
if (!$id_vente) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Vérifier que la vente existe et est en attente
    $stmt = $pdo->prepare("SELECT statut FROM ventes WHERE id_vente = ?");
    $stmt->execute([$id_vente]);
    $vente = $stmt->fetch();

    if (!$vente) {
        $_SESSION['error'] = "Vente non trouvée!";
    } elseif ($vente['statut'] != 'en_attente') {
        $_SESSION['error'] = "Seules les ventes en attente peuvent être annulées!";
    } else {
        // Restaurer les stocks
        $stmt = $pdo->prepare("
            SELECT vd.id_produit, vd.quantite_vendue 
            FROM vente_details vd 
            WHERE vd.id_vente = ?
        ");
        $stmt->execute([$id_vente]);
        $details = $stmt->fetchAll();

        foreach ($details as $detail) {
            $stmt = $pdo->prepare("
                UPDATE produits 
                SET quantite_stock = quantite_stock + ? 
                WHERE id_produit = ?
            ");
            $stmt->execute([$detail['quantite_vendue'], $detail['id_produit']]);
        }

        // Annuler la vente
        $stmt = $pdo->prepare("UPDATE ventes SET statut = 'annulee' WHERE id_vente = ?");
        $stmt->execute([$id_vente]);

        $pdo->commit();
        $_SESSION['success'] = "✅ Vente #$id_vente annulée avec succès!";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "❌ Erreur: " . $e->getMessage();
}

header('Location: liste.php');
exit;
