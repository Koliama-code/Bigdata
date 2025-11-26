<?php
require_once '../includes/config.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // UTILISEZ id_utilisateur AU LIEU DE id
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['nom_utilisateur'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom_complet'] = $user['nom_complet'];
            $_SESSION['login_time'] = time();

            header('Location: ../index.php');
            exit;
        } else {
            $error = "Identifiants incorrects";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-illustration">
            <div class="illustration-content">
                <div class="illustration-icon">🔐</div>
                <h2>BRALIMA MegaData</h2>
                <p>Système de gestion intégré</p>
            </div>
        </div>

        <div class="auth-form-section">
            <div class="auth-header">
                <h1>Connexion</h1>
                <p>Accédez à votre espace sécurisé</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Entrez votre nom d'utilisateur" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                </div>

                <button type="submit" class="btn-primary">Se connecter</button>
            </form>

            <div class="auth-links">
                <a href="creer-compte.php">Créer un compte</a>
                <a href="mot-de-passe-oublie.php">Mot de passe oublié ?</a>
            </div>
        </div>
    </div>
</body>

</html>