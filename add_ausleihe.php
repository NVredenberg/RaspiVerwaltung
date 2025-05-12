<?php
session_start();
$servername = "localhost";
$username = "test";
$password = "test1234";
$dbname = "raspi";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Daten aus dem Formular abrufen
$Koffer_IDs = $_POST['Koffer_ID'];
$Bauteil_ID = $_POST['Bauteil_ID'];
$Ausleihdatum = date('Y-m-d');
$Nutzer = 'Oberstufe';  // Standardwert für Nutzer

foreach ($Koffer_IDs as $Koffer_ID) {
    // SQL-Abfrage zum Einfügen eines neuen Ausleihvorgangs
    $sql_insert = $conn->prepare("INSERT INTO ausleihe_tabelle (Koffer_ID, Bauteil_ID, Nutzer, Ausleihdatum) VALUES (?, ?, ?, ?)");
    $sql_insert->bind_param("iiss", $Koffer_ID, $Bauteil_ID, $Nutzer, $Ausleihdatum);

    if ($sql_insert->execute()) {
        // SQL-Abfrage zum Reduzieren der IST-Menge des Bauteils
        $sql_update = $conn->prepare("UPDATE bauteil_tabelle SET IST_Menge = IST_Menge - 1 WHERE ID = ?");
        $sql_update->bind_param("i", $Bauteil_ID);
        if (!$sql_update->execute()) {
            echo "Fehler beim Aktualisieren der IST-Menge: " . $conn->error;
        }
    } else {
        echo "Fehler: " . $conn->error;
    }
}

//echo "Bauteil erfolgreich ausgeliehen und IST-Menge aktualisiert";

$conn->close();
?>
