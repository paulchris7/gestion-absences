<?php
session_start();

/**
 * Vérifie si l'utilisateur est connecté en tant qu'administrateur
 * Redirige vers la page de connexion si ce n'est pas le cas
 */
function require_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        $_SESSION['error'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
        header('Location: /index.php');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur est connecté en tant qu'étudiant
 * Redirige vers la page de connexion si ce n'est pas le cas
 */
function require_etudiant() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'etudiant') {
        $_SESSION['error'] = "Vous devez être connecté en tant qu'étudiant pour accéder à cette page.";
        header('Location: /index.php');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur est déjà connecté
 * Redirige vers le tableau de bord approprié si c'est le cas
 */
function redirect_if_logged_in() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'admin') {
            header('Location: /dashboard_admin.php');
        } else {
            header('Location: /dashboard_etudiant.php');
        }
        exit;
    }
}

/**
 * Affiche un message d'alerte
 */
function display_alert() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert success"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert error"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['info'])) {
        echo '<div class="alert info"><i class="fas fa-info-circle"></i> ' . $_SESSION['info'] . '</div>';
        unset($_SESSION['info']);
    }
}
