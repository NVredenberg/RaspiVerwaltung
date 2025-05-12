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
$id = $_POST['Koffer_ID'];
$Besitzer_Oberstufe = $_POST['Besitzer_Oberstufe'];
$Besitzer_Mittelstufe = $_POST['Besitzer_Mittelstufe'];

// SQL-Abfrage zum Aktualisieren des Koffers
$sql = $conn->prepare("UPDATE koffer_tabelle SET Besitzer_Oberstufe=?, Besitzer_Mittelstufe=? WHERE Koffer_ID=?");
$sql->bind_param("ssi", $Besitzer_Oberstufe, $Besitzer_Mittelstufe, $id);

if ($sql->execute()) {
    echo "Koffer erfolgreich aktualisiert";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>