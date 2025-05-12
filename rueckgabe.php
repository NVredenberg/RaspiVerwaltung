<?php
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    // Daten aus dem Formular abrufen
    $Ausleihe_ID = $_POST['Ausleihe_ID'];
    $Rueckgabedatum = date('Y-m-d');

    // Ausleihvorgang aktualisieren
    $db->update(
        'ausleihe_tabelle',
        ['Rueckgabedatum' => $Rueckgabedatum],
        'Ausleihe_ID = ?',
        [$Ausleihe_ID]
    );

    // Bauteil-ID abrufen
    $ausleihe = $db->fetch(
        "SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID = ?",
        [$Ausleihe_ID]
    );

    if ($ausleihe) {
        // IST-Menge des Bauteils erhöhen
        $db->query(
            "UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = ?",
            [$ausleihe['Bauteil_ID']]
        );
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
