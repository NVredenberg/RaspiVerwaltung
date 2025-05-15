<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();

    $return_ids = $_POST['return_ids'];
    $Rueckgabedatum = date('Y-m-d');

    $success = true;
    $errors = [];
    $processed = 0;

    foreach ($return_ids as $loan_id) {
        try {
            $loan = $db->fetch(
                "SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID = ? AND Rueckgabedatum IS NULL",
                [$loan_id]
            );

            if ($loan) {
                $db->query(
                    "UPDATE ausleihe_tabelle SET Rueckgabedatum = ? WHERE Ausleihe_ID = ?",
                    [$Rueckgabedatum, $loan_id]
                );

                $db->query(
                    "UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = ?",
                    [$loan['Bauteil_ID']]
                );

                $processed++;
            } else {
                $errors[] = "Ausleihe ID {$loan_id} nicht gefunden oder bereits zurückgegeben";
            }
        } catch (Exception $e) {
            $errors[] = "Fehler bei Ausleihe ID {$loan_id}: " . $e->getMessage();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "{$processed} Rückgaben erfolgreich verarbeitet",
        'errors' => $errors
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 