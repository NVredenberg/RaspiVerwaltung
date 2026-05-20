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
$activeLoans = [];
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
    $activeLoans = $db->fetchAll('
        SELECT a.Ausleihe_ID, a.Ausleihdatum, b.Bauteilname, b.Kategorie AS BauteilKategorie,
               k.Koffer_ID, k.Bezeichnung, k.Kategorie, k.Zielgruppe
        FROM ausleihe_tabelle a
        JOIN bauteil_tabelle b ON a.Bauteil_ID = b.ID
        JOIN koffer_tabelle k ON a.Koffer_ID = k.Koffer_ID
        WHERE a.Rueckgabedatum IS NULL
        ORDER BY a.Ausleihdatum DESC, k.Bezeichnung, b.Bauteilname
    ');
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Die Massenvorgänge konnten nicht geladen werden.';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Massenvorgänge | Raspi-Verwaltung</title>
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
                <h1 class="page-title">Massenvorgänge</h1>
                <p class="page-subtitle">Mehrere Teile gleichzeitig ausleihen oder offene Ausleihen gesammelt zurückgeben.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <section class="content-panel p-4 h-100">
                    <h2 class="h4 mb-3"><i class="fas fa-layer-group me-2"></i>Massenausleihe</h2>
                    <form id="batchLoanForm">
                        <div class="mb-3">
                            <label class="form-label">Inventar</label>
                            <select class="form-select" name="Bauteil_ID[]" multiple required size="10">
                                <?php foreach ($bauteile as $bauteil): ?>
                                    <?php $category = normalize_category((string)($bauteil['Kategorie'] ?? 'Sonstiges')); ?>
                                    <option value="<?php echo e($bauteil['ID']); ?>">
                                        <?php echo e($categories[$category]); ?> · <?php echo e($bauteil['Bauteilname']); ?> (verfügbar: <?php echo e($bauteil['IST_Menge']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Sets &amp; Koffer</label>
                            <div class="loan-choice-grid">
                                <?php foreach ($koffer as $k): ?>
                                    <?php $category = normalize_category((string)($k['Kategorie'] ?? 'Sonstiges')); ?>
                                    <label class="choice-card" for="batch_koffer_<?php echo e($k['Koffer_ID']); ?>">
                                        <input class="form-check-input me-2" type="checkbox"
                                               name="Koffer_ID[]"
                                               value="<?php echo e($k['Koffer_ID']); ?>"
                                               id="batch_koffer_<?php echo e($k['Koffer_ID']); ?>">
                                        <strong><?php echo e(koffer_label($k)); ?></strong>
                                        <div class="text-muted small"><?php echo e($categories[$category]); ?> · <?php echo e($k['Zielgruppe']); ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Ausleihen
                        </button>
                    </form>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="content-panel p-4 h-100">
                    <h2 class="h4 mb-3"><i class="fas fa-undo me-2"></i>Massenrückgabe</h2>
                    <form id="batchReturnForm">
                        <div class="table-responsive mb-3 scroll-panel">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Auswahl</th>
                                        <th>Inventar</th>
                                        <th>Set</th>
                                        <th>Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($activeLoans): ?>
                                        <?php foreach ($activeLoans as $loan): ?>
                                            <?php $loanCategory = normalize_category((string)($loan['BauteilKategorie'] ?? 'Sonstiges')); ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                           name="return_ids[]"
                                                           value="<?php echo e($loan['Ausleihe_ID']); ?>"
                                                           class="form-check-input">
                                                </td>
                                                <td>
                                                    <strong><?php echo e($loan['Bauteilname']); ?></strong>
                                                    <div class="text-muted small"><?php echo e($categories[$loanCategory]); ?></div>
                                                </td>
                                                <td>
                                                    <?php echo e(koffer_label($loan)); ?>
                                                    <div class="text-muted small"><?php echo e($loan['Zielgruppe']); ?></div>
                                                </td>
                                                <td><?php echo e(date('d.m.Y', strtotime($loan['Ausleihdatum']))); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state py-4">
                                                    <i class="fas fa-check-circle"></i>
                                                    <p class="mb-0">Keine offenen Ausleihen.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-undo me-2"></i>Ausgewählte zurückgeben
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo e(csp_nonce()); ?>">
        $(document).ready(function() {
            function showAjaxError(xhr) {
                const response = xhr.responseJSON || {};
                alert(response.error || 'Der Vorgang konnte nicht abgeschlossen werden.');
            }

            $('#batchLoanForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_batch_ausleihe.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });

            $('#batchReturnForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_batch_return.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });
        });
    </script>
</body>
</html>
