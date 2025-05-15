<?php
require_once  __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    // Daten aus dem Formular abrufen
    $Koffer_IDs = $_POST['Koffer_ID'];
    $Bauteil_IDs = $_POST['Bauteil_ID'];
    $Ausleihdatum = date('Y-m-d');
    $Nutzer = 'Oberstufe';  // Standardwert für Nutzer

    $success = true;
    $errors = [];

    // Für jeden Koffer und jedes Bauteil einen Ausleihvorgang erstellen
    foreach ($Koffer_IDs as $Koffer_ID) {
        foreach ($Bauteil_IDs as $Bauteil_ID) {
            try {
                // Neuen Ausleihvorgang einfügen
                $ausleiheData = [
                    'Koffer_ID' => $Koffer_ID,
                    'Bauteil_ID' => $Bauteil_ID,
                    'Nutzer' => $Nutzer,
                    'Ausleihdatum' => $Ausleihdatum
                ];
                
                $db->insert('ausleihe_tabelle', $ausleiheData);

                // IST-Menge des Bauteils reduzieren
                $db->query(
                    "UPDATE bauteil_tabelle SET IST_Menge = IST_Menge - 1 WHERE ID = ?",
                    [$Bauteil_ID]
                );
            } catch (Exception $e) {
                $success = false;
                $errors[] = "Fehler bei Koffer {$Koffer_ID} und Bauteil {$Bauteil_ID}: " . $e->getMessage();
            }
        }
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
