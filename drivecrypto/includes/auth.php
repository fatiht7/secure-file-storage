<?php
session_start();

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
        exit('Requête refusée : jeton de sécurité invalide.');
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
