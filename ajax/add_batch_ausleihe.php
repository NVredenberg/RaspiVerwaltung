<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    $Koffer_IDs = $_POST['Koffer_ID'];
    $Bauteil_IDs = $_POST['Bauteil_ID'];
    $Ausleihdatum = date('Y-m-d');
    $Nutzer = 'Oberstufe';  // Default user

    $success = true;
    $errors = [];
    $processed = 0;

    foreach ($Koffer_IDs as $Koffer_ID) {
        foreach ($Bauteil_IDs as $Bauteil_ID) {
            try {
                $available = $db->fetch(
                    "SELECT IST_Menge FROM bauteil_tabelle WHERE ID = ?",
                    [$Bauteil_ID]
                );

                if ($available && $available['IST_Menge'] > 0) {
                    $ausleiheData = [
                        'Koffer_ID' => $Koffer_ID,
                        'Bauteil_ID' => $Bauteil_ID,
                        'Nutzer' => $Nutzer,
                        'Ausleihdatum' => $Ausleihdatum
                    ];
                    
                    $db->insert('ausleihe_tabelle', $ausleiheData);
                    $db->query(
                        "UPDATE bauteil_tabelle SET IST_Menge = IST_Menge - 1 WHERE ID = ?",
                        [$Bauteil_ID]
                    );

                    $processed++;
                } else {
                    $errors[] = "Bauteil ID {$Bauteil_ID} ist nicht mehr verfügbar";
                }
            } catch (Exception $e) {
                $errors[] = "Fehler bei Koffer {$Koffer_ID} und Bauteil {$Bauteil_ID}: " . $e->getMessage();
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "{$processed} Ausleihen erfolgreich verarbeitet",
        'errors' => $errors
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 