<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    // Daten aus dem Formular abrufen
    $data = [
        'Besitzer_Oberstufe' => $_POST['Besitzer_Oberstufe'],
        'Besitzer_Mittelstufe' => $_POST['Besitzer_Mittelstufe']
    ];

    // Neuen Koffer einfügen
    $id = $db->insert('koffer_tabelle', $data);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}