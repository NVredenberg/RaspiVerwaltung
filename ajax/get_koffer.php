<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    // ID des Koffers abrufen
    $id = $_GET['id'];

    // Koffer abrufen
    $koffer = $db->fetch("SELECT * FROM koffer_tabelle WHERE Koffer_ID = ?", [$id]);
    
    if ($koffer) {
        echo json_encode($koffer);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Koffer nicht gefunden']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}