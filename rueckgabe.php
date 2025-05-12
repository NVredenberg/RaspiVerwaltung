<?php
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
$Ausleihe_ID = $_POST['Ausleihe_ID'];
$Rueckgabedatum = date('Y-m-d');

// SQL-Abfrage zum Aktualisieren des Rückgabedatums
$sql_update_rueckgabe = $conn->prepare("UPDATE ausleihe_tabelle SET Rueckgabedatum=? WHERE Ausleihe_ID=?");
$sql_update_rueckgabe->bind_param("si", $Rueckgabedatum, $Ausleihe_ID);

if ($sql_update_rueckgabe->execute()) {
    // SQL-Abfrage zum Abrufen der Bauteil_ID
    $sql_select = $conn->prepare("SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID=?");
    $sql_select->bind_param("i", $Ausleihe_ID);
    $sql_select->execute();
    $result = $sql_select->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $Bauteil_ID = $row['Bauteil_ID'];
        // SQL-Abfrage zum Erhöhen der IST-Menge des Bauteils
        $sql_update_ist_menge = $conn->prepare("UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = ?");
        $sql_update_ist_menge->bind_param("i", $Bauteil_ID);
        if (!$sql_update_ist_menge->execute()) {
            echo "Fehler beim Aktualisieren der IST-Menge: " . $conn->error;
        }
    } else {
        echo "Fehler beim Abrufen der Bauteil_ID: " . $conn->error;
    }
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>
