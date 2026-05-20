<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $id = input_int($_POST, 'id', 1);

    $loanCount = $db->fetch('SELECT COUNT(*) AS count FROM ausleihe_tabelle WHERE Koffer_ID = ?', [$id]);
    if ((int)($loanCount['count'] ?? 0) > 0) {
        json_response(['success' => false, 'error' => 'Dieses Set ist bereits in Ausleihen enthalten und wird nicht gelöscht.'], 409);
    }

    $set = $db->fetch('SELECT Bezeichnung FROM koffer_tabelle WHERE Koffer_ID = ?', [$id]);
    $affected = $db->delete('koffer_tabelle', 'Koffer_ID = ?', [$id]);
    audit_log($db, 'delete', 'set', $id, 'Set gelöscht', ['name' => $set['Bezeichnung'] ?? '']);
    json_response(['success' => true, 'affected' => $affected]);
} catch (InvalidArgumentException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Set konnte nicht gelöscht werden.'], 500);
}
