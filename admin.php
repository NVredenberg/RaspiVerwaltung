<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/audit.php';

secure_session_start();
send_security_headers();
require_admin();

$message = '';
$error = '';

function status_label(string $status): string
{
    return match ($status) {
        'active' => 'Freigegeben',
        'pending' => 'Wartet auf Freigabe',
        'rejected' => 'Gesperrt',
        default => $status,
    };
}

try {
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_valid_csrf();

        $action = input_string($_POST, 'action', 30);
        $userId = input_int($_POST, 'user_id', 1);

        if ($userId === current_user_id()) {
            throw new InvalidArgumentException('Das eigene Admin-Konto kann hier nicht geändert werden.');
        }

        $target = $db->fetch('SELECT id, username, status, role FROM users WHERE id = ?', [$userId]);
        if (!$target) {
            throw new InvalidArgumentException('Benutzerkonto nicht gefunden.');
        }

        if ($action === 'approve') {
            $db->update('users', [
                'status' => 'active',
                'approved_by' => current_user_id(),
                'approved_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$userId]);
            audit_log($db, 'approve_user', 'user', $userId, 'Konto freigegeben', ['username' => $target['username']]);
            $message = 'Konto wurde freigegeben.';
        } elseif ($action === 'reject') {
            $db->update('users', [
                'status' => 'rejected',
                'approved_by' => null,
                'approved_at' => null,
            ], 'id = ?', [$userId]);
            audit_log($db, 'reject_user', 'user', $userId, 'Konto gesperrt', ['username' => $target['username']]);
            $message = 'Konto wurde gesperrt.';
        } else {
            throw new InvalidArgumentException('Unbekannte Aktion.');
        }
    }

    $pendingUsers = $db->fetchAll("
        SELECT id, username, role, status, created_at
        FROM users
        WHERE status = 'pending'
        ORDER BY created_at ASC
    ");

    $users = $db->fetchAll("
        SELECT u.id, u.username, u.role, u.status, u.created_at, u.approved_at, approver.username AS approved_by_name
        FROM users u
        LEFT JOIN users approver ON u.approved_by = approver.id
        ORDER BY FIELD(u.status, 'pending', 'active', 'rejected'), u.username
    ");

    $auditEntries = $db->fetchAll("
        SELECT username, action, entity_type, entity_id, summary, created_at
        FROM audit_log
        ORDER BY created_at DESC
        LIMIT 25
    ");
} catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Admin-Daten konnten nicht geladen oder gespeichert werden.';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Inventarverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <div class="page-header">
            <div>
                <h1 class="page-title">Admin</h1>
                <p class="page-subtitle">Registrierungen freigeben und Änderungen nachvollziehen.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo e($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <section class="metric-grid">
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($pendingUsers ?? [])); ?></span>
                <span class="metric-label">Warten auf Freigabe</span>
            </div>
            <div class="metric-card">
                <span class="metric-value"><?php echo e(count($users ?? [])); ?></span>
                <span class="metric-label">Konten gesamt</span>
            </div>
        </section>

        <section class="content-panel mb-4">
            <div class="p-4 border-bottom">
                <h2 class="h4 mb-0"><i class="fas fa-user-clock me-2"></i>Offene Registrierungen</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th>Registriert</th>
                            <th class="text-end">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingUsers)): ?>
                            <?php foreach ($pendingUsers as $user): ?>
                                <tr>
                                    <td><strong><?php echo e($user['username']); ?></strong></td>
                                    <td><?php echo e(date('d.m.Y H:i', strtotime($user['created_at']))); ?></td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-success btn-sm" type="submit">
                                                <i class="fas fa-check me-1"></i>Freigeben
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-outline-danger btn-sm" type="submit">
                                                <i class="fas fa-ban me-1"></i>Sperren
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state py-4">
                                        <i class="fas fa-check-circle"></i>
                                        <p class="mb-0">Keine offenen Registrierungen.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="content-panel mb-4">
            <div class="p-4 border-bottom">
                <h2 class="h4 mb-0"><i class="fas fa-users-cog me-2"></i>Konten</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th>Rolle</th>
                            <th>Status</th>
                            <th>Freigabe</th>
                            <th class="text-end">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($users ?? []) as $user): ?>
                            <tr>
                                <td><strong><?php echo e($user['username']); ?></strong></td>
                                <td><?php echo e($user['role'] === 'admin' ? 'Admin' : 'Nutzer'); ?></td>
                                <td><?php echo e(status_label($user['status'])); ?></td>
                                <td>
                                    <?php if ($user['approved_at']): ?>
                                        <?php echo e(date('d.m.Y H:i', strtotime($user['approved_at']))); ?>
                                        <?php if ($user['approved_by_name']): ?>
                                            <div class="text-muted small">durch <?php echo e($user['approved_by_name']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ((int)$user['id'] !== current_user_id()): ?>
                                        <?php if ($user['status'] !== 'active'): ?>
                                            <form method="POST" class="d-inline">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button class="btn btn-success btn-sm" type="submit">Freigeben</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($user['status'] !== 'rejected'): ?>
                                            <form method="POST" class="d-inline">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Sperren</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Aktuelles Konto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="content-panel">
            <div class="p-4 border-bottom">
                <h2 class="h4 mb-0"><i class="fas fa-history me-2"></i>Letzte Änderungen</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Zeit</th>
                            <th>Nutzer</th>
                            <th>Bereich</th>
                            <th>Änderung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($auditEntries)): ?>
                            <?php foreach ($auditEntries as $entry): ?>
                                <tr>
                                    <td><?php echo e(date('d.m.Y H:i', strtotime($entry['created_at']))); ?></td>
                                    <td><?php echo e($entry['username']); ?></td>
                                    <td><?php echo e($entry['entity_type']); ?> #<?php echo e($entry['entity_id'] ?? '-'); ?></td>
                                    <td><?php echo e($entry['summary']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state py-4">
                                        <i class="fas fa-history"></i>
                                        <p class="mb-0">Noch keine protokollierten Änderungen.</p>
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
</body>
</html>
