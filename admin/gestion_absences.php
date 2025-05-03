<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est un administrateur
require_admin();

// Initialiser les variables
$absences = [];
$filieres = [];
$modules = [];
$filiere_filter = $_GET['filiere'] ?? '';
$module_filter = $_GET['module'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$justifiee_filter = $_GET['justifiee'] ?? '';

try {
    $pdo = connect();
    
    // Récupérer toutes les filières
    $stmt = $pdo->query("SELECT * FROM filieres ORDER BY code");
    $filieres = $stmt->fetchAll();
    
    // Récupérer les modules (en fonction de la filière sélectionnée)
    $query = "SELECT * FROM modules";
    $params = [];
    
    if (!empty($filiere_filter)) {
        $query .= " WHERE filiere_id = :filiere_id";
        $params['filiere_id'] = $filiere_filter;
    }
    
    $query .= " ORDER BY semestre, code";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $modules = $stmt->fetchAll();
    
    // Construire la requête pour les absences
    $query = "
        SELECT a.*, e.nom as etudiant_nom, e.prenom as etudiant_prenom, e.apogee,
               s.date_seance, s.heure_debut, s.heure_fin, s.type_seance, s.salle,
               m.nom as module_nom, m.code as module_code, f.code as filiere_code
        FROM absences a
        JOIN etudiants e ON a.etudiant_id = e.id
        JOIN seances s ON a.seance_id = s.id
        JOIN modules m ON s.module_id = m.id
        JOIN filieres f ON m.filiere_id = f.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filtrer par filière si spécifié
    if (!empty($filiere_filter)) {
        $query .= " AND m.filiere_id = :filiere_id";
        $params['filiere_id'] = $filiere_filter;
    }
    
    // Filtrer par module si spécifié
    if (!empty($module_filter)) {
        $query .= " AND m.id = :module_id";
        $params['module_id'] = $module_filter;
    }
    
    // Filtrer par date de début si spécifié
    if (!empty($date_debut)) {
        $query .= " AND s.date_seance >= :date_debut";
        $params['date_debut'] = $date_debut;
    }
    
    // Filtrer par date de fin si spécifié
    if (!empty($date_fin)) {
        $query .= " AND s.date_seance <= :date_fin";
        $params['date_fin'] = $date_fin;
    }
    
    // Filtrer par justification si spécifié
    if ($justifiee_filter !== '') {
        $query .= " AND a.justifiee = :justifiee";
        $params['justifiee'] = $justifiee_filter;
    }
    
    $query .= " ORDER BY s.date_seance DESC, s.heure_debut DESC, e.nom, e.prenom";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $absences = $stmt->fetchAll();
    
    // Récupérer les justificatifs pour chaque absence
    foreach ($absences as &$absence) {
        $stmt = $pdo->prepare("
            SELECT * FROM justificatifs WHERE absence_id = :absence_id
        ");
        $stmt->execute(['absence_id' => $absence['id']]);
        $absence['justificatifs'] = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur de base de données: ' . $e->getMessage();
}

// Inclure l'en-tête
include '../includes/header.php';
?>

<div class="flex justify-between align-center mb-20">
    <h1>Gestion des absences</h1>
</div>

<?php display_alert(); ?>

<div class="card mb-20">
    <div class="card-header">
        <h2>Filtres</h2>
    </div>
    <div class="card-body">
        <form action="gestion_absences.php" method="get" class="flex gap-10" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label for="filiere">Filière</label>
                <select class="form-select" id="filiere" name="filiere">
                    <option value="">Toutes les filières</option>
                    <?php foreach ($filieres as $filiere): ?>
                        <option value="<?php echo $filiere['id']; ?>" <?php echo ($filiere_filter == $filiere['id']) ? 'selected' : ''; ?>>
                            <?php echo $filiere['code'] . ' - ' . $filiere['nom']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label for="module">Module</label>
                <select class="form-select" id="module" name="module">
                    <option value="">Tous les modules</option>
                    <?php foreach ($modules as $module): ?>
                        <option value="<?php echo $module['id']; ?>" <?php echo ($module_filter == $module['id']) ? 'selected' : ''; ?>>
                            <?php echo $module['code'] . ' - ' . $module['nom']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="date_debut">Date début</label>
                <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="date_fin">Date fin</label>
                <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="justifiee">Justifiée</label>
                <select class="form-select" id="justifiee" name="justifiee">
                    <option value="">Toutes</option>
                    <option value="1" <?php echo ($justifiee_filter === '1') ? 'selected' : ''; ?>>Oui</option>
                    <option value="0" <?php echo ($justifiee_filter === '0') ? 'selected' : ''; ?>>Non</option>
                </select>
            </div>
            
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
                <a href="gestion_absences.php" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Liste des absences</h2>
    </div>
    <div class="card-body">
        <?php if (empty($absences)): ?>
            <p>Aucune absence trouvée.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Étudiant</th>
                        <th>Filière</th>
                        <th>Module</th>
                        <th>Type</th>
                        <th>Salle</th>
                        <th>Justifiée</th>
                        <th>Justificatif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($absences as $absence): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($absence['date_seance'])); ?></td>
                            <td><?php echo date('H:i', strtotime($absence['heure_debut'])) . ' - ' . date('H:i', strtotime($absence['heure_fin'])); ?></td>
                            <td><?php echo $absence['apogee'] . ' - ' . $absence['etudiant_prenom'] . ' ' . $absence['etudiant_nom']; ?></td>
                            <td><?php echo $absence['filiere_code']; ?></td>
                            <td><?php echo $absence['module_code']; ?></td>
                            <td><?php echo $absence['type_seance']; ?></td>
                            <td><?php echo $absence['salle']; ?></td>
                            <td>
                                <?php if ($absence['justifiee']): ?>
                                    <span class="badge success">Oui</span>
                                <?php else: ?>
                                    <span class="badge danger">Non</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($absence['justificatifs'])): ?>
                                    <?php foreach ($absence['justificatifs'] as $justificatif): ?>
                                        <a href="<?php echo $justificatif['fichier']; ?>" target="_blank" class="btn btn-sm">
                                            <i class="fas fa-file-pdf"></i> Voir
                                        </a>
                                        <span class="badge <?php echo ($justificatif['statut'] === 'Validé') ? 'success' : (($justificatif['statut'] === 'Refusé') ? 'danger' : 'warning'); ?>">
                                            <?php echo $justificatif['statut']; ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <em>Aucun</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Soumission automatique du formulaire de filtre
        const filiereSelect = document.getElementById('filiere');
        if (filiereSelect) {
            filiereSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
