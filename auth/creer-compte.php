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

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide";
    }

    // Vérification si utilisateur existe déjà
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE nom_utilisateur = ? OR email = ?");
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
            $role = 'user'; // Rôle par défaut

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, nom_complet, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $nom_complet, $role]);

            $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
            $show_success = true;
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
                <div class="illustration-icon">🚀</div>
                <h2>Commencez l'aventure</h2>
                <p>Rejoignez l'équipe BRALIMA</p>
            </div>
        </div>

        <!-- Section Formulaire -->
        <div class="auth-form-section">
            <div class="auth-header">
                <h1>Créer un compte</h1>
                <p>Remplissez vos informations pour commencer</p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert success">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🎉</div>
                        <h3 style="margin-bottom: 10px;">Compte créé avec succès!</h3>
                        <p style="margin-bottom: 20px;">Vous pouvez maintenant vous connecter à votre compte.</p>
                        <a href="login.php" class="btn-primary" style="display: inline-block; padding: 12px 30px; text-decoration: none;">
                            Se connecter
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <div class="code-hint">
                    <strong>💡 Information:</strong> Vous avez besoin d'un code d'accès pour créer un compte. Contactez l'administrateur.
                </div>

                <form method="POST" class="auth-form" id="registerForm">
                    <div class="form-group">
                        <label for="nom_complet">Nom complet *</label>
                        <input type="text" id="nom_complet" name="nom_complet" class="form-control"
                            placeholder="Ex: John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" id="username" name="username" class="form-control"
                            placeholder="Ex: johndoe" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email *</label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="exemple@bralima.cd" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Minimum 6 caractères" required>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            placeholder="Répétez votre mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label for="access_code">Code d'accès *</label>
                        <input type="text" id="access_code" name="access_code" class="form-control"
                            placeholder="Entrez le code d'accès" required>
                    </div>

                    <button type="submit" class="btn-primary">
                        Créer mon compte
                    </button>
                </form>

                <div class="auth-links">
                    <a href="login.php">← Déjà un compte? Se connecter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>

</html>