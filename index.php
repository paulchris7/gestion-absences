<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Rediriger si déjà connecté
redirect_if_logged_in();

// Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    if (empty($identifier) || empty($password) || empty($user_type)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        try {
            $pdo = connect();
            
            if ($user_type === 'admin') {
                // Connexion administrateur
                $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE username = :username");
                $stmt->execute(['username' => $identifier]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                    header('Location: dashboard_admin.php');
                    exit;
                } else {
                    $error = 'Identifiant ou mot de passe incorrect.';
                }
            } else {
                // Connexion étudiant
                $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE apogee = :apogee");
                $stmt->execute(['apogee' => $identifier]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'etudiant';
                    $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                    $_SESSION['user_filiere'] = $user['filiere_id'];
                    header('Location: dashboard_etudiant.php');
                    exit;
                } else {
                    $error = 'Numéro Apogée ou mot de passe incorrect.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données: ' . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php display_alert(); ?>
            
            <form action="index.php" method="post">
                <div class="auth-tabs">
                    <div class="auth-tab <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] === 'admin') ? 'active' : ''; ?>" data-type="admin">
                        <i class="fas fa-user-shield"></i> Administrateur
                    </div>
                    <div class="auth-tab <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'etudiant') ? 'active' : ''; ?>" data-type="etudiant">
                        <i class="fas fa-user-graduate"></i> Étudiant
                    </div>
                </div>
                
                <input type="hidden" name="user_type" id="user_type" value="<?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'etudiant') ? 'etudiant' : 'admin'; ?>">
                
                <div class="form-group">
                    <label for="identifier" id="identifier_label">
                        <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'etudiant') ? 'Numéro Apogée' : 'Nom d\'utilisateur'; ?>
                    </label>
                    <input type="text" class="form-control" id="identifier" name="identifier" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-20">
                <p>Vous êtes étudiant et vous n'avez pas de compte ?</p>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.auth-tab');
        const userTypeInput = document.getElementById('user_type');
        const identifierLabel = document.getElementById('identifier_label');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Retirer la classe active de tous les onglets
                tabs.forEach(t => t.classList.remove('active'));
                
                // Ajouter la classe active à l'onglet cliqué
                this.classList.add('active');
                
                // Mettre à jour le champ caché
                const userType = this.getAttribute('data-type');
                userTypeInput.value = userType;
                
                // Mettre à jour le label
                identifierLabel.textContent = userType === 'etudiant' ? 'Numéro Apogée' : 'Nom d\'utilisateur';
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
