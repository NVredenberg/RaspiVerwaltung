<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/view_helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $id = input_int($_POST, 'ID', 1);
    $data = [
        'Bauteilname' => input_string($_POST, 'Bauteilname', 100),
        'Kategorie' => normalize_category(input_string($_POST, 'Kategorie', 50)),
        'SOLL_Menge' => input_int($_POST, 'SOLL_Menge', 0),
        'IST_Menge' => input_int($_POST, 'IST_Menge', 0),
        'Lagerort' => input_string($_POST, 'Lagerort', 100),
        'Beschreibung' => input_string($_POST, 'Beschreibung', 1000, false),
    ];

    $affected = $db->update('bauteil_tabelle', $data, 'ID = ?', [$id]);
    audit_log($db, 'update', 'inventory', $id, 'Inventar aktualisiert', $data);
    json_response(['success' => true, 'affected' => $affected]);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Inventareintrag konnte nicht aktualisiert werden.'], 500);
}
