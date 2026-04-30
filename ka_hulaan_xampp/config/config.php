<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Ka-Hulaan');
define('BASE_URL', '/ka_hulaan_xampp');

define('DB_HOST', 'localhost');
define('DB_NAME', 'ka_hulaan_game');
define('DB_USER', 'root');
define('DB_PASS', '');

define('POINTS_PER_CORRECT', 10);

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function normalize_answer(string $value): string
{
    return strtoupper(preg_replace('/[^A-Z0-9]/', '', $value));
}
