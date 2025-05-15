<?php


session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Bauteile und Koffer abrufen
    $bauteile = $db->fetchAll("SELECT ID, Bauteilname FROM bauteil_tabelle");
    $koffer = $db->fetchAll("SELECT Koffer_ID, Besitzer_Oberstufe, Besitzer_Mittelstufe FROM koffer_tabelle");
} catch (Exception $e) {
    $error = "Ein Fehler ist aufgetreten: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bauteil Ausleihe</title>
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
            <div class="card-header">
                <h2>Bauteil ausleihen</h2>
            </div>
            <div class="card-body">
                <form id="ausleiheForm">
                    <div class="mb-3">
                        <label for="Bauteil_ID" class="form-label">Bauteile</label>
                        <select class="form-select" id="Bauteil_ID" name="Bauteil_ID[]" multiple required>
                            <?php foreach ($bauteile as $bauteil): ?>
                                <option value="<?php echo htmlspecialchars($bauteil['ID']); ?>">
                                    <?php echo htmlspecialchars($bauteil['Bauteilname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Halten Sie die Strg-Taste (Windows) oder die Cmd-Taste (Mac) gedrückt, um mehrere Bauteile auszuwählen.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Koffer</label>
                        <div class="row">
                            <?php foreach ($koffer as $k): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="Koffer_ID[]" 
                                               value="<?php echo htmlspecialchars($k['Koffer_ID']); ?>" 
                                               id="koffer_<?php echo $k['Koffer_ID']; ?>">
                                        <label class="form-check-label" for="koffer_<?php echo $k['Koffer_ID']; ?>">
                                            Koffer <?php echo htmlspecialchars($k['Koffer_ID']); ?> 
                                            (<?php echo htmlspecialchars($k['Besitzer_Oberstufe']); ?> / 
                                            <?php echo htmlspecialchars($k['Besitzer_Mittelstufe']); ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Ausleihen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ausleiheForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_ausleihe.php',
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
