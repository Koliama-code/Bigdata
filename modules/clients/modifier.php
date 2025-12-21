<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

$success = '';
$error = '';

// Récupérer l'ID du client
$id_client = $_GET['id'] ?? null;
if (!$id_client) {
    header('Location: liste.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Récupérer le client
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $client = $stmt->fetch();

    if (!$client) {
        $error = "Client non trouvé!";
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_client = sanitize($_POST['nom_client']);
        $telephone = sanitize($_POST['telephone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $adresse = sanitize($_POST['adresse'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE clients 
            SET nom_client = ?, telephone = ?, email = ?, adresse = ?
            WHERE id_client = ?
        ");

        $stmt->execute([$nom_client, $telephone, $email, $adresse, $id_client]);

        $success = "✅ Client modifié avec succès!";

        // Recharger les données
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
        $stmt->execute([$id_client]);
        $client = $stmt->fetch();
    }
} catch (Exception $e) {
    $error = "❌ Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Modifier Client - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #a0aec0;
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #c6f6d5;
            color: #276749;
        }

        .alert-error {
            background: #fed7d7;
            color: #c53030;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>✏️ Modifier le Client</h1>
                <p>Modifier les informations du client</p>
            </div>

            <div class="form-container">
                <?php if ($error && !$client): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                    <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
                <?php else: ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="content-card">
                        <form method="POST">
                            <div class="form-group">
                                <label for="nom_client">Nom du client *</label>
                                <input type="text" id="nom_client" name="nom_client" required
                                    value="<?php echo $client['nom_client']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="text" id="telephone" name="telephone"
                                    value="<?php echo $client['telephone'] ?? ''; ?>"
                                    placeholder="Ex: +243 81 234 5678">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                    value="<?php echo $client['email'] ?? ''; ?>"
                                    placeholder="Ex: client@example.com">
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse</label>
                                <textarea id="adresse" name="adresse" rows="3"
                                    placeholder="Adresse complète du client..."><?php
                                                                                echo $client['adresse'] ?? '';
                                                                                ?></textarea>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
                                <a href="liste.php" class="btn btn-secondary">❌ Annuler</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>