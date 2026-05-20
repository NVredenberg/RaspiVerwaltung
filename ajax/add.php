<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/view_helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $data = [
        'Bauteilname' => input_string($_POST, 'Bauteilname', 100),
        'Kategorie' => normalize_category(input_string($_POST, 'Kategorie', 50)),
        'SOLL_Menge' => input_int($_POST, 'SOLL_Menge', 0),
        'IST_Menge' => input_int($_POST, 'IST_Menge', 0),
        'Lagerort' => input_string($_POST, 'Lagerort', 100),
        'Beschreibung' => input_string($_POST, 'Beschreibung', 1000, false),
    ];

    $id = $db->insert('bauteil_tabelle', $data);
    audit_log($db, 'create', 'inventory', (int)$id, 'Inventar angelegt', $data);
    json_response(['success' => true, 'id' => $id]);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Inventareintrag konnte nicht gespeichert werden.'], 500);
}
