<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';

require_login(true);

try {
    $db = Database::getInstance();
    $id = input_int($_GET, 'id', 1);

    $koffer = $db->fetch('
        SELECT Koffer_ID, Bezeichnung, Kategorie, Zielgruppe, Ansprechpartner, Beschreibung
        FROM koffer_tabelle
        WHERE Koffer_ID = ?
    ', [$id]);

    if (!$koffer) {
        json_response(['success' => false, 'error' => 'Set nicht gefunden.'], 404);
    }

    json_response($koffer);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Set konnte nicht geladen werden.'], 500);
}
