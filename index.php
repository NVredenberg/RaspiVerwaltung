<?php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    $bauteile = $db->fetchAll("SELECT ID, Bauteilname, SOLL_Menge, IST_Menge, Lagerort, Beschreibung FROM bauteil_tabelle");
} catch (Exception $e) {
    $error = "Ein Fehler ist aufgetreten: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bauteil Verwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --card-bg: #fff;
            --card-border: rgba(0, 0, 0, 0.125);
            --table-header-bg: #f8f9fa;
            --text-color: inherit;
        }

        [data-bs-theme="dark"] {
            --card-bg: #212529;
            --card-border: rgba(255, 255, 255, 0.125);
            --table-header-bg: #2c3034;
            --text-color: #e9ecef;
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
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>
    <div class="container mt-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>Bauteil Übersicht
                    </h2>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Neues Bauteil
                </button>
            </div>
            <div class="card-body">
                <div class="search-box position-relative">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Bauteile suchen...">
                </div>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Bauteilname</th>
                                    <th scope="col">SOLL-Menge</th>
                                    <th scope="col">IST-Menge</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Lagerort</th>
                                    <th scope="col">Beschreibung</th>
                                    <th scope="col">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody id="bauteil_tabelle">
                                <?php if (!empty($bauteile)): ?>
                                    <?php foreach ($bauteile as $row): ?>
                                        <tr>
                                            <th scope="row"><?php echo htmlspecialchars($row['ID']); ?></th>
                                            <td><?php echo htmlspecialchars($row['Bauteilname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['SOLL_Menge']); ?></td>
                                            <td><?php echo htmlspecialchars($row['IST_Menge']); ?></td>
                                            <td>
                                                <?php
                                                $diff = $row['SOLL_Menge'] - $row['IST_Menge'];
                                                if ($diff > 0) {
                                                    echo '<span class="status-badge warning"><i class="fas fa-exclamation-circle me-1"></i>Fehlend</span>';
                                                } elseif ($diff < 0) {
                                                    echo '<span class="status-badge "><i class="fas fa-plus-circle me-1"></i>Überzählig</span>';
                                                } else {
                                                    echo '<span class="status-badge success"><i class="fas fa-check-circle me-1"></i>Vollständig</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['Lagerort']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Beschreibung']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-warning btn-sm edit-btn" 
                                                            data-id="<?php echo $row['ID']; ?>" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm delete-btn" 
                                                            data-id="<?php echo $row['ID']; ?>">
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
                                                <p class="mb-0">Keine Bauteile gefunden</p>
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

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Neues Bauteil hinzufügen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <div class="mb-3">
                            <label for="Bauteilname" class="form-label">Bauteilname</label>
                            <input type="text" class="form-control" id="Bauteilname" name="Bauteilname" required>
                        </div>
                        <div class="mb-3">
                            <label for="SOLL_Menge" class="form-label">SOLL-Menge</label>
                            <input type="number" class="form-control" id="SOLL_Menge" name="SOLL_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="IST_Menge" class="form-label">IST-Menge</label>
                            <input type="number" class="form-control" id="IST_Menge" name="IST_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="Lagerort" class="form-label">Lagerort</label>
                            <input type="text" class="form-control" id="Lagerort" name="Lagerort" required>
                        </div>
                        <div class="mb-3">
                            <label for="Beschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="Beschreibung" name="Beschreibung" rows="3"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Abbrechen
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Speichern
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
                        <i class="fas fa-edit me-2"></i>Bauteil bearbeiten
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editID" name="ID">
                        <div class="mb-3">
                            <label for="editBauteilname" class="form-label">Bauteilname</label>
                            <input type="text" class="form-control" id="editBauteilname" name="Bauteilname" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSOLL_Menge" class="form-label">SOLL-Menge</label>
                            <input type="number" class="form-control" id="editSOLL_Menge" name="SOLL_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="editIST_Menge" class="form-label">IST-Menge</label>
                            <input type="number" class="form-control" id="editIST_Menge" name="IST_Menge" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLagerort" class="form-label">Lagerort</label>
                            <input type="text" class="form-control" id="editLagerort" name="Lagerort" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBeschreibung" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="editBeschreibung" name="Beschreibung" rows="3"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Abbrechen
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Speichern
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
            $('#searchInput').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $("#bauteil_tabelle tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // AJAX für das Hinzufügen von Bauteilen
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/add.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        location.reload();
                    }
                });
            });

            // AJAX für das Löschen von Bauteilen
            $('.delete-btn').on('click', function() {
                if (confirm('Möchten Sie dieses Bauteil wirklich löschen?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'ajax/delete.php',
                        data: { id: id },
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });

            // AJAX für das Bearbeiten von Bauteilen
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'GET',
                    url: 'ajax/get.php',
                    data: { id: id },
                    success: function(response) {
                        var bauteil = JSON.parse(response);
                        $('#editID').val(bauteil.ID);
                        $('#editBauteilname').val(bauteil.Bauteilname);
                        $('#editSOLL_Menge').val(bauteil.SOLL_Menge);
                        $('#editIST_Menge').val(bauteil.IST_Menge);
                        $('#editLagerort').val(bauteil.Lagerort);
                        $('#editBeschreibung').val(bauteil.Beschreibung);
                    }
                });
            });

            // AJAX für das Speichern der Bearbeitung
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/update.php',
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