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

// SQL-Abfrage zum Abrufen aller Bauteil-Einträge
$sql = "SELECT ID, Bauteilname, SOLL_Menge, IST_Menge, Lagerort, Beschreibung FROM bauteil_tabelle";
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
    <h1>Bauteile und Koffer Verwaltung</h1>
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
                    <th scope="col">ID</th>
                    <th scope="col">Bauteilname</th>
                    <th scope="col">SOLL-Menge</th>
                    <th scope="col">IST-Menge</th>
                    <th scope="col">Lagerort</th>
                    <th scope="col">Beschreibung</th>
                    <th scope="col">Aktionen</th>
                </tr>
            </thead>
            <tbody id="bauteil_tabelle">
                <?php
                // Daten in Tabelle einfügen
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<th scope='row'>" . $row["ID"] . "</th>";
                        echo "<td>" . $row["Bauteilname"] . "</td>";
                        echo "<td>" . $row["SOLL_Menge"] . "</td>";
                        echo "<td>" . $row["IST_Menge"] . "</td>";
                        echo "<td>" . $row["Lagerort"] . "</td>";
                        echo "<td>" . $row["Beschreibung"] . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-warning btn-sm edit-btn' data-id='" . $row["ID"] . "' data-bs-toggle='modal' data-bs-target='#editModal'>Bearbeiten</button>";
                        echo "<button class='btn btn-danger btn-sm delete-btn' data-id='" . $row["ID"] . "'>Löschen</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Keine Daten gefunden</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <!-- Formular zum Hinzufügen von Bauteilen -->
        <h2>Neues Bauteil hinzufügen</h2>
        <form id="addForm">
            <div class="mb-3">
                <label for="Bauteilname" class="form-label">Bauteilname</label>
                <input type="text" class="form-control" id="Bauteilname" name="Bauteilname" required>
            </div>
            <div class="mb-3">
                <label for="SOLL_Menge" class="form-label">SOLL-Menge</label>
                <input type="number" class="form-control" id="SOLL_Menge" name="SOLL_Menge" required>
            </div>
            <div class="mb-3">
                <label for="IST_Menge" class="form-label">IST-Menge</label>
                <input type="number" class="form-control" id="IST_Menge" name="IST_Menge" required>
            </div>
            <div class="mb-3">
                <label for="Lagerort" class="form-label">Lagerort</label>
                <input type="text" class="form-control" id="Lagerort" name="Lagerort">
            </div>
            <div class="mb-3">
                <label for="Beschreibung" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="Beschreibung" name="Beschreibung"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Hinzufügen</button>
        </form>
    </div>

    <!-- Modal zum Bearbeiten von Bauteilen -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Bauteil bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editID" name="ID">
                        <div class="mb-3">
                            <label for="editBauteilname" class="form-label">Bauteilname</label>
                            <input type="text" class="form-control" id="editBauteilname" name="Bauteilname" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSOLL_Menge" class="form-label">SOLL-Menge</label>
                            <input type="number" class="form-control" id="editSOLL_Menge" name="SOLL_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="editIST_Menge" class="form-label">IST-Menge</label>
                            <input type="number" class="form-control" id="editIST_Menge" name="IST_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLagerort" class="form-label">Lagerort</label>
                            <input type="text" class="form-control" id="editLagerort" name="Lagerort">
                        </div>
                        <div class="mb-3">
                            <label for="editBeschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="editBeschreibung" name="Beschreibung"></textarea>
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
            // AJAX für das Hinzufügen von Bauteilen
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'add.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            });

            // AJAX für das Löschen von Bauteilen
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: 'delete.php',
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
                    url: 'get.php',
                    data: { id: id },
                    success: function(response) {
                        var bauteil = JSON.parse(response);
                        $('#editID').val(bauteil.ID);
                        $('#editBauteilname').val(bauteil.Bauteilname);
                        $('#editSOLL_Menge').val(bauteil.SOLL_Menge);
                        $('#editIST_Menge').val(bauteil.IST_Menge);
                        $('#editLagerort').val(bauteil.Lagerort);
                        $('#editBeschreibung').val(bauteil.Beschreibung);
                    }
                });
            });

            // AJAX für das Bearbeiten von Bauteilen
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'update.php',
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