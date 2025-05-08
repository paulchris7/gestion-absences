-- Script SQL pour la base de données de Gestion des Absences
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_absences;
USE gestion_absences;

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des filières
CREATE TABLE IF NOT EXISTS filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table des modules
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    filiere_id INT NOT NULL,
    semestre ENUM('S1', 'S2') NOT NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE
);

-- Table des responsables de modules
CREATE TABLE IF NOT EXISTS responsables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20)
);

-- Association entre responsables et modules
CREATE TABLE IF NOT EXISTS module_responsable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    responsable_id INT NOT NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (responsable_id) REFERENCES responsables(id) ON DELETE CASCADE,
    UNIQUE KEY (module_id, responsable_id)
);

-- Table des étudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apogee VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    filiere_id INT NOT NULL,
    photo VARCHAR(255),
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id)
);

-- Table des inscriptions aux modules
CREATE TABLE IF NOT EXISTS inscriptions_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    module_id INT NOT NULL,
    annee_universitaire VARCHAR(9) NOT NULL, -- Format: "2024-2025"
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY (etudiant_id, module_id, annee_universitaire)
);

-- Table des séances
CREATE TABLE IF NOT EXISTS seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    date_seance DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    type_seance ENUM('Cours', 'TD', 'TP') NOT NULL,
    salle VARCHAR(50),
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- Table des absences
CREATE TABLE IF NOT EXISTS absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    seance_id INT NOT NULL,
    module_id INT NOT NULL,  -- Colonne ajoutée
    justifiee BOOLEAN DEFAULT FALSE,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (seance_id) REFERENCES seances(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,  -- Contrainte ajoutée
    UNIQUE KEY (etudiant_id, seance_id)
);

-- Table des justificatifs d'absence (pour l'évolution future)
CREATE TABLE IF NOT EXISTS justificatifs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    absence_id INT NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('En attente', 'Validé', 'Refusé') DEFAULT 'En attente',
    commentaire TEXT,
    FOREIGN KEY (absence_id) REFERENCES absences(id) ON DELETE CASCADE
);

-- Table pour les QR Codes (pour l'évolution future)
CREATE TABLE IF NOT EXISTS qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    code_unique VARCHAR(255) NOT NULL UNIQUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
);

-- Insertion d'un administrateur par défaut
-- Note: le mot de passe est '1234567890'
INSERT INTO administrateurs (username, password, nom, prenom, email) VALUES
('admin', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 'Administrateur', 'Système', 'admin@example.com');

-- Insertion d'administrateurs supplémentaires 
-- Note: le mot de passe pour tous les comptes est '1234567890'
INSERT INTO administrateurs (username, password, nom, prenom, email) VALUES
('bouarifi', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 'Bouarifi', 'Walid', 'bouarifi@ensa.ma'),
('responsable1', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 'Ahmed', 'Bennani', 'ahmed.bennani@ensa.ma');

-- Insertion de filières
INSERT INTO filieres (code, nom, description) VALUES
('GI', 'Génie Informatique', 'Formation en développement logiciel, réseaux et systèmes d\'information'),
('RSSP', 'Réseaux et Systèmes de Sécurité et de Production', 'Formation en cybersécurité et administration des réseaux'),
('GIL', 'Génie Industriel et Logistique', 'Formation en optimisation des processus industriels'),
('GE', 'Génie Électrique', 'Formation en systèmes électriques et électroniques'),
('GM', 'Génie Mécanique', 'Formation en conception et fabrication mécanique');

-- Insertion de modules pour les filières
INSERT INTO modules (code, nom, filiere_id, semestre) VALUES
-- Modules pour Génie Informatique
('GI101', 'Programmation Web', 1, 'S1'),
('GI102', 'Bases de Données', 1, 'S1'),
('GI103', 'Algorithmique Avancée', 1, 'S1'),
('GI104', 'Réseaux Informatiques', 1, 'S1'),
('GI105', 'Systèmes d\'exploitation', 1, 'S1'),
('GI106', 'Mathématiques pour l\'informatique', 1, 'S1'),
-- Modules pour Génie Informatique (S2)
('GI201', 'Java et POO', 1, 'S2'),
('GI202', 'Développement Mobile', 1, 'S2'),
('GI203', 'Intelligence Artificielle', 1, 'S2'),
('GI204', 'Architecture des Ordinateurs', 1, 'S2'),
('GI205', 'Génie Logiciel', 1, 'S2'),
('GI206', 'Cybersécurité', 1, 'S2'),

