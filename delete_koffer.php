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
$sql = "DELETE FROM koffer_tabelle WHERE Koffer_ID = $id";

if ($conn->query($sql) === TRUE) {
    echo "Koffer erfolgreich gelöscht";
} else {
    echo "Fehler: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>