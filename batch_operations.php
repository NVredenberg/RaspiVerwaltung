<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    $bauteile = $db->fetchAll("SELECT ID, Bauteilname, IST_Menge FROM bauteil_tabelle");
    $koffer = $db->fetchAll("SELECT Koffer_ID, Besitzer_Oberstufe, Besitzer_Mittelstufe FROM koffer_tabelle");
    
    $activeLoans = $db->fetchAll("
        SELECT a.*, b.Bauteilname, k.Besitzer_Oberstufe, k.Besitzer_Mittelstufe 
        FROM ausleihe_tabelle a 
        JOIN bauteil_tabelle b ON a.Bauteil_ID = b.ID 
        JOIN koffer_tabelle k ON a.Koffer_ID = k.Koffer_ID 
        WHERE a.Rueckgabedatum IS NULL
    ");
} catch (Exception $e) {
    $error = "Ein Fehler ist aufgetreten: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Batch Operationen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('includes/header.php'); ?>
    <div class="container mt-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Massenausleihe</h3>
                    </div>
                    <div class="card-body">
                        <form id="batchLoanForm">
                            <div class="mb-3">
                                <label class="form-label">Bauteile</label>
                                <select class="form-select" name="Bauteil_ID[]" multiple required>
                                    <?php foreach ($bauteile as $bauteil): ?>
                                        <option value="<?php echo htmlspecialchars($bauteil['ID']); ?>">
                                            <?php echo htmlspecialchars($bauteil['Bauteilname']); ?> 
                                            (Verfügbar: <?php echo htmlspecialchars($bauteil['IST_Menge']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Halten Sie die Strg-Taste (Windows) oder die Cmd-Taste (Mac) gedrückt, um mehrere Bauteile auszuwählen.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Koffer</label>
                                <div class="row">
                                    <?php foreach ($koffer as $k): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="Koffer_ID[]" 
                                                       value="<?php echo htmlspecialchars($k['Koffer_ID']); ?>" 
                                                       id="koffer_<?php echo $k['Koffer_ID']; ?>">
                                                <label class="form-check-label" for="koffer_<?php echo $k['Koffer_ID']; ?>">
                                                    Koffer <?php echo htmlspecialchars($k['Koffer_ID']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Massenausleihe durchführen</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Massenrückgabe</h3>
                    </div>
                    <div class="card-body">
                        <form id="batchReturnForm">
                            <div class="mb-3">
                                <label class="form-label">Aktive Ausleihen</label>
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-striped">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th>Auswählen</th>
                                                <th>Bauteil</th>
                                                <th>Koffer</th>
                                                <th>Ausleihdatum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activeLoans as $loan): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" 
                                                               name="return_ids[]" 
                                                               value="<?php echo htmlspecialchars($loan['Ausleihe_ID']); ?>"
                                                               class="form-check-input">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($loan['Bauteilname']); ?></td>
                                                    <td>Koffer <?php echo htmlspecialchars($loan['Koffer_ID']); ?></td>
                                                    <td><?php echo htmlspecialchars($loan['Ausleihdatum']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Ausgewählte zurückgeben</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#batchLoanForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_batch_ausleihe.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Fehler bei der Massenausleihe: ' + xhr.responseText);
                    }
                });
            });

            $('#batchReturnForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_batch_return.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Fehler bei der Massenrückgabe: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html> 