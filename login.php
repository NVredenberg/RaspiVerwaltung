<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/audit.php';

secure_session_start();
send_security_headers();

if (is_logged_in() && session_user_is_active()) {
    header('Location: index.php');
    exit;
}

$error = '';
$isInitialSetup = false;

try {
    $db = Database::getInstance();
    $userCount = $db->fetch('SELECT COUNT(*) AS count FROM users');
    $isInitialSetup = (int)($userCount['count'] ?? 0) === 0;
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Login ist gerade nicht möglich. Bitte später erneut versuchen.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        require_valid_csrf();

        $username = input_string($_POST, 'username', 50);
        $password = input_string($_POST, 'password', 255);

        if ($isInitialSetup) {
            $passwordConfirm = input_string($_POST, 'password_confirm', 255);

            if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
                throw new InvalidArgumentException('Benutzername: 3-50 Zeichen, erlaubt sind Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich.');
            }

            if (strlen($password) < 10) {
                throw new InvalidArgumentException('Das Admin-Passwort muss mindestens 10 Zeichen lang sein.');
            }

            if ($password !== $passwordConfirm) {
                throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.');
            }

            $db->beginTransaction();
            $userCount = $db->fetch('SELECT COUNT(*) AS count FROM users');
            if ((int)($userCount['count'] ?? 0) !== 0) {
                throw new InvalidArgumentException('Die Ersteinrichtung wurde bereits abgeschlossen.');
            }

            $adminId = (int)$db->insert('users', [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'approved_at' => date('Y-m-d H:i:s'),
            ]);
            $db->commit();

            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $adminId;
            $_SESSION['role'] = 'admin';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            try {
                audit_log($db, 'create_admin', 'user', $adminId, 'Admin-Konto angelegt');
            } catch (Exception $auditException) {
                error_log($auditException->getMessage());
            }

            header('Location: index.php');
            exit;
        }

        $user = $db->fetch(
            'SELECT id, username, password, role, status FROM users WHERE username = ?',
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = $user['status'] === 'pending'
                    ? 'Dieses Konto wartet noch auf Freigabe durch einen Admin.'
                    : 'Dieses Konto ist nicht freigegeben.';
            } else {
                session_regenerate_id(true);
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Benutzername oder Passwort ist falsch.';
        }
    } catch (InvalidArgumentException $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log($e->getMessage());
        $error = 'Login ist gerade nicht möglich. Bitte später erneut versuchen.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Inventarverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5 col-xl-4">
                <section class="content-panel p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-layer-group fa-2x text-primary mb-3"></i>
                        <h1 class="page-title"><?php echo $isInitialSetup ? 'Admin einrichten' : 'Anmelden'; ?></h1>
                        <p class="page-subtitle">
                            <?php echo $isInitialSetup ? 'Das erste Konto wird automatisch als Admin angelegt.' : 'Zugriff auf Inventar, Koffer und Ausleihen.'; ?>
                        </p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername</label>
                            <input type="text" class="form-control" id="username" name="username" autocomplete="username" maxlength="50" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="<?php echo $isInitialSetup ? 'new-password' : 'current-password'; ?>" required>
                            <?php if ($isInitialSetup): ?>
                                <div class="form-text">Mindestens 10 Zeichen.</div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isInitialSetup): ?>
                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">Passwort wiederholen</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i><?php echo $isInitialSetup ? 'Admin anlegen' : 'Einloggen'; ?>
                        </button>
                    </form>

                    <?php if (!$isInitialSetup): ?>
                        <div class="text-center mt-3">
                            <a href="register.php">Neues Konto registrieren</a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
