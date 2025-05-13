<?php
require_once __DIR__ . '/includes/Database.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

try {
    $db = Database::getInstance();

    // SQL-Abfrage zum Abrufen aller Ausleihvorgänge
    $sql = "SELECT a.Ausleihe_ID, a.Koffer_ID, k.Besitzer_Oberstufe, k.Besitzer_Mittelstufe, a.Bauteil_ID, b.Bauteilname, a.Ausleihdatum, a.Rueckgabedatum
            FROM ausleihe_tabelle a
            JOIN koffer_tabelle k ON a.Koffer_ID = k.Koffer_ID
            JOIN bauteil_tabelle b ON a.Bauteil_ID = b.ID";
    $ausleihen = $db->fetchAll($sql);

    // SQL-Abfrage zum Abrufen der Koffer_IDs und Bauteilbezeichnungen für die Dropdown-Menüs
    $koffer = $db->fetchAll("SELECT DISTINCT Koffer_ID FROM koffer_tabelle");
    $bauteile = $db->fetchAll("SELECT DISTINCT Bauteilname FROM bauteil_tabelle");
} catch (Exception $e) {
    die("Fehler bei der Datenbankabfrage: " . $e->getMessage());
}
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
                    <?php foreach ($koffer as $k): ?>
                        <option value="<?php echo htmlspecialchars($k['Koffer_ID']); ?>">
                            <?php echo htmlspecialchars($k['Koffer_ID']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <select class="form-control" id="filterBauteil">
                    <option value="">Filter nach Bauteilname</option>
                    <?php foreach ($bauteile as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['Bauteilname']); ?>">
                            <?php echo htmlspecialchars($b['Bauteilname']); ?>
                        </option>
                    <?php endforeach; ?>
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
                <?php if (!empty($ausleihen)): ?>
                    <?php foreach ($ausleihen as $row): ?>
                        <tr>
                            <th scope='row'><?php echo htmlspecialchars($row['Ausleihe_ID']); ?></th>
                            <td><?php echo htmlspecialchars($row['Koffer_ID']); ?></td>
                            <td><?php echo htmlspecialchars($row['Besitzer_Oberstufe']); ?></td>
                            <td><?php echo htmlspecialchars($row['Besitzer_Mittelstufe']); ?></td>
                            <td><?php echo htmlspecialchars($row['Bauteil_ID']); ?></td>
                            <td><?php echo htmlspecialchars($row['Bauteilname']); ?></td>
                            <td><?php echo htmlspecialchars($row['Ausleihdatum']); ?></td>
                            <td><?php echo htmlspecialchars($row['Rueckgabedatum']); ?></td>
                            <td>
                                <?php if (is_null($row['Rueckgabedatum'])): ?>
                                    <button class='btn btn-primary btn-sm rueckgabe-btn' data-id='<?php echo htmlspecialchars($row['Ausleihe_ID']); ?>'>Zurückgeben</button>
                                <?php else: ?>
                                    Bereits zurückgegeben
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='9'>Keine Daten gefunden</td></tr>
                <?php endif; ?>
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
