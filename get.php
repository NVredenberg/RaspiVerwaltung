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

// ID des Bauteils abrufen
$id = $_GET['id'];

// SQL-Abfrage zum Abrufen der Bauteildaten
$sql = $conn->prepare("SELECT * FROM bauteil_tabelle WHERE ID = ?");
$sql->bind_param("i", $id);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}

$conn->close();
?>