<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'bralima_bigdata');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('APP_NAME', 'BRALIMA MegaData Manager');
define('APP_VERSION', '1.0');

// ⭐⭐ CONFIGURATION SESSION - AJOUTEZ CE CI ⭐⭐
ini_set('session.cookie_lifetime', 86400); // 24 heures
ini_set('session.gc_maxlifetime', 86400);  // 24 heures
session_set_cookie_params(86400);          // 24 heures

// Démarrer la session AVANT tout output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction de connexion à la base de données
function getDBConnection()
{
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion: " . $e->getMessage());
    }
}

// Fonction pour nettoyer les données
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
