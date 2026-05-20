<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/view_helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $id = input_int($_POST, 'Koffer_ID', 1);
    $data = [
        'Bezeichnung' => input_string($_POST, 'Bezeichnung', 120),
        'Kategorie' => normalize_category(input_string($_POST, 'Kategorie', 50)),
        'Zielgruppe' => input_string($_POST, 'Zielgruppe', 120),
        'Ansprechpartner' => input_string($_POST, 'Ansprechpartner', 120, false),
        'Beschreibung' => input_string($_POST, 'Beschreibung', 1000, false),
    ];

    $affected = $db->update('koffer_tabelle', $data, 'Koffer_ID = ?', [$id]);
    audit_log($db, 'update', 'set', $id, 'Set aktualisiert', $data);
    json_response(['success' => true, 'affected' => $affected]);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Set konnte nicht aktualisiert werden.'], 500);
}
