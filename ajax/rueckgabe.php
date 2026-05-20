<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $ausleiheId = input_int($_POST, 'Ausleihe_ID', 1);
    $rueckgabedatum = date('Y-m-d');

    $db->beginTransaction();

    $ausleihe = $db->fetch(
        'SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID = ? AND Rueckgabedatum IS NULL',
        [$ausleiheId]
    );

    if (!$ausleihe) {
        throw new InvalidArgumentException('Ausleihe wurde nicht gefunden oder ist bereits zurückgegeben.');
    }

    $updated = $db->query(
        'UPDATE ausleihe_tabelle SET Rueckgabedatum = ? WHERE Ausleihe_ID = ? AND Rueckgabedatum IS NULL',
        [$rueckgabedatum, $ausleiheId]
    )->rowCount();

    if ($updated !== 1) {
        throw new InvalidArgumentException('Ausleihe wurde bereits zurückgegeben.');
    }

    $db->query(
        'UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = ?',
        [$ausleihe['Bauteil_ID']]
    );
    audit_log($db, 'return', 'loan', $ausleiheId, 'Ausleihe zurückgegeben', [
        'bauteil_id' => (int)$ausleihe['Bauteil_ID'],
    ]);
    audit_log($db, 'stock_increase', 'inventory', (int)$ausleihe['Bauteil_ID'], 'Bestand durch Rückgabe erhöht', [
        'loan_id' => $ausleiheId,
    ]);

    $db->commit();
    json_response(['success' => true]);
} catch (InvalidArgumentException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    json_response(['success' => false, 'error' => $e->getMessage()], 422);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log($e->getMessage());
    json_response(['success' => false, 'error' => 'Rückgabe konnte nicht gespeichert werden.'], 500);
}
