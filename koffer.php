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

// SQL-Abfrage zum Abrufen aller Koffer-Einträge
$sql = "SELECT Koffer_ID, Besitzer_Oberstufe, Besitzer_Mittelstufe FROM koffer_tabelle";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <    <h1>Bauteile und Koffer Verwaltung</h1>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Mein Projekt</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Bauteile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="koffer.php">Koffer/Nutzer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ausleihe.php">Ausleihen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="uebersicht.php">Übersicht</a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <span class="navbar-text me-2">
                        Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a class="btn btn-outline-danger" href="logout.php">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Koffer_ID</th>
                    <th scope="col">Besitzer Oberstufe</th>
                    <th scope="col">Besitzer Mittelstufe</th>
                    <th scope="col">Aktionen</th>
                </tr>
            </thead>
            <tbody id="koffer_tabelle">
                <?php
                // Daten in Tabelle einfügen
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<th scope='row'>" . $row["Koffer_ID"] . "</th>";
                        echo "<td>" . $row["Besitzer_Oberstufe"] . "</td>";
                        echo "<td>" . $row["Besitzer_Mittelstufe"] . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-warning btn-sm edit-btn' data-id='" . $row["Koffer_ID"] . "' data-bs-toggle='modal' data-bs-target='#editModal'>Bearbeiten</button>";
                        echo "<button class='btn btn-danger btn-sm delete-btn' data-id='" . $row["Koffer_ID"] . "'>Löschen</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Keine Daten gefunden</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <!-- Formular zum Hinzufügen von Koffern -->
        <h2>Neuen Koffer hinzufügen</h2>
        <form id="addForm">
            <div class="mb-3">
                <label for="Besitzer_Oberstufe" class="form-label">Besitzer Oberstufe</label>
                <input type="text" class="form-control" id="Besitzer_Oberstufe" name="Besitzer_Oberstufe" required>
            </div>
            <div class="mb-3">
                <label for="Besitzer_Mittelstufe" class="form-label">Besitzer Mittelstufe</label>
                <input type="text" class="form-control" id="Besitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
            </div>
            <button type="submit" class="btn btn-primary">Hinzufügen</button>
        </form>
    </div>

    <!-- Modal zum Bearbeiten von Koffern -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Koffer bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editID" name="Koffer_ID">
                        <div class="mb-3">
                            <label for="editBesitzer_Oberstufe" class="form-label">Besitzer Oberstufe</label>
                            <input type="text" class="form-control" id="editBesitzer_Oberstufe" name="Besitzer_Oberstufe" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBesitzer_Mittelstufe" class="form-label">Besitzer Mittelstufe</label>
                            <input type="text" class="form-control" id="editBesitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // AJAX für das Hinzufügen von Koffern
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'add_koffer.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            });

            // AJAX für das Löschen von Koffern
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: 'delete_koffer.php',
                    data: { id: id },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            });

            // Bearbeiten-Button klicken
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'GET',
                    url: 'get_koffer.php',
                    data: { id: id },
                    success: function(response) {
                        var koffer = JSON.parse(response);
                        $('#editID').val(koffer.Koffer_ID);
                        $('#editBesitzer_Oberstufe').val(koffer.Besitzer_Oberstufe);
                        $('#editBesitzer_Mittelstufe').val(koffer.Besitzer_Mittelstufe);
                    }
                });
            });

            // AJAX für das Bearbeiten von Koffern
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'update_koffer.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response);
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