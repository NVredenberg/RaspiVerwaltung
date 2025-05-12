CREATE DATABASE IF NOT EXISTS raspi;
USE raspi;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bauteil_tabelle (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Bauteilname VARCHAR(100) NOT NULL,
    SOLL_Menge INT NOT NULL,
    IST_Menge INT NOT NULL,
    Lagerort VARCHAR(100) NOT NULL,
    Beschreibung TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS koffer_tabelle (
    Koffer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Besitzer_Oberstufe VARCHAR(100) NOT NULL,
    Besitzer_Mittelstufe VARCHAR(100) NOT NULL,
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

CREATE INDEX idx_bauteil_name ON bauteil_tabelle(Bauteilname);
CREATE INDEX idx_koffer_besitzer ON koffer_tabelle(Besitzer_Oberstufe, Besitzer_Mittelstufe);
CREATE INDEX idx_ausleihe_dates ON ausleihe_tabelle(Ausleihdatum, Rueckgabedatum);
