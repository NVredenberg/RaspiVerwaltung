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
$id = $_POST['ID'];
$Bauteilname = $_POST['Bauteilname'];
$SOLL_Menge = $_POST['SOLL_Menge'];
$IST_Menge = $_POST['IST_Menge'];
$Lagerort = $_POST['Lagerort'];
$Beschreibung = $_POST['Beschreibung'];

// SQL-Abfrage zum Aktualisieren des Bauteils
$sql = $conn->prepare("UPDATE bauteil_tabelle SET Bauteilname=?, SOLL_Menge=?, IST_Menge=?, Lagerort=?, Beschreibung=? WHERE ID=?");
$sql->bind_param("siissi", $Bauteilname, $SOLL_Menge, $IST_Menge, $Lagerort, $Beschreibung, $id);

if ($sql->execute()) {
    echo "Bauteil erfolgreich aktualisiert";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>