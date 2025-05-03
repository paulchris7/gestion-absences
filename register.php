<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Rediriger si déjà connecté
redirect_if_logged_in();

// Initialiser les variables
$error = '';
$success = '';
$filieres = [];
$modules = [];

try {
    $pdo = connect();
    
    // Récupérer toutes les filières
    $stmt = $pdo->query("SELECT * FROM filieres ORDER BY nom");
    $filieres = $stmt->fetchAll();
    
    // Si une filière est sélectionnée, récupérer ses modules
    if (isset($_POST['filiere_id']) && !empty($_POST['filiere_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE filiere_id = :filiere_id ORDER BY semestre, nom");
        $stmt->execute(['filiere_id' => $_POST['filiere_id']]);
        $modules = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = 'Erreur de connexion à la base de données: ' . $e->getMessage();
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $apogee = $_POST['apogee'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $filiere_id = $_POST['filiere_id'] ?? '';
    $module_ids = $_POST['module_ids'] ?? [];
    
    // Validation des champs
    if (empty($apogee) || empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($filiere_id) || empty($module_ids)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        try {
            $pdo = connect();
            
            // Vérifier si l'apogée existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE apogee = :apogee");
            $stmt->execute(['apogee' => $apogee]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Ce numéro Apogée est déjà utilisé.';
            } else {
                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Cette adresse email est déjà utilisée.';
                } else {
                    // Tout est bon, on peut insérer l'étudiant
                    $pdo->beginTransaction();
                    
                    // Hachage du mot de passe
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertion de l'étudiant
                    $stmt = $pdo->prepare("
                        INSERT INTO etudiants (apogee, nom, prenom, email, password, filiere_id)
                        VALUES (:apogee, :nom, :prenom, :email, :password, :filiere_id)
                    ");
                    $stmt->execute([
                        'apogee' => $apogee,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'password' => $hashed_password,
                        'filiere_id' => $filiere_id
                    ]);
                    
                    $etudiant_id = $pdo->lastInsertId();
                    
                    // Inscription aux modules sélectionnés
                    $annee_universitaire = date('Y') . '-' . (date('Y') + 1);
                    
                    foreach ($module_ids as $module_id) {
                        $stmt = $pdo->prepare("
                            INSERT INTO inscriptions_modules (etudiant_id, module_id, annee_universitaire)
                            VALUES (:etudiant_id, :module_id, :annee_universitaire)
                        ");
                        $stmt->execute([
                            'etudiant_id' => $etudiant_id,
                            'module_id' => $module_id,
                            'annee_universitaire' => $annee_universitaire
                        ]);
                    }
                    
                    $pdo->commit();
                    
                    $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                    
                    // Redirection vers la page de connexion après 2 secondes
                    header("refresh:2;url=index.php");
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de l\'inscription: ' . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-plus"></i> Inscription Étudiant</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="apogee">Numéro Apogée</label>
                    <input type="text" class="form-control" id="apogee" name="apogee" value="<?php echo $_POST['apogee'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="filiere_id">Filière</label>
                    <select class="form-select" id="filiere_id" name="filiere_id" required>
                        <option value="">Sélectionner une filière</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?php echo $filiere['id']; ?>" <?php echo (isset($_POST['filiere_id']) && $_POST['filiere_id'] == $filiere['id']) ? 'selected' : ''; ?>>
                                <?php echo $filiere['code'] . ' - ' . $filiere['nom']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (!empty($modules)): ?>
                <div class="form-group">
                    <label>Modules</label>
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label><strong>Semestre 1</strong></label>
                                <?php foreach ($modules as $module): ?>
                                    <?php if ($module['semestre'] === 'S1'): ?>
                                    <div class="form-check">
                                        <input type="checkbox" id="module_<?php echo $module['id']; ?>" name="module_ids[]" value="<?php echo $module['id']; ?>" 
                                            <?php echo (isset($_POST['module_ids']) && in_array($module['id'], $_POST['module_ids'])) ? 'checked' : ''; ?>>
                                        <label for="module_<?php echo $module['id']; ?>"><?php echo $module['code'] . ' - ' . $module['nom']; ?></label>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="form-group">
                                <label><strong>Semestre 2</strong></label>
                                <?php foreach ($modules as $module): ?>
                                    <?php if ($module['semestre'] === 'S2'): ?>
                                    <div class="form-check">
                                        <input type="checkbox" id="module_<?php echo $module['id']; ?>" name="module_ids[]" value="<?php echo $module['id']; ?>"
                                            <?php echo (isset($_POST['module_ids']) && in_array($module['id'], $_POST['module_ids'])) ? 'checked' : ''; ?>>
                                        <label for="module_<?php echo $module['id']; ?>"><?php echo $module['code'] . ' - ' . $module['nom']; ?></label>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <button type="submit" name="register" class="btn btn-block">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-20">
                <p>Vous avez déjà un compte ?</p>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filiereSelect = document.getElementById('filiere_id');
        
        filiereSelect.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
