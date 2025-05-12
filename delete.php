<?php
$servername = "localhost";
$username = "raspiVer";
$password = "Verwaltung2025";
$dbname = "raspi";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// ID des zu löschenden Bauteils abrufen
$id = $_POST['id'];

// SQL-Abfrage zum Löschen des Bauteils
$sql = "DELETE FROM bauteil_tabelle WHERE ID = $id";

if ($conn->query($sql) === TRUE) {
    echo "Bauteil erfolgreich gelöscht";
} else {
    echo "Fehler: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>