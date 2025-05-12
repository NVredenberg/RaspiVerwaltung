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

// SQL-Abfrage zum Abrufen aller Ausleihvorgänge
$sql = "SELECT a.Ausleihe_ID, a.Koffer_ID, k.Besitzer_Oberstufe, k.Besitzer_Mittelstufe, a.Bauteil_ID, b.Bauteilname, a.Ausleihdatum, a.Rueckgabedatum
        FROM ausleihe_tabelle a
        JOIN koffer_tabelle k ON a.Koffer_ID = k.Koffer_ID
        JOIN bauteil_tabelle b ON a.Bauteil_ID = b.ID";
$result = $conn->query($sql);

// Fehlerbehandlung hinzufügen
if (!$result) {
    die("Fehler bei der SQL-Abfrage: " . $conn->error);
}

// SQL-Abfrage zum Abrufen der Koffer_IDs und Bauteilbezeichnungen für die Dropdown-Menüs
$sql_koffer = "SELECT DISTINCT Koffer_ID FROM koffer_tabelle";
$result_koffer = $conn->query($sql_koffer);

$sql_bauteile = "SELECT DISTINCT Bauteilname FROM bauteil_tabelle";
$result_bauteile = $conn->query($sql_bauteile);
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
        <div class="row mb-3">
            <div class="col">
                <select class="form-control" id="filterKoffer">
                    <option value="">Filter nach Koffer_ID</option>
                    <?php
                    if ($result_koffer->num_rows > 0) {
                        while($row = $result_koffer->fetch_assoc()) {
                            echo "<option value='" . $row["Koffer_ID"] . "'>" . $row["Koffer_ID"] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col">
                <select class="form-control" id="filterBauteil">
                    <option value="">Filter nach Bauteilname</option>
                    <?php
                    if ($result_bauteile->num_rows > 0) {
                        while($row = $result_bauteile->fetch_assoc()) {
                            echo "<option value='" . $row["Bauteilname"] . "'>" . $row["Bauteilname"] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Ausleihe_ID</th>
                    <th scope="col">Koffer_ID</th>
                    <th scope="col">Besitzer Oberstufe</th>
                    <th scope="col">Besitzer Mittelstufe</th>
                    <th scope="col">Bauteil_ID</th>
                    <th scope="col">Bauteilname</th>
                    <th scope="col">Ausleihdatum</th>
                    <th scope="col">Rückgabedatum</th>
                    <th scope="col">Aktionen</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                // Daten in Tabelle einfügen
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<th scope='row'>" . $row["Ausleihe_ID"] . "</th>";
                        echo "<td>" . $row["Koffer_ID"] . "</td>";
                        echo "<td>" . $row["Besitzer_Oberstufe"] . "</td>";
                        echo "<td>" . $row["Besitzer_Mittelstufe"] . "</td>";
                        echo "<td>" . $row["Bauteil_ID"] . "</td>";
                        echo "<td>" . $row["Bauteilname"] . "</td>";
                        echo "<td>" . $row["Ausleihdatum"] . "</td>";
                        echo "<td>" . $row["Rueckgabedatum"] . "</td>";
                        echo "<td>";
                        if (is_null($row["Rueckgabedatum"])) {
                            echo "<button class='btn btn-primary btn-sm rueckgabe-btn' data-id='" . $row["Ausleihe_ID"] . "'>Zurückgeben</button>";
                        } else {
                            echo "Bereits zurückgegeben";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>Keine Daten gefunden</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
    $(document).ready(function() {
        // AJAX für das Zurückgeben von Bauteilen
        $('.rueckgabe-btn').on('click', function() {
            var ausleiheId = $(this).data('id');
            var filterKoffer = $('#filterKoffer').val();
            var filterBauteil = $('#filterBauteil').val();
            $.ajax({
                type: 'POST',
                url: 'rueckgabe.php',
                data: { Ausleihe_ID: ausleiheId },
                success: function(response) {
                    //alert(response);
                    // Filterwerte speichern
                    localStorage.setItem('filterKoffer', filterKoffer);
                    localStorage.setItem('filterBauteil', filterBauteil);
                    location.reload();
                }
            });
        });

        // Filterfunktion für Koffer_ID
        $('#filterKoffer').on('change', function() {
            var value = $(this).val().toLowerCase();
            $('#tableBody tr').filter(function() {
                $(this).toggle($(this).find('td:nth-child(2)').text().toLowerCase().indexOf(value) > -1 || value === "");
            });
        });

        // Filterfunktion für Bauteilbezeichnung
        $('#filterBauteil').on('change', function() {
            var value = $(this).val().toLowerCase();
            $('#tableBody tr').filter(function() {
                $(this).toggle($(this).find('td:nth-child(6)').text().toLowerCase().indexOf(value) > -1 || value === "");
            });
        });

        // Filterwerte wiederherstellen
        var savedFilterKoffer = localStorage.getItem('filterKoffer');
        var savedFilterBauteil = localStorage.getItem('filterBauteil');
        if (savedFilterKoffer) {
            $('#filterKoffer').val(savedFilterKoffer).trigger('change');
        }
        if (savedFilterBauteil) {
            $('#filterBauteil').val(savedFilterBauteil).trigger('change');
        }
    });
    </script>
</body>
</html>
<?php
// Verbindung schließen
$conn->close();
?>
