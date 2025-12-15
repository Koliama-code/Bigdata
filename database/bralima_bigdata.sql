-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 27 nov. 2025 à 19:02
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bralima_bigdata`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories_produit`
--

CREATE TABLE `categories_produit` (
  `id_categorie` int(11) NOT NULL,
  `nom_categorie` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories_produit`
--

INSERT INTO `categories_produit` (`id_categorie`, `nom_categorie`, `description`) VALUES
(1, 'Bières', 'Produits brassicoles de la BRALIMA'),
(2, 'Boissons Gazeuses', 'Boissons non alcoolisées'),
(3, 'Glaces', 'Blocs de glace pour conservation');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id_client` int(11) NOT NULL,
  `nom_client` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id_client`, `nom_client`, `adresse`, `telephone`, `email`, `date_creation`, `statut`) VALUES
(1, 'Nathanaël Koliama', 'Q. Himbi, AV de la Mission Num 241', '+243998525877', 'nathanaelkoliama@gmail.com', '2025-11-22 15:32:10', 'actif'),
(2, 'dido', 'Q. Himbi, AV de la Mission Num 241', '+256998525877', 'nathanaelkoliama@gmail.com', '2025-11-25 13:21:20', 'actif'),
(3, 'ADOLPHE', 'Q. Himbi, AV de la Mission Num 241', '+243998525877', 'nathanaelkoliama@gmail.com', '2025-11-25 14:11:27', 'actif'),
(4, 'Yvonne Mwaomange', 'Bloc Motumbe, Q.Plateau Boyoma', '+243998525877', 'yvon@gmail.com', '2025-11-26 16:08:41', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id_produit` int(11) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `quantite_stock` int(11) DEFAULT 0,
  `stock_alerte` int(11) DEFAULT 10,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_produit`, `designation`, `id_categorie`, `prix_unitaire`, `quantite_stock`, `stock_alerte`, `date_creation`) VALUES
(1, 'Primus 65cl', 1, 1500.00, 100, 10, '2025-11-20 21:13:47'),
(2, 'Turbo King 65cl', 1, 1800.00, 300, 10, '2025-11-20 21:13:47'),
(3, 'Mutzig Class 65cl', 1, 1600.00, 99, 10, '2025-11-20 21:13:47'),
(4, 'Fanta Orange 33cl', 2, 800.00, 70, 10, '2025-11-20 21:13:47'),
(5, 'Coca Cola 33cl', 2, 800.00, 4, 10, '2025-11-20 21:13:47'),
(9, 'Turbo 330cl', 1, 2000.00, 50, 10, '2025-11-21 20:36:31'),
(10, 'Energy malt', 2, 1500.00, 0, 10, '2025-11-24 18:06:39'),
(13, 'Vitalo', 2, 1000.00, 50, 10, '2025-11-25 13:19:21'),
(14, 'CLASS', 1, 2000.00, 0, 10, '2025-11-25 14:09:58');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `role` enum('admin','gestionnaire','consultant') DEFAULT 'consultant',
  `statut` enum('actif','inactif') DEFAULT 'actif',
  `date_creation` datetime DEFAULT current_timestamp(),
  `token_reset` varchar(100) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom_utilisateur`, `email`, `mot_de_passe`, `nom_complet`, `role`, `statut`, `date_creation`, `token_reset`, `token_expiration`) VALUES
(1, 'admin', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur System', 'admin', 'actif', '2025-11-20 21:13:47', '56dcf4553e21531d8c69b50334ba0b8bd6a32bd389871e1ce6be93f5f1116fc2', '2025-11-25 18:54:13'),
(2, 'gestionnaire', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gestionnaire Ventes', 'gestionnaire', 'actif', '2025-11-20 21:13:47', NULL, NULL),
(3, 'Kol', 'kol@gmail.com', '$2y$10$M1LHGqk8xIK/S7uDNe5FyOoT1TK1Id8ngZ2lmRMuTuZBsYzd77g56', 'KOLIAMA Nathanaël', '', 'actif', '2025-11-25 18:41:34', 'b7aa4d610de54bc1f8786da29c69466d64210fb838e105c79fb607968db6d26e', '2025-11-25 18:56:06');

-- --------------------------------------------------------

--
-- Structure de la table `ventes`
--

CREATE TABLE `ventes` (
  `id_vente` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `date_vente` datetime DEFAULT current_timestamp(),
  `montant_total` decimal(15,2) DEFAULT 0.00,
  `statut` enum('en_attente','confirmee','annulee') DEFAULT 'confirmee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ventes`
--

INSERT INTO `ventes` (`id_vente`, `id_client`, `date_vente`, `montant_total`, `statut`) VALUES
(3, 1, '2025-11-24 18:14:53', 215000.00, 'confirmee'),
(5, 2, '2025-11-25 13:24:03', 650000.00, 'confirmee'),
(6, 3, '2025-11-25 14:12:57', 860000.00, 'confirmee'),
(7, 4, '2025-11-26 16:11:29', 497500.00, 'confirmee');

-- --------------------------------------------------------

--
-- Structure de la table `vente_details`
--

CREATE TABLE `vente_details` (
  `id_detail` int(11) NOT NULL,
  `id_vente` int(11) DEFAULT NULL,
  `id_produit` int(11) DEFAULT NULL,
  `quantite_vendue` int(11) NOT NULL,
  `prix_vente` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vente_details`
--

INSERT INTO `vente_details` (`id_detail`, `id_vente`, `id_produit`, `quantite_vendue`, `prix_vente`) VALUES
(6, 3, 10, 90, 1500.00),
(7, 3, 4, 100, 800.00),
(9, 5, 13, 50, 1000.00),
(10, 5, 1, 400, 1500.00),
(11, 6, 14, 150, 2000.00),
(12, 6, 4, 700, 800.00),
(13, 7, 10, 9, 1500.00),
(14, 7, 5, 5, 800.00),
(15, 7, 3, 300, 1600.00);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories_produit`
--
ALTER TABLE `categories_produit`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id_client`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `idx_produits_categorie` (`id_categorie`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `nom_utilisateur` (`nom_utilisateur`);

--
-- Index pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD PRIMARY KEY (`id_vente`),
  ADD KEY `idx_ventes_date` (`date_vente`),
  ADD KEY `idx_ventes_client` (`id_client`);

--
-- Index pour la table `vente_details`
--
ALTER TABLE `vente_details`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_vente` (`id_vente`),
  ADD KEY `id_produit` (`id_produit`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories_produit`
--
ALTER TABLE `categories_produit`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `ventes`
--
ALTER TABLE `ventes`
  MODIFY `id_vente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `vente_details`
--
ALTER TABLE `vente_details`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories_produit` (`id_categorie`);

--
-- Contraintes pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD CONSTRAINT `ventes_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`);

--
-- Contraintes pour la table `vente_details`
--
ALTER TABLE `vente_details`
  ADD CONSTRAINT `vente_details_ibfk_1` FOREIGN KEY (`id_vente`) REFERENCES `ventes` (`id_vente`) ON DELETE CASCADE,
  ADD CONSTRAINT `vente_details_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
