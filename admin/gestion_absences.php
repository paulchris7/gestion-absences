<?php
require_once __DIR__ . '/../config.php';

$query = $db->query("
    SELECT 
        a.id, 
        e.nom AS etudiant_nom, 
        e.prenom, 
        m.nom AS module_nom, 
        a.date_enregistrement, 
        a.justifiee,
        a.motif_justification
    FROM absences a
    JOIN etudiants e ON a.etudiant_id = e.id
    JOIN modules m ON a.module_id = m.id
    ORDER BY a.date_enregistrement DESC
");


$absences = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Absences</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Liste des absences</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Module</th>
                <th>Date</th>
                <th>Justification</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absences as $absence): ?>
                <tr>
                    <td><?= htmlspecialchars($absence['etudiant_nom'] . ' ' . $absence['prenom']) ?></td>
                    <td><?= htmlspecialchars($absence['module_nom']) ?></td>
                    <td><?= htmlspecialchars($absence['date_enregistrement']) ?></td>
                    <td><?= htmlspecialchars($absence['motif_justification']) ?></td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