-- Modules pour RSSP
('RSSP101', 'Sécurité Réseaux', 2, 'S1'),
('RSSP102', 'Administration Système', 2, 'S1'),
('RSSP103', 'Cryptographie', 2, 'S1'),
('RSSP104', 'Virtualisation', 2, 'S1'),
('RSSP201', 'Ethical Hacking', 2, 'S2'),
('RSSP202', 'Cloud Computing', 2, 'S2'),
('RSSP203', 'Forensique Numérique', 2, 'S2'),

-- Modules pour GIL
('GIL101', 'Gestion de Production', 3, 'S1');

-- Insertion de responsables
INSERT INTO responsables (nom, prenom, email, telephone) VALUES
('Dupont', 'Jean', 'jean.dupont@example.com', '0600000000'),
('Martin', 'Claire', 'claire.martin@example.com', '0600000001'),
('El Amrani', 'Salma', 'salma.elamrani@ensa.ma', '0612345678'),
('Ouazzani', 'Karim', 'karim.ouazzani@ensa.ma', '0623456789'),
('Benali', 'Nadia', 'nadia.benali@ensa.ma', '0634567890'),
('Tazi', 'Youssef', 'youssef.tazi@ensa.ma', '0645678901');

-- Association des responsables aux modules
INSERT INTO module_responsable (module_id, responsable_id) VALUES
(1, 1), -- Programmation Web - Jean Dupont
(2, 2), -- Bases de Données - Claire Martin
(3, 3), -- Sécurité Réseaux - Salma El Amrani
(4, 4), -- Gestion de Production - Karim Ouazzani
(5, 1), -- Algorithmique Avancée - Jean Dupont
(6, 3), -- Réseaux Informatiques - Salma El Amrani
(7, 4), -- Systèmes d'exploitation - Karim Ouazzani
(8, 2); -- Mathématiques pour l'informatique - Claire Martin

