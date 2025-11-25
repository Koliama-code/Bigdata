<?php
// Vérifier si l'utilisateur est connecté
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Rediriger vers la page de login si non connecté
function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /bralima_app/auth/login.php');
        exit;
    }
}

// Vérifier les permissions
function hasPermission($required_role)
{
    if (!isLoggedIn()) return false;

    $roles = ['consultant' => 1, 'gestionnaire' => 2, 'admin' => 3];
    $user_level = $roles[$_SESSION['role']] ?? 0;
    $required_level = $roles[$required_role] ?? 0;

    return $user_level >= $required_level;
}

// Rediriger si pas les permissions
function requirePermission($required_role)
{
    if (!hasPermission($required_role)) {
        $_SESSION['error'] = "Accès non autorisé!";
        header('Location: ../index.php');
        exit;
    }
}

// Obtenir les informations de l'utilisateur connecté
function getCurrentUser()
{
    if (!isLoggedIn()) return null;

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'nom_complet' => $_SESSION['nom_complet'],
        'role' => $_SESSION['role']
    ];
}
