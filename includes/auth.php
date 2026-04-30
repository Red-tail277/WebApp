<?php
require_once __DIR__ . '/../config/config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $stmt = db()->prepare('SELECT user_id, full_name, email, created_at FROM users WHERE user_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function redirect_if_logged_in(): void
{
    if (is_logged_in()) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}
