<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';

secure_session_start();
send_security_headers();
require_login();

$ausleihen = [];
$koffer = [];
$bauteile = [];
$error = '';
$categories = inventory_categories();

try {
    $db = Database::getInstance();
    $ausleihen = $db->fetchAll('
        SELECT a.Ausleihe_ID, a.Koffer_ID, a.Bauteil_ID, a.Nutzer, a.Ausleihdatum, a.Rueckgabedatum,
               k.Bezeichnung, k.Kategorie AS KofferKategorie, k.Zielgruppe, k.Ansprechpartner,
               b.Bauteilname, b.Kategorie AS BauteilKategorie
        FROM ausleihe_tabelle a
        JOIN koffer_tabelle k ON a.Koffer_ID = k.Koffer_ID
        JOIN bauteil_tabelle b ON a.Bauteil_ID = b.ID
        ORDER BY a.Rueckgabedatum IS NOT NULL, a.Ausleihdatum DESC, k.Bezeichnung, b.Bauteilname
    ');
    $koffer = $db->fetchAll('
        SELECT DISTINCT Koffer_ID, Bezeichnung, Kategorie, Zielgruppe
        FROM koffer_tabelle
        ORDER BY Bezeichnung, Koffer_ID
    ');
    $bauteile = $db->fetchAll('
        SELECT DISTINCT Bauteilname
        FROM bauteil_tabelle
        ORDER BY Bauteilname
    ');
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Die Ausleihübersicht konnte nicht geladen werden.';
}

$activeCount = 0;
foreach ($ausleihen as $row) {
    if ($row['Rueckgabedatum'] === null) {
        $activeCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ausleihübersicht | Raspi-Verwaltung</title>
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
                <h1 class="page-title">Ausleihübersicht</h1>
                <p class="page-subtitle">Offene und erledigte Vorgänge nach Set, Inventar und Status filtern.</p>
            </div>
        </div>

        <section class="metric-grid">
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($ausleihen)); ?></span>
                <span class="metric-label">Vorgänge gesamt</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e($activeCount); ?></span>
                <span class="metric-label">Aktive Ausleihen</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($ausleihen) - $activeCount); ?></span>
                <span class="metric-label">Zurückgegeben</span>
            </div>
        </section>

        <section class="content-panel">
            <div class="panel-toolbar">
                <div>
                    <label for="filterKoffer" class="form-label">Set / Koffer</label>
                    <select class="form-select" id="filterKoffer">
                        <option value="">Alle Sets</option>
                        <?php foreach ($koffer as $k): ?>
                            <option value="<?php echo e(koffer_label($k)); ?>">
                                <?php echo e(koffer_label($k)); ?> (<?php echo e($k['Zielgruppe']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filterBauteil" class="form-label">Inventar</label>
                    <select class="form-select" id="filterBauteil">
                        <option value="">Alle Einträge</option>
                        <?php foreach ($bauteile as $b): ?>
                            <option value="<?php echo e($b['Bauteilname']); ?>"><?php echo e($b['Bauteilname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filterStatus" class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Alle Status</option>
                        <option value="active">Aktiv</option>
                        <option value="returned">Zurückgegeben</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Vorgang</th>
                            <th scope="col">Set / Koffer</th>
                            <th scope="col">Inventar</th>
                            <th scope="col">Ausgeliehen</th>
                            <th scope="col">Rückgabe</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Aktion</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if ($ausleihen): ?>
                            <?php foreach ($ausleihen as $row): ?>
                                <?php
                                $isActive = $row['Rueckgabedatum'] === null;
                                $status = $isActive ? 'active' : 'returned';
                                $partCategory = normalize_category((string)($row['BauteilKategorie'] ?? 'Sonstiges'));
                                $kofferCategory = normalize_category((string)($row['KofferKategorie'] ?? 'Sonstiges'));
                                ?>
                                <tr data-koffer="<?php echo e(koffer_label($row)); ?>" data-bauteil="<?php echo e($row['Bauteilname']); ?>" data-status="<?php echo e($status); ?>">
                                    <td>
                                        <strong>#<?php echo e($row['Ausleihe_ID']); ?></strong>
                                        <div class="text-muted small">von <?php echo e($row['Nutzer']); ?></div>
                                    </td>
                                    <td>
                                        <strong><?php echo e(koffer_label($row)); ?></strong>
                                        <div class="text-muted small"><?php echo e($categories[$kofferCategory]); ?> · <?php echo e($row['Zielgruppe']); ?></div>
                                    </td>
                                    <td>
                                        <strong><?php echo e($row['Bauteilname']); ?></strong>
                                        <div class="text-muted small"><?php echo e($categories[$partCategory]); ?> · ID <?php echo e($row['Bauteil_ID']); ?></div>
                                    </td>
                                    <td><?php echo e(date('d.m.Y', strtotime($row['Ausleihdatum']))); ?></td>
                                    <td>
                                        <?php echo $row['Rueckgabedatum'] ? e(date('d.m.Y', strtotime($row['Rueckgabedatum']))) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="status-badge status-active"><i class="fas fa-clock"></i>Aktiv</span>
                                        <?php else: ?>
                                            <span class="status-badge status-returned"><i class="fas fa-check"></i>Zurückgegeben</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($isActive): ?>
                                            <button class="btn btn-primary btn-sm btn-rueckgabe" data-id="<?php echo e($row['Ausleihe_ID']); ?>">
                                                <i class="fas fa-undo me-1"></i>Zurückgeben
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Erledigt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p class="mb-0">Keine Ausleihen gefunden.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo e(csp_nonce()); ?>">
        $(document).ready(function() {
            function applyFilters() {
                const kofferValue = $('#filterKoffer').val();
                const bauteilValue = $('#filterBauteil').val();
                const statusValue = $('#filterStatus').val();

                $('#tableBody tr[data-status]').each(function() {
                    const $row = $(this);
                    const kofferMatch = kofferValue === '' || $row.data('koffer') === kofferValue;
                    const bauteilMatch = bauteilValue === '' || $row.data('bauteil') === bauteilValue;
                    const statusMatch = statusValue === '' || $row.data('status') === statusValue;
                    $row.toggle(kofferMatch && bauteilMatch && statusMatch);
                });
            }

            $('#filterKoffer, #filterBauteil, #filterStatus').on('change', function() {
                localStorage.setItem(this.id, $(this).val());
                applyFilters();
            });

            ['filterKoffer', 'filterBauteil', 'filterStatus'].forEach(function(id) {
                const value = localStorage.getItem(id);
                if (value) {
                    $('#' + id).val(value);
                }
            });
            applyFilters();

            $('.btn-rueckgabe').on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/rueckgabe.php',
                    data: { Ausleihe_ID: $(this).data('id') },
                    success: function() { location.reload(); },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        alert(response.error || 'Die Rückgabe konnte nicht gespeichert werden.');
                    }
                });
            });
        });
    </script>
</body>
</html>
