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
$sql_update_rueckgabe = "UPDATE ausleihe_tabelle SET Rueckgabedatum='$Rueckgabedatum' WHERE Ausleihe_ID=$Ausleihe_ID";
if ($conn->query($sql_update_rueckgabe) === TRUE) {
    // SQL-Abfrage zum Abrufen der Bauteil_ID
    $sql_select = "SELECT Bauteil_ID FROM ausleihe_tabelle WHERE Ausleihe_ID=$Ausleihe_ID";
    $result = $conn->query($sql_select);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $Bauteil_ID = $row['Bauteil_ID'];
        // SQL-Abfrage zum Erhöhen der IST-Menge des Bauteils
        $sql_update_ist_menge = "UPDATE bauteil_tabelle SET IST_Menge = IST_Menge + 1 WHERE ID = $Bauteil_ID";
        if ($conn->query($sql_update_ist_menge) === TRUE) {
            //echo "Bauteil erfolgreich zurückgegeben und IST-Menge aktualisiert";
        } else {
            echo "Fehler beim Aktualisieren der IST-Menge: " . $conn->error;
        }
    } else {
        echo "Fehler beim Abrufen der Bauteil_ID: " . $conn->error;
    }
} else {
    echo "Fehler: " . $sql_update_rueckgabe . "<br>" . $conn->error;
}

$conn->close();
?>
