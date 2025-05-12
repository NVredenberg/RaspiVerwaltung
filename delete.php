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
$sql = $conn->prepare("DELETE FROM bauteil_tabelle WHERE ID = ?");
$sql->bind_param("i", $id);

if ($sql->execute()) {
    echo "Bauteil erfolgreich gelöscht";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>