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
$Besitzer_Oberstufe = $_POST['Besitzer_Oberstufe'];
$Besitzer_Mittelstufe = $_POST['Besitzer_Mittelstufe'];

// SQL-Abfrage zum Einfügen eines neuen Koffers
$sql = $conn->prepare("INSERT INTO koffer_tabelle (Besitzer_Oberstufe, Besitzer_Mittelstufe) VALUES (?, ?)");
$sql->bind_param("ss", $Besitzer_Oberstufe, $Besitzer_Mittelstufe);

if ($sql->execute()) {
    echo "Neuer Koffer erfolgreich hinzugefügt";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>