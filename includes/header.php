<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Absences - ENSA</title>
    <link rel="stylesheet" href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/etudiant/') !== false) ? '../assets/styles.css' : 'assets/styles.css'; ?>">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <h1>ENSA <span>Gestion des Absences</span></h1>
            </div>
            <nav class="main-nav">
                <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                    <ul>
                        <li><a href="/gestion-absences/dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        <li><a href="/gestion-absences/admin/gestion_filieres.php"><i class="fas fa-graduation-cap"></i> Filières</a></li>
                        <li><a href="/gestion-absences/admin/gestion_modules.php"><i class="fas fa-book"></i> Modules</a></li>
                        <li><a href="/gestion-absences/admin/gestion_etudiants.php"><i class="fas fa-user-graduate"></i> Étudiants</a></li>
                        <li><a href="/gestion-absences/admin/gestion_absences.php"><i class="fas fa-calendar-times"></i> Absences</a></li>
                        <li><a href="/gestion-absences/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>

                <?php elseif(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'etudiant'): ?>
                    <ul>
                        <li><a href="/dashboard_etudiant.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                        <li><a href="/etudiant/bilan_absences.php"><i class="fas fa-calendar-times"></i> Mes absences</a></li>
                        <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                <?php else: ?>
                    <ul>
                        <li><a href="/index.php"><i class="fas fa-home"></i> Accueil</a></li>
                        <li><a href="/register.php"><i class="fas fa-user-plus"></i> Inscription</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
