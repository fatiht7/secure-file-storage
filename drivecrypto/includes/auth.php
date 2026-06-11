<?php
$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $is_https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');

session_start();
require_once __DIR__ . '/i18n.php';

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function require_valid_csrf_token(): void
{
    $session_token = $_SESSION['csrf_token'] ?? '';
    $submitted_token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($session_token)
        || !is_string($submitted_token)
        || $session_token === ''
        || !hash_equals($session_token, $submitted_token)
    ) {
        http_response_code(403);
        exit(translate('invalid_csrf'));
    }
}

function require_auth(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}
