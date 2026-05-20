USE raspi;

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
