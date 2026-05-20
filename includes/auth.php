<?php
declare(strict_types=1);

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

function is_logged_in(): bool
{
    secure_session_start();
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function current_username(): string
{
    secure_session_start();
    return (string)($_SESSION['username'] ?? 'unbekannt');
}

function json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_login(bool $json = false): void
{
    if (is_logged_in()) {
        return;
    }

    if ($json) {
        json_response(['success' => false, 'error' => 'Nicht angemeldet.'], 401);
    }

    header('Location: login.php');
    exit;
}

function csrf_token(): string
{
    secure_session_start();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
}

function request_csrf_token(): string
{
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (is_string($headerToken) && $headerToken !== '') {
        return $headerToken;
    }

    $postToken = $_POST['_csrf'] ?? '';
    return is_string($postToken) ? $postToken : '';
}

function require_valid_csrf(bool $json = false): void
{
    secure_session_start();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = request_csrf_token();

    if (is_string($sessionToken) && $sessionToken !== '' && $requestToken !== '' && hash_equals($sessionToken, $requestToken)) {
        return;
    }

    if ($json) {
        json_response(['success' => false, 'error' => 'Die Sicherheitsprüfung ist abgelaufen. Bitte Seite neu laden.'], 419);
    }

    http_response_code(419);
    exit('Die Sicherheitsprüfung ist abgelaufen. Bitte Seite neu laden.');
}

function input_string(array $source, string $key, int $maxLength = 255, bool $required = true): string
{
    $value = trim((string)($source[$key] ?? ''));

    if ($required && $value === '') {
        throw new InvalidArgumentException('Pflichtfeld fehlt: ' . $key);
    }

    if (strlen($value) > $maxLength) {
        throw new InvalidArgumentException('Eingabe ist zu lang: ' . $key);
    }

    return $value;
}

function input_int(array $source, string $key, int $min = 0): int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT);

    if ($value === false || $value < $min) {
        throw new InvalidArgumentException('Ungültige Zahl: ' . $key);
    }

    return $value;
}

function input_int_array(array $source, string $key, int $min = 1): array
{
    $values = $source[$key] ?? null;
    if (!is_array($values) || $values === []) {
        throw new InvalidArgumentException('Auswahl fehlt: ' . $key);
    }

    $ids = [];
    foreach ($values as $value) {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id < $min) {
            throw new InvalidArgumentException('Ungültige Auswahl: ' . $key);
        }
        $ids[] = $id;
    }

    return array_values(array_unique($ids));
}
