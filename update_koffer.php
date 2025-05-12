<?php
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    // Daten aus dem Formular abrufen
    $data = [
        'Besitzer_Oberstufe' => $_POST['Besitzer_Oberstufe'],
        'Besitzer_Mittelstufe' => $_POST['Besitzer_Mittelstufe']
    ];

    // Koffer aktualisieren
    $affected = $db->update('koffer_tabelle', $data, 'Koffer_ID = ?', [$_POST['Koffer_ID']]);
    echo json_encode(['success' => true, 'affected' => $affected]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}