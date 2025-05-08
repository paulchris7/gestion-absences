<?php
require_once __DIR__ . '/../config.php';

$query = $db->query("
    SELECT 
        e.id, 
        e.nom, 
        e.prenom, 
        e.email, 
        e.apogee, 
        m.nom AS module_nom 
    FROM etudiants e
    JOIN inscriptions_modules i ON e.id = i.etudiant_id
    JOIN modules m ON i.module_id = m.id
    ORDER BY e.nom
");

$etudiants = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Liste des étudiants inscrits</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Apogée</th>
                <th>Module</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants as $etudiant): ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['email']) ?></td>
                    <td><?= htmlspecialchars($etudiant['apogee']) ?></td>
                    <td><?= htmlspecialchars($etudiant['module_nom']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
