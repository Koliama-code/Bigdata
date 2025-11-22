-- Création de la base de données
CREATE DATABASE IF NOT EXISTS bralima_bigdata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bralima_bigdata;

-- Table CLIENTS
CREATE TABLE clients (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom_client VARCHAR(100) NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif') DEFAULT 'actif'
);

-- Table CATEGORIES_PRODUIT
CREATE TABLE categories_produit (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(50) NOT NULL,
    description TEXT
);

-- Table PRODUITS
CREATE TABLE produits (
    id_produit INT PRIMARY KEY AUTO_INCREMENT,
    designation VARCHAR(100) NOT NULL,
    id_categorie INT,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    quantite_stock INT DEFAULT 0,
    stock_alerte INT DEFAULT 10,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categories_produit(id_categorie)
);

-- Table VENTES
CREATE TABLE ventes (
    id_vente INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(15,2) DEFAULT 0,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'confirmee',
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
);

-- Table VENTE_DETAILS (ancienne table CONTENIR)
CREATE TABLE vente_details (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_vente INT,
    id_produit INT,
    quantite_vendue INT NOT NULL,
    prix_vente DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_vente) REFERENCES ventes(id_vente) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
);

-- Table UTILISATEURS (pour l'administration)
CREATE TABLE utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom_utilisateur VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(100) NOT NULL,
    role ENUM('admin', 'gestionnaire', 'consultant') DEFAULT 'consultant',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des données de base
INSERT INTO categories_produit (nom_categorie, description) VALUES
('Bières', 'Produits brassicoles de la BRALIMA'),
('Boissons Gazeuses', 'Boissons non alcoolisées'),
('Glaces', 'Blocs de glace pour conservation');

INSERT INTO produits (designation, id_categorie, prix_unitaire, quantite_stock) VALUES
('Primus 65cl', 1, 1500, 500),
('Turbo King 65cl', 1, 1800, 300),
('Mutzig Class 65cl', 1, 1600, 400),
('Fanta Orange 33cl', 2, 800, 1000),
('Coca Cola 33cl', 2, 800, 1200),
('Bloc Glace 10kg', 3, 500, 200);

INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, nom_complet, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur System', 'admin'),
('gestionnaire', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gestionnaire Ventes', 'gestionnaire');

-- Création des index pour l'optimisation
CREATE INDEX idx_ventes_date ON ventes(date_vente);
CREATE INDEX idx_ventes_client ON ventes(id_client);
CREATE INDEX idx_produits_categorie ON produits(id_categorie);