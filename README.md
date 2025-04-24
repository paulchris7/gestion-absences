# Système de Gestion des Absences

Une application web complète permettant la gestion des absences des étudiants, avec différentes interfaces pour administrateurs et étudiants, et intégrant à terme un système de scan de code QR pour l'enregistrement automatique des présences.

## 📋 Fonctionnalités

### Pour les administrateurs
- 🔐 Authentification sécurisée  
- 🏛️ Gestion complète des filières (ajout, modification, suppression)  
- 📚 Gestion des modules (12 modules par filière, répartis en S1 et S2)  
- 👩‍🏫 Gestion des responsables de module  
- 👥 Visualisation des étudiants inscrits par module  
- 📊 Suivi des absences enregistrées  

### Pour les étudiants
- 📝 Inscription via formulaire dédié  
- 🔑 Connexion avec numéro Apogée et mot de passe  
- 📖 Consultation du bilan personnel d'absences  

## 📁 Structure du projet

```bash
gestion-absences/
│
├── 🏠 index.php                 # Page d'accueil et de connexion
├── ✍️ register.php              # Formulaire d'inscription pour les étudiants
├── 📊 dashboard_admin.php       # Tableau de bord administrateur
├── 🎓 dashboard_etudiant.php    # Interface étudiant
├── 🚪 logout.php                # Déconnexion
│
├── ⚙️ config/
│   └── 🔌 db.php                # Connexion PDO à la base de données
│
├── 🧩 includes/
│   ├── 🖼️ header.php            # En-tête HTML commun
│   ├── 🦶 footer.php            # Pied de page commun
│   └── 🔒 auth.php              # Vérification des connexions
│
├── 👨‍💼 admin/
│   ├── 📂 gestion_filieres.php  # Gestion des filières
│   ├── 📂 gestion_modules.php   # Gestion des modules et responsables
│   ├── 📂 gestion_etudiants.php # Liste des étudiants inscrits
│   └── 📂 gestion_absences.php  # Affichage des absences
│
├── 👩‍🎓 etudiant/
│   └── 📑 bilan_absences.php    # Bilan personnel d'absences
│
├── 🗄️ sql/
│   └── 📜 base.sql              # Script SQL pour la base de données
│
└── 🎨 assets/
    └── 💅 style.css             # Feuille de style CSS
```

## 🛠️ Prérequis

- PHP 7.4 ou supérieur  
- MySQL 5.7 ou supérieur  
- Serveur web (Apache, Nginx, etc.)  
- Extension PDO PHP activée  

## 🚀 Évolutions futures

- **Upload de fichiers** : téléversement de photo lors de l’inscription  
- **Confirmation d'inscription par e-mail** : envoi automatique de mails  
- **Scan de QR Code** : enregistrement de présence par scan  
- **Import/Export de données** : via fichiers XML/CSV  
- **Statistiques dynamiques** : tableau de bord AJAX  
- **Architecture MVC** : refonte selon le modèle MVC  
- **Gestion des justificatifs** : soumission et validation de justificatifs  

## 📚 Contexte du projet

Développé dans le cadre du module **Conception et Développement d’Applications Web avec PHP et MySQL**  
à l’ENSA Marrakech (Université Cadi AYYAD), filière GI3, département Techniques de l'Information et Mathématiques.

## 📝 Licence

Ce projet est sous licence **MIT**.

## 🤝 Encadreur

- BOUARIFI Walid (Responsable du module)  

## 🤝 Contributeurs

- Safia AIT HAMMOUD  
- Azar AGHRIB  
- Paul Christopher AIMÉ  
- Mohamed YOUSSOUFI HABIB
