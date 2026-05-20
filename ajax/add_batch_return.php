<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $returnIds = input_int_array($_POST, 'return_ids', 1);
    $rueckgabedatum = date('Y-m-d');
    $processed = 0;

    $db->beginTransaction();

    foreach ($returnIds as $loanId) {
        $loan = $db->fetch(
            'SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID = ? AND Rueckgabedatum IS NULL',
            [$loanId]
        );

        if (!$loan) {
            throw new InvalidArgumentException('Eine ausgewählte Ausleihe wurde nicht gefunden oder ist bereits zurückgegeben.');
        }

        $updated = $db->query(
            'UPDATE ausleihe_tabelle SET Rueckgabedatum = ? WHERE Ausleihe_ID = ? AND Rueckgabedatum IS NULL',
            [$rueckgabedatum, $loanId]
        )->rowCount();

        if ($updated !== 1) {
            throw new InvalidArgumentException('Eine ausgewählte Ausleihe wurde bereits zurückgegeben.');
        }

        $db->query(
            'UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = ?',
            [$loan['Bauteil_ID']]
        );
        $processed++;
    }

    $db->commit();
    json_response(['success' => true, 'processed' => $processed]);
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
    json_response(['success' => false, 'error' => 'Massenrückgabe konnte nicht gespeichert werden.'], 500);
}
