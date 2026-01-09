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

    // Vérifier que la vente existe et est en attente
    $stmt = $pdo->prepare("SELECT statut FROM ventes WHERE id_vente = ?");
    $stmt->execute([$id_vente]);
    $vente = $stmt->fetch();

    if (!$vente) {
        $_SESSION['error'] = "Vente non trouvée!";
    } elseif ($vente['statut'] != 'en_attente') {
        $_SESSION['error'] = "La vente n'est pas en attente de confirmation!";
    } else {
        // Confirmer la vente
        $stmt = $pdo->prepare("UPDATE ventes SET statut = 'confirmee' WHERE id_vente = ?");
        $stmt->execute([$id_vente]);
        $_SESSION['success'] = "✅ Vente #$id_vente confirmée avec succès!";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Erreur: " . $e->getMessage();
}

header('Location: liste.php');
exit;
