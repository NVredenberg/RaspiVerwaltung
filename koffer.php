<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/audit.php';

secure_session_start();
send_security_headers();
require_login();

$koffer = [];
$error = '';
$categories = inventory_categories();

try {
    $db = Database::getInstance();
    $latestSetAudit = latest_audit_subquery('set', 'kt.Koffer_ID');
    $koffer = $db->fetchAll("
        SELECT kt.Koffer_ID, kt.Bezeichnung, kt.Kategorie, kt.Zielgruppe, kt.Ansprechpartner, kt.Beschreibung,
               {$latestSetAudit} AS Letzte_Aenderung
        FROM koffer_tabelle kt
        ORDER BY Kategorie, Bezeichnung, Koffer_ID
    ");
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Die Koffer und Sets konnten nicht geladen werden.';
}

$categoryCount = [];
foreach ($koffer as $row) {
    $category = normalize_category((string)($row['Kategorie'] ?? 'Sonstiges'));
    $categoryCount[$category] = ($categoryCount[$category] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sets & Koffer | Raspi-Verwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Sets &amp; Koffer</h1>
                <p class="page-subtitle">Unterschiedliche Koffer, Klassen und Einsatzbereiche sauber benennen.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Set hinzufügen
            </button>
        </div>

        <section class="metric-grid" aria-label="Koffer Kennzahlen">
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($koffer)); ?></span>
                <span class="metric-label">Sets &amp; Koffer</span>
            </div>
            <?php foreach ($categories as $value => $label): ?>
                <div class="metric-card">
                    <span class="metric-value"><?php echo e($categoryCount[$value] ?? 0); ?></span>
                    <span class="metric-label"><?php echo e($label); ?></span>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="content-panel">
            <div class="panel-toolbar">
                <div>
                    <label for="searchInput" class="form-label">Suche</label>
                    <input type="search" class="form-control" id="searchInput" placeholder="Bezeichnung, Klasse oder Ansprechpartner">
                </div>
                <div>
                    <label for="categoryFilter" class="form-label">Kategorie</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">Alle Kategorien</option>
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Bezeichnung</th>
                            <th scope="col">Kategorie</th>
                            <th scope="col">Zielgruppe / Klasse</th>
                            <th scope="col">Ansprechpartner</th>
                            <th scope="col">Beschreibung</th>
                            <th scope="col">Letzte Änderung</th>
                            <th scope="col" class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="koffer_tabelle">
                        <?php if ($koffer): ?>
                            <?php foreach ($koffer as $row): ?>
                                <?php $category = normalize_category((string)($row['Kategorie'] ?? 'Sonstiges')); ?>
                                <tr data-category="<?php echo e($category); ?>">
                                    <td>
                                        <strong><?php echo e(koffer_label($row)); ?></strong>
                                        <div class="text-muted small">ID <?php echo e($row['Koffer_ID']); ?></div>
                                    </td>
                                    <td><span class="category-badge"><?php echo e($categories[$category]); ?></span></td>
                                    <td><?php echo e($row['Zielgruppe'] ?? 'Allgemein'); ?></td>
                                    <td><?php echo e($row['Ansprechpartner'] ?? ''); ?></td>
                                    <td><?php echo e($row['Beschreibung'] ?? ''); ?></td>
                                    <td class="small text-muted"><?php echo e($row['Letzte_Aenderung'] ?? 'Noch keine Änderung protokolliert'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-outline-primary btn-sm edit-btn"
                                                    data-id="<?php echo e($row['Koffer_ID']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    title="Bearbeiten">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-id="<?php echo e($row['Koffer_ID']); ?>"
                                                    title="Löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-toolbox"></i>
                                        <p class="mb-0">Noch keine Sets oder Koffer vorhanden.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Set hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label for="Bezeichnung" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="Bezeichnung" name="Bezeichnung" maxlength="120" required placeholder="z. B. Arduino-Koffer 8A">
                        </div>
                        <div class="mb-3">
                            <label for="Kategorie" class="form-label">Kategorie</label>
                            <select class="form-select" id="Kategorie" name="Kategorie" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="Zielgruppe" class="form-label">Zielgruppe / Klasse</label>
                            <input type="text" class="form-control" id="Zielgruppe" name="Zielgruppe" maxlength="120" required placeholder="z. B. 8A, EF Informatik, Werkstatt">
                        </div>
                        <div class="mb-3">
                            <label for="Ansprechpartner" class="form-label">Ansprechpartner</label>
                            <input type="text" class="form-control" id="Ansprechpartner" name="Ansprechpartner" maxlength="120">
                        </div>
                        <div class="mb-3">
                            <label for="Beschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="Beschreibung" name="Beschreibung" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Set bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editKoffer_ID" name="Koffer_ID">
                        <div class="mb-3">
                            <label for="editBezeichnung" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="editBezeichnung" name="Bezeichnung" maxlength="120" required>
                        </div>
                        <div class="mb-3">
                            <label for="editKategorie" class="form-label">Kategorie</label>
                            <select class="form-select" id="editKategorie" name="Kategorie" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editZielgruppe" class="form-label">Zielgruppe / Klasse</label>
                            <input type="text" class="form-control" id="editZielgruppe" name="Zielgruppe" maxlength="120" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAnsprechpartner" class="form-label">Ansprechpartner</label>
                            <input type="text" class="form-control" id="editAnsprechpartner" name="Ansprechpartner" maxlength="120">
                        </div>
                        <div class="mb-3">
                            <label for="editBeschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="editBeschreibung" name="Beschreibung" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo e(csp_nonce()); ?>">
        $(document).ready(function() {
            function applyFilters() {
                const search = $('#searchInput').val().toLowerCase();
                const category = $('#categoryFilter').val();

                $('#koffer_tabelle tr[data-category]').each(function() {
                    const $row = $(this);
                    const matchesSearch = search === '' || $row.text().toLowerCase().includes(search);
                    const matchesCategory = category === '' || $row.data('category') === category;
                    $row.toggle(matchesSearch && matchesCategory);
                });
            }

            $('#searchInput, #categoryFilter').on('input change', applyFilters);

            function showAjaxError(xhr) {
                const response = xhr.responseJSON || {};
                alert(response.error || 'Der Vorgang konnte nicht abgeschlossen werden.');
            }

            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add_koffer.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });

            $('.delete-btn').on('click', function() {
                if (!confirm('Dieses Set wirklich löschen? Offene Ausleihen dazu werden ebenfalls entfernt.')) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: 'ajax/delete_koffer.php',
                    data: { id: $(this).data('id') },
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });

            $('.edit-btn').on('click', function() {
                $.ajax({
                    type: 'GET',
                    url: 'ajax/get_koffer.php',
                    data: { id: $(this).data('id') },
                    success: function(koffer) {
                        $('#editKoffer_ID').val(koffer.Koffer_ID);
                        $('#editBezeichnung').val(koffer.Bezeichnung);
                        $('#editKategorie').val(koffer.Kategorie || 'Sonstiges');
                        $('#editZielgruppe').val(koffer.Zielgruppe || 'Allgemein');
                        $('#editAnsprechpartner').val(koffer.Ansprechpartner || '');
                        $('#editBeschreibung').val(koffer.Beschreibung || '');
                    },
                    error: showAjaxError
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/update_koffer.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });
        });
    </script>
</body>
</html>
