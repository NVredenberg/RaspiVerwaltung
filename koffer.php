<?php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    $koffer = $db->fetchAll("SELECT Koffer_ID, Besitzer_Oberstufe, Besitzer_Mittelstufe FROM koffer_tabelle");
} catch (Exception $e) {
    $error = "Ein Fehler ist aufgetreten: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Koffer Verwaltung</title>
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
                <h2>Koffer Übersicht</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Neuer Koffer</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                            <?php if (!empty($koffer)): ?>
                                <?php foreach ($koffer as $row): ?>
                                    <tr>
                                        <th scope="row"><?php echo htmlspecialchars($row['Koffer_ID']); ?></th>
                                        <td><?php echo htmlspecialchars($row['Besitzer_Oberstufe']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Besitzer_Mittelstufe']); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm edit-btn" 
                                                    data-id="<?php echo $row['Koffer_ID']; ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal">
                                                Bearbeiten
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="<?php echo $row['Koffer_ID']; ?>">
                                                Löschen
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Keine Daten gefunden</td>
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
                    <h5 class="modal-title">Neuen Koffer hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label for="Besitzer_Oberstufe" class="form-label">Besitzer Oberstufe</label>
                            <input type="text" class="form-control" id="Besitzer_Oberstufe" name="Besitzer_Oberstufe" required>
                        </div>
                        <div class="mb-3">
                            <label for="Besitzer_Mittelstufe" class="form-label">Besitzer Mittelstufe</label>
                            <input type="text" class="form-control" id="Besitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
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
                    <h5 class="modal-title">Koffer bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editKoffer_ID" name="Koffer_ID">
                        <div class="mb-3">
                            <label for="editBesitzer_Oberstufe" class="form-label">Besitzer Oberstufe</label>
                            <input type="text" class="form-control" id="editBesitzer_Oberstufe" name="Besitzer_Oberstufe" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBesitzer_Mittelstufe" class="form-label">Besitzer Mittelstufe</label>
                            <input type="text" class="form-control" id="editBesitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
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
            // AJAX für das Hinzufügen von Koffern
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'add_koffer.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        location.reload();
                    }
                });
            });

            // AJAX für das Löschen von Koffern
            $('.delete-btn').on('click', function() {
                if (confirm('Möchten Sie diesen Koffer wirklich löschen?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'delete_koffer.php',
                        data: { id: id },
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });

            // AJAX für das Bearbeiten von Koffern
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'GET',
                    url: 'get_koffer.php',
                    data: { id: id },
                    success: function(response) {
                        var koffer = JSON.parse(response);
                        $('#editKoffer_ID').val(koffer.Koffer_ID);
                        $('#editBesitzer_Oberstufe').val(koffer.Besitzer_Oberstufe);
                        $('#editBesitzer_Mittelstufe').val(koffer.Besitzer_Mittelstufe);
                    }
                });
            });

            // AJAX für das Speichern der Bearbeitung
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'update_koffer.php',
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