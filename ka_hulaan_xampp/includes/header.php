<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css" />
</head>
<body>
  <header class="site-header">
    <a class="brand" href="<?= BASE_URL ?>/dashboard.php">
      <span class="brand-mark">K</span>
      <span>
        <strong>Ka-Hulaan</strong>
        <small>Digital Learning Game</small>
      </span>
    </a>
    <nav class="top-nav">
      <?php if ($user): ?>
        <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/profile.php">Profile</a>
        <a href="<?= BASE_URL ?>/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Login</a>
        <a href="<?= BASE_URL ?>/signup.php">Sign Up</a>
      <?php endif; ?>
    </nav>
  </header>
  <main class="page-shell">
