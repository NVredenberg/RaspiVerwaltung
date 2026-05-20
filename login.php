<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/includes/view_helpers.php';
require_once __DIR__ . '/includes/Database.php';

secure_session_start();
send_security_headers();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_valid_csrf();

        $username = input_string($_POST, 'username', 50);
        $password = input_string($_POST, 'password', 255);
        $db = Database::getInstance();

        $user = $db->fetch(
            'SELECT id, username, password FROM users WHERE username = ?',
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: index.php');
            exit;
        }

        $error = 'Benutzername oder Passwort ist falsch.';
    } catch (InvalidArgumentException $e) {
        $error = 'Bitte Benutzername und Passwort eingeben.';
    } catch (Exception $e) {
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
                        <h1 class="page-title">Anmelden</h1>
                        <p class="page-subtitle">Zugriff auf Inventar, Koffer und Ausleihen.</p>
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
                            <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Einloggen
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
