<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';

secure_session_start();
send_security_headers();
require_login();

$bauteile = [];
$koffer = [];
$error = '';
$categories = inventory_categories();

try {
    $db = Database::getInstance();
    $bauteile = $db->fetchAll('
        SELECT ID, Bauteilname, Kategorie, IST_Menge
        FROM bauteil_tabelle
        WHERE IST_Menge > 0
        ORDER BY Kategorie, Bauteilname
    ');
    $koffer = $db->fetchAll('
        SELECT Koffer_ID, Bezeichnung, Kategorie, Zielgruppe, Ansprechpartner
        FROM koffer_tabelle
        ORDER BY Kategorie, Bezeichnung, Koffer_ID
    ');
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Die Ausleihe konnte nicht vorbereitet werden.';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ausleihe | Raspi-Verwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Neue Ausleihe</h1>
                <p class="page-subtitle">Wähle verfügbare Teile und die passenden Sets oder Koffer aus.</p>
            </div>
        </div>

        <section class="content-panel p-4">
            <form id="ausleiheForm">
                <div class="mb-4">
                    <label for="Bauteil_ID" class="form-label">Inventar</label>
                    <select class="form-select" id="Bauteil_ID" name="Bauteil_ID[]" multiple required size="10">
                        <?php foreach ($bauteile as $bauteil): ?>
                            <?php $category = normalize_category((string)($bauteil['Kategorie'] ?? 'Sonstiges')); ?>
                            <option value="<?php echo e($bauteil['ID']); ?>">
                                <?php echo e($categories[$category]); ?> · <?php echo e($bauteil['Bauteilname']); ?> (verfügbar: <?php echo e($bauteil['IST_Menge']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Mehrere Einträge können gemeinsam ausgeliehen werden.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Sets &amp; Koffer</label>
                    <div class="loan-choice-grid">
                        <?php foreach ($koffer as $k): ?>
                            <?php $category = normalize_category((string)($k['Kategorie'] ?? 'Sonstiges')); ?>
                            <label class="choice-card" for="koffer_<?php echo e($k['Koffer_ID']); ?>">
                                <input class="form-check-input me-2" type="checkbox"
                                       name="Koffer_ID[]"
                                       value="<?php echo e($k['Koffer_ID']); ?>"
                                       id="koffer_<?php echo e($k['Koffer_ID']); ?>">
                                <strong><?php echo e(koffer_label($k)); ?></strong>
                                <div class="text-muted small"><?php echo e($categories[$category]); ?> · <?php echo e($k['Zielgruppe']); ?></div>
                                <?php if (!empty($k['Ansprechpartner'])): ?>
                                    <div class="text-muted small">Kontakt: <?php echo e($k['Ansprechpartner']); ?></div>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-exchange-alt me-2"></i>Ausleihe speichern
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo e(csp_nonce()); ?>">
        $(document).ready(function() {
            $('#ausleiheForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_ausleihe.php',
                    data: $(this).serialize(),
                    success: function() { location.href = 'uebersicht.php'; },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        alert(response.error || 'Die Ausleihe konnte nicht gespeichert werden.');
                    }
                });
            });
        });
    </script>
</body>
</html>
