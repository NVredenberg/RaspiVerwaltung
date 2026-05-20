CREATE DATABASE IF NOT EXISTS raspi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE raspi;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    status ENUM('pending', 'active', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS bauteil_tabelle (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Bauteilname VARCHAR(100) NOT NULL,
    Kategorie VARCHAR(50) NOT NULL DEFAULT 'Raspberry',
    SOLL_Menge INT NOT NULL DEFAULT 0,
    IST_Menge INT NOT NULL DEFAULT 0,
    Lagerort VARCHAR(100) NOT NULL,
    Beschreibung TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (SOLL_Menge >= 0),
    CHECK (IST_Menge >= 0)
);

CREATE TABLE IF NOT EXISTS koffer_tabelle (
    Koffer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Bezeichnung VARCHAR(120) NOT NULL,
    Kategorie VARCHAR(50) NOT NULL DEFAULT 'Raspberry',
    Zielgruppe VARCHAR(120) NOT NULL DEFAULT 'Allgemein',
    Ansprechpartner VARCHAR(120),
    Beschreibung TEXT,
    Besitzer_Oberstufe VARCHAR(100),
    Besitzer_Mittelstufe VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ausleihe_tabelle (
    Ausleihe_ID INT AUTO_INCREMENT PRIMARY KEY,
    Koffer_ID INT NOT NULL,
    Bauteil_ID INT NOT NULL,
    Nutzer VARCHAR(50) NOT NULL,
    Ausleihdatum DATE NOT NULL,
    Rueckgabedatum DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Koffer_ID) REFERENCES koffer_tabelle(Koffer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Bauteil_ID) REFERENCES bauteil_tabelle(ID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    summary VARCHAR(255) NOT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bauteil_name ON bauteil_tabelle(Bauteilname);
CREATE INDEX idx_bauteil_kategorie ON bauteil_tabelle(Kategorie);
CREATE INDEX idx_koffer_kategorie ON koffer_tabelle(Kategorie);
CREATE INDEX idx_koffer_zielgruppe ON koffer_tabelle(Zielgruppe);
CREATE INDEX idx_ausleihe_dates ON ausleihe_tabelle(Ausleihdatum, Rueckgabedatum);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_audit_entity ON audit_log(entity_type, entity_id, created_at);
CREATE INDEX idx_audit_user ON audit_log(user_id, created_at);