-- Insertion d'étudiants de test
-- Note: le mot de passe pour tous les comptes est '1234567890'
INSERT INTO etudiants (apogee, nom, prenom, email, password, filiere_id, photo) VALUES
('E12345', 'Alaoui', 'Mohammed', 'mohammed.alaoui@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, 'uploads/photos/E12345.jpg'),
('E12346', 'Berrada', 'Fatima', 'fatima.berrada@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, 'uploads/photos/E12346.jpg'),
('E12347', 'Chraibi', 'Younes', 'younes.chraibi@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 2, 'uploads/photos/E12347.jpg'),
('E12348', 'Doukkali', 'Sanae', 'sanae.doukkali@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 2, 'uploads/photos/E12348.jpg'),
('E12349', 'El Fassi', 'Ahmed', 'ahmed.elfassi@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 3, NULL),
('E12350', 'Fathi', 'Laila', 'laila.fathi@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 3, NULL),
-- Ajout d'étudiants supplémentaires pour GI
('E12351', 'Ghali', 'Omar', 'omar.ghali@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, NULL),
('E12352', 'Hassani', 'Khadija', 'khadija.hassani@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, NULL),
('E12353', 'Idrissi', 'Rachid', 'rachid.idrissi@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, NULL),
('E12354', 'Jamal', 'Zineb', 'zineb.jamal@etud.ensa.ma', '$2y$10$NH7a6tVFdJjoxFYeMx2Sk.FuaSR3T.6brnG3osNT3iX/01ApXtt7e', 1, NULL);

-- Inscription des étudiants aux modules (année universitaire 2024-2025)
INSERT INTO inscriptions_modules (etudiant_id, module_id, annee_universitaire) VALUES
-- Inscriptions Mohammed Alaoui (GI)
(1, 1, '2024-2025'), -- Programmation Web
(1, 2, '2024-2025'), -- Bases de Données
(1, 3, '2024-2025'), -- Algorithmique Avancée
(1, 4, '2024-2025'), -- Réseaux Informatiques

-- Inscriptions Fatima Berrada (GI)
(2, 1, '2024-2025'), -- Programmation Web
(2, 2, '2024-2025'), -- Bases de Données
(2, 3, '2024-2025'), -- Algorithmique Avancée
(2, 4, '2024-2025'), -- Réseaux Informatiques

-- Inscriptions Younes Chraibi (RSSP)
(3, 13, '2024-2025'), -- Sécurité Réseaux
(3, 14, '2024-2025'), -- Administration Système
(3, 15, '2024-2025'), -- Cryptographie
(3, 16, '2024-2025'), -- Virtualisation

-- Inscriptions Sanae Doukkali (RSSP)
(4, 13, '2024-2025'), -- Sécurité Réseaux
(4, 14, '2024-2025'), -- Administration Système
(4, 15, '2024-2025'), -- Cryptographie
(4, 16, '2024-2025'); -- Virtualisation

-- Création de séances pour certains modules
INSERT INTO seances (module_id, date_seance, heure_debut, heure_fin, type_seance, salle) VALUES
-- Séances pour Programmation Web
(1, '2024-10-03', '08:30:00', '10:30:00', 'Cours', 'Salle A1'),
(1, '2024-10-10', '08:30:00', '10:30:00', 'Cours', 'Salle A1'),
(1, '2024-10-17', '08:30:00', '10:30:00', 'Cours', 'Salle A1'),
(1, '2024-10-03', '14:00:00', '16:00:00', 'TD', 'Salle B2'),
(1, '2024-10-10', '14:00:00', '16:00:00', 'TD', 'Salle B2'),
(1, '2024-10-17', '14:00:00', '16:00:00', 'TP', 'Salle C3'),

-- Séances pour Bases de Données
(2, '2024-10-04', '08:30:00', '10:30:00', 'Cours', 'Salle A2'),
(2, '2024-10-11', '08:30:00', '10:30:00', 'Cours', 'Salle A2'),
(2, '2024-10-18', '08:30:00', '10:30:00', 'Cours', 'Salle A2'),
(2, '2024-10-04', '14:00:00', '16:00:00', 'TD', 'Salle B1'),
(2, '2024-10-11', '14:00:00', '16:00:00', 'TD', 'Salle B1'),
(2, '2024-10-18', '14:00:00', '16:00:00', 'TP', 'Salle C2'),

-- Séances pour Sécurité Réseaux
(13, '2024-10-05', '08:30:00', '10:30:00', 'Cours', 'Salle A3'),
(13, '2024-10-12', '08:30:00', '10:30:00', 'Cours', 'Salle A3'),
(13, '2024-10-19', '08:30:00', '10:30:00', 'TP', 'Salle C1');

-- Enregistrement de quelques absences
INSERT INTO absences (etudiant_id, seance_id, module_id, justifiee) VALUES
(1, 2, 1, FALSE),  -- Séance 2 → module 1 (GI101)
(1, 5, 1, TRUE),   -- Séance 5 → module 1
(2, 8, 2, FALSE),  -- Séance 8 → module 2 (GI102)
(3, 14, 13, FALSE);-- Séance 14 → module 13 (RSSP101)

-- Ajout de justificatifs pour certaines absences
INSERT INTO justificatifs (absence_id, fichier, statut, commentaire) VALUES
(2, 'uploads/justificatifs/E12345_20241010.pdf', 'Validé', 'Certificat médical validé');

-- Génération de QR codes pour certains étudiants
INSERT INTO qr_codes (etudiant_id, code_unique) VALUES
(1, 'E12345-GI-202425'),
(2, 'E12346-GI-202425'),
(3, 'E12347-RSSP-202425'),
(4, 'E12348-RSSP-202425');
