<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est un étudiant
require_etudiant();

// Initialiser les variables
$etudiant = null;
$modules = [];
$absences = [];
$total_absences = 0;
$absences_justifiees = 0;

try {
    $pdo = connect();
    
    // Récupérer les informations de l'étudiant
    $stmt = $pdo->prepare("
        SELECT e.*, f.nom as filiere_nom, f.code as filiere_code
        FROM etudiants e
        JOIN filieres f ON e.filiere_id = f.id
        WHERE e.id = :id
    ");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $etudiant = $stmt->fetch();
    
    if (!$etudiant) {
        throw new Exception('Étudiant non trouvé.');
    }
    
    // Récupérer les modules de l'étudiant
    $stmt = $pdo->prepare("
        SELECT m.*, im.annee_universitaire
        FROM inscriptions_modules im
        JOIN modules m ON im.module_id = m.id
        WHERE im.etudiant_id = :etudiant_id
        ORDER BY m.semestre, m.nom
    ");
    $stmt->execute(['etudiant_id' => $_SESSION['user_id']]);
    $modules = $stmt->fetchAll();
    
    // Récupérer les absences de l'étudiant
    $stmt = $pdo->prepare("
        SELECT a.*, s.date_seance, s.heure_debut, s.heure_fin, s.type_seance, m.nom as module_nom, m.code as module_code
        FROM absences a
        JOIN seances s ON a.seance_id = s.id
        JOIN modules m ON s.module_id = m.id
        WHERE a.etudiant_id = :etudiant_id
        ORDER BY s.date_seance DESC, s.heure_debut DESC
        LIMIT 5
    ");
    $stmt->execute(['etudiant_id' => $_SESSION['user_id']]);
    $absences = $stmt->fetchAll();
    
    // Calculer les statistiques d'absences
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM absences WHERE etudiant_id = :etudiant_id");
    $stmt->execute(['etudiant_id' => $_SESSION['user_id']]);
    $total_absences = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM absences WHERE etudiant_id = :etudiant_id AND justifiee = 1");
    $stmt->execute(['etudiant_id' => $_SESSION['user_id']]);
    $absences_justifiees = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<h1 class="mb-20">Tableau de bord étudiant</h1>

<?php display_alert(); ?>

<?php if ($etudiant): ?>
<div class="card mb-20">
    <div class="card-header">
        <h2>Informations personnelles</h2>
    </div>
    <div class="card-body">
        <div class="flex" style="flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1; min-width: 300px;">
                <p><strong>Nom:</strong> <?php echo $etudiant['prenom'] . ' ' . $etudiant['nom']; ?></p>
                <p><strong>Numéro Apogée:</strong> <?php echo $etudiant['apogee']; ?></p>
                <p><strong>Email:</strong> <?php echo $etudiant['email']; ?></p>
                <p><strong>Filière:</strong> <?php echo $etudiant['filiere_code'] . ' - ' . $etudiant['filiere_nom']; ?></p>
                <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y', strtotime($etudiant['date_inscription'])); ?></p>
            </div>
            <?php if ($etudiant['photo']): ?>
            <div style="flex: 0 0 150px;">
                <img src="<?php echo $etudiant['photo']; ?>" alt="Photo de profil" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px;">
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard-stats">
    <div class="stat-card primary">
        <i class="fas fa-book"></i>
        <div class="stat-card-content">
            <h3><?php echo count($modules); ?></h3>
            <p>Modules inscrits</p>
        </div>
    </div>
    
    <div class="stat-card danger">
        <i class="fas fa-calendar-times"></i>
        <div class="stat-card-content">
            <h3><?php echo $total_absences; ?></h3>
            <p>Total absences</p>
        </div>
    </div>
    
    <div class="stat-card success">
        <i class="fas fa-check-circle"></i>
        <div class="stat-card-content">
            <h3><?php echo $absences_justifiees; ?></h3>
            <p>Absences justifiées</p>
        </div>
    </div>
    
    <div class="stat-card warning">
        <i class="fas fa-exclamation-circle"></i>
        <div class="stat-card-content">
            <h3><?php echo $total_absences - $absences_justifiees; ?></h3>
            <p>Absences non justifiées</p>
        </div>
    </div>
</div>

<div class="flex gap-10" style="flex-wrap: wrap;">
    <div class="card" style="flex: 1; min-width: 300px;">
        <div class="card-header">
            <h2>Mes modules</h2>
        </div>
        <div class="card-body">
            <?php if (empty($modules)): ?>
                <p>Vous n'êtes inscrit à aucun module.</p>
            <?php else: ?>
                <div class="form-group">
                    <label><strong>Semestre 1</strong></label>
                    <ul class="list-group">
                        <?php foreach ($modules as $module): ?>
                            <?php if ($module['semestre'] === 'S1'): ?>
                                <li class="list-group-item">
                                    <?php echo $module['code'] . ' - ' . $module['nom']; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label><strong>Semestre 2</strong></label>
                    <ul class="list-group">
                        <?php foreach ($modules as $module): ?>
                            <?php if ($module['semestre'] === 'S2'): ?>
                                <li class="list-group-item">
                                    <?php echo $module['code'] . ' - ' . $module['nom']; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card" style="flex: 1; min-width: 300px;">
        <div class="card-header">
            <h2>Dernières absences</h2>
            <a href="etudiant/bilan_absences.php" class="btn btn-sm">Voir toutes</a>
        </div>
        <div class="card-body">
            <?php if (empty($absences)): ?>
                <p>Aucune absence enregistrée.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Type</th>
                            <th>Justifiée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($absences as $absence): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($absence['date_seance'])); ?></td>
                                <td><?php echo $absence['module_code']; ?></td>
                                <td><?php echo $absence['type_seance']; ?></td>
                                <td>
                                    <?php if ($absence['justifiee']): ?>
                                        <span class="badge success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge danger">Non</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
