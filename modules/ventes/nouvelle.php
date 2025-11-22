<?php
include '../../includes/config.php';
include '../../includes/auth.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // 1. Créer la vente
        $id_client = $_POST['id_client'] ?: null;
        $stmt = $pdo->prepare("
            INSERT INTO ventes (id_client, montant_total, statut) 
            VALUES (?, 0, 'en_attente')
        ");
        $stmt->execute([$id_client]);
        $id_vente = $pdo->lastInsertId();

        $montant_total = 0;

        // 2. Ajouter les produits
        foreach ($_POST['produits'] as $produit_data) {
            $id_produit = $produit_data['id_produit'];
            $quantite = intval($produit_data['quantite']);

            if ($quantite > 0) {
                // Récupérer le prix du produit
                $stmt = $pdo->prepare("SELECT prix_unitaire, quantite_stock FROM produits WHERE id_produit = ?");
                $stmt->execute([$id_produit]);
                $produit = $stmt->fetch();

                if (!$produit) {
                    throw new Exception("Produit non trouvé");
                }

                if ($produit['quantite_stock'] < $quantite) {
                    throw new Exception("Stock insuffisant pour " . $produit['designation']);
                }

                $prix_vente = $produit['prix_unitaire'];
                $sous_total = $prix_vente * $quantite;
                $montant_total += $sous_total;

                // Ajouter le détail de vente
                $stmt = $pdo->prepare("
                    INSERT INTO vente_details (id_vente, id_produit, quantite_vendue, prix_vente) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$id_vente, $id_produit, $quantite, $prix_vente]);

                // Mettre à jour le stock
                $stmt = $pdo->prepare("
                    UPDATE produits 
                    SET quantite_stock = quantite_stock - ? 
                    WHERE id_produit = ?
                ");
                $stmt->execute([$quantite, $id_produit]);
            }
        }

        // 3. Mettre à jour le montant total de la vente
        $stmt = $pdo->prepare("UPDATE ventes SET montant_total = ? WHERE id_vente = ?");
        $stmt->execute([$montant_total, $id_vente]);

        $pdo->commit();

        // Confirmer automatiquement si demandé
        if (isset($_POST['confirmer_auto'])) {
            $stmt = $pdo->prepare("UPDATE ventes SET statut = 'confirmee' WHERE id_vente = ?");
            $stmt->execute([$id_vente]);
        }

        $success = "✅ Vente créée avec succès! ID: #" . $id_vente;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Vente - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 800px;
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

        .btn-success {
            background: #38a169;
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

        .produit-ligne {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .produit-ligne select,
        .produit-ligne input {
            flex: 1;
        }

        .produit-ligne .btn-remove {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .total-section {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>💰 Nouvelle Vente</h1>
                <p>Enregistrer une nouvelle transaction commerciale</p>
            </div>

            <div class="form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="content-card">
                    <form method="POST" id="venteForm">
                        <div class="form-group">
                            <label for="id_client">Client (optionnel)</label>
                            <select id="id_client" name="id_client">
                                <option value="">Sélectionner un client</option>
                                <?php
                                $pdo = getDBConnection();
                                $stmt = $pdo->query("SELECT * FROM clients WHERE statut = 'actif' ORDER BY nom_client");
                                while ($client = $stmt->fetch()):
                                ?>
                                    <option value="<?php echo $client['id_client']; ?>">
                                        <?php echo $client['nom_client']; ?> - <?php echo $client['telephone']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <h3>📦 Produits de la vente</h3>
                            <div id="produits-container">
                                <div class="produit-ligne">
                                    <select name="produits[0][id_produit]" required onchange="updatePrix(this)">
                                        <option value="">Sélectionner un produit</option>
                                        <?php
                                        $pdo = getDBConnection();
                                        $stmt = $pdo->query("
                                            SELECT p.*, cp.nom_categorie 
                                            FROM produits p 
                                            LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
                                            WHERE p.quantite_stock > 0 
                                            ORDER BY p.designation
                                        ");
                                        while ($produit = $stmt->fetch()):
                                        ?>
                                            <option value="<?php echo $produit['id_produit']; ?>" data-prix="<?php echo $produit['prix_unitaire']; ?>" data-stock="<?php echo $produit['quantite_stock']; ?>">
                                                <?php echo $produit['designation']; ?> - <?php echo number_format($produit['prix_unitaire'], 0, ',', ' '); ?> CDF (Stock: <?php echo $produit['quantite_stock']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="number" name="produits[0][quantite]" min="1" value="1" required
                                        placeholder="Quantité" onchange="calculerTotal()">
                                    <span class="sous-total">0 CDF</span>
                                    <button type="button" class="btn-remove" onclick="removeProduit(this)">🗑️</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" onclick="addProduit()">➕ Ajouter un produit</button>
                        </div>

                        <div class="total-section">
                            <h3>Total: <span id="montant-total">0</span> CDF</h3>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="confirmer_auto" value="1" checked>
                                Confirmer automatiquement cette vente
                            </label>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 2rem;">
                            <button type="submit" class="btn btn-success">💾 Enregistrer la vente</button>
                            <a href="liste.php" class="btn btn-secondary">❌ Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        let produitCount = 1;

        function addProduit() {
            const container = document.getElementById('produits-container');
            const newLigne = document.createElement('div');
            newLigne.className = 'produit-ligne';
            newLigne.innerHTML = `
            <select name="produits[${produitCount}][id_produit]" required onchange="updatePrix(this)">
                <option value="">Sélectionner un produit</option>
                <?php
                $stmt = $pdo->query("
                    SELECT p.*, cp.nom_categorie 
                    FROM produits p 
                    LEFT JOIN categories_produit cp ON p.id_categorie = cp.id_categorie 
                    WHERE p.quantite_stock > 0 
                    ORDER BY p.designation
                ");
                while ($produit = $stmt->fetch()):
                ?>
                <option value="<?php echo $produit['id_produit']; ?>" data-prix="<?php echo $produit['prix_unitaire']; ?>" data-stock="<?php echo $produit['quantite_stock']; ?>">
                    <?php echo $produit['designation']; ?> - <?php echo number_format($produit['prix_unitaire'], 0, ',', ' '); ?> CDF (Stock: <?php echo $produit['quantite_stock']; ?>)
                </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="produits[${produitCount}][quantite]" min="1" value="1" required 
                   placeholder="Quantité" onchange="calculerTotal()">
            <span class="sous-total">0 CDF</span>
            <button type="button" class="btn-remove" onclick="removeProduit(this)">🗑️</button>
        `;
            container.appendChild(newLigne);
            produitCount++;
        }

        function removeProduit(button) {
            if (document.querySelectorAll('.produit-ligne').length > 1) {
                button.parentElement.remove();
                calculerTotal();
            }
        }

        function updatePrix(select) {
            const ligne = select.parentElement;
            const quantiteInput = ligne.querySelector('input[type="number"]');
            const sousTotalSpan = ligne.querySelector('.sous-total');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption.value) {
                const prix = parseFloat(selectedOption.getAttribute('data-prix'));
                const stock = parseInt(selectedOption.getAttribute('data-stock'));
                const quantite = parseInt(quantiteInput.value) || 1;

                // Limiter la quantité au stock disponible
                if (quantite > stock) {
                    quantiteInput.value = stock;
                    alert('Quantité ajustée au stock disponible: ' + stock);
                }

                const sousTotal = prix * (quantiteInput.value || 1);
                sousTotalSpan.textContent = sousTotal.toLocaleString() + ' CDF';
            } else {
                sousTotalSpan.textContent = '0 CDF';
            }

            calculerTotal();
        }

        function calculerTotal() {
            let total = 0;
            document.querySelectorAll('.produit-ligne').forEach(ligne => {
                const select = ligne.querySelector('select');
                const quantiteInput = ligne.querySelector('input[type="number"]');
                const selectedOption = select.options[select.selectedIndex];

                if (selectedOption.value && quantiteInput.value) {
                    const prix = parseFloat(selectedOption.getAttribute('data-prix'));
                    const quantite = parseInt(quantiteInput.value);
                    total += prix * quantite;

                    // Mettre à jour le sous-total
                    const sousTotalSpan = ligne.querySelector('.sous-total');
                    sousTotalSpan.textContent = (prix * quantite).toLocaleString() + ' CDF';
                }
            });

            document.getElementById('montant-total').textContent = total.toLocaleString();
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            calculerTotal();
            document.querySelectorAll('select').forEach(select => {
                if (select.value) updatePrix(select);
            });
        });
    </script>
</body>

</html>