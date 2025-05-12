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

// ID des Koffers abrufen
$id = $_GET['id'];

// SQL-Abfrage zum Abrufen der Kofferdaten
$sql = $conn->prepare("SELECT * FROM koffer_tabelle WHERE Koffer_ID = ?");
$sql->bind_param("s", $id);

$result = $conn->query($sql);


if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}

$conn->close();
?>