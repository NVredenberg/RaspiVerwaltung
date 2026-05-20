<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/Database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (($argv[1] ?? '') === '' || ($argv[2] ?? '') === '') {
    fwrite(STDERR, "Usage: php util/create_user.php <username> <password>\n");
    exit(1);
}

$username = trim($argv[1]);
$password = $argv[2];

if (strlen($username) > 50 || strlen($password) < 10) {
    fwrite(STDERR, "Username max. 50 Zeichen, Passwort mindestens 10 Zeichen.\n");
    exit(1);
}

$db = Database::getInstance();
$db->insert('users', [
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
]);

echo "Benutzer wurde angelegt.\n";
