<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    // Daten aus dem Formular abrufen
    $data = [
        'Bauteilname' => $_POST['Bauteilname'],
        'SOLL_Menge' => $_POST['SOLL_Menge'],
        'IST_Menge' => $_POST['IST_Menge'],
        'Lagerort' => $_POST['Lagerort'],
        'Beschreibung' => $_POST['Beschreibung']
    ];

    // Neues Bauteil einfügen
    $id = $db->insert('bauteil_tabelle', $data);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}