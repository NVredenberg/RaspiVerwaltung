<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (($argv[1] ?? '') === '' || ($argv[2] ?? '') === '') {
    fwrite(STDERR, "Usage: php util/create_user.php <username> <password> [admin|user]\n");
    exit(1);
}

$username = trim($argv[1]);
$password = $argv[2];
$role = ($argv[3] ?? 'user') === 'admin' ? 'admin' : 'user';

if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username) || strlen($password) < 10) {
    fwrite(STDERR, "Username: 3-50 Zeichen [A-Za-z0-9._-], Passwort mindestens 10 Zeichen.\n");
    exit(1);
}

$db = Database::getInstance();
$db->insert('users', [
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'role' => $role,
    'status' => 'active',
    'approved_at' => date('Y-m-d H:i:s'),
]);

echo "Benutzer wurde aktiv angelegt.\n";
