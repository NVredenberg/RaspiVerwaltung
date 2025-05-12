<?php
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    // ID des zu löschenden Koffers abrufen
    $id = $_POST['id'];

    // Koffer löschen
    $affected = $db->delete('koffer_tabelle', 'Koffer_ID = ?', [$id]);
    echo json_encode(['success' => true, 'affected' => $affected]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}