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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --card-bg: #fff;
            --card-border: rgba(0, 0, 0, 0.125);
            --table-header-bg: #f8f9fa;
            --text-color: inherit;
            --filter-bg: #f8f9fa;
            --filter-border: #dee2e6;
        }

        [data-bs-theme="dark"] {
            --card-bg: #212529;
            --card-border: rgba(255, 255, 255, 0.125);
            --table-header-bg: #2c3034;
            --text-color: #e9ecef;
            --filter-bg: #2c3034;
            --filter-border: #495057;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.5rem;
        }
        
        .table {
            margin-bottom: 0;
            color: var(--text-color);
        }
        
        .table thead th {
            border-top: none;
            background-color: var(--table-header-bg);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table td, .table th {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        
        .btn-warning:hover {
            background-color: #ffca2c;
            border-color: #ffc720;
            color: #000;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-1px);
        }
        
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .form-control {
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--card-border);
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
        }
        
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-color);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--card-border);
        }
        
        .status-badge {
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
        }
        
        .status-badge.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .table-container {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .search-box {
            max-width: 300px;
            margin-bottom: 1rem;
        }
        
        .search-box .form-control {
            padding-left: 2.5rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-color);
            opacity: 0.5;
        }

        [data-bs-theme="dark"] .status-badge.warning {
            background-color: #664d03;
            color: #ffda6a;
        }
        
        [data-bs-theme="dark"] .status-badge.success {
            background-color: #0f5132;
            color: #75b798;
        }
        
        [data-bs-theme="dark"] .status-badge.danger {
            background-color: #842029;
            color: #ea868f;
        }

        .filter-section {
            background-color: var(--filter-bg);
            border: 1px solid var(--filter-border);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .filter-section label {
            color: var(--text-color);
            font-weight: 500;
        }

        .pagination {
            margin-top: 1rem;
            justify-content: center;
        }

        .pagination .page-link {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .pagination .page-link:hover {
            background-color: var(--table-header-bg);
            border-color: var(--card-border);
        }
    </style>
</head>
<body >
<?php include('includes/header.php'); ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-list-alt me-2"></i>Ausleihübersicht
            </h2>
            <div class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                <?php echo count($ausleihen); ?> Einträge gefunden
            </div>
        </div>

        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="filter-label">
                        <i class="fas fa-briefcase me-1"></i>Koffer
                    </label>
                    <select class="form-select" id="filterKoffer">
                        <option value="">Alle Koffer</option>
                        <?php foreach ($koffer as $k): ?>
                            <option value="<?php echo htmlspecialchars($k['Koffer_ID']); ?>">
                                Koffer <?php echo htmlspecialchars($k['Koffer_ID']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">
                        <i class="fas fa-microchip me-1"></i>Bauteil
                    </label>
                    <select class="form-select" id="filterBauteil">
                        <option value="">Alle Bauteile</option>
                        <?php foreach ($bauteile as $b): ?>
                            <option value="<?php echo htmlspecialchars($b['Bauteilname']); ?>">
                                <?php echo htmlspecialchars($b['Bauteilname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">
                        <i class="fas fa-filter me-1"></i>Status
                    </label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Alle Status</option>
                        <option value="active">Aktive Ausleihen</option>
                        <option value="returned">Zurückgegebene Ausleihen</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Koffer</th>
                            <th scope="col">Besitzer</th>
                            <th scope="col">Bauteil</th>
                            <th scope="col">Ausleihdatum</th>
                            <th scope="col">Rückgabedatum</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (!empty($ausleihen)): ?>
                            <?php foreach ($ausleihen as $row): ?>
                                <tr>
                                    <td class="text-muted">#<?php echo htmlspecialchars($row['Ausleihe_ID']); ?></td>
                                    <td>
                                        <strong>Koffer <?php echo htmlspecialchars($row['Koffer_ID']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div>Oberstufe: <?php echo htmlspecialchars($row['Besitzer_Oberstufe']); ?></div>
                                            <div>Mittelstufe: <?php echo htmlspecialchars($row['Besitzer_Mittelstufe']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div>ID: <?php echo htmlspecialchars($row['Bauteil_ID']); ?></div>
                                            <div class="text-primary"><?php echo htmlspecialchars($row['Bauteilname']); ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo date('d.m.Y', strtotime($row['Ausleihdatum'])); ?></td>
                                    <td>
                                        <?php if ($row['Rueckgabedatum']): ?>
                                            <?php echo date('d.m.Y', strtotime($row['Rueckgabedatum'])); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (is_null($row['Rueckgabedatum'])): ?>
                                            <span class="status-badge status-active">
                                                <i class="fas fa-clock me-1"></i>Aktiv
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-returned">
                                                <i class="fas fa-check me-1"></i>Zurückgegeben
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (is_null($row['Rueckgabedatum'])): ?>
                                            <button class='btn btn-primary btn-rueckgabe' data-id='<?php echo htmlspecialchars($row['Ausleihe_ID']); ?>'>
                                                <i class="fas fa-undo me-1"></i>Zurückgeben
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>Bereits zurückgegeben
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Keine Ausleihen gefunden</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // AJAX für das Zurückgeben von Bauteilen
        $('.btn-rueckgabe').on('click', function() {
            var ausleiheId = $(this).data('id');
            var filterKoffer = $('#filterKoffer').val();
            var filterBauteil = $('#filterBauteil').val();
            var filterStatus = $('#filterStatus').val();
            
            $.ajax({
                type: 'POST',
                url: 'ajax/rueckgabe.php',
                data: { Ausleihe_ID: ausleiheId },
                success: function(response) {
                    localStorage.setItem('filterKoffer', filterKoffer);
                    localStorage.setItem('filterBauteil', filterBauteil);
                    localStorage.setItem('filterStatus', filterStatus);
                    location.reload();
                }
            });
        });

        // Filterfunktionen
        function applyFilters() {
            var kofferValue = $('#filterKoffer').val().toLowerCase();
            var bauteilValue = $('#filterBauteil').val().toLowerCase();
            var statusValue = $('#filterStatus').val();

            $('#tableBody tr').each(function() {
                var $row = $(this);
                var kofferMatch = kofferValue === "" || $row.find('td:nth-child(2)').text().toLowerCase().includes(kofferValue);
                var bauteilMatch = bauteilValue === "" || $row.find('td:nth-child(4)').text().toLowerCase().includes(bauteilValue);
                var statusMatch = statusValue === "" || 
                    (statusValue === 'active' && $row.find('.status-active').length > 0) ||
                    (statusValue === 'returned' && $row.find('.status-returned').length > 0);

                $row.toggle(kofferMatch && bauteilMatch && statusMatch);
            });
        }

        $('#filterKoffer, #filterBauteil, #filterStatus').on('change', applyFilters);

        // Filterwerte wiederherstellen
        var savedFilterKoffer = localStorage.getItem('filterKoffer');
        var savedFilterBauteil = localStorage.getItem('filterBauteil');
        var savedFilterStatus = localStorage.getItem('filterStatus');

        if (savedFilterKoffer) $('#filterKoffer').val(savedFilterKoffer);
        if (savedFilterBauteil) $('#filterBauteil').val(savedFilterBauteil);
        if (savedFilterStatus) $('#filterStatus').val(savedFilterStatus);

        applyFilters();
    });
    </script>
</body>
</html>
