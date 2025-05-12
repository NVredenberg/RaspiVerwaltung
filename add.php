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
$Bauteilname = $_POST['Bauteilname'];
$SOLL_Menge = $_POST['SOLL_Menge'];
$IST_Menge = $_POST['IST_Menge'];
$Lagerort = $_POST['Lagerort'];
$Beschreibung = $_POST['Beschreibung'];

// SQL-Abfrage zum Einfügen eines neuen Bauteils
$sql = $conn->prepare("INSERT INTO bauteil_tabelle (Bauteilname, SOLL_Menge, IST_Menge, Lagerort, Beschreibung) VALUES (?, ?, ?, ?, ?)");
$sql->bind_param("siiss", $Bauteilname, $SOLL_Menge, $IST_Menge, $Lagerort, $Beschreibung);

if ($sql->execute()) {
    echo "Neues Bauteil erfolgreich hinzugefügt";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>