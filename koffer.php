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
<body>
<?php include('includes/header.php'); ?>
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>Koffer Übersicht
                    </h2>
                    <p class="text-muted mb-0 mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo count($koffer); ?> Koffer verwaltet
                    </p>
                </div>
                <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-1"></i>Neuer Koffer
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Koffer</th>
                                    <th scope="col">Besitzer Oberstufe</th>
                                    <th scope="col">Besitzer Mittelstufe</th>
                                    <th scope="col" class="text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody id="koffer_tabelle">
                                <?php if (!empty($koffer)): ?>
                                    <?php foreach ($koffer as $row): ?>
                                        <tr>
                                            <td>
                                                <span class="koffer-id">Koffer <?php echo htmlspecialchars($row['Koffer_ID']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['Besitzer_Oberstufe']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Besitzer_Mittelstufe']); ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-outline-primary btn-action edit-btn" 
                                                        data-id="<?php echo $row['Koffer_ID']; ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal">
                                                    <i class="fas fa-edit me-1"></i>Bearbeiten
                                                </button>
                                            
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-briefcase"></i>
                                                <p class="text-muted mb-0">Keine Koffer vorhanden</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Neuen Koffer hinzufügen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-4">
                            <label for="Besitzer_Oberstufe" class="form-label">
                                <i class="fas fa-user-graduate me-1"></i>Besitzer Oberstufe
                            </label>
                            <input type="text" class="form-control" id="Besitzer_Oberstufe" name="Besitzer_Oberstufe" required>
                        </div>
                        <div class="mb-4">
                            <label for="Besitzer_Mittelstufe" class="form-label">
                                <i class="fas fa-user me-1"></i>Besitzer Mittelstufe
                            </label>
                            <input type="text" class="form-control" id="Besitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Abbrechen
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Speichern
                            </button>
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
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Koffer bearbeiten
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editKoffer_ID" name="Koffer_ID">
                        <div class="mb-4">
                            <label for="editBesitzer_Oberstufe" class="form-label">
                                <i class="fas fa-user-graduate me-1"></i>Besitzer Oberstufe
                            </label>
                            <input type="text" class="form-control" id="editBesitzer_Oberstufe" name="Besitzer_Oberstufe" required>
                        </div>
                        <div class="mb-4">
                            <label for="editBesitzer_Mittelstufe" class="form-label">
                                <i class="fas fa-user me-1"></i>Besitzer Mittelstufe
                            </label>
                            <input type="text" class="form-control" id="editBesitzer_Mittelstufe" name="Besitzer_Mittelstufe" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Abbrechen
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Speichern
                            </button>
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
                    url: 'ajax/add_koffer.php',
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
                        url: 'ajax/delete_koffer.php',
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
                    url: 'ajax/get_koffer.php',
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
                    url: 'ajax/update_koffer.php',
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