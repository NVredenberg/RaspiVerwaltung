<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';

secure_session_start();
send_security_headers();

if (is_logged_in() && session_user_is_active()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

try {
    $db = Database::getInstance();
    $userCount = $db->fetch('SELECT COUNT(*) AS count FROM users');
    if ((int)($userCount['count'] ?? 0) === 0) {
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Registrierung ist gerade nicht möglich. Bitte später erneut versuchen.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        require_valid_csrf();

        $username = input_string($_POST, 'username', 50);
        $password = input_string($_POST, 'password', 255);
        $passwordConfirm = input_string($_POST, 'password_confirm', 255);

        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            throw new InvalidArgumentException('Benutzername: 3-50 Zeichen, erlaubt sind Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich.');
        }

        if (strlen($password) < 10) {
            throw new InvalidArgumentException('Das Passwort muss mindestens 10 Zeichen lang sein.');
        }

        if ($password !== $passwordConfirm) {
            throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.');
        }

        $existing = $db->fetch('SELECT id FROM users WHERE username = ?', [$username]);
        if ($existing) {
            throw new InvalidArgumentException('Dieser Benutzername ist bereits vergeben.');
        }

        $db->insert('users', [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'pending',
        ]);

        $success = 'Registrierung gespeichert. Ein Admin muss das Konto jetzt freigeben.';
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = 'Registrierung konnte nicht gespeichert werden.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrierung | Inventarverwaltung</title>
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
                        <i class="fas fa-user-plus fa-2x text-primary mb-3"></i>
                        <h1 class="page-title">Registrieren</h1>
                        <p class="page-subtitle">Neue Konten werden vor der Nutzung manuell freigegeben.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo e($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername</label>
                            <input type="text" class="form-control" id="username" name="username" autocomplete="username" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
                            <div class="form-text">Mindestens 10 Zeichen.</div>
                        </div>
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Passwort wiederholen</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Registrierung senden
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php">Zur Anmeldung</a>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
