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
    $confirm_password = $_POST['confirm_password'];
    $email = sanitize($_POST['email']);
    $nom_complet = sanitize($_POST['nom_complet']);
    $access_code = $_POST['access_code'];

    $errors = [];

    // Vérification code d'accès
    if ($access_code !== ACCESS_CODE) {
        $errors[] = "Code d'accès invalide";
    }

    // Vérification mot de passe
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    // Vérification si utilisateur existe déjà
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE nom_utilisateur = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $errors[] = "Nom d'utilisateur ou email déjà utilisé";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur de vérification: " . $e->getMessage();
    }

    // Création du compte si pas d'erreurs
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Par défaut

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, email, nom_complet, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email, $nom_complet, $role]);

            $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la création: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="auth-container">
        <!-- Section Illustration -->
        <div class="auth-illustration">
            <div class="illustration-content">
                <div class="illustration-icon">👤</div>
                <h2>Rejoignez-nous</h2>
                <p>Créez votre compte pour accéder au système</p>
            </div>
        </div>

        <!-- Section Formulaire -->
        <div class="auth-form-section">
            <div class="auth-header">
                <h1>Créer un compte</h1>
                <p>Remplissez les informations pour votre compte</p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
                <div class="auth-links">
                    <a href="login.php" class="btn-primary" style="text-align: center; display: block;">Se connecter</a>
                </div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="nom_complet">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet" class="form-control" placeholder="Votre nom complet" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Choisissez un nom d'utilisateur" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="votre@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 caractères" required>
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Répétez le mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label for="access_code">Code d'accès</label>
                        <input type="text" id="access_code" name="access_code" class="form-control" placeholder="Code requis pour l'inscription" required>
                        <small style="color: var(--gray); font-size: 0.85rem;">Demandez le code d'accès à l'administrateur</small>
                    </div>

                    <button type="submit" class="btn-primary">
                        Créer le compte
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <a href="login.php">← Retour à la connexion</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>

</html>