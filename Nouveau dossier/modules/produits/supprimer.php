<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

// Récupérer l'ID du produit
$id_produit = $_GET['id'] ?? null;
if (!$id_produit) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Vérifier si le produit existe
    $stmt = $pdo->prepare("SELECT designation FROM produits WHERE id_produit = ?");
    $stmt->execute([$id_produit]);
    $produit = $stmt->fetch();

    if (!$produit) {
        $_SESSION['error'] = "Produit non trouvé!";
        header('Location: liste.php');
        exit;
    }

    // Supprimer le produit
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id_produit = ?");
    $stmt->execute([$id_produit]);

    $_SESSION['success'] = "✅ Produit '" . $produit['designation'] . "' supprimé avec succès!";
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Erreur lors de la suppression: " . $e->getMessage();
}

header('Location: liste.php');
exit;
