<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/audit.php';

require_login(true);
require_valid_csrf(true);

try {
    $db = Database::getInstance();
    $kofferIds = input_int_array($_POST, 'Koffer_ID', 1);
    $bauteilIds = input_int_array($_POST, 'Bauteil_ID', 1);
    $ausleihdatum = date('Y-m-d');
    $nutzer = current_username();
    $processed = 0;

    $db->beginTransaction();

    foreach ($kofferIds as $kofferId) {
        foreach ($bauteilIds as $bauteilId) {
            $updated = $db->query(
                'UPDATE bauteil_tabelle SET IST_Menge = IST_Menge - 1 WHERE ID = ? AND IST_Menge > 0',
                [$bauteilId]
            )->rowCount();

            if ($updated !== 1) {
                throw new InvalidArgumentException('Ein gewählter Inventareintrag ist nicht mehr verfügbar.');
            }

            $loanId = (int)$db->insert('ausleihe_tabelle', [
                'Koffer_ID' => $kofferId,
                'Bauteil_ID' => $bauteilId,
                'Nutzer' => $nutzer,
                'Ausleihdatum' => $ausleihdatum,
            ]);
            audit_log($db, 'loan', 'loan', $loanId, 'Ausleihe angelegt', [
                'koffer_id' => $kofferId,
                'bauteil_id' => $bauteilId,
            ]);
            audit_log($db, 'stock_decrease', 'inventory', $bauteilId, 'Bestand durch Ausleihe reduziert', [
                'loan_id' => $loanId,
                'koffer_id' => $kofferId,
            ]);
            $processed++;
        }
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
    json_response(['success' => false, 'error' => 'Ausleihe konnte nicht gespeichert werden.'], 500);
}
