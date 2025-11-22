<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

$id_client = $_GET['id'] ?? null;
if (!$id_client) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Désactiver le client
    $stmt = $pdo->prepare("UPDATE clients SET statut = 'inactif' WHERE id_client = ?");
    $stmt->execute([$id_client]);

    $_SESSION['success'] = "✅ Client désactivé avec succès!";
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Erreur: " . $e->getMessage();
}

header('Location: liste.php');
exit;
