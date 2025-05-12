<?php
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    // ID des zu löschenden Bauteils abrufen
    $id = $_POST['id'];

    // Bauteil löschen
    $affected = $db->delete('bauteil_tabelle', 'ID = ?', [$id]);
    echo json_encode(['success' => true, 'affected' => $affected]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}