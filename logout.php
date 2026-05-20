<?php
require_once __DIR__ . '/includes/auth.php';

secure_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_valid_csrf();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
}

session_destroy();
header('Location: login.php');
exit;
