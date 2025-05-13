<?php
/* usage: 
    > php create_user.php <username> <password> 
*/
$servername = "localhost";
$username = "raspiVer";
$password = "Verwaltung2025";
$dbname = "raspi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$username = $argv[1];
$password = $argv[2];

$password = password_hash($password, PASSWORD_DEFAULT);

if ($username == "" || $password == "") {
    echo "Username und Passwort sind erforderlich";
    exit(1);
}

$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

$conn->close();
