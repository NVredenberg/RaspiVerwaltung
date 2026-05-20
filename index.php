<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/audit.php';

secure_session_start();
send_security_headers();
require_login();

$bauteile = [];
$error = '';
$categories = inventory_categories();

try {
    $db = Database::getInstance();
    $latestInventoryAudit = latest_audit_subquery('inventory', 'bt.ID');
    $bauteile = $db->fetchAll("
        SELECT bt.ID, bt.Bauteilname, bt.Kategorie, bt.SOLL_Menge, bt.IST_Menge, bt.Lagerort, bt.Beschreibung,
               {$latestInventoryAudit} AS Letzte_Aenderung
        FROM bauteil_tabelle bt
        ORDER BY Kategorie, Bauteilname
    ");
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Das Inventar konnte nicht geladen werden.';
}

$totalSoll = 0;
$totalIst = 0;
$missingRows = 0;
$categoryCount = [];
foreach ($bauteile as $row) {
    $totalSoll += (int)$row['SOLL_Menge'];
    $totalIst += (int)$row['IST_Menge'];
    if ((int)$row['SOLL_Menge'] > (int)$row['IST_Menge']) {
        $missingRows++;
    }
    $category = (string)($row['Kategorie'] ?? 'Sonstiges');
    $categoryCount[$category] = ($categoryCount[$category] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventar | Raspi-Verwaltung</title>
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
                <h1 class="page-title">Inventar</h1>
                <p class="page-subtitle">Raspberrys, Arduino-Koffer, PC-Teile und weiteres Zubehör zentral verwalten.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Eintrag hinzufügen
            </button>
        </div>

        <section class="metric-grid" aria-label="Inventar Kennzahlen">
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($bauteile)); ?></span>
                <span class="metric-label">Inventareinträge</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e($totalIst); ?> / <?php echo e($totalSoll); ?></span>
                <span class="metric-label">Verfügbar / Soll</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e($missingRows); ?></span>
                <span class="metric-label">Einträge mit Fehlbestand</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($categoryCount)); ?></span>
                <span class="metric-label">Genutzte Kategorien</span>
            </div>
        </section>

        <section class="content-panel">
            <div class="panel-toolbar">
                <div>
                    <label for="searchInput" class="form-label">Suche</label>
                    <input type="search" class="form-control" id="searchInput" placeholder="Name, Lagerort oder Beschreibung">
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
                <div>
                    <label for="stockFilter" class="form-label">Bestand</label>
                    <select class="form-select" id="stockFilter">
                        <option value="">Alle Bestände</option>
                        <option value="missing">Fehlbestand</option>
                        <option value="complete">Vollständig</option>
                        <option value="surplus">Überzählig</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Inventar</th>
                            <th scope="col">Kategorie</th>
                            <th scope="col">Bestand</th>
                            <th scope="col">Status</th>
                            <th scope="col">Lagerort</th>
                            <th scope="col">Beschreibung</th>
                            <th scope="col">Letzte Änderung</th>
                            <th scope="col" class="text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="bauteil_tabelle">
                        <?php if ($bauteile): ?>
                            <?php foreach ($bauteile as $row): ?>
                                <?php
                                $diff = (int)$row['SOLL_Menge'] - (int)$row['IST_Menge'];
                                $status = $diff > 0 ? 'missing' : ($diff < 0 ? 'surplus' : 'complete');
                                $category = normalize_category((string)($row['Kategorie'] ?? 'Sonstiges'));
                                ?>
                                <tr data-category="<?php echo e($category); ?>" data-stock="<?php echo e($status); ?>">
                                    <td>
                                        <strong><?php echo e($row['Bauteilname']); ?></strong>
                                        <div class="text-muted small">ID <?php echo e($row['ID']); ?></div>
                                    </td>
                                    <td><span class="category-badge"><?php echo e($categories[$category]); ?></span></td>
                                    <td><?php echo e($row['IST_Menge']); ?> / <?php echo e($row['SOLL_Menge']); ?></td>
                                    <td>
                                        <?php if ($status === 'missing'): ?>
                                            <span class="status-badge warning"><i class="fas fa-exclamation-circle"></i>Fehlend</span>
                                        <?php elseif ($status === 'surplus'): ?>
                                            <span class="status-badge danger"><i class="fas fa-plus-circle"></i>Überzählig</span>
                                        <?php else: ?>
                                            <span class="status-badge success"><i class="fas fa-check-circle"></i>Vollständig</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($row['Lagerort']); ?></td>
                                    <td><?php echo e($row['Beschreibung']); ?></td>
                                    <td class="small text-muted"><?php echo e($row['Letzte_Aenderung'] ?? 'Noch keine Änderung protokolliert'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-outline-primary btn-sm edit-btn"
                                                    data-id="<?php echo e($row['ID']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    title="Bearbeiten">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-id="<?php echo e($row['ID']); ?>"
                                                    title="Löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p class="mb-0">Noch keine Inventareinträge vorhanden.</p>
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
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Inventar hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label for="Bauteilname" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="Bauteilname" name="Bauteilname" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="Kategorie" class="form-label">Kategorie</label>
                            <select class="form-select" id="Kategorie" name="Kategorie" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="SOLL_Menge" class="form-label">Soll-Menge</label>
                                <input type="number" min="0" class="form-control" id="SOLL_Menge" name="SOLL_Menge" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="IST_Menge" class="form-label">Ist-Menge</label>
                                <input type="number" min="0" class="form-control" id="IST_Menge" name="IST_Menge" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="Lagerort" class="form-label">Lagerort</label>
                            <input type="text" class="form-control" id="Lagerort" name="Lagerort" maxlength="100" required>
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
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Inventar bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editID" name="ID">
                        <div class="mb-3">
                            <label for="editBauteilname" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="editBauteilname" name="Bauteilname" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="editKategorie" class="form-label">Kategorie</label>
                            <select class="form-select" id="editKategorie" name="Kategorie" required>
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="editSOLL_Menge" class="form-label">Soll-Menge</label>
                                <input type="number" min="0" class="form-control" id="editSOLL_Menge" name="SOLL_Menge" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="editIST_Menge" class="form-label">Ist-Menge</label>
                                <input type="number" min="0" class="form-control" id="editIST_Menge" name="IST_Menge" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editLagerort" class="form-label">Lagerort</label>
                            <input type="text" class="form-control" id="editLagerort" name="Lagerort" maxlength="100" required>
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
                const stock = $('#stockFilter').val();

                $('#bauteil_tabelle tr[data-category]').each(function() {
                    const $row = $(this);
                    const matchesSearch = search === '' || $row.text().toLowerCase().includes(search);
                    const matchesCategory = category === '' || $row.data('category') === category;
                    const matchesStock = stock === '' || $row.data('stock') === stock;
                    $row.toggle(matchesSearch && matchesCategory && matchesStock);
                });
            }

            $('#searchInput, #categoryFilter, #stockFilter').on('input change', applyFilters);

            function showAjaxError(xhr) {
                const response = xhr.responseJSON || {};
                alert(response.error || 'Der Vorgang konnte nicht abgeschlossen werden.');
            }

            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });

            $('.delete-btn').on('click', function() {
                if (!confirm('Diesen Inventareintrag wirklich löschen?')) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: 'ajax/delete.php',
                    data: { id: $(this).data('id') },
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });

            $('.edit-btn').on('click', function() {
                $.ajax({
                    type: 'GET',
                    url: 'ajax/get.php',
                    data: { id: $(this).data('id') },
                    success: function(bauteil) {
                        $('#editID').val(bauteil.ID);
                        $('#editBauteilname').val(bauteil.Bauteilname);
                        $('#editKategorie').val(bauteil.Kategorie || 'Sonstiges');
                        $('#editSOLL_Menge').val(bauteil.SOLL_Menge);
                        $('#editIST_Menge').val(bauteil.IST_Menge);
                        $('#editLagerort').val(bauteil.Lagerort);
                        $('#editBeschreibung').val(bauteil.Beschreibung);
                    },
                    error: showAjaxError
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/update.php',
                    data: $(this).serialize(),
                    success: function() { location.reload(); },
                    error: showAjaxError
                });
            });
        });
    </script>
</body>
</html>
