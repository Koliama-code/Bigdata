<?php
require_once '../includes/config.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("<div class='auth-container'><div class='alert error'>Token invalide</div></div>");
}

// Vérifier le token
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE token_reset = ? AND token_expiration > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("<div class='auth-container'><div class='alert error'>Token invalide ou expiré</div></div>");
    }
} catch (PDOException $e) {
    die("<div class='auth-container'><div class='alert error'>Erreur: " . $e->getMessage() . "</div></div>");
}

// Traitement du nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // CORRECTION : utiliser id_utilisateur au lieu de id
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, token_reset = NULL, token_expiration = NULL WHERE id_utilisateur = ?");
            $stmt->execute([$hashed_password, $user['id_utilisateur']]);

            $success = "Mot de passe réinitialisé avec succès!";
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="auth-container">
        <!-- Section Illustration -->
        <div class="auth-illustration">
            <div class="illustration-content">
                <div class="illustration-icon">🆕</div>
                <h2>Nouveau mot de passe</h2>
                <p>Choisissez un mot de passe sécurisé</p>
            </div>
        </div>

        <!-- Section Formulaire -->
        <div class="auth-form-section">
            <div class="auth-header">
                <h1>Nouveau mot de passe</h1>
                <p>Pour: <strong><?= htmlspecialchars($user['nom_utilisateur']) ?></strong></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
                <div class="auth-links">
                    <a href="login.php" class="btn-primary" style="text-align: center; display: block;">Se connecter</a>
                </div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 caractères" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Répétez le mot de passe" required>
                    </div>

                    <button type="submit" class="btn-primary">
                        Réinitialiser le mot de passe
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <a href="login.php">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>

</html>