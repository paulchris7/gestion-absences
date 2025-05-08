<?php
require_once 'config.php';
$filieres = getFilieres($db); // <-- Ajouter cette ligne
$modules = [];

// Si une filière est sélectionnée...
if(isset($_GET['filiere_id']) && !empty($_GET['filiere_id'])) {
    $filiere_id = intval($_GET['filiere_id']);
    $modules = getModulesByFiliere($db, $filiere_id);
}
// Fonction pour récupérer les filières depuis la base de données
function getFilieres($db) {
    $query = $db->query("SELECT * FROM filieres ORDER BY nom");
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les modules d'une filière
function getModulesByFiliere($db, $filiere_id) {
    $query = $db->prepare("SELECT * FROM modules WHERE filiere_id = ? ORDER BY nom");
    $query->execute([$filiere_id]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour vérifier si un email existe déjà
function emailExists($db, $email) {
    $query = $db->prepare("SELECT COUNT(*) FROM etudiants WHERE email = ?");
    $query->execute([$email]);
    return $query->fetchColumn() > 0;
}

// Fonction pour vérifier si un numéro Apogée existe déjà
function apogeeExists($db, $apogee) {
    $query = $db->prepare("SELECT COUNT(*) FROM etudiants WHERE apogee = ?");
    $query->execute([$apogee]);
    return $query->fetchColumn() > 0;
}

// Fonction pour hacher un mot de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Fonction pour valider une photo uploadée
function validatePhoto($file) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return "Le format de l'image doit être JPEG ou PNG.";
    }
    
    if ($file['size'] > $maxSize) {
        return "La taille de l'image ne doit pas dépasser 2MB.";
    }
    
    return true;
}
?>
<html>
<link 
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
  rel="stylesheet" 
  integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" 
  crossorigin="anonymous">
<link rel="stylesheet" href="assets/styles.css">
<div class="container mt-5">
    <h2 class="text-center mb-4">Inscription au système de gestion des absences</h2>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Formulaire d'inscription
                </div>
                <div class="card-body">
                    <form action="traitement_inscription.php" method="post" enctype="multipart/form-data" id="formInscription">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prenom">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="form-text text-muted">Votre email institutionnel (@etud.ensa.ma)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="apogee">Numéro Apogée *</label>
                            <input type="text" class="form-control" id="apogee" name="apogee" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="filiere">Filière *</label>
                            <select class="form-control" id="filiere" name="filiere" required>
                                <option value="">-- Sélectionnez une filière --</option>
                                <?php foreach ($filieres as $filiere): ?>
                                    <option value="<?= $filiere['id'] ?>" <?= isset($filiere_id) && $filiere_id == $filiere['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($filiere['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="module">Module *</label>
                            <select class="form-control" id="module" name="module" required>
                                <option value="">-- Sélectionnez d'abord une filière --</option>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Minimum 8 caractères, avec au moins une majuscule et un chiffre</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo">Photo </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/jpeg,image/png" required>
                                <label class="custom-file-label" for="photo">Choisir un fichier</label>
                            </div>
                            <small class="form-text text-muted">Format JPEG ou PNG, max 2MB</small>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="conditions" name="conditions" required>
                            <label class="form-check-label" for="conditions">J'accepte les conditions d'utilisation *</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#filiere').change(function() {
        var filiereId = $(this).val();

        if(filiereId) {
            $.ajax({
                url: 'get_modules.php',
                type: 'GET',
                data: { filiere_id: filiereId },
                success: function(data) {
                    var modules = JSON.parse(data);
                    var moduleSelect = $('#module');
                    moduleSelect.empty();
                    moduleSelect.append('<option value="">-- Sélectionnez un module --</option>');
                    modules.forEach(function(module) {
                        moduleSelect.append('<option value="' + module.id + '">' + module.nom + '</option>');
                    });
                }
            });
        } else {
            $('#module').html('<option value="">-- Sélectionnez d\'abord une filière --</option>');
        }
    });
</script>

</html>
