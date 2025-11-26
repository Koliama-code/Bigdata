<?php
require_once '../includes/config.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = sanitize($_POST['username']);

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Générer token de reset
            $token = generateToken();
            $expiration = date('Y-m-d H:i:s', time() + TOKEN_EXPIRATION);

            // CORRECTION : utiliser id_utilisateur au lieu de id
            $stmt = $pdo->prepare("UPDATE utilisateurs SET token_reset = ?, token_expiration = ? WHERE id_utilisateur = ?");
            $stmt->execute([$token, $expiration, $user['id_utilisateur']]);

            // Lien de reset (en développement)
            $reset_link = "http://localhost/bralima_app/auth/reset-password.php?token=" . $token;
            $message = "Lien de réinitialisation: " . $reset_link;
        }

        // Toujours afficher le même message pour la sécurité
        $info = "Si l'utilisateur existe, un lien de réinitialisation a été généré.";
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="auth-container">
        <!-- Section Illustration -->
        <div class="auth-illustration">
            <div class="illustration-content">
                <div class="illustration-icon">🔑</div>
                <h2>Réinitialisation</h2>
                <p>Récupérez l'accès à votre compte</p>
            </div>
        </div>

        <!-- Section Formulaire -->
        <div class="auth-form-section">
            <div class="auth-header">
                <h1>Mot de passe oublié</h1>
                <p>Entrez votre nom d'utilisateur pour réinitialiser</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($info)): ?>
                <div class="alert info"><?= $info ?></div>
                <?php if (isset($reset_link)): ?>
                    <div class="alert warning">
                        <strong>Lien de reset (développement):</strong><br>
                        <a href="<?= $reset_link ?>" style="word-break: break-all; font-size: 0.9rem;"><?= $reset_link ?></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Entrez votre nom d'utilisateur" required>
                </div>

                <button type="submit" class="btn-primary">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <div class="auth-links">
                <a href="login.php">← Retour à la connexion</a>
                <a href="creer-compte.php">Créer un compte</a>
            </div>
        </div>
    </div>
</body>

</html>