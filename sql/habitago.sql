CREATE DATABASE habitago
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE habitago;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'ROLE_USER',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT NULL
);


CREATE TABLE logement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    surface DECIMAL(6,2) NOT NULL,
    type_logement VARCHAR(50) NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contrat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    logement_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE DEFAULT NULL,
    montant_loyer DECIMAL(10,2) NOT NULL,
    montant_caution DECIMAL(10,2) NOT NULL,
    statut VARCHAR(50) NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_contrat_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_contrat_logement
        FOREIGN KEY (logement_id)
        REFERENCES logement(id)
        ON DELETE CASCADE
);

CREATE TABLE paiement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrat_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_paiement DATE NOT NULL,
    moyen_paiement VARCHAR(50) NOT NULL,
    statut VARCHAR(50) NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_paiement_contrat
        FOREIGN KEY (contrat_id)
        REFERENCES contrat(id)
        ON DELETE CASCADE
);

CREATE TABLE consommation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logement_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    valeur DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) NOT NULL,
    periode_debut DATE NOT NULL,
    periode_fin DATE NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_consommation_logement
        FOREIGN KEY (logement_id)
        REFERENCES logement(id)
        ON DELETE CASCADE
);

CREATE TABLE incident (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logement_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL,
    priorite VARCHAR(50) NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_resolution DATETIME DEFAULT NULL,

    CONSTRAINT fk_incident_logement
        FOREIGN KEY (logement_id)
        REFERENCES logement(id)
        ON DELETE CASCADE
);

