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

// ID des zu löschenden Koffers abrufen
$id = $_POST['id'];

// SQL-Abfrage zum Löschen des Koffers
$sql = $conn->prepare("DELETE FROM koffer_tabelle WHERE Koffer_ID = ?");
$sql->bind_param("i", $id);

if ($sql->execute()) {
    echo "Koffer erfolgreich gelöscht";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>