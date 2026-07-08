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

ALTER TABLE bauteil_tabelle
    ADD COLUMN IF NOT EXISTS Kategorie VARCHAR(50) NOT NULL DEFAULT 'Raspberry' AFTER Bauteilname;

ALTER TABLE koffer_tabelle
    ADD COLUMN IF NOT EXISTS Bezeichnung VARCHAR(120) NULL AFTER Koffer_ID,
    ADD COLUMN IF NOT EXISTS Kategorie VARCHAR(50) NOT NULL DEFAULT 'Raspberry' AFTER Bezeichnung,
    ADD COLUMN IF NOT EXISTS Zielgruppe VARCHAR(120) NOT NULL DEFAULT 'Allgemein' AFTER Kategorie,
    ADD COLUMN IF NOT EXISTS Ansprechpartner VARCHAR(120) NULL AFTER Zielgruppe,
    ADD COLUMN IF NOT EXISTS Beschreibung TEXT NULL AFTER Ansprechpartner;

ALTER TABLE koffer_tabelle
    MODIFY Besitzer_Oberstufe VARCHAR(100) NULL,
    MODIFY Besitzer_Mittelstufe VARCHAR(100) NULL;

UPDATE koffer_tabelle
SET Bezeichnung = CONCAT('Koffer ', Koffer_ID)
WHERE Bezeichnung IS NULL OR Bezeichnung = '';

ALTER TABLE koffer_tabelle
    MODIFY Bezeichnung VARCHAR(120) NOT NULL;

CREATE INDEX IF NOT EXISTS idx_bauteil_kategorie ON bauteil_tabelle(Kategorie);
CREATE INDEX IF NOT EXISTS idx_koffer_kategorie ON koffer_tabelle(Kategorie);
CREATE INDEX IF NOT EXISTS idx_koffer_zielgruppe ON koffer_tabelle(Zielgruppe);

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role ENUM('admin', 'user') NOT NULL DEFAULT 'user' AFTER password,
    ADD COLUMN IF NOT EXISTS status ENUM('pending', 'active', 'rejected') NOT NULL DEFAULT 'active' AFTER role,
    ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER status,
    ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL AFTER approved_by,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER approved_at,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER updated_at;

UPDATE users
SET status = 'active'
WHERE status IS NULL OR status = '';

UPDATE users u
JOIN (SELECT MIN(id) AS first_id FROM users) first_user ON u.id = first_user.first_id
SET u.role = 'admin', u.status = 'active', u.approved_at = COALESCE(u.approved_at, u.created_at)
WHERE NOT EXISTS (
    SELECT 1 FROM (SELECT id FROM users WHERE role = 'admin' LIMIT 1) existing_admin
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

CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_log(entity_type, entity_id, created_at);
CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_log(user_id, created_at);
