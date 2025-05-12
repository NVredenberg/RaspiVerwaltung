<?php


session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Datenbankverbindung
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

// SQL-Abfrage zum Abrufen aller Bauteile und Koffer
$sql_bauteile = "SELECT ID, Bauteilname FROM bauteil_tabelle";
$result_bauteile = $conn->query($sql_bauteile);

$sql_koffer = "SELECT Koffer_ID, Besitzer_Oberstufe, Besitzer_Mittelstufe FROM koffer_tabelle";
$result_koffer = $conn->query($sql_koffer);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('includes/header.php'); ?>
    <div class="container mt-5">
        <!-- Formular zum Ausleihen von Bauteilen -->
        <h2>Bauteil ausleihen</h2>
        <form id="ausleiheForm">
            <div class="mb-3">
                <label for="Koffer_ID" class="form-label">Koffer</label>
                <select class="form-control" id="Koffer_ID" name="Koffer_ID[]" multiple required>
                    <?php
                    if ($result_koffer->num_rows > 0) {
                        while($row = $result_koffer->fetch_assoc()) {
                           echo "<option value='" . $row["Koffer_ID"] . "'>Koffer " . $row["Koffer_ID"] . " - Oberstufe: " . $row["Besitzer_Oberstufe"] . ", Mittelstufe: " . $row["Besitzer_Mittelstufe"] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="Bauteil_ID" class="form-label">Bauteil</label>
                <select class="form-control" id="Bauteil_ID" name="Bauteil_ID" required>
                    <?php
                    if ($result_bauteile->num_rows > 0) {
                        while($row = $result_bauteile->fetch_assoc()) {
                            echo "<option value='" . $row["ID"] . "'>" . $row["Bauteilname"] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ausleihen</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // AJAX für das Ausleihen von Bauteilen
            $('#ausleiheForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'add_ausleihe.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        //alert(response);
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Verbindung schließen
$conn->close();
?>
