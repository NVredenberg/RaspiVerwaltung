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
$sql = "INSERT INTO bauteil_tabelle (Bauteilname, SOLL_Menge, IST_Menge, Lagerort, Beschreibung) VALUES ('$Bauteilname', $SOLL_Menge, $IST_Menge, '$Lagerort', '$Beschreibung')";

if ($conn->query($sql) === TRUE) {
    echo "Neues Bauteil erfolgreich hinzugefügt";
} else {
    echo "Fehler: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>