<?php
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    // ID des Bauteils abrufen
    $id = $_GET['id'];

    // Bauteil abrufen
    $bauteil = $db->fetch("SELECT * FROM bauteil_tabelle WHERE ID = ?", [$id]);
    
    if ($bauteil) {
        echo json_encode($bauteil);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Bauteil nicht gefunden']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}