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
$id = $_POST['Koffer_ID'];
$Besitzer_Oberstufe = $_POST['Besitzer_Oberstufe'];
$Besitzer_Mittelstufe = $_POST['Besitzer_Mittelstufe'];

// SQL-Abfrage zum Aktualisieren des Koffers
$sql = "UPDATE koffer_tabelle SET Besitzer_Oberstufe='$Besitzer_Oberstufe', Besitzer_Mittelstufe='$Besitzer_Mittelstufe' WHERE Koffer_ID=$id";

if ($conn->query($sql) === TRUE) {
    echo "Koffer erfolgreich aktualisiert";
} else {
    echo "Fehler: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>