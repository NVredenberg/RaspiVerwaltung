<?php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    $bauteile = $db->fetchAll("SELECT ID, Bauteilname, SOLL_Menge, IST_Menge, Lagerort, Beschreibung FROM bauteil_tabelle");
} catch (Exception $e) {
    $error = "Ein Fehler ist aufgetreten: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bauteil Verwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('includes/header.php'); ?>
    <div class="container mt-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Bauteil Übersicht</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Neues Bauteil</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                            <?php if (!empty($bauteile)): ?>
                                <?php foreach ($bauteile as $row): ?>
                                    <tr>
                                        <th scope="row"><?php echo htmlspecialchars($row['ID']); ?></th>
                                        <td><?php echo htmlspecialchars($row['Bauteilname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['SOLL_Menge']); ?></td>
                                        <td><?php echo htmlspecialchars($row['IST_Menge']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Lagerort']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Beschreibung']); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm edit-btn" 
                                                    data-id="<?php echo $row['ID']; ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal">
                                                Bearbeiten
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="<?php echo $row['ID']; ?>">
                                                Löschen
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Keine Daten gefunden</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neues Bauteil hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                            <input type="text" class="form-control" id="Lagerort" name="Lagerort" required>
                        </div>
                        <div class="mb-3">
                            <label for="Beschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="Beschreibung" name="Beschreibung"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-primary">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bauteil bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <input type="text" class="form-control" id="editLagerort" name="Lagerort" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBeschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="editBeschreibung" name="Beschreibung"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-primary">Speichern</button>
                        </div>
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
                        location.reload();
                    }
                });
            });

            // AJAX für das Löschen von Bauteilen
            $('.delete-btn').on('click', function() {
                if (confirm('Möchten Sie dieses Bauteil wirklich löschen?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'delete.php',
                        data: { id: id },
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });

            // AJAX für das Bearbeiten von Bauteilen
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

            // AJAX für das Speichern der Bearbeitung
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'update.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>  