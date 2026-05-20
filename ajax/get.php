<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';

require_login(true);

try {
    $db = Database::getInstance();
    $id = input_int($_GET, 'id', 1);

    $bauteil = $db->fetch('
        SELECT ID, Bauteilname, Kategorie, SOLL_Menge, IST_Menge, Lagerort, Beschreibung
        FROM bauteil_tabelle
        WHERE ID = ?
    ', [$id]);

    if (!$bauteil) {
        json_response(['success' => false, 'error' => 'Inventareintrag nicht gefunden.'], 404);
    }

    json_response($bauteil);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Inventareintrag konnte nicht geladen werden.'], 500);
}
